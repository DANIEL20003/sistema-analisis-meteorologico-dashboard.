<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', '0');
ob_start();

require_once('../config/database.php');
require_once('../model/MLoadDataL0.php');

class CLoadDataL0
{
    private $db;
    private $model;
    private $database;

    public function __construct()
    {
        $this->database = new Database();
        $this->db = $this->database->getConnection();
        $this->model = new MLoadDataL0($this->db);
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
                'L0'
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
                return ['valid' => false, 'message' => 'El archivo debe tener al menos 2 líneas de encabezado'];
            }

            // ✅ Obtener segunda línea (encabezados de columnas)
            $headerLine = strtolower(trim($lines[1]));

            error_log("=== VALIDACIÓN DE ENCABEZADOS ===");
            error_log("Línea 2: $headerLine");

            // ✅ Palabras clave que deben estar presentes
            $palabrasClave = [
                'direccion' => ['dir', 'direccion', 'direction', 'diravg', 'winddir'],
                'velocidad' => ['spd', 'velocidad', 'speed', 'spdavg', 'windspeed', 'vel']
            ];

            $tieneDireccion = false;
            $tieneVelocidad = false;

            // Buscar palabras relacionadas con dirección
            foreach ($palabrasClave['direccion'] as $palabra) {
                if (strpos($headerLine, $palabra) !== false) {
                    $tieneDireccion = true;
                    error_log("✓ Encontrada palabra de dirección: $palabra");
                    break;
                }
            }

            // Buscar palabras relacionadas con velocidad
            foreach ($palabrasClave['velocidad'] as $palabra) {
                if (strpos($headerLine, $palabra) !== false) {
                    $tieneVelocidad = true;
                    error_log("✓ Encontrada palabra de velocidad: $palabra");
                    break;
                }
            }

            // ✅ Validar que tenga ambas
            if (!$tieneDireccion) {
                return ['valid' => false, 'message' => 'El archivo no contiene columna de dirección del viento'];
            }

            if (!$tieneVelocidad) {
                return ['valid' => false, 'message' => 'El archivo no contiene columna de velocidad del viento'];
            }

            error_log("✓ Encabezados válidos");
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

            error_log("=== VALIDACIÓN DE CALIDAD DE DATOS ===");

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
                    strpos($lineLower, 'status') !== false
                ) {
                    continue;
                }

                $campos = array_map('trim', explode($separador, $line));

                if (count($campos) < 4)
                    continue;

                $totalLineasDatos++;

                // Buscar valores numéricos válidos
                $tieneValorValido = false;

                foreach ($campos as $valor) {
                    if (is_numeric($valor)) {
                        $num = floatval($valor);

                        // Dirección válida (mayor a 0 y menor a 360, excluyendo 0 y 360)
                        if ($num > 0.0 && $num < 360.0) {
                            $tieneValorValido = true;
                            break;
                        }

                        // Velocidad válida (mayor a 0 y menor a 50)
                        if ($num > 0.0 && $num < 50.0) {
                            $tieneValorValido = true;
                            break;
                        }
                    }
                }

                if ($tieneValorValido) {
                    $lineasConDatosValidos++;
                }
            }

            error_log("📊 RESULTADO VALIDACIÓN:");
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
                    'message' => 'El archivo NO contiene datos válidos. Todos los registros tienen valores 0.0 o 360.0 (INVALID).'
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
        set_time_limit(0);
        ini_set('max_execution_time', '0');
        ini_set('memory_limit', '1024M');
        $tempFilePath = null;

        try {
            $idCarga = $_POST['id_carga'] ?? '';
            $batchSize = $_POST['batch_size'] ?? 1000;

            if (empty($idCarga)) {
                throw new Exception('ID de carga no proporcionado');
            }

            $cargaInfo = $this->model->getCargaArchivoInfo($idCarga);
            if (!$cargaInfo) {
                throw new Exception('Información de carga no encontrada');
            }

            $tempFilePath = $this->model->getTempFilePath($idCarga);
            if (!$tempFilePath || !file_exists($tempFilePath)) {
                throw new Exception('Archivo temporal no encontrado');
            }

            $this->model->updateCargaArchivoEstado($idCarga, 'PROCESANDO');

            $estacionInfo = $this->model->obtenerEstacionPorId($cargaInfo['id_estacion']);

            // Procesar archivo CSV
            $resultado = $this->processCSVFile($tempFilePath, $estacionInfo, $idCarga);

            if ($resultado['success']) {
                // ✅ PRIMERO: Generar archivo LIMPIO (mientras tempFilePath existe)
                $cleanFilePath = $this->generateCleanFile($tempFilePath, $estacionInfo, $resultado['fecha_inicio'], $resultado['fecha_fin']);

                // ✅ SEGUNDO: Renombrar y mover archivo CRUDO
                $finalPath = $this->renameAndSaveFile($tempFilePath, $estacionInfo, $resultado['fecha_inicio'], $resultado['fecha_fin']);

                // ✅ TERCERO: Actualizar base de datos
                $this->model->updateCargaArchivoFinalWithOriginalName($idCarga, basename($finalPath), $resultado['registros_validos'], basename($finalPath));
                $this->model->updateCargaArchivoEstado($idCarga, 'COMPLETADO');

                // ✅ CUARTO: Eliminar archivo temporal (ahora sí)
                if (file_exists($tempFilePath)) {
                    unlink($tempFilePath);
                    error_log("✓ Archivo temporal eliminado exitosamente: $tempFilePath");
                }

                // ✅✅✅ NUEVO: REFRESCAR VISTAS MATERIALIZADAS ✅✅✅
                try {
                    error_log("🔄 Refrescando vistas materializadas después de carga L0...");

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
                        'archivo_limpio' => basename($cleanFilePath),
                        'advertencias' => $resultado['advertencias'] ?? []
                    ]
                ]);
            } else {
                throw new Exception($resultado['message']);
            }

        } catch (Exception $e) {
            if (isset($idCarga) && !empty($idCarga)) {
                $this->model->updateCargaArchivoEstado($idCarga, 'ERROR');
            }

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


    private function generateCleanFile($tempFilePath, $estacionInfo, $fechaInicio, $fechaFin)
    {
        try {
            error_log("=== GENERACIÓN DE ARCHIVO LIMPIO ===");

            // Detectar columnas del archivo original
            $deteccion = $this->detectarColumnas($tempFilePath);

            if (!$deteccion) {
                throw new Exception('No se pudieron detectar columnas para limpieza');
            }

            $separadorOriginal = $deteccion['separador'];

            // ✅ NUEVO: Logging del separador detectado
            error_log("📌 Separador original detectado: '$separadorOriginal'");

            // Crear directorio de destino: uploads/{NOMBRE_ESTACION}/Limpios/L0/
            $nombreEstacion = preg_replace('/[^A-Za-z0-9_\-]/', '_', $estacionInfo['nombre']);
            $baseDir = '../uploads/';
            $estacionDir = $baseDir . $nombreEstacion . '/';
            $limpiosDir = $estacionDir . 'Limpios/';
            $l0LimpiosDir = $limpiosDir . 'L0/';

            if (!is_dir($l0LimpiosDir)) {
                mkdir($l0LimpiosDir, 0755, true);
            }

            // Generar nombre del archivo limpio
            $fechaInicioFormato = date('d-m-Y', strtotime($fechaInicio));
            $fechaFinFormato = date('d-m-Y', strtotime($fechaFin));
            $cleanFileName = "{$nombreEstacion}_L0_{$fechaInicioFormato}_{$fechaFinFormato}_Limpios.csv";
            $cleanFilePath = $l0LimpiosDir . $cleanFileName;

            // Abrir archivo de entrada y salida
            $handleInput = fopen($tempFilePath, 'r');
            if (!$handleInput) {
                throw new Exception('No se pudo abrir archivo de entrada');
            }

            $handleOutput = fopen($cleanFilePath, 'w');
            if (!$handleOutput) {
                fclose($handleInput);
                throw new Exception('No se pudo crear archivo limpio');
            }

            // ✅ SIEMPRE escribir encabezados con punto y coma
            $headerLine1 = ";GenWind_30s_SDI12;GenWind_30s_SDI12\n";
            $headerLine2 = "time; ;status;DirAvg;status;SpdAvg\n";
            fwrite($handleOutput, $headerLine1);
            fwrite($handleOutput, $headerLine2);

            $lineasLimpias = 0;
            $lineasRechazadas = 0;

            // Procesar cada línea
            while (($line = fgets($handleInput)) !== false) {
                $line = trim($line);

                if (empty($line))
                    continue;

                $lineLower = strtolower($line);

                // Saltar encabezados originales
                if (
                    strpos($lineLower, 'genwind') !== false ||
                    strpos($lineLower, 'time') !== false ||
                    strpos($lineLower, 'date') !== false ||
                    strpos($lineLower, 'status') !== false
                ) {
                    continue;
                }

                // Limpiar y validar línea (cleanDataLine ya retorna con ;)
                $lineaLimpia = $this->cleanDataLine($line, $deteccion);

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
            error_log("   Separador usado: punto y coma (;)");
            error_log("   Líneas limpias: $lineasLimpias");
            error_log("   Líneas rechazadas: $lineasRechazadas");

            return $cleanFilePath;

        } catch (Exception $e) {
            error_log("❌ Error generando archivo limpio: " . $e->getMessage());
            throw new Exception('Error generando archivo limpio: ' . $e->getMessage());
        }
    }
    private function cleanDataLine($line, $deteccion)
    {
        try {
            $separador = $deteccion['separador'];
            $colFecha = $deteccion['colFecha'];
            $colHora = $deteccion['colHora'];
            $colAmPm = $deteccion['colAmPm'];
            $colDireccion = $deteccion['colDireccion'];
            $colVelocidad = $deteccion['colVelocidad'];

            $campos = array_map('trim', explode($separador, $line));

            // Validar que existen todas las columnas necesarias
            $maxCol = max($colFecha, $colHora, $colDireccion, $colVelocidad);
            if ($colAmPm !== null) {
                $maxCol = max($maxCol, $colAmPm);
            }

            if (count($campos) <= $maxCol) {
                return null;
            }

            // Extraer valores originales
            $fechaTexto = $campos[$colFecha];
            $horaTexto = $campos[$colHora];
            $ampm = '';

            // Manejar AM/PM
            if ($deteccion['horaIncluyeAmPm']) {
                if (preg_match('/^(.+?)\s*(AM|PM)$/i', $horaTexto, $matches)) {
                    $horaTexto = trim($matches[1]);
                    $ampm = strtoupper($matches[2]);
                }
            } else if ($colAmPm !== null) {
                $ampm = strtoupper(trim($campos[$colAmPm]));
            }

            $velocidadTexto = $campos[$colVelocidad];
            $direccionTexto = $campos[$colDireccion];

            // REGLA 1: Normalizar fecha a formato completo (MM/DD/YYYY)
            $fechaLimpia = $this->normalizeDateFormat($fechaTexto);
            if (!$fechaLimpia) {
                return null;
            }

            // REGLA 2: Normalizar hora (H:MM:SS AM/PM)
            $horaLimpia = $this->normalizeTimeFormat($horaTexto, $ampm);
            if (!$horaLimpia) {
                return null;
            }

            // REGLA 3 y 4: Validar y normalizar dirección (0-360)
            $direccion = $this->parseNumericValue($direccionTexto);
            if ($direccion < 0 || $direccion > 360) {
                $direccion = 0;
            }
            $direccion = intval(round($direccion)); // Convertir a entero

            // REGLA 3 y 4: Validar y normalizar velocidad (≥ 0)
            $velocidad = $this->parseNumericValue($velocidadTexto);
            if ($velocidad < 0) {
                $velocidad = 0;
            }
            $velocidad = number_format($velocidad, 3, '.', ''); // 3 decimales

            // REGLA 4: Coherencia Status-Valor
            $statusDireccion = 'VALID';
            $statusVelocidad = 'VALID';

            if ($velocidad == 0.0 || $velocidad == '0.000') {
                $statusVelocidad = 'INVALID';
                $statusDireccion = 'INVALID'; // Si velocidad es 0, ambos son INVALID
            } else if ($direccion == 0 || $direccion == 360) {
                $statusDireccion = 'INVALID';
            }

            // REGLA 2: Unificar separador a punto y coma (;)
            // Formato: MM/DD/YYYY;H:MM:SS AM/PM;status;DirAvg;status;SpdAvg
            $lineaLimpia = sprintf(
                "%s;%s;%s;%d;%s;%s",
                $fechaLimpia,
                $horaLimpia,
                $statusDireccion,
                $direccion,
                $statusVelocidad,
                $velocidad
            );

            return $lineaLimpia;

        } catch (Exception $e) {
            error_log("Error limpiando línea: " . $e->getMessage());
            return null;
        }
    }
    private function normalizeDateFormat($fechaTexto)
    {
        try {
            $separador = '/';
            if (strpos($fechaTexto, '-') !== false) {
                $separador = '-';
            }

            $partes = explode($separador, $fechaTexto);
            if (count($partes) != 3) {
                return null;
            }

            // MM/DD/YY o MM/DD/YYYY
            $mes = intval($partes[0]);
            $dia = intval($partes[1]);
            $anio = intval($partes[2]);

            // REGLA 1: Convertir año de 2 dígitos a 4 dígitos
            if ($anio < 100) {
                // Asumimos que años 00-50 son 2000-2050, y 51-99 son 1951-1999
                if ($anio <= 50) {
                    $anio = 2000 + $anio;
                } else {
                    $anio = 1900 + $anio;
                }
            }

            // Validar fecha
            if (!checkdate($mes, $dia, $anio)) {
                error_log("❌ Fecha inválida: $mes/$dia/$anio");
                return null;
            }

            // Retornar formato MM/DD/YYYY
            return sprintf('%d/%d/%d', $mes, $dia, $anio);

        } catch (Exception $e) {
            return null;
        }
    }

    private function normalizeTimeFormat($horaTexto, $ampm = '')
    {
        try {
            $partes = explode(':', $horaTexto);
            if (count($partes) < 2) {
                return null;
            }

            $hora = intval($partes[0]);
            $minuto = intval($partes[1]);
            $segundo = isset($partes[2]) ? intval($partes[2]) : 0;

            // Validar rangos
            if ($hora < 0 || $hora > 23 || $minuto < 0 || $minuto > 59 || $segundo < 0 || $segundo > 59) {
                return null;
            }

            // Convertir formato 24h a 12h con AM/PM
            if (empty($ampm)) {
                // Si no hay AM/PM, convertir desde formato 24h
                if ($hora == 0) {
                    $hora12 = 12;
                    $ampm = 'AM';
                } else if ($hora < 12) {
                    $hora12 = $hora;
                    $ampm = 'AM';
                } else if ($hora == 12) {
                    $hora12 = 12;
                    $ampm = 'PM';
                } else {
                    $hora12 = $hora - 12;
                    $ampm = 'PM';
                }
            } else {
                // Ya tiene AM/PM, mantener hora original
                $hora12 = $hora;
                if ($hora12 == 0) {
                    $hora12 = 12;
                }
            }

            // Formato: H:MM:SS AM/PM (sin cero inicial en hora si es < 10)
            return sprintf('%d:%02d:%02d %s', $hora12, $minuto, $segundo, $ampm);

        } catch (Exception $e) {
            return null;
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
                    strpos($lineLower, 'status') !== false && strpos($lineLower, 'spdavg') !== false
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

            error_log("=== DETECCIÓN INTELIGENTE ===");
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
                    (strpos($lineLower, 'date') !== false && strpos($lineLower, 'status') !== false)
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
                if (count($campos) >= 5) { // Al menos 5 columnas
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

        // Puntuaciones
        $puntuacionFecha = array_fill(0, $totalColumnas, 0);
        $puntuacionHora = array_fill(0, $totalColumnas, 0);
        $puntuacionAmPm = array_fill(0, $totalColumnas, 0);
        $puntuacionDireccion = array_fill(0, $totalColumnas, 0);
        $puntuacionVelocidad = array_fill(0, $totalColumnas, 0);

        foreach ($lineas as $index => $linea) {
            $campos = array_map('trim', explode($separador, $linea));

            if ($index == 0) {
                error_log("Primera línea analizada:");
                for ($i = 0; $i < min(10, count($campos)); $i++) {
                    error_log("  Col[$i] = '{$campos[$i]}'");
                }
            }

            foreach ($campos as $colIndex => $valor) {
                if (empty($valor))
                    continue;

                // ✅ FECHA (solo fecha, sin hora)
                if (preg_match('/^\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4}$/', $valor)) {
                    $puntuacionFecha[$colIndex] += 5;
                }

                // ✅ HORA CON AM/PM INCLUIDO (formato: "12:00:11 AM")
                if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?\s*(AM|PM)$/i', $valor)) {
                    $puntuacionHora[$colIndex] += 10;
                    $puntuacionAmPm[$colIndex] += 10;
                }
                // ✅ HORA SIN AM/PM (formato: "6:41:37")
                else if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $valor)) {
                    $puntuacionHora[$colIndex] += 3;
                }

                // ✅ AM/PM SEPARADO
                if (strtoupper($valor) == 'AM' || strtoupper($valor) == 'PM') {
                    $puntuacionAmPm[$colIndex] += 5;
                }

                // ✅ VALORES NUMÉRICOS
                if (is_numeric($valor)) {
                    $num = floatval($valor);
                    $partes = explode('.', $valor);
                    $decimales = isset($partes[1]) ? strlen($partes[1]) : 0;

                    // ✅ DIRECCIÓN: 0-360
                    if ($num >= 0 && $num <= 360) {
                        // Valores típicos de dirección
                        if ($num == 360 || $num == 0) {
                            $puntuacionDireccion[$colIndex] += 1; // Menor puntuación para 0/360
                        } else if ($num > 50 && $num < 360) {
                            $puntuacionDireccion[$colIndex] += 5; // Alta probabilidad
                        } else if ($num >= 1 && $num <= 359) {
                            $puntuacionDireccion[$colIndex] += 3;
                        }
                    }

                    // ✅ VELOCIDAD: 0-50, AHORA ACEPTA VALORES MUY PEQUEÑOS
                    if ($num >= 0 && $num <= 50) {
                        // Velocidades con decimales (0.001 - 50)
                        if ($decimales >= 1 && $decimales <= 4) {
                            $puntuacionVelocidad[$colIndex] += 5;
                        }
                        // Cualquier valor pequeño también cuenta
                        else if ($num > 0 && $num <= 15) {
                            $puntuacionVelocidad[$colIndex] += 3;
                        }
                        // Incluso 0 puede ser velocidad
                        else if ($num == 0) {
                            $puntuacionVelocidad[$colIndex] += 1;
                        }
                    }
                }
            }
        }

        // ✅ DETERMINAR COLUMNAS
        $colFecha = array_search(max($puntuacionFecha), $puntuacionFecha);
        $colHora = array_search(max($puntuacionHora), $puntuacionHora);

        // ✅ DETECTAR SI AM/PM ESTÁ EN LA MISMA COLUMNA
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

        // ✅ DIRECCIÓN (excluir fecha, hora, AM/PM)
        $colDireccion = null;
        $maxPuntajeDireccion = 0;
        foreach ($puntuacionDireccion as $col => $puntaje) {
            if ($col == $colFecha || $col == $colHora || $col == $colAmPm)
                continue;
            if ($puntaje > $maxPuntajeDireccion) {
                $maxPuntajeDireccion = $puntaje;
                $colDireccion = $col;
            }
        }

        // ✅ VELOCIDAD (excluir fecha, hora, AM/PM, dirección)
        $colVelocidad = null;
        $maxPuntajeVelocidad = 0;
        foreach ($puntuacionVelocidad as $col => $puntaje) {
            if ($col == $colFecha || $col == $colHora || $col == $colAmPm || $col == $colDireccion)
                continue;
            if ($puntaje > $maxPuntajeVelocidad) {
                $maxPuntajeVelocidad = $puntaje;
                $colVelocidad = $col;
            }
        }

        // ✅ LOGS DETALLADOS
        error_log("📊 PUNTUACIONES:");
        error_log("  Fecha: " . json_encode($puntuacionFecha));
        error_log("  Hora: " . json_encode($puntuacionHora));
        error_log("  Dirección: " . json_encode($puntuacionDireccion));
        error_log("  Velocidad: " . json_encode($puntuacionVelocidad));

        error_log("📊 RESULTADO DETECCIÓN:");
        error_log("  Separador: '$separador'");
        error_log("  Fecha: Col[$colFecha] = " . ($colFecha !== false ? $puntuacionFecha[$colFecha] : 0) . " pts");
        error_log("  Hora: Col[$colHora] = " . ($colHora !== false ? $puntuacionHora[$colHora] : 0) . " pts " . ($horaIncluyeAmPm ? "(incluye AM/PM)" : ""));
        if ($colAmPm !== null) {
            error_log("  AM/PM: Col[$colAmPm] = {$puntuacionAmPm[$colAmPm]} pts");
        }
        error_log("  Dirección: Col[" . ($colDireccion ?? 'null') . "] = $maxPuntajeDireccion pts");
        error_log("  Velocidad: Col[" . ($colVelocidad ?? 'null') . "] = $maxPuntajeVelocidad pts");

        // ✅ VALIDAR DETECCIÓN MÍNIMA
        if ($colFecha === false || $colHora === false) {
            error_log("❌ No se detectaron columnas de fecha/hora");
            return null;
        }

        // ✅ FALLBACK: Si no se detectaron dirección/velocidad, usar posiciones por defecto
        if ($colDireccion === null || $colVelocidad === null) {
            error_log("⚠️ Aplicando fallback por posición...");

            if ($separador == ';') {
                if ($totalColumnas >= 7) {
                    $colVelocidad = 4; // Posición típica
                    $colDireccion = 6;
                }
            } else if ($separador == ',') {
                if ($totalColumnas >= 6) {
                    $colDireccion = 3;
                    $colVelocidad = 5;
                }
            }

            error_log("  Fallback → Dirección: Col[$colDireccion], Velocidad: Col[$colVelocidad]");
        }

        // ✅ VALIDAR RESULTADO FINAL
        if ($colDireccion === null || $colVelocidad === null) {
            error_log("❌ Detección fallida incluso con fallback");
            return null;
        }

        error_log("✅ COLUMNAS DETECTADAS EXITOSAMENTE");

        return [
            'separador' => $separador,
            'colFecha' => $colFecha,
            'colHora' => $colHora,
            'colAmPm' => $colAmPm,
            'horaIncluyeAmPm' => $horaIncluyeAmPm,
            'colDireccion' => $colDireccion,
            'colVelocidad' => $colVelocidad
        ];
    }


    private function lineaTieneValoresReales($line, $separador)
    {
        $campos = array_map('trim', explode($separador, $line));

        $tieneValorNumerico = false;
        $tieneValorSignificativo = false;

        foreach ($campos as $valor) {
            if (is_numeric($valor)) {
                $num = floatval($valor);

                $tieneValorNumerico = true;

                // ✅ VELOCIDAD: Cualquier valor > 0 y < 50
                if ($num > 0 && $num < 50) {
                    $tieneValorSignificativo = true;
                    break;
                }

                // ✅ DIRECCIÓN: Entre 1 y 359 (excluyendo 0 y 360 exactos)
                if ($num > 1 && $num < 359) {
                    $tieneValorSignificativo = true;
                    break;
                }
            }
        }

        return $tieneValorNumerico || $tieneValorSignificativo;
    }


    private function leerLineasDesdeElFinal($filePath, $separador, $maxLineas = 1000)
    {
        $lineas = [];

        try {
            $file = new SplFileObject($filePath, 'r');
            $file->seek(PHP_INT_MAX);
            $totalLineas = $file->key();

            error_log("Total líneas en archivo: " . number_format($totalLineas));

            $inicioLectura = max(0, $totalLineas - $maxLineas);
            $lineasLeidas = 0;

            for ($i = $totalLineas; $i >= $inicioLectura && $lineasLeidas < $maxLineas; $i--) {
                try {
                    $file->seek($i);
                    $line = trim($file->current());

                    if (empty($line))
                        continue;

                    $lineLower = strtolower($line);

                    // Saltar encabezados
                    if (
                        strpos($lineLower, 'genwind') !== false ||
                        strpos($lineLower, 'time') !== false ||
                        strpos($lineLower, 'date') !== false
                    ) {
                        continue;
                    }

                    $lineas[] = $line;
                    $lineasLeidas++;

                } catch (Exception $e) {
                    continue;
                }
            }

            error_log("✓ Leídas " . count($lineas) . " líneas desde el final");

            return $lineas;

        } catch (Exception $e) {
            error_log("Error leyendo desde el final: " . $e->getMessage());
            return [];
        }
    }


    private function detectarRangosSinDatos($filePath)
    {
        try {
            $deteccion = $this->detectarColumnas($filePath);

            if (!$deteccion) {
                return [];
            }

            $separador = $deteccion['separador'];
            $colFecha = $deteccion['colFecha'];
            $colHora = $deteccion['colHora'];
            $colAmPm = $deteccion['colAmPm'];
            $colDireccion = $deteccion['colDireccion'];
            $colVelocidad = $deteccion['colVelocidad'];

            $handle = fopen($filePath, 'r');
            if (!$handle) {
                return [];
            }

            $rangosSinDatos = [];
            $rangoActual = null;
            $lineNumber = 0;

            // ✅ MUESTREAR: Revisar 1 de cada 10 líneas (10% del archivo)
            $sampleRate = 10;
            $linesSampled = 0;
            $maxSamples = 10000; // Máximo 10,000 líneas muestreadas

            while (($line = fgets($handle)) !== false) {
                $lineNumber++;

                // ✅ Solo procesar 1 de cada 10 líneas
                if ($lineNumber % $sampleRate !== 0) {
                    continue;
                }

                $linesSampled++;

                // ✅ Detener después de 10,000 muestras
                if ($linesSampled >= $maxSamples) {
                    break;
                }

                $line = trim($line);

                if (empty($line))
                    continue;

                $lineLower = strtolower($line);

                // Saltar encabezados
                if (
                    strpos($lineLower, 'genwind') !== false ||
                    strpos($lineLower, 'time') !== false ||
                    strpos($lineLower, 'date') !== false ||
                    strpos($lineLower, 'status') !== false
                ) {
                    continue;
                }

                $campos = array_map('trim', explode($separador, $line));

                $maxCol = max($colFecha, $colHora, $colDireccion, $colVelocidad);
                if ($colAmPm !== null) {
                    $maxCol = max($maxCol, $colAmPm);
                }

                if (count($campos) <= $maxCol) {
                    continue;
                }

                // Extraer datos
                $fechaTexto = $campos[$colFecha];
                $horaTexto = $campos[$colHora];
                $ampm = ($colAmPm !== null) ? $campos[$colAmPm] : '';
                $velocidadTexto = $campos[$colVelocidad];
                $direccionTexto = $campos[$colDireccion];

                // Parsear fecha/hora
                $resultado = $this->parseDateTimeComplete($fechaTexto, $horaTexto, $ampm);

                if (!$resultado) {
                    continue;
                }

                $fechaHora = $resultado['fechahora'];

                // Parsear valores
                $velocidad = $this->parseNumericValue($velocidadTexto);
                $direccion = $this->parseNumericValue($direccionTexto);

                // ✅ DETECTAR SI ES "SIN DATOS"
                $esSinDatos = false;

                if ($velocidad == 0 && $direccion == 0) {
                    $esSinDatos = true;
                } else if ($velocidad == 0 && $direccion == 360) {
                    $esSinDatos = true;
                } else if ($velocidad < 0.1 && ($direccion == 0 || $direccion == 360)) {
                    $esSinDatos = true;
                }

                // ✅ CONSTRUIR RANGOS
                if ($esSinDatos) {
                    if ($rangoActual === null) {
                        $rangoActual = [
                            'inicio' => $fechaHora,
                            'fin' => $fechaHora,
                            'total' => $sampleRate // Multiplicar por tasa de muestreo
                        ];
                    } else {
                        $rangoActual['fin'] = $fechaHora;
                        $rangoActual['total'] += $sampleRate;
                    }
                } else {
                    if ($rangoActual !== null && $rangoActual['total'] >= 100) {
                        $rangosSinDatos[] = $rangoActual;
                    }
                    $rangoActual = null;
                }
            }

            // Cerrar último rango
            if ($rangoActual !== null && $rangoActual['total'] >= 100) {
                $rangosSinDatos[] = $rangoActual;
            }

            fclose($handle);

            error_log("📊 Rangos sin datos detectados (muestreo 1/$sampleRate): " . count($rangosSinDatos));

            return $rangosSinDatos;

        } catch (Exception $e) {
            error_log("Error detectando rangos sin datos: " . $e->getMessage());
            return [];
        }
    }


    private function processCSVFile($filePath, $estacionInfo, $idCarga)
    {
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

            // ✅ DETECTAR COLUMNAS ANALIZANDO DATOS REALES
            $deteccion = $this->detectarColumnas($filePath);

            if (!$deteccion) {
                throw new Exception('No se pudieron detectar las columnas automáticamente');
            }

            $separador = $deteccion['separador'];
            $colFecha = $deteccion['colFecha'];
            $colHora = $deteccion['colHora'];
            $colAmPm = $deteccion['colAmPm'];
            $colDireccion = $deteccion['colDireccion'];
            $colVelocidad = $deteccion['colVelocidad'];

            error_log("=== PROCESAMIENTO CON COLUMNAS DETECTADAS ===");

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
                    strpos($lineLower, 'status') !== false
                ) {
                    continue;
                }

                $registrosProcesados++;

                $campos = array_map('trim', explode($separador, $line));

                // Validar que existen las columnas
                $maxCol = max($colFecha, $colHora, $colDireccion, $colVelocidad);
                if ($colAmPm !== null) {
                    $maxCol = max($maxCol, $colAmPm);
                }

                if (count($campos) <= $maxCol) {
                    $registrosRechazados++;
                    continue;
                }

                // ✅ EXTRAER DATOS SEGÚN DETECCIÓN
                $fechaTexto = $campos[$colFecha];
                $horaTexto = $campos[$colHora];

                // ✅ MANEJAR AM/PM (puede estar en la hora o separado)
                $ampm = '';
                if ($deteccion['horaIncluyeAmPm']) {
                    // AM/PM está en la misma columna (ej: "12:00:11 AM")
                    // Extraer AM/PM y limpiar hora
                    if (preg_match('/^(.+?)\s*(AM|PM)$/i', $horaTexto, $matches)) {
                        $horaTexto = trim($matches[1]);
                        $ampm = strtoupper($matches[2]);
                    }
                } else if ($colAmPm !== null) {
                    // AM/PM está en columna separada
                    $ampm = $campos[$colAmPm];
                }
                $velocidadTexto = $campos[$colVelocidad];
                $direccionTexto = $campos[$colDireccion];

                if ($registrosProcesados <= 3) {
                    error_log("Línea $lineNumber: F='$fechaTexto', H='$horaTexto', AP='$ampm', V='$velocidadTexto', D='$direccionTexto'");
                }

                // Parsear fecha/hora
                $resultado = $this->parseDateTimeComplete($fechaTexto, $horaTexto, $ampm);

                if (!$resultado) {
                    $registrosRechazados++;
                    continue;
                }

                $fecha = $resultado['fecha'];
                $hora = $resultado['hora'];
                $fechaHoraCompleta = $resultado['fechahora'];

                // Parsear valores
                $velocidad_viento = $this->parseNumericValue($velocidadTexto);
                $direccion_viento = $this->parseNumericValue($direccionTexto);

                // Ajustar rangos
                if ($velocidad_viento < 0 || $velocidad_viento > 50) {
                    $velocidad_viento = 0;
                }
                if ($direccion_viento < 0 || $direccion_viento > 360) {
                    $direccion_viento = 0;
                }

                // Rastrear años
                $anioActual = intval(substr($fecha, 0, 4));
                if ($anioMinimo === null || $anioActual < $anioMinimo) {
                    $anioMinimo = $anioActual;
                }
                if ($anioMaximo === null || $anioActual > $anioMaximo) {
                    $anioMaximo = $anioActual;
                }

                // Agregar al lote
                $loteActual[] = [
                    'fecha_hora' => $fechaHoraCompleta,
                    'fecha' => $fecha,
                    'hora' => $hora,
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
                    $this->model->insertL0Data($idCarga, $loteActual);

                    $porcentaje = round(($registrosValidos / $registrosProcesados) * 100, 1);
                    error_log("✓ $registrosValidos/$registrosProcesados ($porcentaje%)");

                    $loteActual = [];
                    gc_collect_cycles();
                }
            }

            fclose($handle);

            // Insertar último lote
            if (!empty($loteActual)) {
                $this->model->insertL0Data($idCarga, $loteActual);
            }

            if ($registrosValidos == 0) {
                throw new Exception('No se encontraron datos válidos');
            }

            // ✅ INSERTAR ÚLTIMO LOTE
            if (!empty($loteActual)) {
                $this->model->insertL0Data($idCarga, $loteActual);
                error_log("✓ Lote final: " . count($loteActual) . " registros");
            }

            error_log("✅ VÁLIDOS: $registrosValidos | RECHAZADOS: $registrosRechazados");



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

    private function readFileWithEncodingDetection($filePath)
    {
        $content = file_get_contents($filePath);
        $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);

        if ($encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        }

        return $content;
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


    private $formatoFechaCache = null;



    private function parseNumericValue($value)
    {
        $value = trim($value);

        // ✅ Si está vacío o es NULL, retornar 0
        if ($value === '' || strtoupper($value) === 'NULL' || strtoupper($value) === 'INVALID' || strtoupper($value) === 'VALID') {
            return 0;
        }

        // Reemplazar coma por punto
        $value = str_replace(',', '.', $value);

        // Eliminar caracteres no numéricos
        $value = preg_replace('/[^0-9\.\-+]/', '', $value);

        if ($value === '' || $value === '.' || $value === '+' || $value === '-') {
            return 0;
        }

        $numericValue = floatval($value);

        if (is_nan($numericValue) || !is_finite($numericValue)) {
            return 0;
        }

        return $numericValue;
    }


    private function validateL0Data($velocidad, $direccion)
    {
        // ✅ Permitir 0 como valor válido
        if ($velocidad < 0 || $velocidad > 50) {
            return false;
        }

        if ($direccion < 0 || $direccion > 360) {
            return false;
        }

        return true;
    }

    private function renameAndSaveFile($tempFilePath, $estacionInfo, $fechaInicio, $fechaFin)
    {
        // Obtener nombre de la estación
        $nombreEstacion = $estacionInfo['nombre'] ?? 'UNKNOWN';

        // Sanitizar el nombre de la estación (espacios y caracteres especiales)
        $nombreEstacion = preg_replace('/[^A-Za-z0-9_\-]/', '_', $nombreEstacion);

        // Construir ruta: uploads/{NOMBRE_ESTACION}/Crudos/L0/
        $baseDir = '../uploads/';
        $estacionDir = $baseDir . $nombreEstacion . '/';
        $crudosDir = $estacionDir . 'Crudos/';
        $l0Dir = $crudosDir . 'L0/';

        // Crear directorios si no existen
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0755, true);
        }
        if (!is_dir($estacionDir)) {
            mkdir($estacionDir, 0755, true);
        }
        if (!is_dir($crudosDir)) {
            mkdir($crudosDir, 0755, true);
        }
        if (!is_dir($l0Dir)) {
            mkdir($l0Dir, 0755, true);
        }

        // Formato fecha: DD-MM-YYYY
        $fechaInicioFormato = date('d-m-Y', strtotime($fechaInicio));
        $fechaFinFormato = date('d-m-Y', strtotime($fechaFin));

        // NOMBRE: NOMBRE_ESTACION_L0_FECHA-INICIO_FECHA-FIN.csv
        $newFileName = "{$nombreEstacion}_L0_{$fechaInicioFormato}_{$fechaFinFormato}_Crudos.csv";

        // Sanitizar nombre del archivo
        $newFileName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $newFileName);
        $finalPath = $l0Dir . $newFileName;

        // Si ya existe, agregar timestamp
        if (file_exists($finalPath)) {
            $timestamp = time();
            $newFileName = "{$nombreEstacion}_L0_{$fechaInicioFormato}_{$fechaFinFormato}_{$timestamp}_Crudos.csv";
            $finalPath = $l0Dir . $newFileName;
        }

        // Mover archivo temporal a ubicación final
        if (!rename($tempFilePath, $finalPath)) {
            throw new Exception('Error al mover archivo a ubicación final');
        }

        error_log("✅ Archivo crudo L0 guardado en: $finalPath");
        return $finalPath;
    }



}

// Ejecutar controlador
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new CLoadDataL0();
    $controller->handleRequest();
}
?>