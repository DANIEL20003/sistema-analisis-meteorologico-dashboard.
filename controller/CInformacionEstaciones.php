<?php
// controlador/CInformacionEstaciones.php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Habilitar logging para debugging
error_reporting(E_ALL);
ini_set('log_errors', 1);

require_once '../model/MInformacionEstaciones.php';

/**
 * Función auxiliar para formatear la ubicación de una estación
 * @param array $estacion Datos de la estación
 * @return string Ubicación formateada
 */
function formatearUbicacion($estacion) {
    $ubicacion = [];
    
    if (!empty($estacion['comunidad'])) {
        $ubicacion[] = $estacion['comunidad'];
    }
    if (!empty($estacion['parroquia'])) {
        $ubicacion[] = $estacion['parroquia'];
    }
    if (!empty($estacion['canton'])) {
        $ubicacion[] = $estacion['canton'];
    }
    if (!empty($estacion['provincia'])) {
        $ubicacion[] = $estacion['provincia'];
    }
    
    return implode(', ', $ubicacion);
}

try {
    error_log("🚀 CInformacionEstaciones iniciado - " . date('Y-m-d H:i:s'));
    
    $modeloEstaciones = new MInformacionEstaciones();
    error_log("✅ Modelo inicializado");
    
    // Verificar si hay acción especificada o ID de estación
    if (isset($_GET['id_estacion'])) {
        // Obtener información detallada de una estación específica
        $id_estacion = intval($_GET['id_estacion']);
        
        if ($id_estacion <= 0) {
            throw new Exception("ID de estación inválido");
        }
        
        error_log("🔍 Obteniendo información detallada de estación ID: " . $id_estacion);
        
        // Obtener información completa de la estación
        $estacion_completa = $modeloEstaciones->obtenerInformacionEstacion($id_estacion);
        
        // Formatear la respuesta para el frontend
        $response = [
            'success' => true,
            'data' => [
                'estacion' => [
                    'id' => $estacion_completa['estacion']['id_estacion'],
                    'codigo' => $estacion_completa['estacion']['codigo'],
                    'nombre' => $estacion_completa['estacion']['nombre'],
                    'ubicacion' => formatearUbicacion($estacion_completa['estacion']),
                    'elevacion' => $estacion_completa['estacion']['altura_terreno'] . 'm',
                    'coordenadas' => [
                        'lat' => $estacion_completa['estacion']['latitud'],
                        'lng' => $estacion_completa['estacion']['longitud']
                    ],
                    'tag' => $estacion_completa['estacion']['tag_codigo_iner'],
                    'instalacion' => $modeloEstaciones->formatearFecha($estacion_completa['estacion']['fecha_instalacion']),
                    'estado' => $estacion_completa['estacion']['estado_texto'],
                    'status' => $estacion_completa['estacion']['status'],
                    'provincia' => $estacion_completa['estacion']['provincia'],
                    'canton' => $estacion_completa['estacion']['canton'],
                    'parroquia' => $estacion_completa['estacion']['parroquia'],
                    'ruta_fotografia' => $estacion_completa['estacion']['ruta_fotografia']
                ],
                'componentes' => array_map(function($comp) {
                    return [
                        'tipo' => $comp['tipo_componente']
                    ];
                }, $estacion_completa['componentes']),
                'sensores' => array_map(function($sensor) {
                    return [
                        'tipo' => $sensor['tipo_sensor']
                    ];
                }, $estacion_completa['sensores'])
            ]
        ];
        
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
    } elseif (isset($_GET['action'])) {
        $action = $_GET['action'];
        error_log("🎯 Acción solicitada: " . $action);
        
        switch ($action) {
            case 'get_all_stations':
                error_log("🔍 Procesando get_all_stations...");
                
                try {
                    $estaciones = $modeloEstaciones->obtenerTodasLasEstaciones();
                    error_log("📊 Modelo retornó " . count($estaciones) . " estaciones");
                    
                    if (empty($estaciones)) {
                        throw new Exception("No se encontraron estaciones meteorológicas válidas");
                    }
                    
                    // Formatear estaciones para el frontend del mapa
                    $estaciones_formateadas = array();
                    error_log("🎨 Formateando estaciones para frontend...");
                    
                    foreach ($estaciones as $index => $est) {
                        $coords = $modeloEstaciones->formatearCoordenadas($est['latitud'], $est['longitud']);
                        
                        // Generar código de imagen basado en el código de la estación
                        $imageCode = 'E_' . preg_replace('/[^a-zA-Z0-9]/', '', $est['codigo']);
                        if (strlen($imageCode) > 10) {
                            $imageCode = substr($imageCode, 0, 10);
                        }
                        
                        $estacion_formateada = [
                            'id' => intval($est['id_estacion']),
                            'codigo' => $est['codigo'],
                            'nombre' => $est['nombre'],
                            'coords' => [$coords['lat'], $coords['lng']],
                            'location' => formatearUbicacion($est),
                            'status' => $est['status'],
                            'elevation' => $est['altura_terreno'] . 'm',
                            'provincia' => $est['provincia'],
                            'canton' => $est['canton'],
                            'parroquia' => $est['parroquia'],
                            'comunidad' => $est['comunidad'],
                            'tag' => $est['tag_codigo_iner'],
                            'estado_texto' => $est['estado_texto'],
                            'ruta_fotografia' => $est['ruta_fotografia'],
                            'imageCode' => $imageCode
                        ];
                        
                        $estaciones_formateadas[] = $estacion_formateada;
                        error_log("📍 Estación " . ($index + 1) . " formateada: " . $est['codigo']);
                    }
                    
                    $response = [
                        'success' => true,
                        'data' => $estaciones_formateadas,
                        'total' => count($estaciones_formateadas),
                        'timestamp' => date('Y-m-d H:i:s'),
                        'message' => 'Estaciones cargadas correctamente'
                    ];
                    
                    error_log("✅ Respuesta JSON generada con " . count($estaciones_formateadas) . " estaciones");
                    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    
                } catch (Exception $e) {
                    error_log("❌ Error en get_all_stations: " . $e->getMessage());
                    throw $e;
                }
                break;
                
            case 'get_statistics':
                error_log("🔍 Procesando get_statistics...");
                
                try {
                    $stats = $modeloEstaciones->obtenerEstadisticasGenerales();
                    
                    $response = [
                        'success' => true,
                        'data' => $stats,
                        'timestamp' => date('Y-m-d H:i:s')
                    ];
                    
                    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    
                } catch (Exception $e) {
                    error_log("❌ Error en get_statistics: " . $e->getMessage());
                    throw $e;
                }
                break;
                
            case 'get_provinces':
                error_log("🔍 Procesando get_provinces...");
                
                try {
                    $provincias = $modeloEstaciones->obtenerProvinciasConEstaciones();
                    
                    $response = [
                        'success' => true,
                        'data' => $provincias,
                        'total' => count($provincias)
                    ];
                    
                    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    
                } catch (Exception $e) {
                    error_log("❌ Error en get_provinces: " . $e->getMessage());
                    throw $e;
                }
                break;
                
            case 'health_check':
                error_log("🔍 Procesando health_check...");
                
                $health_check = [
                    'status' => 'healthy',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'database' => 'connected',
                    'total_estaciones' => 0,
                    'version' => '2.0.0-Ecuador'
                ];
                
                try {
                    $estaciones = $modeloEstaciones->obtenerTodasLasEstaciones();
                    $health_check['total_estaciones'] = count($estaciones);
                    $health_check['database'] = 'connected';
                    
                    error_log("🏥 Health check: " . count($estaciones) . " estaciones disponibles");
                    
                } catch (Exception $e) {
                    $health_check['status'] = 'unhealthy';
                    $health_check['database'] = 'error: ' . $e->getMessage();
                    error_log("❌ Health check falló: " . $e->getMessage());
                }
                
                $response = [
                    'success' => true,
                    'data' => $health_check
                ];
                
                echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                break;
                
            default:
                error_log("❌ Acción no válida: " . $action);
                throw new Exception("Acción no válida: " . $action);
        }
        
    } else {
        // Si no hay parámetros, mostrar información de ayuda
        error_log("❌ No hay parámetro 'action' o 'id_estacion'");
        throw new Exception("Parámetro requerido: id_estacion o action");
    }
    
} catch (Exception $e) {
    // Determinar código HTTP apropiado
    $error_code = 400;
    $error_message = $e->getMessage();
    
    if (strpos($error_message, 'conexión') !== false || 
        strpos($error_message, 'base de datos') !== false ||
        strpos($error_message, 'SQL') !== false) {
        $error_code = 500;
    }
    
    http_response_code($error_code);
    
    $response = [
        'success' => false,
        'error' => $error_message,
        'timestamp' => date('Y-m-d H:i:s'),
        'debug_info' => [
            'archivo' => basename(__FILE__),
            'linea' => $e->getLine(),
            'version' => '2.0.0-Ecuador'
        ]
    ];
    
    // Log detallado del error
    error_log("❌ ERROR CRÍTICO en " . basename(__FILE__));
    error_log("   Mensaje: " . $error_message);
    error_log("   Archivo: " . $e->getFile());
    error_log("   Línea: " . $e->getLine());
    error_log("   Stack: " . $e->getTraceAsString());
    
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

error_log("🏁 CInformacionEstaciones finalizado");
?>