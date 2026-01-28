<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', '0');

// 🔥 AGREGAR ESTAS LÍNEAS PARA ASEGURAR LOS LÍMITES
set_time_limit(600);
ini_set('max_execution_time', '600');
ini_set('memory_limit', '1024M');
ini_set('max_input_time', '600');

ob_start();

require_once('../config/database.php');
require_once('../model/MLoadDataL1.php');

class CLoadDataL1
{
    private $db;
    private $model;
    private $database;

    public function __construct()
    {
        $this->database = new Database();
        $this->db = $this->database->getConnection();
        $this->model = new MLoadDataL1($this->db);
    }

    public function handleRequest()
    {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        // ✅ LIMPIAR ARCHIVOS TEMPORALES ANTIGUOS AL INICIO
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
        $tempFilePath = null; // ✅ Variable para rastrear archivo temporal

        try {
            // Validar que se recibió el archivo
            if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Error al recibir el archivo');
            }

            $file = $_FILES['archivo'];
            $idEstacion = $_POST['id_estacion'] ?? '';
            $idUsuario = $_POST['id_usuario'] ?? 1;

            // Validaciones
            if (empty($idEstacion)) {
                throw new Exception('Debe seleccionar una estación');
            }

            // Validar que el archivo es CSV
            if (!preg_match('/\.csv$/i', $file['name'])) {
                throw new Exception('El archivo debe tener extensión .csv');
            }

            // Obtener información de la estación
            $estacionInfo = $this->model->obtenerEstacionPorId($idEstacion);
            if (!$estacionInfo) {
                throw new Exception('Estación no encontrada');
            }

            // Crear directorio temporal si no existe
            $tempDir = '../controller/uploads/temp/';
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // Generar nombre único para archivo temporal
            $tempFileName = 'temp_' . time() . '_' . basename($file['name']);
            $tempFilePath = $tempDir . $tempFileName;

            if (!move_uploaded_file($file['tmp_name'], $tempFilePath)) {
                throw new Exception('Error al guardar archivo temporal');
            }

            // ✅ VALIDAR ENCABEZADOS DEL ARCHIVO
            $headerValidation = $this->validateCSVHeaders($tempFilePath);
            if (!$headerValidation['valid']) {
                throw new Exception($headerValidation['message']);
            }
            $dataQualityValidation = $this->validateDataQuality($tempFilePath);
            if (!$dataQualityValidation['valid']) {
                throw new Exception($dataQualityValidation['message']);
            }


            // ✅ Validar duplicados de archivo
            if ($this->model->checkDuplicateFile($idEstacion, $file['name'])) {
                throw new Exception('El archivo ya existe para esta estación. No se permiten duplicados.');
            }

            // ✅ Validar duplicados de rango de fechas
            $dateValidation = $this->model->validateDateRangeForStation($idEstacion, $tempFilePath);
            if (!$dateValidation['valid']) {
                throw new Exception('Rango de fechas duplicado: ' . $dateValidation['reason']);
            }

            // Crear registro en cargas_archivos
            $idCarga = $this->model->createCargaArchivo(
                $idUsuario,
                $idEstacion,
                $file['name'],
                'L1'
            );

            // Analizar archivo
            $totalRegistros = $this->analyzeCSVFile($tempFilePath);

            // Actualizar total de registros
            $this->model->updateCargaArchivo($idCarga, $totalRegistros);

            // Guardar ruta temporal
            $this->model->saveTempFilePath($idCarga, $tempFilePath);

            $response = [
                'success' => true,
                'message' => "Archivo analizado: {$totalRegistros} registros encontrados",
                'data' => [
                    'id_carga' => $idCarga,
                    'total_registros' => $totalRegistros,
                    'archivo_nombre' => $file['name']
                ]
            ];



            // ✅ LIMPIAR BUFFER ANTES DE ENVIAR
            ob_clean();
            echo json_encode($response);

        } catch (Exception $e) {
            // ✅ ELIMINAR ARCHIVO TEMPORAL EN CASO DE ERROR
            if ($tempFilePath && file_exists($tempFilePath)) {
                unlink($tempFilePath);
                error_log("✗ Archivo temporal eliminado por error: $tempFilePath");
            }

            ob_clean();
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }


    private function validateCSVHeaders($filePath)
    {
        try {
            if (!file_exists($filePath)) {
                return ['valid' => false, 'message' => 'Archivo no encontrado'];
            }

            $fileContent = file_get_contents($filePath);
            $lines = explode("\n", $fileContent);

            if (count($lines) < 2) {
                return ['valid' => false, 'message' => 'El archivo debe tener al menos 2 líneas'];
            }

            error_log("=== VALIDACIÓN DE ENCABEZADOS L1 ===");

            // ✅ Palabras clave para variables meteorológicas L1
            $palabrasClave = [
                'temperatura' => ['temperatura', 'temp', 'temperature', 'tair', 'temperatura_aire', 'temp_aire', 'ta', 'airtemp'],
                'humedad' => ['humedad', 'humidity', 'rh', 'humedad_relativa', 'hr', 'hum', 'relativehumidity'],
                'presion' => ['presion', 'pressure', 'p', 'presion_barometrica', 'pb', 'press', 'baro', 'barometric'],
                'radiacion' => ['radiacion', 'radiation', 'rad', 'radiacion_solar', 'rs', 'solar', 'solarradiation'],
                'velocidad_viento' => ['velocidad', 'speed', 'spd', 'spdavg', 'windspeed', 'vel_viento', 'wind_speed', 'vel', 'wind']
            ];

            $variablesDetectadas = [];

            // ✅ BUSCAR EN TODAS LAS PRIMERAS 5 LÍNEAS (no solo línea 2)
            for ($i = 0; $i < min(5, count($lines)); $i++) {
                $headerLine = strtolower(trim($lines[$i]));

                if (empty($headerLine))
                    continue;

                error_log("Analizando línea " . ($i + 1) . ": $headerLine");

                // Buscar cada variable meteorológica
                foreach ($palabrasClave as $variable => $palabras) {
                    // Si ya detectamos esta variable, saltar
                    if (in_array($variable, $variablesDetectadas)) {
                        continue;
                    }

                    foreach ($palabras as $palabra) {
                        if (strpos($headerLine, $palabra) !== false) {
                            $variablesDetectadas[] = $variable;
                            error_log("✓ Encontrada variable '$variable': $palabra en línea " . ($i + 1));
                            break;
                        }
                    }
                }
            }

            // ✅ CRITERIO FLEXIBLE: Al menos 3 variables meteorológicas
            $totalDetectadas = count(array_unique($variablesDetectadas));

            error_log("📊 Total variables detectadas: $totalDetectadas");
            error_log("Variables: " . implode(', ', array_unique($variablesDetectadas)));

            if ($totalDetectadas < 3) {
                // ✅ MENSAJE MÁS DETALLADO
                $mensaje = "El archivo debe contener al menos 3 variables meteorológicas. ";
                $mensaje .= "Detectadas: $totalDetectadas (" . implode(', ', array_unique($variablesDetectadas)) . "). ";
                $mensaje .= "Esperadas: temperatura, humedad, presión, radiación solar, velocidad del viento.";

                error_log("❌ Validación fallida: $mensaje");

                return ['valid' => false, 'message' => $mensaje];
            }

            error_log("✓ Variables detectadas: " . implode(', ', array_unique($variablesDetectadas)));
            error_log("✓ Encabezados L1 válidos");

            return ['valid' => true, 'message' => 'Encabezados válidos'];

        } catch (Exception $e) {
            return ['valid' => false, 'message' => 'Error al validar encabezados: ' . $e->getMessage()];
        }
    }

    private function validateDataQuality($filePath)
    {
        try {
            if (!file_exists($filePath)) {
                return ['valid' => false, 'message' => 'Archivo no encontrado'];
            }

            $fileContent = file_get_contents($filePath);
            $lines = explode("\n", $fileContent);

            // Detectar separador
            $separador = null;
            foreach ($lines as $line) {
                if (empty(trim($line)))
                    continue;
                $separador = (substr_count($line, ';') > substr_count($line, ',')) ? ';' : ',';
                break;
            }

            if (!$separador) {
                return ['valid' => false, 'message' => 'No se pudo detectar el formato del archivo'];
            }

            $totalLineasDatos = 0;
            $lineasConDatosValidos = 0;

            error_log("=== VALIDACIÓN DE CALIDAD DE DATOS L1 ===");

            foreach ($lines as $index => $line) {
                $line = trim($line);
                if (empty($line))
                    continue;

                $lineLower = strtolower($line);

                // Saltar encabezados
                if (
                    strpos($lineLower, 'genwind') !== false ||
                    strpos($lineLower, 'time') !== false ||
                    strpos($lineLower, 'date') !== false ||
                    strpos($lineLower, 'status') !== false ||
                    strpos($lineLower, 'temperature') !== false ||
                    strpos($lineLower, 'humidity') !== false
                ) {
                    continue;
                }

                $campos = array_map('trim', explode($separador, $line));

                if (count($campos) < 6)
                    continue;

                $totalLineasDatos++;

                // Buscar valores numéricos válidos para variables L1
                $tieneValorValido = false;

                foreach ($campos as $valor) {
                    if (is_numeric($valor)) {
                        $num = floatval($valor);

                        // Validar rangos meteorológicos típicos
                        // Temperatura: -50 a 60°C
                        if ($num >= -50 && $num <= 60) {
                            $tieneValorValido = true;
                            break;
                        }
                        // Humedad: 0 a 100%
                        if ($num >= 0 && $num <= 100) {
                            $tieneValorValido = true;
                            break;
                        }
                        // Presión barométrica: 900 a 1200 hPa
                        if ($num >= 700 && $num <= 1200) {
                            $tieneValorValido = true;
                            break;
                        }
                        // Radiación solar: 0 a 1500 W/m²
                        if ($num >= 0 && $num <= 1500) {
                            $tieneValorValido = true;
                            break;
                        }
                        // Velocidad del viento: 0 a 50 m/s
                        if ($num >= 0 && $num <= 50) {
                            $tieneValorValido = true;
                            break;
                        }
                    }
                }

                if ($tieneValorValido) {
                    $lineasConDatosValidos++;
                }
            }

            error_log("📊 RESULTADO VALIDACIÓN L1:");
            error_log("  Total líneas de datos: $totalLineasDatos");
            error_log("  Líneas con datos válidos: $lineasConDatosValidos");

            // Calcular porcentaje de datos válidos
            $porcentajeValidos = $totalLineasDatos > 0
                ? ($lineasConDatosValidos / $totalLineasDatos) * 100
                : 0;

            error_log("  Porcentaje de datos válidos: " . round($porcentajeValidos, 2) . "%");

            // ✅ CRITERIO: Si NO hay datos válidos, rechazar
            if ($lineasConDatosValidos == 0) {
                return [
                    'valid' => false,
                    'message' => 'El archivo NO contiene datos válidos. Todos los registros tienen valores inválidos o fuera de rango.'
                ];
            }

            // ✅ CRITERIO: Si hay menos del 5% de datos válidos, rechazar
            if ($porcentajeValidos < 5) {
                return [
                    'valid' => false,
                    'message' => "El archivo tiene muy pocos datos válidos (" . round($porcentajeValidos, 1) . "%). Se requiere al menos 5% de datos válidos."
                ];
            }

            // ✅ TODO BIEN - Sin advertencias
            return [
                'valid' => true,
                'message' => 'Archivo con datos válidos'
            ];

        } catch (Exception $e) {
            error_log("❌ Error en validación de calidad: " . $e->getMessage());
            return ['valid' => false, 'message' => 'Error al validar calidad de datos: ' . $e->getMessage()];
        }
    }


    private function processData()
    {
        set_time_limit(0);  // Sin límite de tiempo
        ini_set('max_execution_time', '0');
        ini_set('memory_limit', '2048M');
        $tempFilePath = null;

        try {
            $idCarga = $_POST['id_carga'] ?? '';
            $batchSize = $_POST['batch_size'] ?? 1000;

            if (empty($idCarga)) {
                throw new Exception('ID de carga no proporcionado');
            }

            // Obtener información de la carga
            $cargaInfo = $this->model->getCargaArchivoInfo($idCarga);
            if (!$cargaInfo) {
                throw new Exception('Información de carga no encontrada');
            }

            // Obtener ruta del archivo temporal
            $tempFilePath = $this->model->getTempFilePath($idCarga);
            if (!$tempFilePath || !file_exists($tempFilePath)) {
                throw new Exception('Archivo temporal no encontrado');
            }

            // Marcar como procesando
            $this->model->updateCargaArchivoEstado($idCarga, 'PROCESANDO');

            // Obtener información de la estación
            $estacionInfo = $this->model->obtenerEstacionPorId($cargaInfo['id_estacion']);

            // Procesar archivo CSV
            $resultado = $this->processCSVFile($tempFilePath, $estacionInfo, $idCarga);

            if ($resultado['success']) {
                $cleanFilePath = $this->generateCleanFile($tempFilePath, $estacionInfo, $resultado['fecha_inicio'], $resultado['fecha_fin']);
                // Renombrar y mover archivo
                $finalPath = $this->renameAndSaveFile($tempFilePath, $estacionInfo, $resultado['fecha_inicio'], $resultado['fecha_fin']);

                // Actualizar información final
                $this->model->updateCargaArchivoFinalWithOriginalName($idCarga, basename($finalPath), $resultado['registros_validos'], basename($finalPath));
                $this->model->updateCargaArchivoEstado($idCarga, 'COMPLETADO');

                // ✅ ELIMINAR ARCHIVO TEMPORAL DESPUÉS DE ÉXITO
                if (file_exists($tempFilePath)) {
                    unlink($tempFilePath);
                    error_log("✓ Archivo temporal eliminado exitosamente: $tempFilePath");
                }

                // ✅✅✅ NUEVO: REFRESCAR VISTAS MATERIALIZADAS ✅✅✅
                try {
                    error_log("🔄 Refrescando vistas materializadas después de carga L1...");

                    $refresh_result = pg_query($this->db, "SELECT refrescar_vistas_materializadas()");

                    if ($refresh_result) {
                        error_log("✅ Vistas materializadas refrescadas exitosamente");
                    } else {
                        error_log("⚠️ Error al refrescar vistas: " . pg_last_error($this->db));
                    }

                } catch (Exception $e) {
                    // No detener el proceso si falla el refresco
                    error_log("❌ Excepción al refrescar vistas: " . $e->getMessage());
                }

                // ✅ CONSTRUIR MENSAJE CON ADVERTENCIAS
                $mensaje = "Procesamiento completado: {$resultado['registros_validos']} registros cargados";

                if (!empty($resultado['advertencias'])) {
                    $mensaje .= "\n\n⚠️ ADVERTENCIAS:\n" . implode("\n", $resultado['advertencias']);
                }
                ob_clean();
                echo json_encode([
                    'success' => true,
                    'message' => $mensaje,
                    'data' => [
                        'registros_procesados' => $resultado['registros_procesados'],
                        'registros_validos' => $resultado['registros_validos'],
                        'registros_rechazados' => $resultado['registros_rechazados'],
                        'archivo_final' => basename($finalPath),
                        'advertencias' => $resultado['advertencias'] ?? []
                    ]
                ]);
            } else {
                throw new Exception($resultado['message']);
            }

        } catch (Exception $e) {
            // ✅ ACTUALIZAR ESTADO A ERROR
            if (isset($idCarga) && !empty($idCarga)) {
                $this->model->updateCargaArchivoEstado($idCarga, 'ERROR');
            }

            // ✅ ELIMINAR ARCHIVO TEMPORAL EN CASO DE ERROR
            if ($tempFilePath && file_exists($tempFilePath)) {
                unlink($tempFilePath);
                error_log("✗ Archivo temporal eliminado por error en procesamiento: $tempFilePath");
            }
            ob_clean();
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
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
            $deleted = 0;

            foreach ($files as $file) {
                if (is_file($file)) {
                    // Si el archivo tiene más de 1 hora (3600 segundos)
                    if ($now - filemtime($file) >= 3600) {
                        unlink($file);
                        $deleted++;
                        error_log("✓ Archivo temporal antiguo eliminado: " . basename($file));
                    }
                }
            }

            if ($deleted > 0) {
                error_log("✓ Total archivos temporales antiguos eliminados: $deleted");
            }

        } catch (Exception $e) {
            error_log("Error al limpiar archivos temporales: " . $e->getMessage());
        }
    }


    private function analyzeCSVFile($filePath)
    {
        try {
            if (!file_exists($filePath)) {
                throw new Exception('Archivo no encontrado');
            }

            $fileContent = file_get_contents($filePath);
            $lines = explode("\n", $fileContent);

            $lines = array_filter($lines, function ($line) {
                return trim($line) !== '';
            });

            $totalLineasDatos = 0;

            // ✅ CONTAR SOLO LÍNEAS DE DATOS (no encabezados)
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line))
                    continue;

                // ✅ Detectar si es encabezado
                $lineLower = strtolower($line);
                if (
                    strpos($lineLower, 'genwind') !== false ||
                    strpos($lineLower, 'time') !== false ||
                    strpos($lineLower, 'date') !== false ||
                    strpos($lineLower, 'status') !== false ||
                    strpos($lineLower, 'temperature') !== false ||
                    strpos($lineLower, 'humidity') !== false ||
                    strpos($lineLower, 'pressure') !== false ||
                    strpos($lineLower, 'radiation') !== false
                ) {
                    continue; // Es encabezado, no contar
                }

                // Es línea de datos
                $totalLineasDatos++;
            }

            error_log("📊 Total líneas de datos encontradas: $totalLineasDatos");
            return $totalLineasDatos;

        } catch (Exception $e) {
            return 0;
        }
    }

    private function detectarColumnas($filePath)
    {
        try {
            $fileSize = filesize($filePath);
            $handle = fopen($filePath, 'r');

            if (!$handle) {
                error_log("❌ No se pudo abrir archivo");
                return null;
            }

            error_log("=== DETECCIÓN INTELIGENTE L1 ===");
            error_log("Tamaño: " . number_format($fileSize) . " bytes");

            $separador = null;
            $lineasConDatos = [];
            $lineNumber = 0;
            $maxLineasBuscar = 100;

            // ✅ LEER PRIMERAS 100 LÍNEAS
            while (($line = fgets($handle)) !== false && $lineNumber < $maxLineasBuscar) {
                $lineNumber++;
                $line = trim($line);

                if (empty($line))
                    continue;

                $lineLower = strtolower($line);

                // Saltar encabezados
                if (
                    strpos($lineLower, 'genwind') !== false ||
                    (strpos($lineLower, 'time') !== false && strpos($lineLower, 'status') !== false) ||
                    (strpos($lineLower, 'date') !== false && strpos($lineLower, 'status') !== false) ||
                    strpos($lineLower, 'temperature') !== false ||
                    strpos($lineLower, 'humidity') !== false ||
                    strpos($lineLower, 'pressure') !== false ||
                    strpos($lineLower, 'radiation') !== false
                ) {
                    continue;
                }

                // Detectar separador
                if ($separador === null) {
                    $separador = (substr_count($line, ';') > substr_count($line, ',')) ? ';' : ',';
                    error_log("Separador detectado: '$separador'");
                }

                // ✅ Agregar TODAS las líneas de datos (sin filtro estricto)
                $campos = explode($separador, $line);
                if (count($campos) >= 8) { // L1 necesita más columnas
                    $lineasConDatos[] = $line;

                    if (count($lineasConDatos) >= 50) {
                        error_log("✓ 50 líneas recolectadas");
                        break;
                    }
                }
            }

            fclose($handle);

            if (count($lineasConDatos) < 3) {
                error_log("❌ Insuficientes líneas: " . count($lineasConDatos));
                return null;
            }

            error_log("✓ Total líneas para análisis: " . count($lineasConDatos));

            return $this->analizarLineasParaColumnas($lineasConDatos, $separador);

        } catch (Exception $e) {
            error_log("❌ Error: " . $e->getMessage());
            return null;
        }
    }


    private function analizarLineasParaColumnas($lineas, $separador)
    {
        if (empty($lineas)) {
            return null;
        }

        $primeraLinea = array_map('trim', explode($separador, $lineas[0]));
        $totalColumnas = count($primeraLinea);

        error_log("Analizando " . count($lineas) . " líneas con $totalColumnas columnas");

        // Puntuaciones para todas las variables
        $puntuacionFecha = array_fill(0, $totalColumnas, 0);
        $puntuacionHora = array_fill(0, $totalColumnas, 0);
        $puntuacionAmPm = array_fill(0, $totalColumnas, 0);
        $puntuacionTemperatura = array_fill(0, $totalColumnas, 0);
        $puntuacionHumedad = array_fill(0, $totalColumnas, 0);
        $puntuacionPresion = array_fill(0, $totalColumnas, 0);
        $puntuacionRadiacionGlobal = array_fill(0, $totalColumnas, 0);
        $puntuacionRadiacionDifusa = array_fill(0, $totalColumnas, 0);
        $puntuacionLluvia = array_fill(0, $totalColumnas, 0);
        $puntuacionVelocidadViento = array_fill(0, $totalColumnas, 0);
        $puntuacionDireccionViento = array_fill(0, $totalColumnas, 0);

        foreach ($lineas as $index => $linea) {
            $campos = array_map('trim', explode($separador, $linea));

            if ($index == 0) {
                error_log("Primera línea analizada:");
                for ($i = 0; $i < min(20, count($campos)); $i++) {
                    error_log("  Col[$i] = '{$campos[$i]}'");
                }
            }

            foreach ($campos as $colIndex => $valor) {
                if (empty($valor))
                    continue;

                // FECHA
                if (preg_match('/^\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4}$/', $valor)) {
                    $puntuacionFecha[$colIndex] += 5;
                }

                // HORA CON AM/PM INCLUIDO
                if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?\s*(AM|PM)$/i', $valor)) {
                    $puntuacionHora[$colIndex] += 10;
                    $puntuacionAmPm[$colIndex] += 10;
                }
                // HORA SIN AM/PM
                else if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $valor)) {
                    $puntuacionHora[$colIndex] += 3;
                }

                // AM/PM SEPARADO
                if (strtoupper($valor) == 'AM' || strtoupper($valor) == 'PM') {
                    $puntuacionAmPm[$colIndex] += 5;
                }

                // VALORES NUMÉRICOS
                if (is_numeric($valor)) {
                    $num = floatval($valor);
                    $partes = explode('.', $valor);
                    $decimales = isset($partes[1]) ? strlen($partes[1]) : 0;

                    // TEMPERATURA: -50 a 60°C
                    if ($num >= -50 && $num <= 60 && $decimales >= 1) {
                        $puntuacionTemperatura[$colIndex] += 5;
                    }

                    // HUMEDAD: 0 a 100%
                    if ($num >= 0 && $num <= 100 && $decimales >= 1) {
                        $puntuacionHumedad[$colIndex] += 5;
                    }

                    // PRESIÓN: 700 a 800 hPa
                    if ($num >= 700 && $num <= 800 && $decimales >= 2) {
                        $puntuacionPresion[$colIndex] += 7;
                    }

                    // RADIACIÓN GLOBAL: 0-1500 W/m²
                    if ($num >= 0 && $num <= 1500) {
                        if ($num >= 200 && $num <= 1200) {
                            $puntuacionRadiacionGlobal[$colIndex] += 10;
                        } else if ($num >= 50 && $num < 200) {
                            $puntuacionRadiacionGlobal[$colIndex] += 5;
                        } else if ($num == 0.0) {
                            $puntuacionRadiacionGlobal[$colIndex] += 2;
                        }
                    }

                    // RADIACIÓN DIFUSA: 0-800 W/m² (más baja que global)
                    if ($num >= 0 && $num <= 800) {
                        if ($num >= 30 && $num <= 400) {
                            $puntuacionRadiacionDifusa[$colIndex] += 8;
                        } else if ($num > 0 && $num < 30) {
                            $puntuacionRadiacionDifusa[$colIndex] += 4;
                        } else if ($num == 0.0) {
                            $puntuacionRadiacionDifusa[$colIndex] += 2;
                        }
                    }

                    // ✅ LLUVIA: 0-10 mm (muchos ceros, algunos valores bajos)
                    if ($num >= 0 && $num <= 10) {
                        if ($num == 0.0) {
                            $puntuacionLluvia[$colIndex] += 3; // Muchos ceros
                        } else if ($num > 0 && $num <= 10) {
                            $puntuacionLluvia[$colIndex] += 8; // Valores de lluvia
                        }
                    }

                    // ✅ VELOCIDAD VIENTO: 0-20 m/s
                    if ($num >= 0 && $num <= 20) {
                        if ($num == 0.0) {
                            $puntuacionVelocidadViento[$colIndex] += 2; // Calma
                        } else if ($num > 0 && $num <= 20) {
                            $puntuacionVelocidadViento[$colIndex] += 7; // Viento
                        }
                    }

                    // ✅ DIRECCIÓN VIENTO: 0-360 grados
                    if ($num >= 0 && $num <= 360 && $decimales <= 2) {
                        if ($num == 360.0 || $num == 0.0) {
                            $puntuacionDireccionViento[$colIndex] += 3; // Norte
                        } else if ($num > 0 && $num < 360) {
                            $puntuacionDireccionViento[$colIndex] += 8; // Otras direcciones
                        }
                    }
                }
            }
        }

        // DETERMINAR COLUMNAS
        $colFecha = array_search(max($puntuacionFecha), $puntuacionFecha);
        $colHora = array_search(max($puntuacionHora), $puntuacionHora);

        $horaIncluyeAmPm = ($puntuacionAmPm[$colHora] > 5);
        $colAmPm = null;

        if (!$horaIncluyeAmPm) {
            foreach ($puntuacionAmPm as $col => $puntaje) {
                if ($col != $colHora && $puntaje > 3) {
                    $colAmPm = $col;
                    break;
                }
            }
        }

        // Variables meteorológicas (excluir fecha, hora, AM/PM)
        $cols = [$colFecha, $colHora];
        if ($colAmPm !== null)
            $cols[] = $colAmPm;

        $colTemperatura = $this->buscarMejorColumna($puntuacionTemperatura, $cols);
        $colHumedad = $this->buscarMejorColumna($puntuacionHumedad, array_merge($cols, [$colTemperatura]));
        $colPresion = $this->buscarMejorColumna($puntuacionPresion, array_merge($cols, [$colTemperatura, $colHumedad]));

        // RADIACIÓN GLOBAL (prioridad)
        $colRadiacionGlobal = $this->buscarMejorColumna(
            $puntuacionRadiacionGlobal,
            array_merge($cols, [$colTemperatura, $colHumedad, $colPresion])
        );

        // RADIACIÓN DIFUSA (si existe y es diferente de global)
        $colRadiacionDifusa = $this->buscarMejorColumna(
            $puntuacionRadiacionDifusa,
            array_merge($cols, [$colTemperatura, $colHumedad, $colPresion, $colRadiacionGlobal])
        );

        // ✅ LLUVIA (solo si tiene puntuación significativa >= 20)
        $colLluvia = null;
        $maxLluvia = max($puntuacionLluvia);
        if ($maxLluvia >= 20) {
            $colLluvia = $this->buscarMejorColumna(
                $puntuacionLluvia,
                array_merge($cols, [$colTemperatura, $colHumedad, $colPresion, $colRadiacionGlobal, $colRadiacionDifusa])
            );
        }

        // ✅ VELOCIDAD VIENTO (solo si tiene puntuación >= 20)
        $colVelocidadViento = null;
        $maxVelViento = max($puntuacionVelocidadViento);
        if ($maxVelViento >= 20) {
            $colVelocidadViento = $this->buscarMejorColumna(
                $puntuacionVelocidadViento,
                array_merge($cols, [$colTemperatura, $colHumedad, $colPresion, $colRadiacionGlobal, $colRadiacionDifusa, $colLluvia])
            );
        }

        // ✅ DIRECCIÓN VIENTO (solo si tiene puntuación >= 20)
        $colDireccionViento = null;
        $maxDirViento = max($puntuacionDireccionViento);
        if ($maxDirViento >= 20) {
            $colDireccionViento = $this->buscarMejorColumna(
                $puntuacionDireccionViento,
                array_merge($cols, [$colTemperatura, $colHumedad, $colPresion, $colRadiacionGlobal, $colRadiacionDifusa, $colLluvia, $colVelocidadViento])
            );
        }

        // LOGS DETALLADOS
        error_log("📊 RESULTADO DETECCIÓN L1:");
        error_log("  Fecha: Col[$colFecha] = " . $puntuacionFecha[$colFecha] . " pts");
        error_log("  Hora: Col[$colHora] = " . $puntuacionHora[$colHora] . " pts");
        error_log("  Temperatura: Col[" . ($colTemperatura ?? 'null') . "] = " . ($colTemperatura !== null ? $puntuacionTemperatura[$colTemperatura] : 0) . " pts");
        error_log("  Humedad: Col[" . ($colHumedad ?? 'null') . "] = " . ($colHumedad !== null ? $puntuacionHumedad[$colHumedad] : 0) . " pts");
        error_log("  Presión: Col[" . ($colPresion ?? 'null') . "] = " . ($colPresion !== null ? $puntuacionPresion[$colPresion] : 0) . " pts");
        error_log("  Radiación Global: Col[" . ($colRadiacionGlobal ?? 'null') . "] = " . ($colRadiacionGlobal !== null ? $puntuacionRadiacionGlobal[$colRadiacionGlobal] : 0) . " pts");
        error_log("  Radiación Difusa: Col[" . ($colRadiacionDifusa ?? 'null') . "] = " . ($colRadiacionDifusa !== null ? $puntuacionRadiacionDifusa[$colRadiacionDifusa] : 0) . " pts");
        error_log("  Lluvia: Col[" . ($colLluvia ?? 'null') . "] = $maxLluvia pts");
        error_log("  Velocidad Viento: Col[" . ($colVelocidadViento ?? 'null') . "] = $maxVelViento pts");
        error_log("  Dirección Viento: Col[" . ($colDireccionViento ?? 'null') . "] = $maxDirViento pts");

        // VALIDAR DETECCIÓN MÍNIMA
        if ($colFecha === false || $colHora === false) {
            error_log("❌ No se detectaron columnas de fecha/hora");
            return null;
        }

        $variablesDetectadas = array_filter([$colTemperatura, $colHumedad, $colPresion, $colRadiacionGlobal]);
        if (count($variablesDetectadas) < 3) {
            error_log("❌ Se necesitan al menos 3 variables meteorológicas básicas. Detectadas: " . count($variablesDetectadas));
            return null;
        }

        error_log("✅ COLUMNAS L1 DETECTADAS EXITOSAMENTE");

        return [
            'separador' => $separador,
            'colFecha' => $colFecha,
            'colHora' => $colHora,
            'colAmPm' => $colAmPm,
            'horaIncluyeAmPm' => $horaIncluyeAmPm,
            'colTemperatura' => $colTemperatura,
            'colHumedad' => $colHumedad,
            'colPresion' => $colPresion,
            'colRadiacionGlobal' => $colRadiacionGlobal,
            'colRadiacionDifusa' => $colRadiacionDifusa,
            'colLluvia' => $colLluvia,
            'colVelocidadViento' => $colVelocidadViento,
            'colDireccionViento' => $colDireccionViento
        ];
    }


    private function buscarMejorColumna($puntuaciones, $colsExcluidas)
    {
        $maxPuntaje = 0;
        $mejorCol = null;

        foreach ($puntuaciones as $col => $puntaje) {
            if (in_array($col, $colsExcluidas)) {
                continue;
            }
            if ($puntaje > $maxPuntaje) {
                $maxPuntaje = $puntaje;
                $mejorCol = $col;
            }
        }

        return $mejorCol;
    }


    private function lineaTieneValoresReales($line, $separador)
    {
        $campos = array_map('trim', explode($separador, $line));

        foreach ($campos as $valor) {
            if (is_numeric($valor)) {
                $num = floatval($valor);

                // Variables meteorológicas con rangos válidos
                if (
                    ($num >= -50 && $num <= 60) ||     // Temperatura
                    ($num >= 0 && $num <= 100) ||      // Humedad
                    ($num >= 900 && $num <= 1200) ||   // Presión
                    ($num >= 0 && $num <= 1500) ||     // Radiación
                    ($num >= 0 && $num <= 50)          // Velocidad viento
                ) {
                    return true;
                }
            }
        }

        return false;
    }


    private function processCSVFile($filePath, $estacionInfo, $idCarga)
{
    set_time_limit(0);
    ini_set('memory_limit', '2048M');
    $registrosValidos = 0;
    $registrosRechazados = 0;
    $registrosProcesados = 0;
    $fechaInicio = null;
    $fechaFin = null;
    $anioMinimo = null;
    $anioMaximo = null;

    $loteActual = [];
    $tamanoLote = 1000;

    try {
        if (!file_exists($filePath)) {
            throw new Exception('Archivo no encontrado');
        }

        // ✅ DETECTAR COLUMNAS EXACTAS
        $deteccion = $this->detectarColumnasExactasL1($filePath);

        if (!$deteccion) {
            throw new Exception('No se pudieron detectar las columnas automáticamente');
        }

        error_log("=== PROCESAMIENTO L1 CON COLUMNAS EXACTAS ===");
        error_log("Separador: '{$deteccion['separador']}'");
        error_log("Fecha: Col[{$deteccion['colFecha']}]");
        error_log("Hora: Col[{$deteccion['colHora']}]");
        error_log("Temperatura: Col[" . ($deteccion['colTemperatura'] ?? 'NO EXISTE') . "]");
        error_log("Humedad: Col[" . ($deteccion['colHumedad'] ?? 'NO EXISTE') . "]");
        error_log("Presión: Col[" . ($deteccion['colPresion'] ?? 'NO EXISTE') . "]");
        error_log("Radiación Global: Col[" . ($deteccion['colRadiacionGlobal'] ?? 'NO EXISTE') . "]");
        error_log("Lluvia: Col[" . ($deteccion['colLluvia'] ?? 'NO EXISTE') . "]");
        error_log("Velocidad Viento: Col[" . ($deteccion['colVelocidadViento'] ?? 'NO EXISTE') . "]");
        error_log("Dirección Viento: Col[" . ($deteccion['colDireccionViento'] ?? 'NO EXISTE') . "]");

        // ✅ PROCESAR ARCHIVO
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new Exception('No se pudo abrir el archivo');
        }

        $lineNumber = 0;

        while (($line = fgets($handle)) !== false) {
            $lineNumber++;
            $line = trim($line);

            if (empty($line))
                continue;

            $lineLower = strtolower($line);

            // Saltar encabezados
            if (
                strpos($lineLower, 'genwind') !== false ||
                strpos($lineLower, 'time') !== false ||
                strpos($lineLower, 'date') !== false ||
                strpos($lineLower, 'status') !== false ||
                strpos($lineLower, 'temperature') !== false ||
                strpos($lineLower, 'stat_ta') !== false ||
                strpos($lineLower, 'baro_meas') !== false
            ) {
                continue;
            }

            $registrosProcesados++;

            $campos = array_map('trim', explode($deteccion['separador'], $line));

            // ✅ VERIFICAR QUE TENGA SUFICIENTES COLUMNAS
            $columnasNecesarias = max(
                $deteccion['colFecha'],
                $deteccion['colHora'],
                $deteccion['colTemperatura'] ?? 0,
                $deteccion['colHumedad'] ?? 0,
                $deteccion['colPresion'] ?? 0,
                $deteccion['colRadiacionGlobal'] ?? 0
            );

            if (count($campos) <= $columnasNecesarias) {
                $registrosRechazados++;
                continue;
            }

            // ✅ EXTRAER FECHA Y HORA
            $fechaTexto = $campos[$deteccion['colFecha']];
            $horaTexto = $campos[$deteccion['colHora']];

            // ✅ MANEJAR AM/PM
            $ampm = '';
            if ($deteccion['horaIncluyeAmPm']) {
                if (preg_match('/^(.+?)\s*(AM|PM)$/i', $horaTexto, $matches)) {
                    $horaTexto = trim($matches[1]);
                    $ampm = strtoupper($matches[2]);
                }
            } else if ($deteccion['colAmPm'] !== null && isset($campos[$deteccion['colAmPm']])) {
                $ampm = $campos[$deteccion['colAmPm']];
            }

            // ✅ PARSEAR FECHA/HORA
            $resultado = $this->parseDateTimeComplete($fechaTexto, $horaTexto, $ampm);

            if (!$resultado) {
                $registrosRechazados++;
                continue;
            }

            $fecha = $resultado['fecha'];
            $hora = $resultado['hora'];
            $fechaHoraCompleta = $resultado['fechahora'];

            // ✅ EXTRAER CADA VARIABLE DE SU COLUMNA EXACTA
            // TEMPERATURA
            $temperatura_aire = 0.0;
            if ($deteccion['colTemperatura'] !== null && isset($campos[$deteccion['colTemperatura']])) {
                $temp = $this->parseNumericValue($campos[$deteccion['colTemperatura']]);
                if ($temp !== null && $temp >= -50 && $temp <= 60) {
                    $temperatura_aire = $temp;
                }
            }

            // HUMEDAD
            $humedad_relativa = 0.0;
            if ($deteccion['colHumedad'] !== null && isset($campos[$deteccion['colHumedad']])) {
                $hum = $this->parseNumericValue($campos[$deteccion['colHumedad']]);
                if ($hum !== null && $hum >= 0 && $hum <= 100) {
                    $humedad_relativa = $hum;
                }
            }

            // PRESIÓN
            $presion_barometrica = 0.0;
            if ($deteccion['colPresion'] !== null && isset($campos[$deteccion['colPresion']])) {
                $pres = $this->parseNumericValue($campos[$deteccion['colPresion']]);
                if ($pres !== null && $pres >= 700 && $pres <= 1200) {
                    $presion_barometrica = $pres;
                }
            }

            // RADIACIÓN GLOBAL
            $radiacion_global = 0.0;
            if ($deteccion['colRadiacionGlobal'] !== null && isset($campos[$deteccion['colRadiacionGlobal']])) {
                $rad = $this->parseNumericValue($campos[$deteccion['colRadiacionGlobal']]);
                if ($rad !== null && $rad >= 0 && $rad <= 1500) {
                    $radiacion_global = $rad;
                }
            }

            // LLUVIA (solo si existe la columna)
            $lluvia = 0.0;
            if ($deteccion['colLluvia'] !== null && isset($campos[$deteccion['colLluvia']])) {
                $lluv = $this->parseNumericValue($campos[$deteccion['colLluvia']]);
                if ($lluv !== null && $lluv >= 0 && $lluv <= 500) {
                    $lluvia = $lluv;
                }
            }

            // VELOCIDAD VIENTO (solo si existe la columna)
            $velocidad_viento = 0.0;
            if ($deteccion['colVelocidadViento'] !== null && isset($campos[$deteccion['colVelocidadViento']])) {
                $vel = $this->parseNumericValue($campos[$deteccion['colVelocidadViento']]);
                if ($vel !== null && $vel >= 0 && $vel <= 50) {
                    $velocidad_viento = $vel;
                }
            }

            // DIRECCIÓN VIENTO (solo si existe la columna)
            $direccion_viento = 0.0;
            if ($deteccion['colDireccionViento'] !== null && isset($campos[$deteccion['colDireccionViento']])) {
                $dir = $this->parseNumericValue($campos[$deteccion['colDireccionViento']]);
                if ($dir !== null && $dir >= 0 && $dir <= 360) {
                    $direccion_viento = $dir;
                }
            }

            // ✅ DEBUG: Primeros 3 registros
            if ($registrosProcesados <= 3) {
                error_log("Registro $lineNumber:");
                error_log("  Fecha/Hora: $fechaHoraCompleta");
                error_log("  Temperatura: $temperatura_aire");
                error_log("  Humedad: $humedad_relativa");
                error_log("  Presión: $presion_barometrica");
                error_log("  Radiación Global: $radiacion_global");
                error_log("  Lluvia: $lluvia");
                error_log("  Velocidad Viento: $velocidad_viento");
                error_log("  Dirección Viento: $direccion_viento");
            }

            // ✅ VALIDAR QUE HAYA AL MENOS 3 VARIABLES CON DATOS REALES
            $variablesConDatos = 0;
            if ($temperatura_aire != 0.0) $variablesConDatos++;
            if ($humedad_relativa != 0.0) $variablesConDatos++;
            if ($presion_barometrica != 0.0) $variablesConDatos++;
            if ($radiacion_global != 0.0) $variablesConDatos++;

            if ($variablesConDatos < 3) {
                $registrosRechazados++;
                continue;
            }

            // Rastrear años
            $anioActual = intval(substr($fecha, 0, 4));
            if ($anioMinimo === null || $anioActual < $anioMinimo) {
                $anioMinimo = $anioActual;
            }
            if ($anioMaximo === null || $anioActual > $anioMaximo) {
                $anioMaximo = $anioActual;
            }

            $loteActual[] = [
                'fecha_hora' => $fechaHoraCompleta,
                'fecha' => $fecha,
                'hora' => $hora,
                'temperatura_aire' => $temperatura_aire,
                'humedad_relativa' => $humedad_relativa,
                'presion_barometrica' => $presion_barometrica,
                'radiacion_global' => $radiacion_global,
                'lluvia' => $lluvia,
                'velocidad_viento' => $velocidad_viento,
                'direccion_viento' => $direccion_viento
            ];

            $registrosValidos++;

            if ($fechaInicio === null || $fechaHoraCompleta < $fechaInicio) {
                $fechaInicio = $fechaHoraCompleta;
            }
            if ($fechaFin === null || $fechaHoraCompleta > $fechaFin) {
                $fechaFin = $fechaHoraCompleta;
            }

            // Insertar lote
            if (count($loteActual) >= $tamanoLote) {
                $this->model->insertL1Data($idCarga, $loteActual);

                $porcentaje = round(($registrosValidos / $registrosProcesados) * 100, 1);
                error_log("✓ $registrosValidos/$registrosProcesados ($porcentaje%)");

                $loteActual = [];
                gc_collect_cycles();
            }
        }

        fclose($handle);

        // Insertar último lote
        if (!empty($loteActual)) {
            $this->model->insertL1Data($idCarga, $loteActual);
        }

        if ($registrosValidos == 0) {
            throw new Exception('No se encontraron datos válidos');
        }

        error_log("✅ VÁLIDOS L1: $registrosValidos | RECHAZADOS: $registrosRechazados");

        return [
            'success' => true,
            'registros_validos' => $registrosValidos,
            'registros_rechazados' => $registrosRechazados,
            'registros_procesados' => $registrosProcesados,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'anio_minimo' => $anioMinimo,
            'anio_maximo' => $anioMaximo,
        ];

    } catch (Exception $e) {
        if (isset($handle) && is_resource($handle)) {
            fclose($handle);
        }

        return [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
}
/**
 * ✅ NUEVO: Detectar columnas EXACTAS usando encabezados y análisis de datos
 */
private function detectarColumnasExactasL1($filePath)
{
    try {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            error_log("❌ No se pudo abrir archivo");
            return null;
        }

        error_log("=== DETECCIÓN DEFINITIVA L1 V3 (CORREGIDA) ===");

        $separador = null;
        $todasLasLineas = [];
        
        // ✅ LEER TODO EL ARCHIVO
        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if (!empty($line)) {
                $todasLasLineas[] = $line;
            }
        }
        fclose($handle);

        if (count($todasLasLineas) < 3) {
            error_log("❌ Archivo muy corto");
            return null;
        }

        // ✅ Detectar separador
        $separador = (substr_count($todasLasLineas[0], ',') > substr_count($todasLasLineas[0], ';')) ? ',' : ';';
        error_log("Separador: '$separador'");

        // ✅ Encontrar línea con "date, time"
        $indexEncabezadoClave = null;
        $encabezadoClave = null;
        
        for ($i = 0; $i < min(10, count($todasLasLineas)); $i++) {
            $lineLower = strtolower($todasLasLineas[$i]);
            if (strpos($lineLower, 'date') !== false && strpos($lineLower, 'time') !== false) {
                $indexEncabezadoClave = $i;
                $encabezadoClave = array_map('trim', explode($separador, $todasLasLineas[$i]));
                error_log("✓ Encabezado CLAVE en línea $i: " . count($encabezadoClave) . " columnas");
                break;
            }
        }

        if ($encabezadoClave === null) {
            error_log("❌ No se encontró línea con date/time");
            return null;
        }

        // ✅ Obtener línea anterior (nombres de variables)
        $encabezadoVariables = null;
        if ($indexEncabezadoClave > 0) {
            $encabezadoVariables = array_map('trim', explode($separador, $todasLasLineas[$indexEncabezadoClave - 1]));
            error_log("✓ Encabezado VARIABLES: " . count($encabezadoVariables) . " columnas");
        }

        // ✅ Recolectar líneas de DATOS
        $lineasDatos = [];
        for ($i = $indexEncabezadoClave + 1; $i < min($indexEncabezadoClave + 31, count($todasLasLineas)); $i++) {
            if ($this->lineaTieneValoresReales($todasLasLineas[$i], $separador)) {
                $lineasDatos[] = $todasLasLineas[$i];
            }
        }

        if (empty($lineasDatos)) {
            error_log("❌ No hay datos para analizar");
            return null;
        }

        error_log("✓ Líneas de datos: " . count($lineasDatos));

        // ✅ INICIALIZAR RESULTADO
        $resultado = [
            'separador' => $separador,
            'colFecha' => null,
            'colHora' => null,
            'colAmPm' => null,
            'horaIncluyeAmPm' => false,
            'colTemperatura' => null,
            'colHumedad' => null,
            'colPresion' => null,
            'colRadiacionGlobal' => null,
            'colRadiacionDifusa' => null,
            'colLluvia' => null,
            'colVelocidadViento' => null,
            'colDireccionViento' => null
        ];

        // ✅ PASO 1: FECHA Y HORA
        foreach ($encabezadoClave as $i => $nombre) {
            $n = strtolower(trim($nombre));
            if ($n === 'date') $resultado['colFecha'] = $i;
            if ($n === 'time') $resultado['colHora'] = $i;
        }

        if ($resultado['colFecha'] === null || $resultado['colHora'] === null) {
            error_log("❌ No se encontraron date/time");
            return null;
        }

        // Detectar AM/PM
        $primeraLinea = explode($separador, $lineasDatos[0]);
        if (isset($primeraLinea[$resultado['colHora']]) && preg_match('/AM|PM/i', $primeraLinea[$resultado['colHora']])) {
            $resultado['horaIncluyeAmPm'] = true;
        }

        error_log("✓ Fecha: Col[{$resultado['colFecha']}], Hora: Col[{$resultado['colHora']}]");

        // ✅ PASO 2: MAPEAR VARIABLES A SUS COLUMNAS (VERSIÓN CORREGIDA)
        $mapeoVariables = $this->mapearVariablesAColumnasV2($encabezadoVariables, $encabezadoClave);
        
        error_log("📋 MAPEO DE VARIABLES V2:");
        foreach ($mapeoVariables as $var => $cols) {
            if (!empty($cols)) {
                error_log("  $var: " . implode(', ', $cols));
            }
        }

        // ✅ PASO 3: ASIGNAR COLUMNAS ESPECÍFICAS CON VALIDACIÓN DE DATOS
        
        // TEMPERATURA: Buscar SOLO Stat_TA (NO WindChill, NO TG)
        if (isset($mapeoVariables['temperatura']) && !empty($mapeoVariables['temperatura'])) {
            foreach ($mapeoVariables['temperatura'] as $col) {
                if ($this->validarRangoEnColumna($lineasDatos, $separador, $col, -50, 60, 5)) {
                    $resultado['colTemperatura'] = $col;
                    error_log("✓ TEMPERATURA: Col[$col]");
                    break;
                }
            }
        }

        // HUMEDAD
        if (isset($mapeoVariables['humedad']) && !empty($mapeoVariables['humedad'])) {
            foreach ($mapeoVariables['humedad'] as $col) {
                if ($this->validarRangoEnColumna($lineasDatos, $separador, $col, 0, 100, 5)) {
                    $resultado['colHumedad'] = $col;
                    error_log("✓ HUMEDAD: Col[$col]");
                    break;
                }
            }
        }

        // PRESIÓN
        if (isset($mapeoVariables['presion']) && !empty($mapeoVariables['presion'])) {
            foreach ($mapeoVariables['presion'] as $col) {
                if ($this->validarRangoEnColumna($lineasDatos, $separador, $col, 700, 800, 5)) {
                    $resultado['colPresion'] = $col;
                    error_log("✓ PRESIÓN: Col[$col]");
                    break;
                }
            }
        }

        // LLUVIA
        if (isset($mapeoVariables['lluvia']) && !empty($mapeoVariables['lluvia'])) {
            $resultado['colLluvia'] = $mapeoVariables['lluvia'][0];
            error_log("✓ LLUVIA: Col[{$resultado['colLluvia']}]");
        }

        // ✅ RADIACIÓN GLOBAL Y DIFUSA (LÓGICA MEJORADA)
        $columnasRadiacion = [];
        
        // CASO 1: Radiación con nombres específicos
        if (isset($mapeoVariables['radiacion_global']) && !empty($mapeoVariables['radiacion_global'])) {
            foreach ($mapeoVariables['radiacion_global'] as $col) {
                if ($this->validarRangoEnColumna($lineasDatos, $separador, $col, 0, 1500, 3)) {
                    $resultado['colRadiacionGlobal'] = $col;
                    error_log("✓ RADIACIÓN GLOBAL (nombre): Col[$col]");
                    break;
                }
            }
        }

        if (isset($mapeoVariables['radiacion_difusa']) && !empty($mapeoVariables['radiacion_difusa'])) {
            foreach ($mapeoVariables['radiacion_difusa'] as $col) {
                if ($this->validarRangoEnColumna($lineasDatos, $separador, $col, 0, 800, 3)) {
                    $resultado['colRadiacionDifusa'] = $col;
                    error_log("✓ RADIACIÓN DIFUSA (nombre): Col[$col]");
                    break;
                }
            }
        }

        // CASO 2: Si NO se encontró radiación con nombres específicos, buscar columnas "SR"
        if ($resultado['colRadiacionGlobal'] === null && isset($mapeoVariables['radiacion_sr'])) {
            $columnasRadiacion = $mapeoVariables['radiacion_sr'];
            
            error_log("📊 Columnas SR detectadas: " . count($columnasRadiacion));
            
            if (count($columnasRadiacion) >= 2) {
                // ✅ ESTRATEGIA: Comparar promedios para distinguir Difusa (menor) de Global (mayor)
                $promedios = [];
                foreach ($columnasRadiacion as $col) {
                    if ($this->validarRangoEnColumna($lineasDatos, $separador, $col, 0, 1500, 3)) {
                        $prom = $this->calcularPromedioColumna($lineasDatos, $separador, $col);
                        $promedios[$col] = $prom;
                        error_log("  Col[$col]: promedio = " . round($prom, 2));
                    }
                }
                
                if (count($promedios) >= 2) {
                    arsort($promedios); // Mayor a menor
                    $cols = array_keys($promedios);
                    
                    // ✅ VERIFICAR NOMBRES DE VARIABLES para confirmar orden
                    $col1 = $cols[0];
                    $col2 = $cols[1];
                    
                    $var1 = isset($encabezadoVariables[$col1]) ? strtolower($encabezadoVariables[$col1]) : '';
                    $var2 = isset($encabezadoVariables[$col2]) ? strtolower($encabezadoVariables[$col2]) : '';
                    
                    // Si el nombre contiene "difusa", asignarla correctamente
                    if (strpos($var1, 'dif') !== false) {
                        $resultado['colRadiacionDifusa'] = $col1;
                        $resultado['colRadiacionGlobal'] = $col2;
                    } else if (strpos($var2, 'dif') !== false) {
                        $resultado['colRadiacionDifusa'] = $col2;
                        $resultado['colRadiacionGlobal'] = $col1;
                    } else if (strpos($var1, 'glob') !== false) {
                        $resultado['colRadiacionGlobal'] = $col1;
                        $resultado['colRadiacionDifusa'] = $col2;
                    } else if (strpos($var2, 'glob') !== false) {
                        $resultado['colRadiacionGlobal'] = $col2;
                        $resultado['colRadiacionDifusa'] = $col1;
                    } else {
                        // Sin nombres claros, usar promedios: mayor = Global, menor = Difusa
                        $resultado['colRadiacionGlobal'] = $col1; // Mayor promedio
                        $resultado['colRadiacionDifusa'] = $col2; // Menor promedio
                    }
                    
                    error_log("✓ RADIACIÓN por SR: Global=Col[{$resultado['colRadiacionGlobal']}], Difusa=Col[{$resultado['colRadiacionDifusa']}]");
                } else if (count($promedios) == 1) {
                    $resultado['colRadiacionGlobal'] = array_keys($promedios)[0];
                    error_log("✓ RADIACIÓN (única SR válida): Col[{$resultado['colRadiacionGlobal']}]");
                }
            } else if (count($columnasRadiacion) == 1) {
                // Solo una columna SR, asumirla como Global
                if ($this->validarRangoEnColumna($lineasDatos, $separador, $columnasRadiacion[0], 0, 1500, 3)) {
                    $resultado['colRadiacionGlobal'] = $columnasRadiacion[0];
                    error_log("✓ RADIACIÓN (única SR): Col[{$columnasRadiacion[0]}]");
                }
            }
        }

        // VIENTO
        if (isset($mapeoVariables['velocidad_viento']) && !empty($mapeoVariables['velocidad_viento'])) {
            $resultado['colVelocidadViento'] = $mapeoVariables['velocidad_viento'][0];
            error_log("✓ VELOCIDAD VIENTO: Col[{$resultado['colVelocidadViento']}]");
        }

        if (isset($mapeoVariables['direccion_viento']) && !empty($mapeoVariables['direccion_viento'])) {
            $resultado['colDireccionViento'] = $mapeoVariables['direccion_viento'][0];
            error_log("✓ DIRECCIÓN VIENTO: Col[{$resultado['colDireccionViento']}]");
        }

        // ✅ VALIDAR DETECCIÓN MÍNIMA
        $variablesDetectadas = 0;
        if ($resultado['colTemperatura'] !== null) $variablesDetectadas++;
        if ($resultado['colHumedad'] !== null) $variablesDetectadas++;
        if ($resultado['colPresion'] !== null) $variablesDetectadas++;
        if ($resultado['colRadiacionGlobal'] !== null) $variablesDetectadas++;

        if ($variablesDetectadas < 3) {
            error_log("❌ Solo $variablesDetectadas variables detectadas (se necesitan 3)");
            error_log("   Temperatura: " . ($resultado['colTemperatura'] ?? 'NO'));
            error_log("   Humedad: " . ($resultado['colHumedad'] ?? 'NO'));
            error_log("   Presión: " . ($resultado['colPresion'] ?? 'NO'));
            error_log("   Radiación: " . ($resultado['colRadiacionGlobal'] ?? 'NO'));
            return null;
        }

        // ✅ RESUMEN FINAL
        error_log("✅ DETECCIÓN EXITOSA: $variablesDetectadas/4 básicas + " . 
                  ($resultado['colLluvia'] ? '1' : '0') . " lluvia + " .
                  ($resultado['colVelocidadViento'] ? '1' : '0') . " viento");

        return $resultado;

    } catch (Exception $e) {
        error_log("❌ Error: " . $e->getMessage());
        return null;
    }
}
private function mapearVariablesAColumnasV2($encabezadoVariables, $encabezadoClave)
{
    $mapeo = [
        'temperatura' => [],
        'humedad' => [],
        'presion' => [],
        'lluvia' => [],
        'radiacion_global' => [],
        'radiacion_difusa' => [],
        'radiacion_sr' => [],
        'velocidad_viento' => [],
        'direccion_viento' => []
    ];

    if (!$encabezadoVariables) {
        return $mapeo;
    }

    $variableActual = null;
    
    for ($i = 0; $i < count($encabezadoVariables); $i++) {
        $nombreVar = strtolower(trim($encabezadoVariables[$i]));
        
        // ✅ MEJORA: Detectar inicio de nueva variable CON FILTROS MÁS ESTRICTOS
        if (!empty($nombreVar)) {
            // ✅ TEMPERATURA: Solo Stat_TA (NO WindChill, NO TG)
            if (strpos($nombreVar, 'stat_ta') !== false && 
                strpos($nombreVar, 'tg') === false && 
                strpos($nombreVar, 'windchill') === false) {
                $variableActual = 'temperatura';
            }
            // HUMEDAD
            else if (strpos($nombreVar, 'stat_rh') !== false) {
                $variableActual = 'humedad';
            }
            // PRESIÓN
            else if (strpos($nombreVar, 'stat_pa') !== false || strpos($nombreVar, 'baro_meas') !== false) {
                $variableActual = 'presion';
            }
            // LLUVIA
            else if (strpos($nombreVar, 'lluvia_meas') !== false) {
                $variableActual = 'lluvia';
            }
            // RADIACIÓN GLOBAL (nombres específicos)
            else if (strpos($nombreVar, 'stat_sr_glob') !== false || strpos($nombreVar, 'rad_global') !== false) {
                $variableActual = 'radiacion_global';
            }
            // RADIACIÓN DIFUSA (nombres específicos)
            else if (strpos($nombreVar, 'stat_sr_dif') !== false || strpos($nombreVar, 'rad_difusa') !== false) {
                $variableActual = 'radiacion_difusa';
            }
            // VIENTO
            else if (strpos($nombreVar, 'genwind') !== false) {
                $variableActual = 'viento';
            }
            // ✅ NO cambiar variableActual si es WindChill o TG
            else if (strpos($nombreVar, 'windchill') !== false || 
                     strpos($nombreVar, 'tgmeas') !== false ||
                     strpos($nombreVar, 'stat_ts') !== false) {
                $variableActual = null; // Ignorar estas variables
            }
        }
        
        // Agregar columnas según el tipo en línea 2
        if ($i < count($encabezadoClave)) {
            $nombreCol = strtolower(trim($encabezadoClave[$i]));
            
            if ($variableActual === 'temperatura' && $nombreCol === 'avg') {
                $mapeo['temperatura'][] = $i;
            } else if ($variableActual === 'humedad' && $nombreCol === 'avg') {
                $mapeo['humedad'][] = $i;
            } else if ($variableActual === 'presion' && ($nombreCol === 'avg' || $nombreCol === 'pa')) {
                $mapeo['presion'][] = $i;
            } else if ($variableActual === 'lluvia' && $nombreCol === 'pr') {
                $mapeo['lluvia'][] = $i;
            } else if ($variableActual === 'radiacion_global' && $nombreCol === 'avg') {
                $mapeo['radiacion_global'][] = $i;
            } else if ($variableActual === 'radiacion_difusa' && $nombreCol === 'avg') {
                $mapeo['radiacion_difusa'][] = $i;
            } 
            // ✅ CAPTURAR TODAS LAS COLUMNAS "SR" SIN CLASIFICAR
            else if ($nombreCol === 'sr') {
                $mapeo['radiacion_sr'][] = $i;
            } 
            else if ($variableActual === 'viento' && strpos($nombreCol, 'spdavg') !== false) {
                $mapeo['velocidad_viento'][] = $i;
            } else if ($variableActual === 'viento' && strpos($nombreCol, 'diravg') !== false) {
                $mapeo['direccion_viento'][] = $i;
            }
        }
    }
    
    return $mapeo;
}
private function mapearVariablesAColumnas($encabezadoVariables, $encabezadoClave)
{
    $mapeo = [
        'temperatura' => [],
        'humedad' => [],
        'presion' => [],
        'lluvia' => [],
        'radiacion_global' => [],
        'radiacion_difusa' => [],
        'radiacion_sr' => [],
        'velocidad_viento' => [],
        'direccion_viento' => []
    ];

    if (!$encabezadoVariables) {
        return $mapeo;
    }

    $variableActual = null;
    
    for ($i = 0; $i < count($encabezadoVariables); $i++) {
        $nombreVar = strtolower(trim($encabezadoVariables[$i]));
        
        // Detectar inicio de nueva variable
        if (!empty($nombreVar)) {
            if (strpos($nombreVar, 'stat_ta') !== false && strpos($nombreVar, 'tg') === false && strpos($nombreVar, 'ts') === false) {
                $variableActual = 'temperatura';
            } else if (strpos($nombreVar, 'stat_rh') !== false) {
                $variableActual = 'humedad';
            } else if (strpos($nombreVar, 'stat_pa') !== false || strpos($nombreVar, 'baro_meas') !== false) {
                $variableActual = 'presion';
            } else if (strpos($nombreVar, 'lluvia_meas') !== false) {
                $variableActual = 'lluvia';
            } else if (strpos($nombreVar, 'stat_sr_glob') !== false || strpos($nombreVar, 'rad_global') !== false) {
                $variableActual = 'radiacion_global';
            } else if (strpos($nombreVar, 'stat_sr_dif') !== false || strpos($nombreVar, 'rad_difusa') !== false) {
                $variableActual = 'radiacion_difusa';
            } else if (strpos($nombreVar, 'genwind') !== false) {
                $variableActual = 'viento'; // Marca el inicio del bloque de viento
            } else {
                // Si no reconoce, mantener variable actual
            }
        }
        
        // Agregar columnas según el tipo en línea 2
        if ($i < count($encabezadoClave)) {
            $nombreCol = strtolower(trim($encabezadoClave[$i]));
            
            if ($variableActual === 'temperatura' && $nombreCol === 'avg') {
                $mapeo['temperatura'][] = $i;
            } else if ($variableActual === 'humedad' && $nombreCol === 'avg') {
                $mapeo['humedad'][] = $i;
            } else if ($variableActual === 'presion' && ($nombreCol === 'avg' || $nombreCol === 'pa')) {
                $mapeo['presion'][] = $i;
            } else if ($variableActual === 'lluvia' && $nombreCol === 'pr') {
                $mapeo['lluvia'][] = $i;
            } else if ($variableActual === 'radiacion_global' && $nombreCol === 'avg') {
                $mapeo['radiacion_global'][] = $i;
            } else if ($variableActual === 'radiacion_difusa' && $nombreCol === 'avg') {
                $mapeo['radiacion_difusa'][] = $i;
            } else if ($nombreCol === 'sr') {
                // Cualquier SR sin clasificar
                $mapeo['radiacion_sr'][] = $i;
            } else if ($variableActual === 'viento' && strpos($nombreCol, 'spdavg') !== false) {
                $mapeo['velocidad_viento'][] = $i;
            } else if ($variableActual === 'viento' && strpos($nombreCol, 'diravg') !== false) {
                $mapeo['direccion_viento'][] = $i;
            }
        }
    }
    
    return $mapeo;
}

private function validarRangoEnColumna($lineas, $sep, $col, $min, $max, $minValidos)
{
    $validos = 0;
    foreach ($lineas as $linea) {
        $campos = explode($sep, $linea);
        if (isset($campos[$col])) {
            $val = $this->parseNumericValue($campos[$col]);
            if ($val !== null && $val >= $min && $val <= $max) {
                $validos++;
                if ($validos >= $minValidos) {
                    return true;
                }
            }
        }
    }
    return false;
}

/**
 * ✅ Calcular promedio de valores en una columna (para distinguir radiación global de difusa)
 */
private function calcularPromedioColumna($lineas, $sep, $col)
{
    $suma = 0;
    $count = 0;
    
    foreach ($lineas as $linea) {
        $campos = explode($sep, $linea);
        if (isset($campos[$col])) {
            $val = $this->parseNumericValue($campos[$col]);
            if ($val !== null && $val > 0) {
                $suma += $val;
                $count++;
            }
        }
    }
    
    return $count > 0 ? ($suma / $count) : 0;
}



private function validarRangoHumedad($datos, $sep, $colIndex)
{
    $validos = 0;
    foreach ($datos as $linea) {
        $campos = explode($sep, $linea);
        if (isset($campos[$colIndex])) {
            $val = $this->parseNumericValue($campos[$colIndex]);
            if ($val !== null && $val >= 0 && $val <= 100) {
                $validos++;
                if ($validos >= 5) return true;
            }
        }
    }
    return false;
}

private function validarRangoPresion($datos, $sep, $colIndex)
{
    $validos = 0;
    foreach ($datos as $linea) {
        $campos = explode($sep, $linea);
        if (isset($campos[$colIndex])) {
            $val = $this->parseNumericValue($campos[$colIndex]);
            if ($val !== null && $val >= 700 && $val <= 800) {
                $validos++;
                if ($validos >= 5) return true;
            }
        }
    }
    return false;
}

private function validarRangoRadiacion($datos, $sep, $colIndex)
{
    $validos = 0;
    foreach ($datos as $linea) {
        $campos = explode($sep, $linea);
        if (isset($campos[$colIndex])) {
            $val = $this->parseNumericValue($campos[$colIndex]);
            if ($val !== null && $val >= 0 && $val <= 1500) {
                $validos++;
                if ($validos >= 3) return true;
            }
        }
    }
    return false;
}


    private function parseDateTimeComplete($fechaTexto, $horaTexto, $ampm = '')
    {
        // Parsear fecha
        $separador = '/';
        if (strpos($fechaTexto, '-') !== false) {
            $separador = '-';
        }

        $fechaComponentes = explode($separador, $fechaTexto);
        if (count($fechaComponentes) != 3) {
            error_log("❌ Formato de fecha inválido: $fechaTexto");
            return false;
        }

        // ✅ SIEMPRE MM/DD/YY (formato estadounidense)
        $mes = intval($fechaComponentes[0]);   // Primera parte = MES
        $dia = intval($fechaComponentes[1]);   // Segunda parte = DÍA
        $anio = intval($fechaComponentes[2]);  // Tercera parte = AÑO

        // ✅ Convertir año corto a 4 dígitos
        if ($anio < 100) {
            $anio = 2000 + $anio;  // 17 → 2017, 20 → 2020
        }

        // ✅ Validar que la fecha sea correcta
        if (!checkdate($mes, $dia, $anio)) {
            error_log("❌ Fecha inválida: mes=$mes, día=$dia, año=$anio (original: $fechaTexto)");
            return false;
        }

        // Parsear hora
        $horaComponentes = explode(':', $horaTexto);
        if (count($horaComponentes) < 2) {
            error_log("❌ Formato de hora inválido: $horaTexto");
            return false;
        }

        $hora = intval($horaComponentes[0]);
        $minuto = intval($horaComponentes[1]);
        $segundo = isset($horaComponentes[2]) ? intval($horaComponentes[2]) : 0;

        // ✅ Convertir AM/PM a formato 24 horas
        if (!empty($ampm)) {
            $ampm = strtoupper(trim($ampm));
            if ($ampm == 'PM' && $hora < 12) {
                $hora += 12;  // 3 PM → 15
            } elseif ($ampm == 'AM' && $hora == 12) {
                $hora = 0;    // 12 AM → 00
            }
        }

        // Validar que la hora sea correcta
        if ($hora < 0 || $hora > 23 || $minuto < 0 || $minuto > 59 || $segundo < 0 || $segundo > 59) {
            error_log("❌ Hora inválida: $hora:$minuto:$segundo");
            return false;
        }

        // ✅ Retornar fecha, hora y fechahora separadas
        return [
            'fecha' => sprintf('%04d-%02d-%02d', $anio, $mes, $dia),
            'hora' => sprintf('%02d:%02d:%02d', $hora, $minuto, $segundo),
            'fechahora' => sprintf('%04d-%02d-%02d %02d:%02d:%02d', $anio, $mes, $dia, $hora, $minuto, $segundo)
        ];
    }

    private function parseNumericValue($value)
    {
        $value = trim($value);

        // ✅ Si está vacío o es NULL, retornar NULL para permitir valores NULL en L1
        if ($value === '' || strtoupper($value) === 'NULL' || strtoupper($value) === 'INVALID' || strtoupper($value) === 'VALID') {
            return null;
        }

        // Reemplazar coma por punto
        $value = str_replace(',', '.', $value);

        // Eliminar caracteres no numéricos
        $value = preg_replace('/[^0-9\.\-+]/', '', $value);

        if ($value === '' || $value === '.' || $value === '+' || $value === '-') {
            return null;
        }

        $numericValue = floatval($value);

        if (is_nan($numericValue) || !is_finite($numericValue)) {
            return null;
        }

        return $numericValue;
    }

    private function renameAndSaveFile($tempFilePath, $estacionInfo, $fechaInicio, $fechaFin)
    {
        // Obtener nombre de la estación
        $nombreEstacion = $estacionInfo['nombre'] ?? 'UNKNOWN';

        // Sanitizar el nombre de la estación
        $nombreEstacion = preg_replace('/[^A-Za-z0-9_\-]/', '_', $nombreEstacion);

        // Construir ruta: uploads/{NOMBRE_ESTACION}/Crudos/L1/
        $baseDir = '../uploads/';
        $estacionDir = $baseDir . $nombreEstacion . '/';
        $crudosDir = $estacionDir . 'Crudos/';
        $l1Dir = $crudosDir . 'L1/';

        // Crear directorios si no existen
        if (!is_dir($l1Dir)) {
            mkdir($l1Dir, 0755, true);
        }

        // Formato fecha: DD-MM-YYYY
        $fechaInicioFormato = date('d-m-Y', strtotime($fechaInicio));
        $fechaFinFormato = date('d-m-Y', strtotime($fechaFin));

        // NOMBRE: NOMBRE_ESTACION_L1_FECHA-INICIO_FECHA-FIN_Crudos.csv
        $newFileName = "{$nombreEstacion}_L1_{$fechaInicioFormato}_{$fechaFinFormato}_Crudos.csv";

        // Sanitizar nombre del archivo
        $newFileName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $newFileName);
        $finalPath = $l1Dir . $newFileName;

        // Si ya existe, agregar timestamp
        if (file_exists($finalPath)) {
            $timestamp = time();
            $newFileName = "{$nombreEstacion}_L1_{$fechaInicioFormato}_{$fechaFinFormato}_{$timestamp}_Crudos.csv";
            $finalPath = $l1Dir . $newFileName;
        }

        // Mover archivo temporal a ubicación final
        if (!rename($tempFilePath, $finalPath)) {
            throw new Exception('Error al mover archivo a ubicación final');
        }

        error_log("✅ Archivo crudo L1 guardado en: $finalPath");
        return $finalPath;
    }
    private function generateCleanFile($tempFilePath, $estacionInfo, $fechaInicio, $fechaFin)
{
    try {
        error_log("=== GENERACIÓN DE ARCHIVO LIMPIO L1 UNIVERSAL ===");

        $nombreEstacion = preg_replace('/[^A-Za-z0-9_\-]/', '_', $estacionInfo['nombre']);
        $baseDir = '../uploads/';
        $estacionDir = $baseDir . $nombreEstacion . '/';
        $limpiosDir = $estacionDir . 'Limpios/';
        $l1LimpiosDir = $limpiosDir . 'L1/';

        if (!is_dir($l1LimpiosDir)) {
            mkdir($l1LimpiosDir, 0755, true);
        }

        $fechaInicioFormato = date('d-m-Y', strtotime($fechaInicio));
        $fechaFinFormato = date('d-m-Y', strtotime($fechaFin));
        $cleanFileName = "{$nombreEstacion}_L1_{$fechaInicioFormato}_{$fechaFinFormato}_Limpios.csv";
        $cleanFilePath = $l1LimpiosDir . $cleanFileName;

        $handleInput = fopen($tempFilePath, 'r');
        if (!$handleInput) {
            throw new Exception('No se pudo abrir archivo de entrada');
        }

        $handleOutput = fopen($cleanFilePath, 'w');
        if (!$handleOutput) {
            fclose($handleInput);
            throw new Exception('No se pudo crear archivo limpio');
        }

        $lineasLimpias = 0;
        $lineasRechazadas = 0;
        $encabezadosOriginales = [];
        $separadorDetectado = null;
        $separadorSalida = ';';
        $enSeccionEncabezados = true;
        $totalColumnas = 0;

        error_log("📄 Iniciando limpieza universal de archivo L1");

        while (($line = fgets($handleInput)) !== false) {
            $line = trim($line);

            if (empty($line)) {
                continue;
            }

            $lineLower = strtolower($line);

            // ✅ DETECTAR SEPARADOR EN PRIMERA LÍNEA
            if ($separadorDetectado === null) {
                $separadorDetectado = (substr_count($line, ',') > substr_count($line, ';')) ? ',' : ';';
                error_log("Separador ENTRADA: '$separadorDetectado' | SALIDA: '$separadorSalida'");
            }

            // ✅ IDENTIFICAR Y COPIAR ENCABEZADOS (todas las líneas antes de los datos)
            if ($enSeccionEncabezados) {
                $esEncabezado = (
                    strpos($lineLower, 'stat_ta') !== false ||
                    strpos($lineLower, 'stat_rh') !== false ||
                    strpos($lineLower, 'baro_meas') !== false ||
                    strpos($lineLower, 'rad_difusa') !== false ||
                    strpos($lineLower, 'rad_global') !== false ||
                    strpos($lineLower, 'stat_sr') !== false ||
                    strpos($lineLower, 'lluvia_meas') !== false ||
                    strpos($lineLower, 'genwind') !== false ||
                    strpos($lineLower, 'tgmeas') !== false ||
                    strpos($lineLower, 'windchill') !== false ||
                    (strpos($lineLower, 'date') !== false && strpos($lineLower, 'time') !== false) ||
                    (strpos($lineLower, 'status') !== false && strpos($lineLower, 'avg') !== false)
                );

                if ($esEncabezado) {
                    // Convertir separador y limpiar columnas vacías al final
                    if ($separadorDetectado !== $separadorSalida) {
                        $line = str_replace($separadorDetectado, $separadorSalida, $line);
                    }
                    $line = rtrim($line, $separadorSalida);
                    
                    fwrite($handleOutput, $line . "\n");

                    // Guardar estructura de columnas de la línea "date, time, status, Avg..."
                    if (strpos($lineLower, 'date') !== false && strpos($lineLower, 'time') !== false) {
                        $encabezadosOriginales = explode($separadorSalida, $line);
                        $totalColumnas = count($encabezadosOriginales);
                        error_log("✓ Estructura detectada: $totalColumnas columnas");
                        error_log("  Primeras 10: " . implode(', ', array_slice($encabezadosOriginales, 0, 10)));
                    }
                    continue;
                } else {
                    // Terminaron los encabezados, comienzan los datos
                    $enSeccionEncabezados = false;
                    error_log("✓ Encabezados copiados, iniciando limpieza de datos");
                }
            }

            // ✅ LIMPIAR LÍNEAS DE DATOS (UNIVERSAL)
            $lineaLimpia = $this->cleanDataLineL1Universal(
                $line, 
                $separadorDetectado, 
                $totalColumnas, 
                $separadorSalida
            );

            if ($lineaLimpia) {
                fwrite($handleOutput, $lineaLimpia . "\n");
                $lineasLimpias++;

                if ($lineasLimpias % 10000 == 0) {
                    error_log("✓ Limpiadas: $lineasLimpias líneas");
                }
            } else {
                $lineasRechazadas++;
            }
        }

        fclose($handleInput);
        fclose($handleOutput);

        error_log("✅ Archivo limpio generado: $cleanFilePath");
        error_log("   Separador salida: '$separadorSalida'");
        error_log("   Líneas limpias: $lineasLimpias");
        error_log("   Líneas rechazadas: $lineasRechazadas");
        error_log("   Columnas preservadas: $totalColumnas");

        return $cleanFilePath;

    } catch (Exception $e) {
        error_log("❌ Error generando archivo limpio: " . $e->getMessage());
        throw new Exception('Error generando archivo limpio: ' . $e->getMessage());
    }
}

private function cleanDataLineL1Universal($line, $separadorEntrada, $columnasEsperadas, $separadorSalida = ';')
{
    try {
        $campos = explode($separadorEntrada, $line);

        // ✅ VALIDAR que tenga columnas suficientes
        if (count($campos) < $columnasEsperadas - 5) { // Tolerancia de 5 columnas
            error_log("⚠️ Línea con columnas insuficientes: " . count($campos) . " esperadas: ~$columnasEsperadas");
            return null;
        }

        $camposLimpios = [];

        for ($i = 0; $i < count($campos); $i++) {
            $valor = trim($campos[$i]);

            // ✅ COLUMNA 0: DATE (Fecha)
            if ($i === 0) {
                $fechaLimpia = $this->cleanDateL1Universal($valor);
                $camposLimpios[] = $fechaLimpia;
                continue;
            }

            // ✅ COLUMNA 1: TIME (Hora)
            if ($i === 1) {
                $horaLimpia = $this->cleanTimeL1Universal($valor);
                $camposLimpios[] = $horaLimpia;
                continue;
            }

            // ✅ COLUMNAS PARES (status): Limpiar STATUS
            if ($i % 2 === 0 && $i > 1) {
                // Verificar que existe el valor siguiente
                if (!isset($campos[$i + 1])) {
                    error_log("⚠️ STATUS sin valor en columna $i");
                    break;
                }

                $valorNumerico = trim($campos[$i + 1]);
                $statusLimpio = $this->cleanStatusL1Universal($valor, $valorNumerico);
                $camposLimpios[] = $statusLimpio;
                continue;
            }

            // ✅ COLUMNAS IMPARES (valores): Limpiar VALORES NUMÉRICOS
            if ($i % 2 === 1 && $i > 1) {
                $statusPrevio = isset($camposLimpios[$i - 1]) ? $camposLimpios[$i - 1] : '';
                $valorLimpio = $this->cleanNumericValueL1Universal($valor, $statusPrevio);
                $camposLimpios[] = $valorLimpio;
                continue;
            }
        }

        // ✅ VALIDACIÓN FINAL: No debe terminar en STATUS huérfano
        $ultimoIndice = count($camposLimpios) - 1;
        if ($ultimoIndice >= 2 && ($ultimoIndice % 2 === 0)) {
            error_log("⚠️ Eliminando STATUS huérfano al final");
            array_pop($camposLimpios);
        }

        // ✅ UNIR CON SEPARADOR DE SALIDA
        return implode($separadorSalida, $camposLimpios);

    } catch (Exception $e) {
        error_log("Error limpiando línea L1: " . $e->getMessage());
        return null;
    }
}
private function cleanDateL1Universal($fechaTexto)
{
    try {
        if (empty($fechaTexto)) {
            return $fechaTexto;
        }

        $separador = '/';
        if (strpos($fechaTexto, '-') !== false) {
            $separador = '-';
        }

        $partes = explode($separador, $fechaTexto);
        if (count($partes) != 3) {
            return $fechaTexto;
        }

        $mes = intval($partes[0]);
        $dia = intval($partes[1]);
        $anio = intval($partes[2]);

        // Convertir año corto a completo
        if ($anio < 100) {
            $anio = 2000 + $anio;
        }

        if (!checkdate($mes, $dia, $anio)) {
            error_log("❌ Fecha inválida: $mes/$dia/$anio");
            return $fechaTexto;
        }

        // ✅ FORMATO LIMPIO: M/d/yyyy (sin ceros iniciales)
        return sprintf('%d/%d/%d', $mes, $dia, $anio);

    } catch (Exception $e) {
        error_log("Error limpiando fecha: " . $e->getMessage());
        return $fechaTexto;
    }
}

private function cleanTimeL1Universal($horaTexto)
{
    try {
        if (empty($horaTexto)) {
            return $horaTexto;
        }

        // Validar formato H:MM:SS AM/PM
        if (!preg_match('/^(\d{1,2}):(\d{2}):(\d{2})\s*(AM|PM)$/i', $horaTexto, $matches)) {
            return $horaTexto;
        }

        $hora = intval($matches[1]);
        $minuto = intval($matches[2]);
        $segundo = intval($matches[3]);
        $ampm = strtoupper($matches[4]);

        if ($hora < 1 || $hora > 12 || $minuto < 0 || $minuto > 59 || $segundo < 0 || $segundo > 59) {
            error_log("⚠️ Hora inválida: $hora:$minuto:$segundo $ampm");
            return $horaTexto;
        }

        // ✅ MANTENER FORMATO ORIGINAL
        return sprintf('%d:%02d:%02d %s', $hora, $minuto, $segundo, $ampm);

    } catch (Exception $e) {
        error_log("Error limpiando hora: " . $e->getMessage());
        return $horaTexto;
    }
}

private function cleanStatusL1Universal($status, $valorNumerico)
{
    try {
        $status = strtoupper(trim($status));
        $valor = $this->parseNumericValueSafe($valorNumerico);

        // Si NO hay valor numérico válido → INVALID
        if ($valor === null) {
            return 'INVALID';
        }

        // Cualquier valor numérico (incluido 0.0) es VALID
        return 'VALID';

    } catch (Exception $e) {
        error_log("Error en cleanStatusL1Universal: " . $e->getMessage());
        return 'INVALID';
    }
}

private function cleanNumericValueL1Universal($valor, $status)
{
    try {
        $valor = trim($valor);

        // Manejar textos especiales
        if (stripos($valor, 'VALUE NOT AVAILABLE') !== false || 
            stripos($valor, 'INVALID') !== false ||
            empty($valor)) {
            return '0.0';
        }

        // Parsear valor numérico
        $numerico = $this->parseNumericValueSafe($valor);

        if ($numerico === null) {
            return '0.0';
        }

        // ✅ LIMPIAR SEGÚN RANGOS RAZONABLES (pero sin rechazar)
        
        // Temperaturas extremas
        if ($numerico < -100 || $numerico > 100) {
            error_log("⚠️ Temperatura extrema detectada: $numerico - Ajustando a 0.0");
            return '0.0';
        }

        // Humedad fuera de rango
        if ($numerico > 100 && $numerico < 500) { // Probablemente humedad
            return '100.000';
        }

        // Presión extrema
        if ($numerico > 1200) {
            error_log("⚠️ Presión extrema detectada: $numerico - Ajustando a 0.0");
            return '0.0';
        }

        // ✅ MANTENER VALOR CON 3 DECIMALES
        return number_format($numerico, 3, '.', '');

    } catch (Exception $e) {
        error_log("Error limpiando valor numérico: " . $e->getMessage());
        return '0.0';
    }
}

    private function cleanDataLineL1($line, $separadorEntrada, $columnasEsperadas, $separadorSalida = ';')
    {
        try {
            $campos = explode($separadorEntrada, $line);

            // ✅ VALIDAR que tenga las columnas esperadas
            if (count($campos) < $columnasEsperadas) {
                error_log("⚠️ Línea con columnas insuficientes: " . count($campos) . " esperadas: $columnasEsperadas");
                return null;
            }

            $camposLimpios = [];

            for ($i = 0; $i < count($campos); $i++) {
                $valor = trim($campos[$i]);

                // ✅ COLUMNA 0: DATE (Fecha)
                if ($i === 0) {
                    $fechaLimpia = $this->cleanDateL1($valor);
                    $camposLimpios[] = $fechaLimpia;
                    continue;
                }

                // ✅ COLUMNA 1: TIME (Hora)
                if ($i === 1) {
                    $horaLimpia = $this->cleanTimeL1($valor);
                    $camposLimpios[] = $horaLimpia;
                    continue;
                }

                // ✅ COLUMNAS PARES (status): Aplicar reglas de STATUS
                if ($i % 2 === 0 && $i > 1) {
                    // ✅ VERIFICAR que existe el valor siguiente antes de procesar STATUS
                    if (!isset($campos[$i + 1])) {
                        error_log("⚠️ STATUS sin valor en columna $i - OMITIENDO");
                        break; // Salir del loop, no agregar STATUS huérfano
                    }

                    $valorNumerico = trim($campos[$i + 1]);
                    $statusLimpio = $this->cleanStatusL1($valor, $valorNumerico);
                    $camposLimpios[] = $statusLimpio;
                    continue;
                }

                // ✅ COLUMNAS IMPARES (valores numéricos): Aplicar reglas de VALORES
                if ($i % 2 === 1 && $i > 1) {
                    $statusPrevio = isset($camposLimpios[$i - 1]) ? $camposLimpios[$i - 1] : '';
                    $valorLimpio = $this->cleanNumericValueL1($valor, $statusPrevio, $i);
                    $camposLimpios[] = $valorLimpio;
                    continue;
                }
            }

            // ✅ VALIDACIÓN FINAL: Debe terminar en VALOR, no en STATUS
            $ultimoIndice = count($camposLimpios) - 1;

            // Si termina en posición par (después de DATE y TIME), es STATUS huérfano
            if ($ultimoIndice >= 2 && ($ultimoIndice % 2 === 0)) {
                error_log("⚠️ Eliminando STATUS huérfano al final");
                array_pop($camposLimpios); // Eliminar último elemento
            }

            // ✅ UNIR CON EL SEPARADOR DE SALIDA (punto y coma)
            return implode($separadorSalida, $camposLimpios);

        } catch (Exception $e) {
            error_log("Error limpiando línea L1: " . $e->getMessage());
            return null;
        }
    }
    private function cleanDateL1($fechaTexto)
    {
        try {
            if (empty($fechaTexto)) {
                return $fechaTexto;
            }

            // Detectar separador
            $separador = '/';
            if (strpos($fechaTexto, '-') !== false) {
                $separador = '-';
            }

            $partes = explode($separador, $fechaTexto);
            if (count($partes) != 3) {
                return $fechaTexto; // No modificar si no tiene formato válido
            }

            $mes = intval($partes[0]);
            $dia = intval($partes[1]);
            $anio = intval($partes[2]);

            // ✅ REGLA 1: Convertir año corto a año completo
            if ($anio < 100) {
                // Años 14-18 → 2014-2018
                // Años 00-99 → 2000-2099
                $anio = 2000 + $anio;
            }

            // Validar fecha
            if (!checkdate($mes, $dia, $anio)) {
                error_log("❌ Fecha inválida después de conversión: $mes/$dia/$anio");
                return $fechaTexto; // Retornar original si es inválida
            }

            // ✅ FORMATO LIMPIO: M/d/yyyy (SIN ceros iniciales)
            return sprintf('%d/%d/%d', $mes, $dia, $anio);

        } catch (Exception $e) {
            error_log("Error limpiando fecha L1: " . $e->getMessage());
            return $fechaTexto;
        }
    }
    private function cleanTimeL1($horaTexto)
    {
        try {
            if (empty($horaTexto)) {
                return $horaTexto;
            }

            // ✅ REGLA 2: Mantener formato H:MM:SS AM/PM como está
            // Solo validar que sea válido

            // Extraer componentes
            if (!preg_match('/^(\d{1,2}):(\d{2}):(\d{2})\s*(AM|PM)$/i', $horaTexto, $matches)) {
                return $horaTexto; // Si no coincide, mantener original
            }

            $hora = intval($matches[1]);
            $minuto = intval($matches[2]);
            $segundo = intval($matches[3]);
            $ampm = strtoupper($matches[4]);

            // ✅ Validaciones
            if ($hora < 1 || $hora > 12) {
                error_log("⚠️ Hora inválida en formato 12h: $hora");
                return $horaTexto;
            }

            if ($minuto < 0 || $minuto > 59 || $segundo < 0 || $segundo > 59) {
                error_log("⚠️ Minutos/segundos inválidos: $minuto:$segundo");
                return $horaTexto;
            }

            // ✅ MANTENER FORMATO ORIGINAL (sin ceros iniciales en hora)
            return sprintf('%d:%02d:%02d %s', $hora, $minuto, $segundo, $ampm);

        } catch (Exception $e) {
            error_log("Error limpiando hora L1: " . $e->getMessage());
            return $horaTexto;
        }
    }
    private function cleanStatusL1($status, $valorNumerico)
    {
        try {
            $status = strtoupper(trim($status));
            $valor = $this->parseNumericValueSafe($valorNumerico);

            // ✅ Si NO hay valor numérico → INVALID
            if ($valor === null) {
                return 'INVALID';
            }

            // ✅ IMPORTANTE: 0.0 es un valor VÁLIDO (ej: radiación nocturna)
            // Solo rechazar valores que realmente no existen (null)
            return 'VALID';

        } catch (Exception $e) {
            error_log("Error en cleanStatusL1: " . $e->getMessage());
            return 'INVALID';
        }
    }
    private function cleanNumericValueL1($valor, $status, $columnIndex)
    {
        try {
            $valor = trim($valor);

            // ✅ Manejar "INVALID - VALUE NOT AVAILABLE"
            if (stripos($valor, 'VALUE NOT AVAILABLE') !== false) {
                return '0.0';
            }

            // ✅ Si viene texto "INVALID" convertir a 0.0
            if (stripos($valor, 'INVALID') !== false) {
                return '0.0';
            }

            // ✅ Parsear valor numérico
            $numerico = $this->parseNumericValueSafe($valor);

            if ($numerico === null) {
                return '0.0';
            }

            // ✅ PERMITIR 0.0 como valor válido (radiación nocturna, etc.)
            // No rechazar valores en 0, son datos reales

            // ✅ Temperaturas (AVG_TA, AVG_WindChill, TG1-TG7)
            if ($this->isTemperatureColumn($columnIndex) || $this->isSoilTemperatureColumn($columnIndex)) {
                $rangoMin = $this->isSoilTemperatureColumn($columnIndex) ? -50 : -50;
                $rangoMax = $this->isSoilTemperatureColumn($columnIndex) ? 80 : 60;

                if ($numerico < $rangoMin || $numerico > $rangoMax) {
                    error_log("⚠️ Temperatura fuera de rango en col $columnIndex: $numerico");
                    return '0.0';
                }
                return number_format($numerico, 3, '.', '');
            }

            // ✅ Humedad Relativa (AVG_RH)
            if ($this->isHumidityColumn($columnIndex)) {
                if ($numerico < 0) {
                    return '0.0';
                }
                if ($numerico > 100) {
                    return '100.000';
                }
                return number_format($numerico, 3, '.', '');
            }

            // ✅ Presión Atmosférica (PA)
            if ($this->isPressureColumn($columnIndex)) {
                if ($numerico < 700 || $numerico > 800) {
                    error_log("⚠️ Presión fuera de rango en col $columnIndex: $numerico");
                    return '0.0';
                }
                return number_format($numerico, 3, '.', '');
            }

            // ✅ Precipitación (PR)
            if ($this->isPrecipitationColumn($columnIndex)) {
                if ($numerico < 0) {
                    return '0.0';
                }
                return number_format($numerico, 1, '.', '');
            }

            // ✅ Radiación (Difusa y Global) - 0.0 es válido (noche)
            if ($this->isRadiationColumn($columnIndex)) {
                if ($numerico < 0) {
                    return '0.0';
                }
                // ✅ Mantener 0.0 como válido (radiación nocturna)
                return number_format($numerico, 3, '.', '');
            }

            // ✅ Por defecto, mantener 3 decimales
            return number_format($numerico, 3, '.', '');

        } catch (Exception $e) {
            error_log("Error limpiando valor numérico L1: " . $e->getMessage());
            return '0.0';
        }
    }

    private function validateHeaderStructureL1($encabezados, $separador)
    {
        try {
            if (empty($encabezados)) {
                return false;
            }

            error_log("=== VALIDACIÓN DE ESTRUCTURA DE ENCABEZADOS L1 ===");
            error_log("Total columnas: " . count($encabezados));

            // Las primeras 2 columnas deben ser DATE y TIME
            if (count($encabezados) < 4) {
                error_log("❌ Archivo debe tener al menos 4 columnas (DATE, TIME, STATUS, VALOR)");
                return false;
            }

            // Después de DATE y TIME, debe haber pares STATUS-VALOR
            $columnasRestantes = count($encabezados) - 2;

            if ($columnasRestantes % 2 !== 0) {
                error_log("⚠️ ADVERTENCIA: Después de DATE y TIME debe haber número PAR de columnas");
                error_log("    Columnas después de DATE/TIME: $columnasRestantes");
                error_log("    Se esperan pares: STATUS-VALOR, STATUS-VALOR, ...");
            }

            // Mostrar estructura detectada
            error_log("Estructura detectada:");
            error_log("  [0] DATE: " . $encabezados[0]);
            error_log("  [1] TIME: " . $encabezados[1]);

            for ($i = 2; $i < min(10, count($encabezados)); $i++) {
                $tipo = ($i % 2 === 0) ? 'STATUS' : 'VALOR';
                error_log("  [$i] $tipo: " . $encabezados[$i]);
            }

            return true;

        } catch (Exception $e) {
            error_log("Error validando estructura: " . $e->getMessage());
            return false;
        }
    }

    private function isTemperatureColumn($index)
    {
        // Columnas típicas de temperatura: 3 (AVG_TA), última columna típicamente (AVG_WindChill)
        // Ajustar según estructura real
        return in_array($index, [3, 13, 27, 29]); // Ajustar índices según tu archivo
    }

    private function isHumidityColumn($index)
    {
        // Columna típica de humedad: 5 (AVG_RH)
        return in_array($index, [5]);
    }

    private function isPressureColumn($index)
    {
        // Columna típica de presión: 7 (PA)
        return in_array($index, [7]);
    }

    private function isPrecipitationColumn($index)
    {
        // Columna de precipitación: 9 (PR) - solo en archivos L115-L118
        return in_array($index, [9]);
    }

    private function isRadiationColumn($index)
    {
        // Columnas de radiación: 9, 11 (SR difusa y global) o 11, 13 dependiendo de estructura
        return in_array($index, [9, 11, 13]);
    }

    private function isSoilTemperatureColumn($index)
    {
        // Columnas de temperatura de suelo: TG1-TG7 (aproximadamente columnas 13-25)
        return $index >= 13 && $index <= 27 && $index % 2 === 1;
    }

    private function parseNumericValueSafe($value)
    {
        $value = trim($value);

        if ($value === '' || strtoupper($value) === 'NULL' || stripos($value, 'INVALID') !== false) {
            return null;
        }

        // Reemplazar coma por punto
        $value = str_replace(',', '.', $value);

        // Eliminar caracteres no numéricos (excepto punto, signo negativo)
        $value = preg_replace('/[^0-9\.\-]/', '', $value);

        if ($value === '' || $value === '.' || $value === '-') {
            return null;
        }

        $numericValue = floatval($value);

        if (is_nan($numericValue) || !is_finite($numericValue)) {
            return null;
        }

        return $numericValue;
    }



}

// Ejecutar controlador
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new CLoadDataL1();
    $controller->handleRequest();
}
?>