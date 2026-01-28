<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', '0');
ob_start();

$vendorPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($vendorPath)) {
    $vendorPath = __DIR__ . '/vendor/autoload.php';
}
if (!file_exists($vendorPath)) {
    die(json_encode(['success' => false, 'message' => 'No se encuentra vendor/autoload.php']));
}

require_once('../config/database.php');
require_once('../model/MLoadDataResumenes.php');
require_once($vendorPath);

use PhpOffice\PhpSpreadsheet\IOFactory;

class CLoadDataResumenes
{
    private $db;
    private $model;
    private $database;

    public function __construct()
    {
        $this->database = new Database();
        $this->db = $this->database->getConnection();
        $this->model = new MLoadDataResumenes($this->db);
    }

    public function handleRequest()
    {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_POST['action'])) {
            echo json_encode(['success' => false, 'message' => 'Acción no especificada']);
            return;
        }

        switch ($_POST['action']) {
            case 'upload':
                $this->uploadFile();
                break;
            case 'process':
                $this->processData();
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        }
    }

    private function uploadFile()
    {
        $tempFilePath = null;
        $csvFilePath = null;

        try {
            if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Error al recibir el archivo');
            }

            $file = $_FILES['archivo'];
            $idEstacion = $_POST['id_estacion'] ?? '';

            if (empty($idEstacion)) {
                throw new Exception('Debe seleccionar una estación');
            }

            if (!preg_match('/\.xlsx$/i', $file['name'])) {
                throw new Exception('El archivo debe tener extensión .xlsx');
            }

            $estacionInfo = $this->model->obtenerEstacionPorId($idEstacion);
            if (!$estacionInfo) {
                throw new Exception('Estación no encontrada');
            }

            $tempDir = '../controller/uploads/temp/';
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $tempFileName = 'temp_resumenes_' . time() . '_' . basename($file['name']);
            $tempFilePath = $tempDir . $tempFileName;

            if (!move_uploaded_file($file['tmp_name'], $tempFilePath)) {
                throw new Exception('Error al guardar archivo temporal');
            }

            $csvFileName = 'temp_resumenes_' . time() . '.csv';
            $csvFilePath = $tempDir . $csvFileName;
            
            $this->convertirXLSXaCSV($tempFilePath, $csvFilePath);

            $validacion = $this->validarArchivoCSV($csvFilePath);

            if (!$validacion['valid']) {
                throw new Exception($validacion['message']);
            }

            $duplicados = $this->model->verificarAniosDuplicados($idEstacion, $validacion['anios']);

            if (!empty($duplicados)) {
                throw new Exception("Años ya cargados: " . implode(', ', $duplicados));
            }

            $idCarga = $this->model->createCargaArchivo(1, $idEstacion, $file['name'], 'RESUMENES');
            $this->model->saveTempFilePath($idCarga, $csvFilePath);

            if (file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }

            ob_clean();
            echo json_encode([
                'success' => true,
                'message' => "Validado. Años: " . implode(', ', $validacion['anios']),
                'data' => [
                    'id_carga' => $idCarga,
                    'anios' => $validacion['anios']
                ]
            ]);

        } catch (Exception $e) {
            if ($tempFilePath && file_exists($tempFilePath)) unlink($tempFilePath);
            if ($csvFilePath && file_exists($csvFilePath)) unlink($csvFilePath);
            ob_clean();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function convertirXLSXaCSV($xlsxPath, $csvPath)
    {
        $spreadsheet = IOFactory::load($xlsxPath);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        $csvFile = fopen($csvPath, 'w');

        for ($row = 1; $row <= $highestRow; $row++) {
            $rowData = [];
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $cellAddress = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row;
                $valor = $sheet->getCell($cellAddress)->getValue();
                $rowData[] = $valor ?? '';
            }
            fputcsv($csvFile, $rowData, ';');
        }

        fclose($csvFile);
    }

    private function validarArchivoCSV($csvPath)
    {
        $file = fopen($csvPath, 'r');
        $aniosEncontrados = [];
        
        while (($linea = fgetcsv($file, 0, ';')) !== false) {
            foreach ($linea as $valor) {
                $valor = trim($valor);
                if (is_numeric($valor) && $valor >= 2000 && $valor <= 2099) {
                    $anio = intval($valor);
                    if (!in_array($anio, $aniosEncontrados)) {
                        $aniosEncontrados[] = $anio;
                    }
                }
            }
        }

        fclose($file);

        if (empty($aniosEncontrados)) {
            return ['valid' => false, 'message' => 'No se encontraron años'];
        }

        return ['valid' => true, 'anios' => $aniosEncontrados];
    }

    private function processData()
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        try {
            $idCarga = $_POST['id_carga'] ?? '';
            $cargaInfo = $this->model->getCargaArchivoInfo($idCarga);
            $csvFilePath = $this->model->getTempFilePath($idCarga);

            $this->model->updateCargaArchivoEstado($idCarga, 'PROCESANDO');

            $resultado = $this->procesarArchivoCSV($csvFilePath, $idCarga, $cargaInfo['id_estacion']);

            if ($resultado['success']) {
                $this->model->updateCargaArchivo($idCarga, $resultado['registros_insertados']);
                $this->model->updateCargaArchivoEstado($idCarga, 'COMPLETADO');

                if (file_exists($csvFilePath)) {
                    unlink($csvFilePath);
                }

                ob_clean();
                echo json_encode([
                    'success' => true,
                    'message' => "Completado: {$resultado['registros_insertados']} registros",
                    'data' => [
                        'registros_procesados' => $resultado['registros_insertados'],
                        'anios_procesados' => $resultado['anios_procesados']
                    ]
                ]);
            } else {
                throw new Exception($resultado['message']);
            }

        } catch (Exception $e) {
            if (isset($idCarga)) {
                $this->model->updateCargaArchivoEstado($idCarga, 'ERROR');
            }
            ob_clean();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function procesarArchivoCSV($csvPath, $idCarga, $idEstacion)
    {
        // 🔍 DETECTAR SEPARADOR AUTOMÁTICAMENTE
        $separador = $this->detectarSeparador($csvPath);
        error_log("🔍 Separador detectado: '$separador'");

        $file = fopen($csvPath, 'r');
        
        $todasLineas = [];
        while (($linea = fgetcsv($file, 0, $separador)) !== false) {
            // ✅ Filtrar líneas completamente vacías
            $tieneContenido = false;
            foreach ($linea as $celda) {
                if (trim($celda) !== '') {
                    $tieneContenido = true;
                    break;
                }
            }
            if ($tieneContenido) {
                $todasLineas[] = $linea;
            }
        }
        fclose($file);

        error_log("📊 Total líneas con contenido: " . count($todasLineas));

        $registrosInsertados = 0;
        $aniosProcesados = [];
        $lote = [];

        foreach ($todasLineas as $numFila => $linea) {
            $anio = null;
            $columnaAnio = null;

            // 🔍 BUSCAR AÑO EN TODA LA FILA (más flexible)
            foreach ($linea as $col => $valor) {
                $valor = trim($valor);
                if (is_numeric($valor) && $valor >= 2000 && $valor <= 2099) {
                    $anio = intval($valor);
                    $columnaAnio = $col;
                    error_log("🎯 AÑO $anio detectado en fila $numFila, columna $col");
                    break;
                }
            }

            if (!$anio) continue;

            if (!in_array($anio, $aniosProcesados)) {
                $aniosProcesados[] = $anio;
            }

            $bloques = $this->analizarBloques($linea, $columnaAnio);

            if (empty($bloques)) {
                error_log("⚠️ No se encontraron bloques en año $anio");
                continue;
            }

            foreach ($bloques as $tipoBloque => $infoBloque) {
                $meses = $infoBloque['meses'];

                if (empty($meses)) continue;

                $datosPorMes = [];
                
                foreach ($meses as $mes => $colMes) {
                    $datosPorMes[$mes] = [
                        'temperatura_aire' => null,
                        'humedad_relativa' => null,
                        'presion_atmosferica' => null,
                        'radiacion_global' => null,
                        'direccion_viento' => null,
                        'velocidad_viento' => null
                    ];
                }

                // Buscar variables en filas siguientes
                for ($filaVar = $numFila + 1; $filaVar < min($numFila + 15, count($todasLineas)); $filaVar++) {
                    $lineaVar = $todasLineas[$filaVar];
                    
                    // Si encontramos otro año, detener
                    $hayOtroAnio = false;
                    foreach ($lineaVar as $valor) {
                        $v = trim($valor);
                        if (is_numeric($v) && $v >= 2000 && $v <= 2099) {
                            $hayOtroAnio = true;
                            break;
                        }
                    }
                    if ($hayOtroAnio) break;

                    // Buscar variable en cualquier columna inicial
                    $variable = null;
                    foreach ($lineaVar as $idx => $texto) {
                        if ($idx > 5) break; // Solo primeras columnas
                        $variable = $this->matchVariable(trim($texto));
                        if ($variable) break;
                    }

                    if (!$variable) continue;

                    // Llenar valores para cada mes
                    foreach ($meses as $mes => $colMes) {
                        if (!isset($lineaVar[$colMes])) continue;
                        
                        $valor = $this->limpiarValor($lineaVar[$colMes]);
                        
                        if ($valor !== null) {
                            $datosPorMes[$mes][$variable] = $valor;
                        }
                    }
                }

                // Crear registros
                foreach ($datosPorMes as $mes => $datos) {
                    $tieneValores = false;
                    foreach ($datos as $valor) {
                        if ($valor !== null) {
                            $tieneValores = true;
                            break;
                        }
                    }

                    if ($tieneValores) {
                        $lote[] = [
                            'id_carga' => $idCarga,
                            'id_estacion' => $idEstacion,
                            'anio' => $anio,
                            'mes' => $mes,
                            'tipo_estadistica' => $tipoBloque,
                            'temperatura_aire' => $datos['temperatura_aire'],
                            'humedad_relativa' => $datos['humedad_relativa'],
                            'presion_atmosferica' => $datos['presion_atmosferica'],
                            'radiacion_global' => $datos['radiacion_global'],
                            'direccion_viento' => $datos['direccion_viento'],
                            'velocidad_viento' => $datos['velocidad_viento']
                        ];
                        $registrosInsertados++;

                        if (count($lote) >= 100) {
                            $this->model->insertResumenesBatch($lote);
                            $lote = [];
                        }
                    }
                }
            }
        }

        if (!empty($lote)) {
            $this->model->insertResumenesBatch($lote);
        }

        error_log("✅ TOTAL: $registrosInsertados registros insertados");

        return [
            'success' => true,
            'registros_insertados' => $registrosInsertados,
            'anios_procesados' => $aniosProcesados
        ];
    }

    /**
     * 🔍 DETECTA AUTOMÁTICAMENTE EL SEPARADOR (;  o ,)
     */
    private function detectarSeparador($csvPath)
    {
        $file = fopen($csvPath, 'r');
        $primerasLineas = [];
        
        for ($i = 0; $i < 10 && !feof($file); $i++) {
            $primerasLineas[] = fgets($file);
        }
        fclose($file);

        $contadores = [';' => 0, ',' => 0];
        
        foreach ($primerasLineas as $linea) {
            $contadores[';'] += substr_count($linea, ';');
            $contadores[','] += substr_count($linea, ',');
        }

        return ($contadores[';'] > $contadores[',']) ? ';' : ',';
    }

    private function analizarBloques($linea, $columnaAnio)
    {
        $bloques = [];
        $bloqueActual = null;
        $colInicioBloque = null;
        $mesesActuales = [];

        for ($col = $columnaAnio + 1; $col < count($linea); $col++) {
            $valor = strtoupper(trim($linea[$col] ?? ''));

            // Detectar bloques
            if (in_array($valor, ['MAX', 'MÁX', 'MAXIMO', 'MÁXIMO'])) {
                if ($bloqueActual && !empty($mesesActuales)) {
                    $bloques[$bloqueActual] = [
                        'col_inicio' => $colInicioBloque,
                        'meses' => $mesesActuales
                    ];
                }
                $bloqueActual = 'MAX';
                $colInicioBloque = $col;
                $mesesActuales = [];
                continue;
            } 
            elseif (in_array($valor, ['AVG', 'PROMEDIO', 'PROM', 'MEAN'])) {
                if ($bloqueActual && !empty($mesesActuales)) {
                    $bloques[$bloqueActual] = [
                        'col_inicio' => $colInicioBloque,
                        'meses' => $mesesActuales
                    ];
                }
                $bloqueActual = 'AVG';
                $colInicioBloque = $col;
                $mesesActuales = [];
                continue;
            }
            elseif (in_array($valor, ['MIN', 'MINIMO', 'MÍNIMO', 'MÍN'])) {
                if ($bloqueActual && !empty($mesesActuales)) {
                    $bloques[$bloqueActual] = [
                        'col_inicio' => $colInicioBloque,
                        'meses' => $mesesActuales
                    ];
                }
                $bloqueActual = 'MIN';
                $colInicioBloque = $col;
                $mesesActuales = [];
                continue;
            }

            // Detectar meses
            if ($bloqueActual) {
                if (is_numeric($valor) && $valor >= 1 && $valor <= 12) {
                    $mesesActuales[intval($valor)] = $col;
                }
                elseif (in_array($valor, ['T', 'TOTAL', 'TOT'])) {
                    $mesesActuales[13] = $col;
                }
            }
        }

        if ($bloqueActual && !empty($mesesActuales)) {
            $bloques[$bloqueActual] = [
                'col_inicio' => $colInicioBloque,
                'meses' => $mesesActuales
            ];
        }

        return $bloques;
    }

    private function limpiarValor($valor)
    {
        $valor = trim($valor);
        if ($valor === '' || $valor === null) return null;
        
        $valor = str_replace(',', '.', $valor);
        
        if (!is_numeric($valor)) return null;
        
        return round(floatval($valor), 4);
    }

    private function matchVariable($texto)
    {
        if (empty($texto)) return null;
        $texto = strtoupper(trim($texto));

        // Remover puntos al final
        $texto = rtrim($texto, '.');

        if (in_array($texto, ['T', 'TA', 'TEMP', 'TEMPERATURA'])) return 'temperatura_aire';
        if (in_array($texto, ['HR', 'HUMEDAD', 'HUM'])) return 'humedad_relativa';
        if (in_array($texto, ['PA', 'PRESION', 'PRES', 'P'])) return 'presion_atmosferica';
        if (in_array($texto, ['RG', 'GR', 'RADIACION', 'RAD'])) return 'radiacion_global';
        if (in_array($texto, ['DV', 'DIR', 'DIRECCION'])) return 'direccion_viento';
        if (in_array($texto, ['VV', 'VEL', 'VELOCIDAD'])) return 'velocidad_viento';

        return null;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new CLoadDataResumenes();
    $controller->handleRequest();
}
?>