<?php
// controlador/CDashboard.php
// IMPORTANTE: No debe haber ningún espacio o salto de línea antes de este <?php

// Deshabilitar output buffering para evitar que warnings se mezclen con JSON
ob_start();

// Configurar headers PRIMERO (antes de cualquier output)
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Configurar para mostrar errores en log pero NO en output
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once '../model/MDashboard.php';

try {
    $modeloDashboard = new MDashboard();

    // ================================================================
    // PRIORIDAD 1: OBTENER ESTACIONES
    // ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_stations') {
        error_log("=== OBTENIENDO ESTACIONES ===");

        $estaciones = $modeloDashboard->obtenerTodasLasEstaciones();

        $response = [
            'success' => true,
            'data' => array_map(function ($estacion) {
                return [
                    'id' => intval($estacion['id_estacion']),
                    'codigo' => $estacion['codigo'],
                    'nombre' => $estacion['nombre'],
                    'ubicacion' => $estacion['comunidad'] . ', ' . $estacion['canton'] . ', ' . $estacion['provincia'],
                    'coordenadas' => [
                        'lat' => floatval($estacion['latitud']),
                        'lng' => floatval($estacion['longitud'])
                    ],
                    'altura' => $estacion['altura_terreno'] . ' m',
                    'tag' => $estacion['tag_codigo_iner'],
                    'estado' => $estacion['estado_texto'],
                    'status' => $estacion['status'],
                    'fecha_instalacion' => $estacion['fecha_instalacion'],
                    'imagenes' => [
                        'fotografia' => $estacion['ruta_fotografia'],
                        'mapa' => $estacion['ruta_mapa']
                    ]
                ];
            }, $estaciones)
        ];

        ob_clean();
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    // ================================================================
    // PRIORIDAD 2: OBTENER AÑOS DISPONIBLES PARA UNA ESTACIÓN
    // ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_years') {
        error_log("=== OBTENIENDO AÑOS ===");

        if (!isset($_GET['id_estacion']) || empty($_GET['id_estacion'])) {
            throw new Exception("ID de estación requerido");
        }

        if (!isset($_GET['nivel']) || empty($_GET['nivel'])) {
            throw new Exception("Nivel de datos requerido");
        }

        $id_estacion = intval($_GET['id_estacion']);
        $nivel = $_GET['nivel'];

        if (!in_array($nivel, ['L0', 'L1', 'L2'])) {
            throw new Exception("Nivel debe ser L0, L1 o L2");
        }

        $anios = $modeloDashboard->obtenerAniosDisponibles($id_estacion, $nivel);

        $response = [
            'success' => true,
            'data' => $anios
        ];

        ob_clean();
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    // ================================================================
    // PRIORIDAD 3: OBTENER MESES DISPONIBLES PARA UNA ESTACIÓN Y AÑO
    // ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_months') {
        error_log("=== OBTENIENDO MESES ===");

        if (!isset($_GET['id_estacion']) || empty($_GET['id_estacion'])) {
            throw new Exception("ID de estación requerido");
        }

        if (!isset($_GET['anio']) || empty($_GET['anio'])) {
            throw new Exception("Año requerido");
        }

        if (!isset($_GET['nivel']) || empty($_GET['nivel'])) {
            throw new Exception("Nivel de datos requerido");
        }

        $id_estacion = intval($_GET['id_estacion']);
        $anio = intval($_GET['anio']);
        $nivel = $_GET['nivel'];

        if (!in_array($nivel, ['L0', 'L1', 'L2'])) {
            throw new Exception("Nivel debe ser L0, L1 o L2");
        }

        $meses = $modeloDashboard->obtenerMesesDisponibles($id_estacion, $anio, $nivel);

        // Agregar nombres de meses en español
        $meses_nombres = [];
        $nombres_meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        ];

        foreach ($meses as $mes) {
            $meses_nombres[] = [
                'numero' => $mes,
                'nombre' => $nombres_meses[$mes]
            ];
        }

        $response = [
            'success' => true,
            'data' => $meses_nombres
        ];

        ob_clean();
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    // ================================================================
    // PRIORIDAD 4: OBTENER INFORMACIÓN COMPLETA DE UNA ESTACIÓN
    // ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_station_info') {
        error_log("=== OBTENIENDO INFORMACIÓN DE ESTACIÓN ===");

        if (!isset($_GET['id_estacion']) || empty($_GET['id_estacion'])) {
            throw new Exception("ID de estación requerido");
        }

        $id_estacion = intval($_GET['id_estacion']);
        $estacion = $modeloDashboard->obtenerInformacionEstacion($id_estacion);

        $response = [
            'success' => true,
            'data' => [
                'id' => $estacion['id_estacion'],
                'codigo' => $estacion['codigo'],
                'nombre' => $estacion['nombre'],
                'ubicacion' => $estacion['comunidad'] . ', ' . $estacion['canton'] . ', ' . $estacion['provincia'],
                'comunidad' => $estacion['comunidad'],
                'canton' => $estacion['canton'],
                'provincia' => $estacion['provincia'],
                'altura' => $estacion['altura_terreno'] . 'm',
                'coordenadas' => [
                    'lat' => floatval($estacion['latitud']),
                    'lng' => floatval($estacion['longitud'])
                ],
                'tag' => $estacion['tag_codigo_iner'],
                'estado' => $estacion['estado_texto'],
                'fecha_instalacion' => $estacion['fecha_instalacion'],
                'estadisticas' => [
                    'total_registros' => intval($estacion['estadisticas']['total_registros']),
                    'registros_l0' => intval($estacion['estadisticas']['registros_l0']),
                    'registros_l1' => intval($estacion['estadisticas']['registros_l1']),
                    'registros_l2' => intval($estacion['estadisticas']['registros_l2']),
                    'ultima_lectura' => $estacion['estadisticas']['ultima_lectura']
                ],
                'imagenes' => [
                    'fotografia' => $estacion['ruta_fotografia'],
                    'mapa' => $estacion['ruta_mapa']
                ]
            ]
        ];

        ob_clean();
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    // ================================================================
    // PRIORIDAD 5: OBTENER DATOS PARA GRÁFICAS DEL NIVEL L0
    // ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_data_l0') {
        error_log("=== OBTENIENDO DATOS L0 ===");

        if (!isset($_GET['id_estacion']) || empty($_GET['id_estacion'])) {
            throw new Exception("ID de estación requerido");
        }

        $id_estacion = intval($_GET['id_estacion']);
        $anio = isset($_GET['anio']) ? intval($_GET['anio']) : null;
        $mes = isset($_GET['mes']) ? intval($_GET['mes']) : null;

        $datos = $modeloDashboard->obtenerDatosL0($id_estacion, $anio, $mes);

        $response = [
            'success' => true,
            'data' => $datos,
            'count' => count($datos)
        ];

        ob_clean();
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }


    // ================================================================
// NUEVA: OBTENER DATOS PARA GRÁFICA L0 - VELOCIDAD DEL VIENTO
// ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_chart_l0_velocidad') {
        error_log("=== OBTENIENDO DATOS PARA GRÁFICA L0 - VELOCIDAD ===");

        if (!isset($_GET['id_estacion']) || empty($_GET['id_estacion'])) {
            throw new Exception("ID de estación requerido");
        }

        $id_estacion = intval($_GET['id_estacion']);

        // Manejo correcto de año y mes
        $anio = isset($_GET['anio']) && $_GET['anio'] !== '' ? intval($_GET['anio']) : null;
        $mes = isset($_GET['mes']) && $_GET['mes'] !== '' && $_GET['mes'] !== 'null' ? intval($_GET['mes']) : null;

        error_log("📋 Parámetros recibidos:");
        error_log("   - ID Estación: " . $id_estacion);
        error_log("   - Año: " . ($anio ?? 'NULL'));
        error_log("   - Mes: " . ($mes ?? 'NULL (todos)'));

        $datos = $modeloDashboard->obtenerDatosL0ParaGrafica($id_estacion, $anio, $mes);

        error_log("✅ Datos obtenidos: " . $datos['total_registros'] . " registros");

        $response = [
            'success' => true,
            'data' => $datos
        ];

        ob_clean();
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    // ================================================================
// NUEVA: OBTENER DATOS PARA GRÁFICA L0 - DIRECCIÓN DEL VIENTO
// ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_chart_l0_direccion') {
        error_log("=== OBTENIENDO DATOS PARA GRÁFICA L0 - DIRECCIÓN ===");

        if (!isset($_GET['id_estacion']) || empty($_GET['id_estacion'])) {
            throw new Exception("ID de estación requerido");
        }

        $id_estacion = intval($_GET['id_estacion']);
        $anio = isset($_GET['anio']) && $_GET['anio'] !== '' ? intval($_GET['anio']) : null;
        $mes = isset($_GET['mes']) && $_GET['mes'] !== '' && $_GET['mes'] !== 'null' ? intval($_GET['mes']) : null;

        error_log("📋 Parámetros recibidos:");
        error_log("   - ID Estación: " . $id_estacion);
        error_log("   - Año: " . ($anio ?? 'NULL'));
        error_log("   - Mes: " . ($mes ?? 'NULL (todos)'));

        $datos = $modeloDashboard->obtenerDireccionesVientoPorHora($id_estacion, $anio, $mes);

        error_log("✅ Datos obtenidos: " . $datos['total_horas'] . " horas");

        $response = [
            'success' => true,
            'data' => $datos
        ];

        ob_clean();
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }


    // ================================================================
    // PRIORIDAD 6: OBTENER DATOS PARA GRÁFICAS DEL NIVEL L1
    // ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_data_l1') {
        error_log("=== OBTENIENDO DATOS L1 ===");

        if (!isset($_GET['id_estacion']) || empty($_GET['id_estacion'])) {
            throw new Exception("ID de estación requerido");
        }

        $id_estacion = intval($_GET['id_estacion']);
        $anio = isset($_GET['anio']) ? intval($_GET['anio']) : null;
        $mes = isset($_GET['mes']) ? intval($_GET['mes']) : null;

        $datos = $modeloDashboard->obtenerDatosL1($id_estacion, $anio, $mes);

        $response = [
            'success' => true,
            'data' => $datos,
            'count' => count($datos)
        ];

        ob_clean();
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    // ================================================================
    // PRIORIDAD 7: OBTENER DATOS PARA GRÁFICAS DEL NIVEL L2
    // ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_data_l2') {
        error_log("=== OBTENIENDO DATOS L2 ===");

        if (!isset($_GET['id_estacion']) || empty($_GET['id_estacion'])) {
            throw new Exception("ID de estación requerido");
        }

        $id_estacion = intval($_GET['id_estacion']);
        $anio = isset($_GET['anio']) ? intval($_GET['anio']) : null;
        $mes = isset($_GET['mes']) ? intval($_GET['mes']) : null;

        $datos = $modeloDashboard->obtenerDatosL2($id_estacion, $anio, $mes);

        $response = [
            'success' => true,
            'data' => $datos,
            'count' => count($datos)
        ];

        ob_clean();
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    // ================================================================
    // PRIORIDAD 8: OBTENER ESTADÍSTICAS GENERALES
    // ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_general_stats') {
        error_log("=== OBTENIENDO ESTADÍSTICAS GENERALES ===");

        $stats = $modeloDashboard->obtenerEstadisticasGenerales();

        $response = [
            'success' => true,
            'data' => [
                'total_estaciones' => intval($stats['total_estaciones']),
                'total_registros' => intval($stats['total_registros']),
                'estaciones_activas' => intval($stats['estaciones_con_datos']),
                'ultima_actualizacion' => $stats['ultima_actualizacion']
            ]
        ];

        ob_clean();
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }
    // ================================================================
// ENDPOINT UNIFICADO: CARGA INICIAL COMPLETA
// Retorna TODA la información necesaria en UNA sola petición
// ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_initial_data') {
        error_log("=== CARGA INICIAL UNIFICADA ===");

        try {
            // 1. Estadísticas generales
            $stats = $modeloDashboard->obtenerEstadisticasGenerales();

            // 2. Todas las estaciones
            $estaciones = $modeloDashboard->obtenerTodasLasEstaciones();

            $response = [
                'success' => true,
                'data' => [
                    'estadisticas' => [
                        'total_estaciones' => intval($stats['total_estaciones']),
                        'total_registros' => intval($stats['total_registros']),
                        'estaciones_activas' => intval($stats['estaciones_con_datos']),
                        'ultima_actualizacion' => $stats['ultima_actualizacion']
                    ],
                    'estaciones' => array_map(function ($estacion) {
                        return [
                            'id' => intval($estacion['id_estacion']),
                            'codigo' => $estacion['codigo'],
                            'nombre' => $estacion['nombre'],
                            'ubicacion' => $estacion['comunidad'] . ', ' . $estacion['canton'] . ', ' . $estacion['provincia'],
                            'imagenes' => [
                                'fotografia' => $estacion['ruta_fotografia'],
                                'mapa' => $estacion['ruta_mapa']
                            ]
                        ];
                    }, $estaciones)
                ]
            ];

            ob_clean();
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit();

        } catch (Exception $e) {
            http_response_code(500);
            ob_clean();
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }
    }

    // ================================================================
// ENDPOINT OPTIMIZADO: INFORMACIÓN COMPLETA DE ESTACIÓN
// Incluye: Info + Estadísticas + Años + Meses disponibles
// ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_station_complete') {
        error_log("=== INFORMACIÓN COMPLETA DE ESTACIÓN ===");

        if (!isset($_GET['id_estacion']) || empty($_GET['id_estacion'])) {
            throw new Exception("ID de estación requerido");
        }

        if (!isset($_GET['nivel']) || empty($_GET['nivel'])) {
            throw new Exception("Nivel de datos requerido");
        }

        $id_estacion = intval($_GET['id_estacion']);
        $nivel = $_GET['nivel'];

        try {
            // 1. Información de estación (incluye estadísticas en una sola query)
            $estacion = $modeloDashboard->obtenerInformacionEstacion($id_estacion);

            // 2. Años y meses en una sola consulta
            $tiempo_data = $modeloDashboard->obtenerAniosYMesesDisponibles($id_estacion, $nivel);

            // 3. Estadísticas por nivel
            $stats_nivel = $modeloDashboard->obtenerEstadisticasEstacionNivel($id_estacion, $nivel);

            $response = [
                'success' => true,
                'data' => [
                    'estacion' => [
                        'id' => $estacion['id_estacion'],
                        'codigo' => $estacion['codigo'],
                        'nombre' => $estacion['nombre'],
                        'ubicacion' => $estacion['comunidad'] . ', ' . $estacion['canton'] . ', ' . $estacion['provincia'],
                        'comunidad' => $estacion['comunidad'],
                        'canton' => $estacion['canton'],
                        'provincia' => $estacion['provincia'],
                        'altura' => $estacion['altura_terreno'] . 'm',
                        'tag' => $estacion['tag_codigo_iner'],
                        'estado' => $estacion['estado_texto'],
                        'fecha_instalacion' => $estacion['fecha_instalacion'],
                        'imagenes' => [
                            'fotografia' => $estacion['ruta_fotografia'],
                            'mapa' => $estacion['ruta_mapa']
                        ]
                    ],
                    'tiempo' => $tiempo_data,
                    'estadisticas' => $stats_nivel
                ]
            ];

            ob_clean();
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit();

        } catch (Exception $e) {
            http_response_code(500);
            ob_clean();
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }
    }

    // ================================================================
// PRIORIDAD 8B: OBTENER ESTADÍSTICAS POR ESTACIÓN Y NIVEL
// ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_station_level_stats') {
        error_log("=== OBTENIENDO ESTADÍSTICAS POR ESTACIÓN Y NIVEL ===");

        if (!isset($_GET['id_estacion']) || empty($_GET['id_estacion'])) {
            throw new Exception("ID de estación requerido");
        }

        if (!isset($_GET['nivel']) || empty($_GET['nivel'])) {
            throw new Exception("Nivel de datos requerido");
        }

        $id_estacion = intval($_GET['id_estacion']);
        $nivel = $_GET['nivel'];

        if (!in_array($nivel, ['L0', 'L1', 'L2'])) {
            throw new Exception("Nivel debe ser L0, L1 o L2");
        }

        $stats = $modeloDashboard->obtenerEstadisticasEstacionNivel($id_estacion, $nivel);

        $response = [
            'success' => true,
            'data' => [
                'total_registros' => intval($stats['total_registros']),
                'estaciones_activas' => intval($stats['estaciones_activas']),
                'ultima_actualizacion' => $stats['ultima_actualizacion']
            ]
        ];

        ob_clean();
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }


    // ================================================================
    // PRIORIDAD 9: OBTENER DATOS COMPARATIVOS
    // ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_comparison_data') {
        error_log("=== OBTENIENDO DATOS COMPARATIVOS ===");

        if (!isset($_GET['estaciones']) || empty($_GET['estaciones'])) {
            throw new Exception("IDs de estaciones requeridos");
        }

        if (!isset($_GET['anio']) || empty($_GET['anio'])) {
            throw new Exception("Año requerido");
        }

        if (!isset($_GET['mes']) || empty($_GET['mes'])) {
            throw new Exception("Mes requerido");
        }

        if (!isset($_GET['nivel']) || empty($_GET['nivel'])) {
            throw new Exception("Nivel de datos requerido");
        }

        $estaciones_ids = explode(',', $_GET['estaciones']);
        $estaciones_ids = array_map('intval', $estaciones_ids);
        $anio = intval($_GET['anio']);
        $mes = intval($_GET['mes']);
        $nivel = $_GET['nivel'];

        if (!in_array($nivel, ['L0', 'L1', 'L2'])) {
            throw new Exception("Nivel debe ser L0, L1 o L2");
        }

        $datos = $modeloDashboard->obtenerDatosComparativos($estaciones_ids, $anio, $mes, $nivel);

        $response = [
            'success' => true,
            'data' => $datos,
            'count' => count($datos)
        ];

        ob_clean();
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    // ================================================================
    // PRIORIDAD 10: VERIFICAR DATOS DE ESTACIÓN
    // ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'check_station_data') {
        error_log("=== VERIFICANDO DATOS DE ESTACIÓN ===");

        if (!isset($_GET['id_estacion']) || empty($_GET['id_estacion'])) {
            throw new Exception("ID de estación requerido");
        }

        if (!isset($_GET['nivel']) || empty($_GET['nivel'])) {
            throw new Exception("Nivel de datos requerido");
        }

        $id_estacion = intval($_GET['id_estacion']);
        $nivel = $_GET['nivel'];

        if (!in_array($nivel, ['L0', 'L1', 'L2'])) {
            throw new Exception("Nivel debe ser L0, L1 o L2");
        }

        $tiene_datos = $modeloDashboard->verificarDatosEstacion($id_estacion, $nivel);

        $response = [
            'success' => true,
            'data' => [
                'id_estacion' => $id_estacion,
                'nivel' => $nivel,
                'tiene_datos' => $tiene_datos
            ]
        ];

        ob_clean();
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }
    // ================================================================
// ENDPOINT: DATOS L0 AGRUPADOS POR HORA (GRÁFICA PROFESIONAL)
// ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_chart_l0_velocidad_hora') {
        error_log("=== OBTENIENDO DATOS L0 POR HORA ===");

        if (!isset($_GET['id_estacion']) || empty($_GET['id_estacion'])) {
            throw new Exception("ID de estación requerido");
        }

        $id_estacion = intval($_GET['id_estacion']);
        $anio = isset($_GET['anio']) && $_GET['anio'] !== '' ? intval($_GET['anio']) : null;
        $mes = isset($_GET['mes']) && $_GET['mes'] !== '' && $_GET['mes'] !== 'null' ? intval($_GET['mes']) : null;

        $datos = $modeloDashboard->obtenerDatosL0PorHora($id_estacion, $anio, $mes);

        $response = [
            'success' => true,
            'data' => $datos
        ];

        ob_clean();
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();


    }


    // ================================================================
// ENDPOINT: CHART L1 - TEMPERATURA DIARIA
// ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_chart_l1_temperatura') {
        error_log("=== OBTENIENDO DATOS L1 - TEMPERATURA ===");

        if (!isset($_GET['id_estacion']) || empty($_GET['id_estacion'])) {
            throw new Exception("ID de estación requerido");
        }

        $id_estacion = intval($_GET['id_estacion']);
        $anio = isset($_GET['anio']) && $_GET['anio'] !== '' ? intval($_GET['anio']) : null;
        $mes = isset($_GET['mes']) && $_GET['mes'] !== '' && $_GET['mes'] !== 'null' ? intval($_GET['mes']) : null;

        error_log("📋 Parámetros recibidos:");
        error_log("   - ID Estación: " . $id_estacion);
        error_log("   - Año: " . ($anio ?? 'NULL'));
        error_log("   - Mes: " . ($mes ?? 'NULL (todos)'));

        $datos = $modeloDashboard->obtenerDatosL1TemperaturaPorHora($id_estacion, $anio, $mes);

        error_log("✅ Datos obtenidos: " . $datos['total_horas'] . " horas");

        $response = [
            'success' => true,
            'data' => $datos
        ];

        ob_clean();
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    // ================================================================
// ENDPOINT: CHART L1 - HUMEDAD RELATIVA (DISTRIBUCIÓN RADAR)
// ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_chart_l1_humedad') {
        error_log("=== OBTENIENDO DATOS L1 - HUMEDAD RELATIVA ===");

        if (!isset($_GET['id_estacion']) || empty($_GET['id_estacion'])) {
            throw new Exception("ID de estación requerido");
        }

        $id_estacion = intval($_GET['id_estacion']);
        $anio = isset($_GET['anio']) && $_GET['anio'] !== '' ? intval($_GET['anio']) : null;
        $mes = isset($_GET['mes']) && $_GET['mes'] !== '' && $_GET['mes'] !== 'null' ? intval($_GET['mes']) : null;

        error_log("📋 Parámetros recibidos:");
        error_log("   - ID Estación: " . $id_estacion);
        error_log("   - Año: " . ($anio ?? 'NULL'));
        error_log("   - Mes: " . ($mes ?? 'NULL (todos)'));

        $datos = $modeloDashboard->obtenerDistribucionHumedadL1($id_estacion, $anio, $mes);

        error_log("✅ Datos obtenidos: " . $datos['total_rangos'] . " rangos");

        $response = [
            'success' => true,
            'data' => $datos
        ];

        ob_clean();
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }
    // ================================================================
// ENDPOINT: CHART L1 - PRESIÓN BAROMÉTRICA (BARRAS)
// ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_chart_l1_presion') {
        error_log("=== OBTENIENDO DATOS L1 - PRESIÓN BAROMÉTRICA ===");

        if (!isset($_GET['id_estacion']) || empty($_GET['id_estacion'])) {
            throw new Exception("ID de estación requerido");
        }

        $id_estacion = intval($_GET['id_estacion']);
        $anio = isset($_GET['anio']) && $_GET['anio'] !== '' ? intval($_GET['anio']) : null;
        $mes = isset($_GET['mes']) && $_GET['mes'] !== '' && $_GET['mes'] !== 'null' ? intval($_GET['mes']) : null;

        error_log("📋 Parámetros recibidos:");
        error_log("   - ID Estación: " . $id_estacion);
        error_log("   - Año: " . ($anio ?? 'NULL'));
        error_log("   - Mes: " . ($mes ?? 'NULL (todos)'));

        $datos = $modeloDashboard->obtenerPresionBarometricaPorDia($id_estacion, $anio, $mes);

        error_log("✅ Datos obtenidos: " . $datos['total_dias'] . " días");

        $response = [
            'success' => true,
            'data' => $datos
        ];

        ob_clean();
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    // ================================================================
// ENDPOINT: CHART L2-1 - TENDENCIA ANUAL MULTIVARIABLE
// ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_chart_l2_tendencia_anual') {
        error_log("=== OBTENIENDO DATOS L2 - TENDENCIA ANUAL ===");

        if (!isset($_GET['id_estacion']) || empty($_GET['id_estacion'])) {
            throw new Exception("ID de estación requerido");
        }

        $id_estacion = intval($_GET['id_estacion']);
        $anio = isset($_GET['anio']) && $_GET['anio'] !== '' ? intval($_GET['anio']) : null;
        $mes = isset($_GET['mes']) && $_GET['mes'] !== '' && $_GET['mes'] !== 'null' ? intval($_GET['mes']) : null;

        error_log("📋 Parámetros recibidos:");
        error_log("   - ID Estación: " . $id_estacion);
        error_log("   - Año: " . ($anio ?? 'NULL'));
        error_log("   - Mes: " . ($mes ?? 'NULL (todos)'));

        $datos = $modeloDashboard->obtenerTendenciaAnualL2($id_estacion, $anio, $mes);

        error_log("✅ Datos obtenidos: " . $datos['total_meses'] . " períodos");

        $response = [
            'success' => true,
            'data' => $datos
        ];

        ob_clean();
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    // ================================================================
// ENDPOINT: CHART L2-2 - CLIMOGRAMA INTEGRAL (TEMPERATURA + VIENTO)
// ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_chart_l2_climograma') {
        error_log("=== OBTENIENDO DATOS L2 - CLIMOGRAMA INTEGRAL ===");

        if (!isset($_GET['id_estacion']) || empty($_GET['id_estacion'])) {
            throw new Exception("ID de estación requerido");
        }

        $id_estacion = intval($_GET['id_estacion']);
        $anio = isset($_GET['anio']) && $_GET['anio'] !== '' ? intval($_GET['anio']) : null;
        $mes = isset($_GET['mes']) && $_GET['mes'] !== '' && $_GET['mes'] !== 'null' ? intval($_GET['mes']) : null;

        error_log("📋 Parámetros recibidos:");
        error_log("   - ID Estación: " . $id_estacion);
        error_log("   - Año: " . ($anio ?? 'NULL'));
        error_log("   - Mes: " . ($mes ?? 'NULL (todos)'));

        $datos = $modeloDashboard->obtenerClimogramaL2($id_estacion, $anio, $mes);

        error_log("✅ Datos obtenidos: " . $datos['total_periodos'] . " períodos");

        $response = [
            'success' => true,
            'data' => $datos
        ];

        ob_clean();
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    // ================================================================
// ENDPOINT: CHART L1 - VELOCIDAD DEL VIENTO + TEMPERATURA (DOBLE EJE)
// ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_chart_l1_viento_temperatura') {
        error_log("=== OBTENIENDO DATOS L1 - VIENTO + TEMPERATURA ===");

        if (!isset($_GET['id_estacion']) || empty($_GET['id_estacion'])) {
            throw new Exception("ID de estación requerido");
        }

        $id_estacion = intval($_GET['id_estacion']);
        $anio = isset($_GET['anio']) && $_GET['anio'] !== '' ? intval($_GET['anio']) : null;
        $mes = isset($_GET['mes']) && $_GET['mes'] !== '' && $_GET['mes'] !== 'null' ? intval($_GET['mes']) : null;

        error_log("📋 Parámetros recibidos:");
        error_log("   - ID Estación: " . $id_estacion);
        error_log("   - Año: " . ($anio ?? 'NULL'));
        error_log("   - Mes: " . ($mes ?? 'NULL (todos)'));

        $datos = $modeloDashboard->obtenerVientoTemperaturaPorHora($id_estacion, $anio, $mes);

        error_log("✅ Datos obtenidos: " . $datos['total_horas'] . " horas");

        $response = [
            'success' => true,
            'data' => $datos
        ];

        ob_clean();
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    // ================================================================
// ENDPOINT: CHART L1-5 - HEATMAP DE PRESIÓN ATMOSFÉRICA
// ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_chart_l1_heatmap_presion') {
        error_log("=== OBTENIENDO DATOS L1 - HEATMAP PRESIÓN ===");

        if (!isset($_GET['id_estacion']) || empty($_GET['id_estacion'])) {
            throw new Exception("ID de estación requerido");
        }

        $id_estacion = intval($_GET['id_estacion']);
        $anio = isset($_GET['anio']) && $_GET['anio'] !== '' ? intval($_GET['anio']) : null;
        $mes = isset($_GET['mes']) && $_GET['mes'] !== '' && $_GET['mes'] !== 'null' ? intval($_GET['mes']) : null;

        error_log("📋 Parámetros recibidos:");
        error_log("   - ID Estación: " . $id_estacion);
        error_log("   - Año: " . ($anio ?? 'NULL'));
        error_log("   - Mes: " . ($mes ?? 'NULL (todos)'));

        $datos = $modeloDashboard->obtenerHeatmapPresionAtmosferica($id_estacion, $anio, $mes);

        error_log("✅ Datos obtenidos: " . $datos['total_puntos'] . " puntos");

        $response = [
            'success' => true,
            'data' => $datos
        ];

        ob_clean();
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }


    // ================================================================
// ENDPOINT: CHART L1-5 - HEATMAP DE RADIACIÓN SOLAR
// ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_chart_l1_heatmap_radiacion') {
        error_log("=== OBTENIENDO DATOS L1 - HEATMAP RADIACIÓN SOLAR ===");

        if (!isset($_GET['id_estacion']) || empty($_GET['id_estacion'])) {
            throw new Exception("ID de estación requerido");
        }

        $id_estacion = intval($_GET['id_estacion']);
        $anio = isset($_GET['anio']) && $_GET['anio'] !== '' ? intval($_GET['anio']) : null;
        $mes = isset($_GET['mes']) && $_GET['mes'] !== '' && $_GET['mes'] !== 'null' ? intval($_GET['mes']) : null;

        error_log("📋 Parámetros recibidos:");
        error_log("   - ID Estación: " . $id_estacion);
        error_log("   - Año: " . ($anio ?? 'NULL'));
        error_log("   - Mes: " . ($mes ?? 'NULL (todos)'));

        $datos = $modeloDashboard->obtenerHeatmapRadiacionSolar($id_estacion, $anio, $mes);

        error_log("✅ Datos obtenidos: " . $datos['total_puntos'] . " puntos");

        $response = [
            'success' => true,
            'data' => $datos
        ];

        ob_clean();
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    // ================================================================
// ENDPOINT: CHART L1-6 - BALANCE ENERGÉTICO (RADIACIÓN + TEMPERATURA + VIENTO)
// ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_chart_l1_balance_energetico') {
        error_log("=== OBTENIENDO DATOS L1 - BALANCE ENERGÉTICO ===");

        if (!isset($_GET['id_estacion']) || empty($_GET['id_estacion'])) {
            throw new Exception("ID de estación requerido");
        }

        $id_estacion = intval($_GET['id_estacion']);
        $anio = isset($_GET['anio']) && $_GET['anio'] !== '' ? intval($_GET['anio']) : null;
        $mes = isset($_GET['mes']) && $_GET['mes'] !== '' && $_GET['mes'] !== 'null' ? intval($_GET['mes']) : null;

        error_log("📋 Parámetros recibidos:");
        error_log("   - ID Estación: " . $id_estacion);
        error_log("   - Año: " . ($anio ?? 'NULL'));
        error_log("   - Mes: " . ($mes ?? 'NULL (todos)'));

        $datos = $modeloDashboard->obtenerBalanceEnergeticoPorHora($id_estacion, $anio, $mes);

        error_log("✅ Datos obtenidos: " . $datos['total_horas'] . " horas");

        $response = [
            'success' => true,
            'data' => $datos
        ];

        ob_clean();
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }
    // ================================================================
// ENDPOINT: CHART L1-7 - PUNTO DE ROCÍO CALCULADO (DEW POINT)
// ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_chart_l1_punto_rocio') {
        error_log("=== OBTENIENDO DATOS L1 - PUNTO DE ROCÍO ===");

        if (!isset($_GET['id_estacion']) || empty($_GET['id_estacion'])) {
            throw new Exception("ID de estación requerido");
        }

        $id_estacion = intval($_GET['id_estacion']);
        $anio = isset($_GET['anio']) && $_GET['anio'] !== '' ? intval($_GET['anio']) : null;
        $mes = isset($_GET['mes']) && $_GET['mes'] !== '' && $_GET['mes'] !== 'null' ? intval($_GET['mes']) : null;

        error_log("📋 Parámetros recibidos:");
        error_log("   - ID Estación: " . $id_estacion);
        error_log("   - Año: " . ($anio ?? 'NULL'));
        error_log("   - Mes: " . ($mes ?? 'NULL (todos)'));

        $datos = $modeloDashboard->obtenerPuntoRocioPorHora($id_estacion, $anio, $mes);

        error_log("✅ Datos obtenidos: " . $datos['total_horas'] . " horas");
        error_log("   - Alertas heladas: " . $datos['total_alertas_heladas']);
        error_log("   - Alertas humedad: " . $datos['total_alertas_humedad']);

        $response = [
            'success' => true,
            'data' => $datos
        ];

        ob_clean();
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }
    // ================================================================
// ENDPOINT: DISTRIBUCIÓN DE DIRECCIONES (ROSA DE VIENTOS)
// ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_chart_l0_rosa_vientos') {
        error_log("=== OBTENIENDO DISTRIBUCIÓN DE DIRECCIONES ===");

        if (!isset($_GET['id_estacion']) || empty($_GET['id_estacion'])) {
            throw new Exception("ID de estación requerido");
        }

        $id_estacion = intval($_GET['id_estacion']);
        $anio = isset($_GET['anio']) && $_GET['anio'] !== '' ? intval($_GET['anio']) : null;
        $mes = isset($_GET['mes']) && $_GET['mes'] !== '' && $_GET['mes'] !== 'null' ? intval($_GET['mes']) : null;

        $datos = $modeloDashboard->obtenerDistribucionDirecciones($id_estacion, $anio, $mes);

        $response = [
            'success' => true,
            'data' => $datos
        ];

        ob_clean();
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }
    // ================================================================
// ENDPOINT: GRÁFICA URBINA TIPO 1 - POR MINUTO
// ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_chart_urbina_hora') {
        error_log("=== OBTENIENDO DATOS URBINA POR HORA ===");

        if (!isset($_GET['id_estacion']) || empty($_GET['id_estacion'])) {
            throw new Exception("ID de estación requerido");
        }

        $id_estacion = intval($_GET['id_estacion']);
        $anio = isset($_GET['anio']) && $_GET['anio'] !== '' ? intval($_GET['anio']) : null;
        $mes = isset($_GET['mes']) && $_GET['mes'] !== '' && $_GET['mes'] !== 'null' ? intval($_GET['mes']) : null;

        $datos = $modeloDashboard->obtenerDatosUrbinaHora($id_estacion, $anio, $mes);

        $response = [
            'success' => true,
            'data' => $datos
        ];

        ob_clean();
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    // ================================================================
// ENDPOINT: CHART 6 - HEATMAP DE VELOCIDAD DEL VIENTO
// ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_chart_heatmap_velocidad') {
        error_log("=== OBTENIENDO DATOS HEATMAP VELOCIDAD ===");

        if (!isset($_GET['id_estacion']) || empty($_GET['id_estacion'])) {
            throw new Exception("ID de estación requerido");
        }

        $id_estacion = intval($_GET['id_estacion']);
        $anio = isset($_GET['anio']) && $_GET['anio'] !== '' ? intval($_GET['anio']) : null;
        $mes = isset($_GET['mes']) && $_GET['mes'] !== '' && $_GET['mes'] !== 'null' ? intval($_GET['mes']) : null;

        error_log("📋 Parámetros recibidos:");
        error_log("   - ID Estación: " . $id_estacion);
        error_log("   - Año: " . ($anio ?? 'NULL'));
        error_log("   - Mes: " . ($mes ?? 'NULL (todos)'));

        $datos = $modeloDashboard->obtenerDatosHeatmapVelocidad($id_estacion, $anio, $mes);

        error_log("✅ Datos Heatmap obtenidos: " . $datos['total_puntos'] . " puntos");

        $response = [
            'success' => true,
            'data' => $datos
        ];

        ob_clean();
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    // ================================================================
// ENDPOINT: CHART 7 - DETECCIÓN DE RACHAS DE VIENTO
// ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_chart_rachas_viento') {
        error_log("=== OBTENIENDO DATOS DETECCIÓN DE RACHAS ===");

        if (!isset($_GET['id_estacion']) || empty($_GET['id_estacion'])) {
            throw new Exception("ID de estación requerido");
        }

        $id_estacion = intval($_GET['id_estacion']);
        $anio = isset($_GET['anio']) && $_GET['anio'] !== '' ? intval($_GET['anio']) : null;
        $mes = isset($_GET['mes']) && $_GET['mes'] !== '' && $_GET['mes'] !== 'null' ? intval($_GET['mes']) : null;

        error_log("📋 Parámetros recibidos:");
        error_log("   - ID Estación: " . $id_estacion);
        error_log("   - Año: " . ($anio ?? 'NULL'));
        error_log("   - Mes: " . ($mes ?? 'NULL (todos)'));

        $datos = $modeloDashboard->obtenerDatosRachasViento($id_estacion, $anio, $mes);

        error_log("✅ Datos Rachas obtenidos: " . $datos['total_rachas'] . " rachas detectadas");

        $response = [
            'success' => true,
            'data' => $datos
        ];

        ob_clean();
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    // ================================================================
// ENDPOINT: GRÁFICA URBINA TIPO 2 - POR SECTOR
// ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_chart_urbina_sector') {
        error_log("=== OBTENIENDO DATOS URBINA POR SECTOR ===");

        if (!isset($_GET['id_estacion']) || empty($_GET['id_estacion'])) {
            throw new Exception("ID de estación requerido");
        }

        $id_estacion = intval($_GET['id_estacion']);
        $anio = isset($_GET['anio']) && $_GET['anio'] !== '' ? intval($_GET['anio']) : null;
        $mes = isset($_GET['mes']) && $_GET['mes'] !== '' && $_GET['mes'] !== 'null' ? intval($_GET['mes']) : null;

        error_log("📋 Parámetros recibidos:");
        error_log("   - ID Estación: " . $id_estacion);
        error_log("   - Año: " . ($anio ?? 'NULL'));
        error_log("   - Mes: " . ($mes ?? 'NULL (todos)'));

        $datos = $modeloDashboard->obtenerDatosUrbinaSector($id_estacion, $anio, $mes);

        error_log("✅ Datos Urbina Sector obtenidos");
        error_log("   - Total sectores: " . $datos['total_sectores']);

        $response = [
            'success' => true,
            'data' => $datos
        ];

        ob_clean();
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    // ================================================================
    // GRÁFICA L2: TENDENCIA ANUAL
    // ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_chart_l2_tendencia_anual') {
        error_log("=== OBTENIENDO TENDENCIA ANUAL L2 ===");

        if (!isset($_GET['id_estacion']) || empty($_GET['id_estacion'])) {
            throw new Exception("ID de estación requerido");
        }

        if (!isset($_GET['anio']) || empty($_GET['anio'])) {
            throw new Exception("Año requerido");
        }

        if (!isset($_GET['mes']) || empty($_GET['mes'])) {
            throw new Exception("Mes requerido");
        }

        $id_estacion = intval($_GET['id_estacion']);
        $anio = intval($_GET['anio']);
        $mes = intval($_GET['mes']);

        error_log("📋 Parámetros: Estación=$id_estacion, Año=$anio, Mes=$mes");

        $datos = $modeloDashboard->obtenerTendenciaAnualL2($id_estacion, $anio, $mes);

        if (empty($datos['labels'])) {
            error_log("⚠️ Sin datos para Tendencia Anual L2");
            $response = [
                'exito' => false,
                'mensaje' => 'Sin datos para Tendencia Anual L2',
                'datos' => []
            ];
        } else {
            error_log("✅ Tendencia Anual L2 obtenida: " . count($datos['labels']) . " registros");
            $response = [
                'exito' => true,
                'datos' => $datos,
                'mensaje' => 'Datos de tendencia anual L2 obtenidos correctamente'
            ];
        }

        ob_clean();
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    // ================================================================
    // GRÁFICA L2: CLIMOGRAMA
    // ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_chart_l2_climograma') {
        error_log("=== OBTENIENDO CLIMOGRAMA L2 ===");

        if (!isset($_GET['id_estacion']) || empty($_GET['id_estacion'])) {
            throw new Exception("ID de estación requerido");
        }

        if (!isset($_GET['anio']) || empty($_GET['anio'])) {
            throw new Exception("Año requerido");
        }

        $id_estacion = intval($_GET['id_estacion']);
        $anio = intval($_GET['anio']);

        error_log("📋 Parámetros: Estación=$id_estacion, Año=$anio");

        $datos = $modeloDashboard->obtenerClimogramaL2($id_estacion, $anio);

        if (empty($datos['labels'])) {
            error_log("⚠️ Sin datos para Climograma L2");
            $response = [
                'exito' => false,
                'mensaje' => 'Sin datos para Climograma L2',
                'datos' => []
            ];
        } else {
            error_log("✅ Climograma L2 obtenido");
            $response = [
                'exito' => true,
                'datos' => $datos,
                'mensaje' => 'Datos del climograma L2 obtenidos correctamente'
            ];
        }

        ob_clean();
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }


    // ================================================================
    // GRÁFICA L2: ANÁLISIS DE TENDENCIAS
    // ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_chart_l2_analisis_tendencias') {
        if (!isset($_GET['id_estacion']) || empty($_GET['id_estacion']))
            throw new Exception("ID de estación requerido");
        if (!isset($_GET['anio']) || empty($_GET['anio']))
            throw new Exception("Año requerido");

        $id_estacion = intval($_GET['id_estacion']);
        $anio = intval($_GET['anio']);

        $datos = $modeloDashboard->obtenerAnalisisTendenciasL2($id_estacion, $anio);

        ob_clean();
        echo json_encode(['exito' => true, 'datos' => $datos], JSON_UNESCAPED_UNICODE);
        exit();
    }

    // ================================================================
    // GRÁFICA L2: DISTRIBUCIÓN ESTACIONAL
    // ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_chart_l2_distribucion_estacional') {
        if (!isset($_GET['id_estacion']) || empty($_GET['id_estacion']))
            throw new Exception("ID de estación requerido");

        $id_estacion = intval($_GET['id_estacion']);

        $datos = $modeloDashboard->obtenerDistribucionEstacionalL2($id_estacion);

        ob_clean();
        echo json_encode(['exito' => true, 'datos' => $datos], JSON_UNESCAPED_UNICODE);
        exit();
    }

    // ================================================================
    // GRÁFICA L2: ANÁLISIS DE PRECIPITACIONES
    // ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_chart_l2_precipitaciones') {
        if (!isset($_GET['id_estacion']) || empty($_GET['id_estacion']))
            throw new Exception("ID de estación requerido");
        if (!isset($_GET['anio']) || empty($_GET['anio']))
            throw new Exception("Año requerido");

        $id_estacion = intval($_GET['id_estacion']);
        $anio = intval($_GET['anio']);

        $datos = $modeloDashboard->obtenerAnalisisPrecipitacionesL2($id_estacion, $anio);

        ob_clean();
        echo json_encode(['exito' => true, 'datos' => $datos], JSON_UNESCAPED_UNICODE);
        exit();
    }

    // ================================================================
    // GRÁFICA L2: ÍNDICE DE ARIDEZ
    // ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_chart_l2_indice_aridez') {
        if (!isset($_GET['id_estacion']) || empty($_GET['id_estacion']))
            throw new Exception("ID de estación requerido");
        if (!isset($_GET['anio']) || empty($_GET['anio']))
            throw new Exception("Año requerido");

        $id_estacion = intval($_GET['id_estacion']);
        $anio = intval($_GET['anio']);

        $datos = $modeloDashboard->obtenerIndiceAridezL2($id_estacion, $anio);

        ob_clean();
        echo json_encode(['exito' => true, 'datos' => $datos], JSON_UNESCAPED_UNICODE);
        exit();
    }

    // ================================================================
    // GRÁFICA L2: ANÁLISIS DE HELADAS
    // ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'get_chart_l2_analisis_heladas') {
        if (!isset($_GET['id_estacion']) || empty($_GET['id_estacion']))
            throw new Exception("ID de estación requerido");
        if (!isset($_GET['anio']) || empty($_GET['anio']))
            throw new Exception("Año requerido");

        $id_estacion = intval($_GET['id_estacion']);
        $anio = intval($_GET['anio']);

        $datos = $modeloDashboard->obtenerAnalisisHeladasL2($id_estacion, $anio);

        ob_clean();
        echo json_encode(['exito' => true, 'datos' => $datos], JSON_UNESCAPED_UNICODE);
        exit();
    }

    // ================================================================
    // ACCIÓN NO VÁLIDA
    // ================================================================
    throw new Exception("No se especificó ninguna acción válida o acción no reconocida");

} catch (Exception $e) {
    http_response_code(400);

    // Extraer mensaje de error más específico
    $error_message = $e->getMessage();

    // Mejorar mensajes de error comunes
    if (strpos($error_message, 'no rows') !== false || strpos($error_message, 'not found') !== false) {
        $error_message = "No se encontraron datos para los parámetros especificados";
    } elseif (strpos($error_message, 'invalid') !== false) {
        $error_message = "Parámetros inválidos proporcionados";
    }

    $response = [
        'success' => false,
        'error' => $error_message
    ];

    error_log("ERROR EN CONTROLADOR DASHBOARD: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    // Limpiar buffer y enviar error
    ob_clean();
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit();
}
?>