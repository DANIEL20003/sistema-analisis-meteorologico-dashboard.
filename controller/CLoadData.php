<?php
// CLoadData.php - Gestión de estaciones y carga de archivos

error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Asegúrate de que la ruta sea correcta según tu estructura CMV
require_once '../model/MLoadData.php';

// =======================================================
// FUNCIONES DE RESPUESTA
// =======================================================

function sendErrorResponse($message, $code = 400) {
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'message' => $message,
        'data' => null
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit();
}

function sendSuccessResponse($message, $data = null) {
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

function handleGetEstaciones($modelo, $input) {
    try {
        // Verificar conexión (se asume que MLoadData::verificarConexion está definido)
        if (!$modelo->verificarConexion()) {
            throw new Exception("No se puede conectar a la base de datos");
        }
        
        // Obtener estaciones usando el modelo
        $estaciones = $modelo->obtenerEstaciones();
        
        if (empty($estaciones)) {
            $responseData = [
                'estaciones' => [],
                'total' => 0,
                'conexion_db' => true,
                'mensaje' => 'No hay estaciones registradas.'
            ];
            sendSuccessResponse("Base de datos conectada. No hay estaciones.", $responseData);
            return;
        }
        
        $responseData = [
            'estaciones' => $estaciones,
            'total' => count($estaciones),
            'conexion_db' => true
        ];
        
        sendSuccessResponse("Estaciones obtenidas correctamente.", $responseData);
        
    } catch (Exception $e) {
        error_log("Error al obtener estaciones: " . $e->getMessage());
        
        // Devolver datos por defecto si hay error de conexión (para no romper la UI)
        $estacionesDefault = [
             ['id_estacion' => 4, 'codigo' => 'ESPOCH', 'nombre' => 'E.M ESPOCH - Energías Alternativas'],
             ['id_estacion' => 1, 'codigo' => 'ALAO', 'nombre' => 'E.M Alao EERSA']
        ];
        
        $responseData = [
            'estaciones' => $estacionesDefault,
            'total' => count($estacionesDefault),
            'warning' => 'Error DB. Usando datos por defecto.',
            'conexion_db' => false
        ];
        
        sendSuccessResponse("Estaciones obtenidas (datos por defecto debido a error)", $responseData);
    }
}

// CLoadData.php (Fragmento modificado: Solo la función handleGetArchivos)

function handleGetArchivos($modelo, $input) {
    // Ya no se requiere ni valida $id_estacion
    $nivel = $input['nivel'] ?? 'L1';
    $tipo_seleccion = $input['tipo_seleccion'] ?? 'LIMPIOS';

    try {
        // Llama al modelo sin pasar id_estacion
        $archivos = $modelo->get_archivos_cargados(null, $nivel, $tipo_seleccion);

        if (empty($archivos)) {
            sendSuccessResponse("No se encontraron archivos para el nivel $nivel.", ['data' => []]);
            return;
        }

        sendSuccessResponse("Archivos cargados correctamente.", ['data' => $archivos]);

    } catch (Exception $e) {
        error_log("Error al obtener archivos: " . $e->getMessage());
        sendErrorResponse("Error al cargar archivos desde el modelo: " . $e->getMessage(), 500);
    }
}
// 1. Agrega esta función junto a las otras (handleGetArchivos, etc)
function handleDelete($modelo, $input) {
    $id = $input['id_carga'] ?? null;
    $nombre = $input['nombre_archivo'] ?? '';
    $nivel = $input['nivel'] ?? '';
    $origen = $input['origen'] ?? 'DB'; // DB o FISICO

    if($modelo->eliminarArchivo($id, $nombre, $nivel, $origen)){
        sendSuccessResponse("Archivo eliminado correctamente.");
    } else {
        sendErrorResponse("No se pudo eliminar el archivo. Verifique permisos.");
    }
}
// =======================================================
// MANEJADOR PARA OBTENER CARPETAS DE ESTACIONES
// =======================================================

function handleGetEstacionesFolders() {
    try {
        // OPCIÓN 1: Si CLoadData.php está en /controller/
        $uploadsPath = dirname(__DIR__) . '/uploads/';
        
        // OPCIÓN 2: Si la ruta anterior no funciona, usa ruta absoluta
        // $uploadsPath = $_SERVER['DOCUMENT_ROOT'] . '/PRACTICASM/uploads/';
        
        // DEBUG: Registrar la ruta que se está intentando
        error_log("Intentando acceder a: " . $uploadsPath);
        
        // Verificar que el directorio existe
        if (!is_dir($uploadsPath)) {
            // Intentar rutas alternativas
            $uploadsPath = dirname(dirname(__DIR__)) . '/uploads/';
            error_log("Ruta alternativa: " . $uploadsPath);
            
            if (!is_dir($uploadsPath)) {
                sendErrorResponse('Directorio uploads no encontrado. Ruta intentada: ' . $uploadsPath, 404);
                return;
            }
        }

        // Obtener todas las carpetas del directorio uploads
        $items = scandir($uploadsPath);
        
        if ($items === false) {
            sendErrorResponse('No se puede leer el directorio uploads', 500);
            return;
        }
        
        // Filtrar solo carpetas (excluir . y ..)
        $folders = [];
        foreach ($items as $item) {
            // Verificar que sea directorio y no sea . o ..
            $fullPath = $uploadsPath . $item;
            if (is_dir($fullPath) && $item !== '.' && $item !== '..') {
                // Filtrar carpetas del sistema
                if ($item[0] !== '.' && $item !== '__MACOSX' && $item !== 'temp') {
                    $folders[] = $item;
                }
            }
        }
        
        if (empty($folders)) {
            sendSuccessResponse('No hay estaciones disponibles en uploads', [
                'folders' => [],
                'count' => 0,
                'path' => $uploadsPath,
                'items_found' => count($items)
            ]);
            return;
        }

        // Ordenar alfabéticamente
        sort($folders);

        sendSuccessResponse('Carpetas de estaciones obtenidas correctamente', [
            'folders' => $folders,
            'count' => count($folders),
            'path' => $uploadsPath
        ]);

    } catch (Exception $e) {
        error_log("Error al obtener carpetas de estaciones: " . $e->getMessage());
        sendErrorResponse('Error al obtener carpetas: ' . $e->getMessage(), 500);
    }
}


// =======================================================
// LÓGICA PRINCIPAL (ROUTER)
// =======================================================

try {
    $modelo = new MLoadData();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        // El frontend usa FormData, por lo que casi siempre estará en $_POST
        $input = $_POST;
        
        // Si el frontend envía JSON (menos común), se puede leer aquí
        if (empty($input)) {
             $json_input = json_decode(file_get_contents('php://input'), true);
             if ($json_input) $input = $json_input;
        }
        
        $action = $input['action'] ?? '';
        
        if ($action === 'get_estaciones') {
            handleGetEstaciones($modelo, $input);
        } else if ($action === 'get_archivos') {
            handleGetArchivos($modelo, $input);
        } else if ($action === 'delete_file') {
            handleDelete($modelo, $input);
        } else if ($action === 'get_estaciones_folders') { // ✅ AGREGAR ESTE BLOQUE
            handleGetEstacionesFolders(); // No necesita $modelo porque lee del sistema de archivos
        } else {
            // Error si se envía una acción no reconocida
            sendErrorResponse("Acción no válida: $action");
        }
        
    } else {
        sendErrorResponse("Método HTTP no soportado");
    }
    
} catch (Exception $e) {
    error_log("Error fatal en CLoadData.php: " . $e->getMessage());
    sendErrorResponse("Error interno del servidor (Excepción fatal).", 500);
}

?>