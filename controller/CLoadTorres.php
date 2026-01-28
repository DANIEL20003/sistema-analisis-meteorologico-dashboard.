<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', '0');
ob_start();

require_once('../config/database.php');
require_once('../model/MLoadTorres.php');

class CLoadTorres
{
    private $db;
    private $model;
    private $database;

    public function __construct()
    {
        $this->database = new Database();
        $this->db = $this->database->getConnection();
        $this->model = new MLoadTorres($this->db);
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
                $this->processFile();
                break;

            default:
                echo json_encode([
                    'success' => false,
                    'message' => 'Acción no válida'
                ]);
        }
    }

    /**
     * FASE 1: Validación y guardado temporal
     */
    private function uploadFile()
    {
        $tempFilePath = null;

        try {
            if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Error al recibir el archivo');
            }

            $file = $_FILES['archivo'];
            $idEstacion = $_POST['id_estacion'] ?? '';
            $nivel = $_POST['nivel'] ?? '';

            if (empty($idEstacion)) {
                throw new Exception('Debe seleccionar una estación');
            }

            if (empty($nivel)) {
                throw new Exception('Debe seleccionar un nivel');
            }

            if (!in_array($nivel, ['L0', 'L1', 'L2'])) {
                throw new Exception('Nivel no válido');
            }

            if (!preg_match('/\.csv$/i', $file['name'])) {
                throw new Exception('El archivo debe tener extensión .csv');
            }

            $estacionInfo = $this->model->obtenerEstacionPorId($idEstacion);
            if (!$estacionInfo) {
                throw new Exception('Estación no encontrada');
            }

            $tempDir = '../controller/uploads/temp/';
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $tempFileName = 'temp_torres_' . time() . '_' . basename($file['name']);
            $tempFilePath = $tempDir . $tempFileName;

            if (!move_uploaded_file($file['tmp_name'], $tempFilePath)) {
                throw new Exception('Error al guardar archivo temporal');
            }

            // Validar encabezados (debe tener al menos 5 variables válidas)
            $headerValidation = $this->model->validateCSVHeaders($tempFilePath);
            if (!$headerValidation['valid']) {
                throw new Exception($headerValidation['message']);
            }

            // Validar calidad de datos (al menos 5% válidos)
            $dataQualityValidation = $this->model->validateDataQuality($tempFilePath);
            if (!$dataQualityValidation['valid']) {
                throw new Exception($dataQualityValidation['message']);
            }

            $totalRegistros = $this->model->countDataLines($tempFilePath);

            ob_clean();
            echo json_encode([
                'success' => true,
                'message' => "Archivo analizado: {$totalRegistros} registros encontrados",
                'data' => [
                    'total_registros' => $totalRegistros,
                    'archivo_nombre' => $file['name'],
                    'temp_path' => $tempFilePath,
                    'nivel' => $nivel
                ]
            ]);

        } catch (Exception $e) {
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

    /**
     * FASE 2: Guardado definitivo
     */
    private function processFile()
{
    $tempFilePath = null;

    try {
        $idEstacion = $_POST['id_estacion'] ?? '';
        $nivel = $_POST['nivel'] ?? '';
        $tempPath = $_POST['temp_path'] ?? '';

        if (empty($idEstacion) || empty($nivel) || empty($tempPath)) {
            throw new Exception('Parámetros incompletos');
        }

        if (!file_exists($tempPath)) {
            throw new Exception('Archivo temporal no encontrado');
        }

        $tempFilePath = $tempPath;

        $estacionInfo = $this->model->obtenerEstacionPorId($idEstacion);
        if (!$estacionInfo) {
            throw new Exception('Estación no encontrada');
        }

        // Extraer fechas del archivo
        $fechas = $this->model->extraerFechaInicioFin($tempFilePath);

        if (!$fechas || !isset($fechas['inicio']) || !isset($fechas['fin'])) {
            throw new Exception('No se pudieron extraer las fechas del archivo');
        }

        $fechaInicio = date('d-m-Y', strtotime($fechas['inicio']));
        $fechaFin = date('d-m-Y', strtotime($fechas['fin']));

        // 🔹 PASO 1: Obtener nombre de la estación y sanitizar
        $nombreEstacion = $estacionInfo['nombre'] ?? 'UNKNOWN';
        $nombreEstacion = preg_replace('/[^A-Za-z0-9_\-]/', '_', $nombreEstacion);
        
        // 🔹 PASO 2: Construir estructura de carpetas
        // uploads/{NOMBRE_ESTACION}/Torres/{NIVEL}/
        $baseDir = '../uploads/';
        $estacionDir = $baseDir . $nombreEstacion . '/';
        $torresDir = $estacionDir . 'Torres/';
        $nivelDir = $torresDir . $nivel . '/';
        
        // 🔹 PASO 3: Crear directorio si no existe
        if (!is_dir($nivelDir)) {
            mkdir($nivelDir, 0755, true);
        }

        // 🔹 PASO 4: Generar nombre del archivo
        // FORMATO: {CODIGO}_{NIVEL}T_{FECHA_INICIO}_{FECHA_FIN}.csv
        $codigoEstacion = $estacionInfo['codigo'] ?? 'UNKNOWN';
        $sufijo = $nivel . 'T'; // L0T, L1T, L2T
        $newFileName = "{$codigoEstacion}_{$sufijo}_{$fechaInicio}_{$fechaFin}.csv";
        $newFileName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $newFileName);

        // 🔹 PASO 5: Ruta completa del archivo final
        $finalPath = $nivelDir . $newFileName;

        // 🔹 PASO 6: Validar si ya existe
        if (file_exists($finalPath)) {
            if (file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }
            throw new Exception('Ya existe un archivo con este rango de fechas para esta estación en nivel ' . $nivel);
        }

        // 🔹 PASO 7: Mover archivo temporal a ubicación final
        if (!rename($tempFilePath, $finalPath)) {
            throw new Exception('Error al mover archivo a ubicación final');
        }

        error_log("✅ Archivo Torres guardado: $finalPath");

        ob_clean();
        echo json_encode([
            'success' => true,
            'message' => "Archivo de torres guardado exitosamente",
            'data' => [
                'registros_procesados' => $this->model->countDataLines($finalPath),
                'archivo_final' => $newFileName,
                'nivel' => $nivel,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'ruta_completa' => $finalPath
            ]
        ]);

    } catch (Exception $e) {
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

    /**
     * Limpia archivos temporales antiguos (más de 1 hora)
     */
    private function cleanOldTempFiles()
    {
        try {
            $tempDir = '../controller/uploads/temp/';

            if (!is_dir($tempDir)) {
                return;
            }

            $files = glob($tempDir . 'temp_torres_*');
            $now = time();
            $deleted = 0;

            foreach ($files as $file) {
                if (is_file($file)) {
                    if ($now - filemtime($file) >= 3600) {
                        unlink($file);
                        $deleted++;
                        error_log("✓ Archivo temporal Torres antiguo eliminado: " . basename($file));
                    }
                }
            }

            if ($deleted > 0) {
                error_log("✓ Total archivos temporales Torres eliminados: $deleted");
            }

        } catch (Exception $e) {
            error_log("Error al limpiar archivos temporales Torres: " . $e->getMessage());
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new CLoadTorres();
    $controller->handleRequest();
}
?>