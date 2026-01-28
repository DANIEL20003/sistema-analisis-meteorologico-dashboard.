<?php
error_reporting(E_ALL);
ini_set('display_errors', '0');

set_time_limit(600);
ini_set('max_execution_time', '600');
ini_set('memory_limit', '1024M');
ini_set('max_input_time', '600');

ob_start();

require_once('../config/database.php');
require_once('../model/MLoadDataL2.php');
require_once('DataCleanerL2.php');

class CLoadDataL2
{
    private $db;
    private $model;
    private $database;

    public function __construct()
    {
        $this->database = new Database();
        $this->db = $this->database->getConnection();
        $this->model = new MLoadDataL2($this->db);
    }

    // ======================================================================
    // GESTIÓN CENTRALIZADA DE RUTAS
    // ======================================================================

    private function getRutaBase()
    {
        return dirname(__DIR__) . '/uploads/';
    }

    private function getRutaCrudos($estacionNombre)
    {
        return $this->getRutaBase() . "$estacionNombre/Crudos/L2/";
    }

    private function getRutaLimpios($estacionNombre)
    {
        return $this->getRutaBase() . "$estacionNombre/Limpios/L2/";
    }

    private function getRutaTemp()
    {
        return __DIR__ . '/uploads/temp/';
    }

    private function getRutaLogs()
    {
        return __DIR__ . '/uploads/logs/';
    }

    /**
     * Sanitiza nombre para usar en rutas de archivo
     */
    private function sanitizeName($nombre)
    {
        return preg_replace('/[^a-zA-Z0-9._-]/', '_', $nombre);
    }

    /**
     * Crea carpeta con validación de permisos
     */
    private function crearCarpeta($ruta)
    {
        if (!is_dir($ruta)) {
            if (!mkdir($ruta, 0755, true)) {
                throw new Exception("No se pudo crear carpeta: $ruta");
            }
            $this->logToFile("✓ Carpeta creada: $ruta");
        }

        if (!is_writable($ruta)) {
            throw new Exception("Sin permisos de escritura en: $ruta");
        }

        return true;
    }

    public function handleRequest()
    {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        $this->cleanOldTempFiles();

        if (!isset($_POST['action'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Acción no especificada'
            ]);
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
                echo json_encode([
                    'success' => false,
                    'message' => 'Acción no válida'
                ]);
        }
    }

    private function uploadFile()
    {
        $tempFilePath = null;

        try {
            if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Error al recibir el archivo');
            }

            $file = $_FILES['archivo'];
            $idEstacion = $_POST['id_estacion'] ?? '';
            $idUsuario = $_POST['id_usuario'] ?? 1;

            if (empty($idEstacion)) {
                throw new Exception('Debe seleccionar una estación');
            }

            if (!preg_match('/\.csv$/i', $file['name'])) {
                throw new Exception('El archivo debe tener extensión .csv');
            }

            $estacionInfo = $this->model->obtenerEstacionPorId($idEstacion);
            if (!$estacionInfo) {
                throw new Exception('Estación no encontrada');
            }

            $tempDir = __DIR__ . '/uploads/temp/';
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $tempFileName = 'temp_' . time() . '_' . basename($file['name']);
            $tempFilePath = $tempDir . $tempFileName;

            if (!move_uploaded_file($file['tmp_name'], $tempFilePath)) {
                throw new Exception('Error al guardar archivo temporal');
            }

            // Crear registro de carga
            $idCarga = $this->model->createCargaArchivo($idUsuario, $idEstacion, $file['name'], 'L2');

            // Analizar CSV y contar líneas
            $totalRegistros = 0;
            if (($h = fopen($tempFilePath, 'r')) !== false) {
                while (($row = fgetcsv($h)) !== false) {
                    $totalRegistros++;
                }
                fclose($h);
            }

            $this->model->updateCargaArchivo($idCarga, $totalRegistros);
            $this->model->saveTempFilePath($idCarga, $tempFilePath);

            // ✅ VERIFICAR DUPLICADOS
            $advertenciaDuplicados = null;
            $rangoFechas = $this->model->extraerRangoFechasCSV($tempFilePath);

            if ($rangoFechas && isset($rangoFechas['fecha_inicio']) && isset($rangoFechas['fecha_fin'])) {
                $resultadoDuplicados = $this->model->verificarDatosDuplicados(
                    $idEstacion,
                    $rangoFechas['fecha_inicio'],
                    $rangoFechas['fecha_fin']
                );

                if ($resultadoDuplicados['tiene_duplicados']) {
                    $totalExistentes = $resultadoDuplicados['total_registros_existentes'];
                    $rangoExistente = $resultadoDuplicados['rango_existente'];

                    $advertenciaDuplicados = [
                        'tipo' => 'duplicados',
                        'total_registros_existentes' => $totalExistentes,
                        'primera_fecha_existente' => $rangoExistente['primera_fecha'] ?? null,
                        'ultima_fecha_existente' => $rangoExistente['ultima_fecha'] ?? null,
                        'fecha_inicio_nueva' => $rangoFechas['fecha_inicio'],
                        'fecha_fin_nueva' => $rangoFechas['fecha_fin'],
                        'mensaje' => "⚠️ ADVERTENCIA: Ya existen {$totalExistentes} registros en el rango de fechas de este archivo. Los datos duplicados serán ignorados automáticamente."
                    ];
                }
            }

            ob_clean();
            $response = [
                'success' => true,
                'message' => "Archivo analizado: {$totalRegistros} registros encontrados",
                'data' => [
                    'id_carga' => $idCarga,
                    'total_registros' => $totalRegistros,
                    'archivo_nombre' => $file['name']
                ]
            ];

            // Agregar advertencia si hay duplicados
            if ($advertenciaDuplicados) {
                $response['advertencia'] = $advertenciaDuplicados;
            }

            echo json_encode($response);

        } catch (Exception $e) {
            if ($tempFilePath && file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }
            ob_clean();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function processData()
    {
        set_time_limit(0);
        ini_set('max_execution_time', '0');
        ini_set('memory_limit', '2048M');

        $tempFilePath = null;
        $idCarga = null;

        try {
            $this->logToFile("═══════════════════════════════════════");
            $this->logToFile("INICIO PROCESAMIENTO L2");
            $this->logToFile("═══════════════════════════════════════");

            // PASO 1: Validar parámetros
            $idCarga = $_POST['id_carga'] ?? '';
            if (empty($idCarga)) {
                throw new Exception('ID de carga no proporcionado');
            }
            $this->logToFile("ID Carga: $idCarga");

            // PASO 2: Obtener información de carga y estación
            $cargaInfo = $this->model->getCargaArchivoInfo($idCarga);
            if (!$cargaInfo) {
                throw new Exception('Información de carga no encontrada');
            }

            $estacionInfo = $this->model->obtenerEstacionPorId($cargaInfo['id_estacion']);
            if (!$estacionInfo) {
                throw new Exception('Información de estación no encontrada');
            }

            $this->logToFile("Estación: " . $estacionInfo['nombre'] . " (ID: " . $estacionInfo['id_estacion'] . ")");
            $this->logToFile("Archivo original: " . $cargaInfo['nombre_archivo']);

            // PASO 3: Verificar archivo temporal
            $tempFilePath = $this->model->getTempFilePath($idCarga);
            if (!$tempFilePath || !file_exists($tempFilePath)) {
                throw new Exception('Archivo temporal no encontrado');
            }
            $this->logToFile("Archivo temporal: " . basename($tempFilePath));

            // PASO 4: Cambiar estado a PROCESANDO
            $this->model->updateCargaArchivoEstado($idCarga, 'PROCESANDO');
            $this->logToFile("Estado: PROCESANDO");

            // PASO 5: Mover procesamiento de archivo crudo para después de la limpieza
            // para asegurar que las fechas sean idénticas a las del archivo limpio
            $this->logToFile("───────────────────────────────────────");

            // PASO 6: Limpieza con DataCleanerL2 (PHP puro)
            $this->logToFile("───────────────────────────────────────");
            $this->logToFile("PASO 2/5: Limpiando datos con DataCleanerL2...");

            $cleaner = new DataCleanerL2();
            $resultado = $cleaner->processCSVFile($tempFilePath, $estacionInfo, $idCarga);

            if (!$resultado || !$resultado['success']) {
                throw new Exception('Error procesando archivo CSV');
            }

            $this->logToFile("✓ Procesado: {$resultado['registros_validos']} válidos, {$resultado['registros_rechazados']} rechazados");

            // PASO 7: Generar 2 CSVs (BD + Completo)
            $this->logToFile("───────────────────────────────────────");
            $this->logToFile("PASO 3/5: Generando archivos limpios...");

            $archivos = $cleaner->generarDosCSVs(
                $resultado['datos_l2'],
                $resultado['datos_completos'],
                $resultado['header_original'],
                $resultado['separador'],
                $estacionInfo,
                $resultado['fecha_inicio'],
                $resultado['fecha_fin']
            );

            // Guardar las fechas para usar en nombres de archivos
            $fechaInicio = $resultado['fecha_inicio'];
            $fechaFin = $resultado['fecha_fin'];

            // PASO 8: Mover archivos limpios a carpeta Limpios/L2/Estacion_X/
            $this->logToFile("───────────────────────────────────────");
            $this->logToFile("PASO 4/5: Guardando archivos finales...");

            // 8.1 Guardar archivo crudo con las mismas fechas que el limpio
            $this->logToFile("→ Guardando archivo crudo...");
            $rawFilePath = $this->saveRawFile($tempFilePath, $estacionInfo, $cargaInfo['nombre_archivo'], $fechaInicio, $fechaFin);

            // 8.2 Guardar archivos limpios
            $this->logToFile("→ Moviendo archivos limpios...");

            // Los archivos generados están en temp, moverlos a Limpios/
            $cleanFileBD = $this->saveCleanFile(
                $archivos['csv_limpio_bd'],
                $estacionInfo,
                $cargaInfo['nombre_archivo'],
                'BD',
                $fechaInicio,
                $fechaFin
            );

            $cleanFileCompleto = $this->saveCleanFile(
                $archivos['csv_completo_limpio'],
                $estacionInfo,
                $cargaInfo['nombre_archivo'],
                'COMPLETO',
                $fechaInicio,
                $fechaFin
            );

            // Borrar archivos temporales generados por cleaner
            if (file_exists($archivos['csv_limpio_bd'])) {
                unlink($archivos['csv_limpio_bd']);
            }
            if (file_exists($archivos['csv_completo_limpio'])) {
                unlink($archivos['csv_completo_limpio']);
            }

            // PASO 9: Insertar en BD solo si hay datos
            $this->logToFile("───────────────────────────────────────");
            $this->logToFile("PASO 5/5: Insertando datos en BD...");

            $datosL2 = $resultado['datos_l2'];
            $totalInsertados = 0;

            if (!empty($datosL2)) {
                $this->model->insertL2Data($idCarga, $datosL2);
                $totalInsertados = count($datosL2);
                $this->logToFile("✓ Insertados $totalInsertados registros en BD");
            } else {
                $this->logToFile("⚠ Sin datos válidos para insertar");
            }

            // PASO 10: Actualizar registro de carga
            $this->model->updateCargaArchivoFinalWithOriginalName(
                $idCarga,
                basename($cleanFileBD),
                $totalInsertados,
                $cargaInfo['nombre_archivo']
            );
            $this->model->updateCargaArchivoEstado($idCarga, 'COMPLETADO');

            // PASO 11: Limpiar archivo temporal original
            if (file_exists($tempFilePath)) {
                unlink($tempFilePath);
                $this->logToFile("✓ Archivo temporal eliminado");
            }

            $this->logToFile("═══════════════════════════════════════");
            $this->logToFile("PROCESAMIENTO COMPLETADO EXITOSAMENTE");
            $this->logToFile("═══════════════════════════════════════");

            // Respuesta al frontend
            ob_clean();
            echo json_encode([
                'success' => true,
                'message' => 'Procesamiento L2 completado exitosamente',
                'registros_insertados' => $totalInsertados,
                'registros_validos' => $resultado['registros_validos'],
                'registros_rechazados' => $resultado['registros_rechazados'],
                'archivos' => [
                    'crudo' => basename($rawFilePath),
                    'limpio_bd' => basename($cleanFileBD),
                    'limpio_completo' => basename($cleanFileCompleto)
                ],
                'rutas' => [
                    'crudo' => $rawFilePath,
                    'limpio_bd' => $cleanFileBD,
                    'limpio_completo' => $cleanFileCompleto
                ]
            ]);
            return;

        } catch (Exception $e) {
            $this->logToFile("═══════════════════════════════════════");
            $this->logToFile("✗✗✗ ERROR EN PROCESAMIENTO ✗✗✗");
            $this->logToFile("Mensaje: " . $e->getMessage());
            $this->logToFile("Archivo: " . $e->getFile() . ":" . $e->getLine());
            $this->logToFile("Trace: " . $e->getTraceAsString());
            $this->logToFile("═══════════════════════════════════════");

            // Actualizar estado a ERROR
            if (isset($idCarga) && !empty($idCarga)) {
                $this->model->updateCargaArchivoEstado($idCarga, 'ERROR');
            }

            // Limpiar archivo temporal si existe
            if ($tempFilePath && file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }

            ob_clean();
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'error_details' => [
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine()
                ]
            ]);
        }
    }

    /**
     * Guarda el archivo crudo (sin procesar) con validación completa
     * Retorna la ruta completa del archivo guardado
     */
    private function saveRawFile($tempFilePath, $estacionInfo, $originalFileName, $fechaInicio = null, $fechaFin = null)
    {
        try {
            $this->logToFile("→ Guardando archivo crudo...");

            // Validar archivo origen
            if (!file_exists($tempFilePath)) {
                throw new Exception("Archivo temporal no existe: $tempFilePath");
            }

            // Preparar destino
            $estacionNombre = $this->sanitizeName($estacionInfo['nombre']);
            $rutaCrudos = $this->getRutaCrudos($estacionNombre);

            // Crear/validar carpeta
            $this->crearCarpeta($rutaCrudos);

            // Generar nombre de archivo siguiendo patrón L0/L1: {ESTACION}_L2_{FECHA-INI}_{FECHA-FIN}_Crudos.csv
            if ($fechaInicio && $fechaFin) {
                // Formatear fechas como DD-MM-YYYY
                $fechaInicioFormato = date('d-m-Y', strtotime($fechaInicio));
                $fechaFinFormato = date('d-m-Y', strtotime($fechaFin));
                $nombreFinal = "{$estacionNombre}_L2_{$fechaInicioFormato}_{$fechaFinFormato}_Crudos.csv";
            } else {
                // Fallback: usar timestamp si no hay fechas
                $timestamp = date('YmdHis');
                $cleanFileName = $this->sanitizeName($originalFileName);
                $nombreFinal = "RAW_{$timestamp}_{$cleanFileName}";
            }

            // Si ya existe, agregar timestamp
            $rutaFinal = $rutaCrudos . $nombreFinal;
            if (file_exists($rutaFinal)) {
                $timestamp = time();
                $nombreFinal = str_replace('.csv', "_{$timestamp}.csv", $nombreFinal);
                $rutaFinal = $rutaCrudos . $nombreFinal;
            }

            // Copiar archivo
            if (!copy($tempFilePath, $rutaFinal)) {
                throw new Exception("Error al copiar archivo crudo a: $rutaFinal");
            }

            // Verificar que se copió correctamente
            if (!file_exists($rutaFinal)) {
                throw new Exception("Archivo crudo no se guardó correctamente");
            }

            $tamanio = filesize($rutaFinal);
            $this->logToFile("✓ Archivo crudo guardado: $nombreFinal ($tamanio bytes)");

            return $rutaFinal;

        } catch (Exception $e) {
            $this->logToFile("✗ Error guardando archivo crudo: " . $e->getMessage());
            throw new Exception("Error al guardar archivo crudo: " . $e->getMessage());
        }
    }

    /**
     * Guarda archivo limpio en carpeta Limpios (usado para archivos ya procesados)
     * Retorna la ruta completa del archivo guardado
     */
    private function saveCleanFile($sourceFilePath, $estacionInfo, $originalFileName, $sufijo = 'CLEAN', $fechaInicio = null, $fechaFin = null)
    {
        try {
            $this->logToFile("→ Guardando archivo limpio ($sufijo)...");

            // Validar archivo origen
            if (!file_exists($sourceFilePath)) {
                throw new Exception("Archivo origen no existe: $sourceFilePath");
            }

            // Preparar destino
            $estacionNombre = $this->sanitizeName($estacionInfo['nombre']);
            $rutaLimpios = $this->getRutaLimpios($estacionNombre);

            // Crear/validar carpeta
            $this->crearCarpeta($rutaLimpios);

            // Generar nombre siguiendo patrón L0/L1: {ESTACION}_L2_{FECHA-INI}_{FECHA-FIN}_Limpios_{TIPO}.csv
            if ($fechaInicio && $fechaFin) {
                // Formatear fechas como DD-MM-YYYY
                $fechaInicioFormato = date('d-m-Y', strtotime($fechaInicio));
                $fechaFinFormato = date('d-m-Y', strtotime($fechaFin));

                // Si es el archivo completo, usamos el nombre estándar terminando en _Limpios.csv
                if ($sufijo === 'COMPLETO') {
                    $nombreFinal = "{$estacionNombre}_L2_{$fechaInicioFormato}_{$fechaFinFormato}_Limpios.csv";
                } else {
                    // Para otros tipos (como BD), agregamos el sufijo: _Limpios_BD.csv
                    $nombreFinal = "{$estacionNombre}_L2_{$fechaInicioFormato}_{$fechaFinFormato}_Limpios_{$sufijo}.csv";
                }
            } else {
                // Fallback: usar el nombre del archivo tal cual viene (ya tiene timestamp)
                $nombreFinal = basename($sourceFilePath);
            }

            $rutaFinal = $rutaLimpios . $nombreFinal;

            // Si ya existe, agregar timestamp
            if (file_exists($rutaFinal)) {
                $timestamp = time();
                $nombreFinal = str_replace('.csv', "_{$timestamp}.csv", $nombreFinal);
                $rutaFinal = $rutaLimpios . $nombreFinal;
            }

            // Copiar archivo
            if (!copy($sourceFilePath, $rutaFinal)) {
                throw new Exception("Error al copiar archivo limpio a: $rutaFinal");
            }

            // Verificar que se copió correctamente
            if (!file_exists($rutaFinal)) {
                throw new Exception("Archivo limpio no se guardó correctamente");
            }

            $tamanio = filesize($rutaFinal);
            $this->logToFile("✓ Archivo limpio guardado: $nombreFinal ($tamanio bytes)");

            return $rutaFinal;

        } catch (Exception $e) {
            $this->logToFile("✗ Error guardando archivo limpio: " . $e->getMessage());
            throw new Exception("Error al guardar archivo limpio: " . $e->getMessage());
        }
    }

    private function logToFile($message)
    {
        try {
            $logDir = $this->getRutaLogs();
            $this->crearCarpeta($logDir);

            $file = $logDir . 'carga_l2.log';
            $date = date('Y-m-d H:i:s');
            file_put_contents($file, "[$date] " . $message . "\n", FILE_APPEND | LOCK_EX);
        } catch (Exception $e) {
            error_log('No se pudo escribir log carga_l2: ' . $e->getMessage());
        }
    }

    private function processCSVFileFallback($filePath, $cargaInfo, $idCarga)
    {
        // Implementación mínima: cuenta líneas y retorna éxito
        try {
            $count = 0;
            if (($h = fopen($filePath, 'r')) !== false) {
                while (($line = fgets($h)) !== false) {
                    $line = trim($line);
                    if ($line === '')
                        continue;
                    // saltar encabezados sencillos
                    $low = strtolower($line);
                    if (strpos($low, 'date') !== false && strpos($low, 'time') !== false)
                        continue;
                    $count++;
                }
                fclose($h);
            }

            return [
                'success' => true,
                'registros_validos' => $count,
                'registros_procesados' => $count,
                'registros_rechazados' => 0,
                'archivo_final' => basename($filePath),
                'advertencias' => []
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function cleanOldTempFiles()
    {
        try {
            $tempDir = '../controller/uploads/temp/';

            if (!is_dir($tempDir)) {
                return;
            }

            $files = glob($tempDir . 'temp_*');
            $now = time();
            foreach ($files as $file) {
                if (is_file($file) && ($now - filemtime($file) >= 3600)) {
                    unlink($file);
                }
            }
        } catch (Exception $e) {
            error_log('Error cleaning temp files L2: ' . $e->getMessage());
        }
    }
}

$controller = new CLoadDataL2();
$controller->handleRequest();
