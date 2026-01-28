<?php
/**
 * CEstacion.php
 * Controlador para gestión de estaciones y archivos
 * Maneja las peticiones del frontend relacionadas con estaciones
 */

error_reporting(E_ALL); // ✅ CAMBIAR TEMPORALMENTE PARA VER ERRORES
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/conexion.php';
require_once '../model/MEstacion.php';

// =======================================================
// FUNCIONES DE RESPUESTA
// =======================================================

function sendErrorResponse($message, $code = 400)
{
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'message' => $message,
        'data' => null
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit();
}

function sendSuccessResponse($message, $data = null)
{
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit();
}

// =======================================================
// MANEJADORES DE ACCIÓN
// =======================================================

/**
 * Obtiene la lista de estaciones disponibles (carpetas en uploads)
 */
function handleGetEstaciones($modelo)
{
    try {
        $estaciones = $modelo->obtenerEstacionesDisponibles();

        if (empty($estaciones)) {
            sendSuccessResponse('No hay estaciones disponibles', [
                'estaciones' => [],
                'total' => 0
            ]);
            return;
        }

        sendSuccessResponse('Estaciones obtenidas correctamente', [
            'estaciones' => $estaciones,
            'total' => count($estaciones)
        ]);

    } catch (Exception $e) {
        error_log("Error en handleGetEstaciones: " . $e->getMessage());
        sendErrorResponse('Error al obtener estaciones: ' . $e->getMessage(), 500);
    }
}
/**
 * Obtiene los archivos de una estación específica por nivel
 */
function handleGetArchivos($modelo, $input)
{
    try {
        $estacion = $input['estacion'] ?? '';
        $nivel = $input['nivel'] ?? 'L0';
        $anio = isset($input['anio']) && $input['anio'] !== '' ? intval($input['anio']) : null;

        if (empty($estacion)) {
            sendErrorResponse('Debe proporcionar el nombre de la estación', 400);
            return;
        }

        if (!in_array($nivel, ['L0', 'L1', 'L2'])) {
            sendErrorResponse('Nivel no válido. Debe ser L0, L1 o L2', 400);
            return;
        }

        if (!$modelo->existeEstacion($estacion)) {
            sendErrorResponse("La estación '$estacion' no existe", 404);
            return;
        }

        $archivos = $modelo->obtenerArchivosCompletos($estacion, $nivel, $anio);

        sendSuccessResponse('Archivos obtenidos correctamente', $archivos);

    } catch (Exception $e) {
        error_log("Error en handleGetArchivos: " . $e->getMessage());
        sendErrorResponse('Error al obtener archivos: ' . $e->getMessage(), 500);
    }
}

/**
 * Obtiene la estructura completa de una estación
 */
function handleGetEstructura($modelo, $input)
{
    try {
        $estacion = $input['estacion'] ?? '';

        if (empty($estacion)) {
            sendErrorResponse('Debe proporcionar el nombre de la estación', 400);
            return;
        }

        $estructura = $modelo->obtenerEstructuraEstacion($estacion);

        sendSuccessResponse('Estructura obtenida correctamente', $estructura);

    } catch (Exception $e) {
        error_log("Error en handleGetEstructura: " . $e->getMessage());
        sendErrorResponse('Error al obtener estructura: ' . $e->getMessage(), 500);
    }
}
/**
 * Obtiene los años disponibles para una estación y nivel
 */
function handleGetAnios($modelo, $input)
{
    try {
        $estacion = $input['estacion'] ?? '';
        $nivel = $input['nivel'] ?? 'L0';

        if (empty($estacion)) {
            sendErrorResponse('Debe proporcionar el nombre de la estación', 400);
            return;
        }

        if (!in_array($nivel, ['L0', 'L1', 'L2'])) {
            sendErrorResponse('Nivel no válido. Debe ser L0, L1 o L2', 400);
            return;
        }

        if (!$modelo->existeEstacion($estacion)) {
            sendErrorResponse("La estación '$estacion' no existe", 404);
            return;
        }

        $anios = $modelo->obtenerAniosDisponibles($estacion, $nivel);

        sendSuccessResponse('Años obtenidos correctamente', [
            'anios' => $anios,
            'total' => count($anios)
        ]);

    } catch (Exception $e) {
        error_log("Error en handleGetAnios: " . $e->getMessage());
        sendErrorResponse('Error al obtener años: ' . $e->getMessage(), 500);
    }
}
/**
 * Obtiene los archivos de Torres de una estación específica
 */
function handleGetArchivosTorres($modelo, $input)
{
    try {
        $estacion = $input['estacion'] ?? '';
        $nivel = $input['nivel'] ?? 'L0';
        $anio = isset($input['anio']) && $input['anio'] !== '' ? intval($input['anio']) : null;

        if (empty($estacion)) {
            sendErrorResponse('Debe proporcionar el nombre de la estación', 400);
            return;
        }

        if (!in_array($nivel, ['L0', 'L1', 'L2'])) {
            sendErrorResponse('Nivel no válido. Debe ser L0, L1 o L2', 400);
            return;
        }

        if (!$modelo->existeEstacion($estacion)) {
            sendErrorResponse("La estación '$estacion' no existe", 404);
            return;
        }

        $torres = $modelo->obtenerArchivosTorres($estacion, $nivel, $anio);

        sendSuccessResponse('Archivos de torres obtenidos correctamente', [
            'torres' => $torres,
            'estacion' => $estacion,
            'nivel' => $nivel,
            'anio' => $anio,
            'total' => count($torres)
        ]);

    } catch (Exception $e) {
        error_log("Error en handleGetArchivosTorres: " . $e->getMessage());
        sendErrorResponse('Error al obtener archivos de torres: ' . $e->getMessage(), 500);
    }
}
function handleGetAniosTorres($modelo, $input)
{
    try {
        $estacion = $input['estacion'] ?? '';
        $nivel = $input['nivel'] ?? 'L0';

        if (empty($estacion)) {
            sendErrorResponse('Debe proporcionar el nombre de la estación', 400);
            return;
        }

        if (!in_array($nivel, ['L0', 'L1', 'L2'])) {
            sendErrorResponse('Nivel no válido. Debe ser L0, L1 o L2', 400);
            return;
        }

        if (!$modelo->existeEstacion($estacion)) {
            sendErrorResponse("La estación '$estacion' no existe", 404);
            return;
        }

        $anios = $modelo->obtenerAniosDisponiblesTorres($estacion, $nivel);

        sendSuccessResponse('Años de torres obtenidos correctamente', [
            'anios' => $anios,
            'total' => count($anios)
        ]);

    } catch (Exception $e) {
        error_log("Error en handleGetAniosTorres: " . $e->getMessage());
        sendErrorResponse('Error al obtener años de torres: ' . $e->getMessage(), 500);
    }
}

/**
 * Elimina un archivo del sistema
 */
function handleEliminarArchivo($modelo, $input)
{
    try {
        $ruta = $input['ruta'] ?? '';

        if (empty($ruta)) {
            sendErrorResponse('Debe proporcionar la ruta del archivo', 400);
            return;
        }

        // Validar que la ruta comience con 'uploads/'
        if (strpos($ruta, 'uploads/') !== 0) {
            sendErrorResponse('Ruta de archivo no válida', 400);
            return;
        }

        $resultado = $modelo->eliminarArchivo($ruta);

        if ($resultado) {
            sendSuccessResponse('Archivo eliminado correctamente', [
                'ruta' => $ruta,
                'fecha_eliminacion' => date('Y-m-d H:i:s')
            ]);
        } else {
            sendErrorResponse('No se pudo eliminar el archivo', 500);
        }

    } catch (Exception $e) {
        error_log("Error en handleEliminarArchivo: " . $e->getMessage());
        sendErrorResponse('Error al eliminar archivo: ' . $e->getMessage(), 500);
    }
}



/**
 * Registra una descarga en la base de datos
 */
function handleRegistrarDescarga($modelo, $input)
{
    try {
        // Log de entrada
        error_log("📝 Datos recibidos para registro: " . json_encode($input));

        // Validar campos requeridos
        $camposRequeridos = ['nombre_solicitante', 'cedula_pasaporte', 'estacion', 'tipo_nivel'];

        foreach ($camposRequeridos as $campo) {
            if (empty($input[$campo])) {
                error_log("❌ Falta campo requerido: $campo");
                sendErrorResponse("El campo '$campo' es requerido", 400);
                return;
            }
        }

        // Validar tipo_nivel
        if (!in_array($input['tipo_nivel'], ['L0', 'L1', 'L2'])) {
            error_log("❌ Tipo de nivel no válido: " . $input['tipo_nivel']);
            sendErrorResponse("Tipo de nivel no válido", 400);
            return;
        }

        // Preparar datos para el modelo
        $datosDescarga = [
            'nombre_solicitante' => trim($input['nombre_solicitante']),
            'institucion' => trim($input['institucion'] ?? ''),
            'cedula_pasaporte' => trim($input['cedula_pasaporte']),
            'motivo' => trim($input['motivo'] ?? ''),
            'estacion' => trim($input['estacion']),
            'tipo_nivel' => $input['tipo_nivel'],
            'tipo_archivo' => 'CSV'
        ];

        error_log("🔄 Intentando registrar descarga...");

        // Registrar en base de datos
        $idRegistro = $modelo->registrarDescarga($datosDescarga);

        if ($idRegistro) {
            error_log("✅ Descarga registrada exitosamente con ID: $idRegistro");
            sendSuccessResponse('Descarga registrada correctamente', [
                'id_registro' => $idRegistro,
                'fecha' => date('Y-m-d H:i:s')
            ]);
        } else {
            error_log("❌ No se obtuvo ID de registro");
            sendErrorResponse('No se pudo registrar la descarga', 500);
        }

    } catch (Exception $e) {
        error_log("❌ Excepción en handleRegistrarDescarga: " . $e->getMessage());
        error_log("❌ Stack trace: " . $e->getTraceAsString());
        sendErrorResponse('Error al registrar descarga: ' . $e->getMessage(), 500);
    }
}

// =======================================================
// ROUTER PRINCIPAL
// =======================================================

try {
    $modelo = new MEstacion();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $input = $_POST;

        // Soporte para JSON si es necesario
        if (empty($input)) {
            $json_input = json_decode(file_get_contents('php://input'), true);
            if ($json_input)
                $input = $json_input;
        }

        $action = $input['action'] ?? '';

        switch ($action) {
            case 'get_estaciones':
                handleGetEstaciones($modelo);
                break;

            case 'get_archivos':
                handleGetArchivos($modelo, $input);
                break;

            case 'get_estructura':
                handleGetEstructura($modelo, $input);
                break;

            case 'get_anios':
                handleGetAnios($modelo, $input);
                break;

            case 'registrar_descarga':
                handleRegistrarDescarga($modelo, $input);
                break;

            case 'get_archivos_torres':
                handleGetArchivosTorres($modelo, $input);
                break;

            case 'eliminar_archivo':
                handleEliminarArchivo($modelo, $input);
                break;

            case 'get_anios_torres':
                handleGetAniosTorres($modelo, $input);
                break;

            case 'get_archivos_torres':
                handleGetArchivosTorres($modelo, $input);
                break;

            default:
                sendErrorResponse("Acción no válida: $action", 400);
                break;
        }

    } else {
        sendErrorResponse("Método HTTP no soportado. Use POST", 405);
    }

} catch (Exception $e) {
    error_log("Error fatal en CEstacion.php: " . $e->getMessage());
    sendErrorResponse("Error interno del servidor", 500);
}
?>