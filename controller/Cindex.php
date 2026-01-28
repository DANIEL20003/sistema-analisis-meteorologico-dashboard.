<?php
// controller/Cindex.php

// Headers para JSON válido
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: -1');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../model/Mindex.php';

try {
    $modeloIndex = new MIndex();
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'get_fotografias':
                handleGetFotografias($modeloIndex);
                break;
                
            case 'get_mapas':
                handleGetMapas($modeloIndex);
                break;
                
            default:
                sendErrorResponse("Acción no válida: $action");
        }
        
    } else {
        sendErrorResponse("Método HTTP no soportado");
    }
    
} catch (Exception $e) {
    // Log del error para debugging
    error_log("Error en Cindex.php: " . $e->getMessage());
    
    // Siempre devolver JSON, nunca HTML
    $errorMessage = "Error interno del servidor: " . $e->getMessage();
    
    // Limpiar el mensaje de error para evitar HTML
    $errorMessage = strip_tags($errorMessage);
    
    sendErrorResponse($errorMessage, 500);
}

/**
 * Enviar respuesta de error en formato consistente
 */
function sendErrorResponse($message, $code = 400) {
    http_response_code($code);
    $response = [
        'success' => false,
        'message' => $message,
        'data' => null
    ];
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit();
}

/**
 * Enviar respuesta de éxito en formato consistente
 */
function sendSuccessResponse($message, $data = null) {
    $response = [
        'success' => true,
        'message' => $message,
        'data' => $data
    ];
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit();
}

/**
 * Maneja la obtención de fotografías de estaciones
 */
function handleGetFotografias($modelo) {
    try {
        // Verificar conexión a la base de datos
        if (!$modelo->verificarConexion()) {
            throw new Exception("No se puede conectar a la base de datos");
        }
        
        // Obtener fotografías válidas desde la base de datos
        $fotografias = $modelo->obtenerFotografiasEstaciones();
        
        // Solo devolver las fotografías válidas, sin imagen por defecto
        $responseData = [
            'fotografias' => $fotografias,
            'total' => count($fotografias),
            'conexion_db' => true,
            'tiene_imagenes' => count($fotografias) > 0
        ];
        
        if (empty($fotografias)) {
            sendSuccessResponse("No hay fotografías válidas de estaciones registradas.", $responseData);
        } else {
            sendSuccessResponse("Fotografías obtenidas correctamente desde la base de datos", $responseData);
        }
        
    } catch (Exception $e) {
        // Log del error
        error_log("Error al obtener fotografías: " . $e->getMessage());
        
        // Devolver respuesta vacía cuando hay error
        $responseData = [
            'fotografias' => [],
            'total' => 0,
            'conexion_db' => false,
            'tiene_imagenes' => false,
            'error' => 'Error al conectar con la base de datos'
        ];
        
        sendSuccessResponse("Error de conexión con la base de datos", $responseData);
    }
}

/**
 * Maneja la obtención de mapas de estaciones
 */
function handleGetMapas($modelo) {
    try {
        // Verificar conexión a la base de datos
        if (!$modelo->verificarConexion()) {
            throw new Exception("No se puede conectar a la base de datos");
        }
        
        // Obtener mapas válidos desde la base de datos
        $mapas = $modelo->obtenerMapasEstaciones();
        
        // Solo devolver los mapas válidos, sin imagen por defecto
        $responseData = [
            'mapas' => $mapas,
            'total' => count($mapas),
            'conexion_db' => true,
            'tiene_imagenes' => count($mapas) > 0
        ];
        
        if (empty($mapas)) {
            sendSuccessResponse("No hay mapas válidos de estaciones registrados.", $responseData);
        } else {
            sendSuccessResponse("Mapas obtenidos correctamente desde la base de datos", $responseData);
        }
        
    } catch (Exception $e) {
        // Log del error
        error_log("Error al obtener mapas: " . $e->getMessage());
        
        // Devolver respuesta vacía cuando hay error
        $responseData = [
            'mapas' => [],
            'total' => 0,
            'conexion_db' => false,
            'tiene_imagenes' => false,
            'error' => 'Error al conectar con la base de datos'
        ];
        
        sendSuccessResponse("Error de conexión con la base de datos", $responseData);
    }
}
?>