<?php
// controlador/CInfoSensor.php
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

require_once '../model/MInforSensor.php';

try {
    $modeloEstaciones = new MInforSensor();

    // ================================================================
    // PRIORIDAD 1: CREAR ESTACIÓN CON ARCHIVOS (POST + multipart/form-data)
    // ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'add_station' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        error_log("=== INICIO CREACIÓN DE ESTACIÓN ===");
        error_log("POST data recibido: " . print_r($_POST, true));
        error_log("FILES data recibido: " . print_r(array_keys($_FILES), true));

        // ✅ VALIDACIÓN CRÍTICA: Verificar que existen arrays de sensores/componentes
        if (!isset($_POST['sensor_tipo']) || !is_array($_POST['sensor_tipo'])) {
            error_log("❌ ERROR CRÍTICO: No se recibió el array sensor_tipo");
            error_log("POST completo: " . print_r($_POST, true));
            ob_clean();
            echo json_encode([
                'success' => false,
                'error' => 'DATOS INCOMPLETOS: No se recibió información de sensores. Verifique que el formulario se está enviando correctamente.'
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        // Contar sensores válidos (con tipo seleccionado diferente de vacío)
        $sensores_validos = 0;
        $sensor_sin_tipo_index = -1;

        foreach ($_POST['sensor_tipo'] as $index => $tipo) {
            error_log("Sensor[$index]: tipo='$tipo' (vacio=" . (empty($tipo) ? 'SI' : 'NO') . ")");

            if (!empty($tipo) && $tipo !== '' && $tipo !== 'null' && intval($tipo) > 0) {
                $sensores_validos++;
            } else {
                $sensor_sin_tipo_index = $index + 1;
            }
        }

        error_log("Total sensores enviados: " . count($_POST['sensor_tipo']));
        error_log("Sensores válidos: $sensores_validos");

        if ($sensores_validos === 0) {
            $mensaje_error = "Debe agregar al menos 1 sensor con tipo seleccionado.";
            if ($sensor_sin_tipo_index > 0) {
                $mensaje_error .= " El SENSOR #$sensor_sin_tipo_index no tiene tipo seleccionado.";
            }

            error_log("❌ ERROR: $mensaje_error");
            ob_clean();
            echo json_encode([
                'success' => false,
                'error' => $mensaje_error
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        // Validar componentes
        if (!isset($_POST['componente_tipo']) || !is_array($_POST['componente_tipo'])) {
            error_log("❌ ERROR CRÍTICO: No se recibió el array componente_tipo");
            ob_clean();
            echo json_encode([
                'success' => false,
                'error' => 'DATOS INCOMPLETOS: No se recibió información de componentes.'
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        // Contar componentes válidos
        $componentes_validos = 0;
        $componente_sin_tipo_index = -1;

        foreach ($_POST['componente_tipo'] as $index => $tipo) {
            error_log("Componente[$index]: tipo='$tipo' (vacio=" . (empty($tipo) ? 'SI' : 'NO') . ")");

            if (!empty($tipo) && $tipo !== '' && $tipo !== 'null' && intval($tipo) > 0) {
                $componentes_validos++;
            } else {
                $componente_sin_tipo_index = $index + 1;
            }
        }

        error_log("Total componentes enviados: " . count($_POST['componente_tipo']));
        error_log("Componentes válidos: $componentes_validos");

        if ($componentes_validos < 3) {
            $mensaje_error = "Debe agregar al menos 3 componentes con tipo seleccionado (actualmente: $componentes_validos).";
            if ($componente_sin_tipo_index > 0) {
                $mensaje_error .= " El COMPONENTE #$componente_sin_tipo_index no tiene tipo seleccionado.";
            }

            error_log("❌ ERROR: $mensaje_error");
            ob_clean();
            echo json_encode([
                'success' => false,
                'error' => $mensaje_error
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        // Validar que se envían los archivos requeridos
        if (!isset($_FILES['fotografia']) || !isset($_FILES['mapa'])) {
            ob_clean();
            echo json_encode([
                'success' => false,
                'error' => 'Los archivos de fotografía y mapa son requeridos'
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        if ($_FILES['fotografia']['error'] !== UPLOAD_ERR_OK) {
            $errorMsg = "Error en el archivo de fotografía";
            switch ($_FILES['fotografia']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $errorMsg .= ": archivo demasiado grande";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $errorMsg .= ": no se seleccionó archivo";
                    break;
                default:
                    $errorMsg .= " (código: " . $_FILES['fotografia']['error'] . ")";
            }
            throw new Exception($errorMsg);
        }

        if ($_FILES['mapa']['error'] !== UPLOAD_ERR_OK) {
            $errorMsg = "Error en el archivo de mapa";
            switch ($_FILES['mapa']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $errorMsg .= ": archivo demasiado grande";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $errorMsg .= ": no se seleccionó archivo";
                    break;
                default:
                    $errorMsg .= " (código: " . $_FILES['mapa']['error'] . ")";
            }
            throw new Exception($errorMsg);
        }

        // Validar extensiones de archivos
        $fotoExt = strtolower(pathinfo($_FILES['fotografia']['name'], PATHINFO_EXTENSION));
        $mapaExt = strtolower(pathinfo($_FILES['mapa']['name'], PATHINFO_EXTENSION));

        $extensionesPermitidas = ['jpg', 'jpeg', 'png'];

        if (!in_array($fotoExt, $extensionesPermitidas)) {
            throw new Exception("La fotografía debe ser JPG, JPEG o PNG");
        }

        if (!in_array($mapaExt, $extensionesPermitidas)) {
            throw new Exception("El mapa debe ser JPG, JPEG o PNG");
        }

        error_log("Archivos validados correctamente");

        try {
            // Limpiar el buffer antes de procesar
            ob_clean();

            // Usar el método del modelo para procesar la estación con archivos
            $id_estacion = $modeloEstaciones->crearEstacion($_POST, $_FILES);

            $response = [
                'success' => true,
                'message' => 'Estación creada correctamente',
                'data' => ['id_estacion' => $id_estacion]
            ];

            error_log("Estación creada exitosamente con ID: " . $id_estacion);

            // Limpiar buffer y enviar respuesta
            ob_clean();
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit();

        } catch (Exception $e) {
            error_log("Error al crear estación: " . $e->getMessage());
            throw $e;
        }
    }

    // ================================================================
    // PRIORIDAD 2: ACTUALIZAR ESTACIÓN CON ARCHIVOS (POST + multipart/form-data)
    // ================================================================
    if (isset($_GET['action']) && $_GET['action'] === 'update_station' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        error_log("=== INICIO ACTUALIZACIÓN DE ESTACIÓN ===");
        error_log("POST data completo: " . print_r($_POST, true));

        // Validar datos requeridos
        if (!isset($_POST['id_estacion']) || empty($_POST['id_estacion'])) {
            throw new Exception("ID de estación requerido");
        }

        $id_estacion = intval($_POST['id_estacion']);

        // ✅ VALIDAR QUE EL NOMBRE ESTÉ PRESENTE
        if (!isset($_POST['nombre']) || empty(trim($_POST['nombre']))) {
            error_log("❌ ERROR: Campo 'nombre' no recibido o vacío");
            error_log("POST recibido: " . print_r($_POST, true));
            throw new Exception("El nombre de la estación es requerido");
        }

        error_log("✅ Nombre recibido: " . $_POST['nombre']);

        // Validar campos requeridos
        $required_fields = ['nombre', 'codigo', 'provincia', 'canton', 'latitud', 'longitud', 'tag_codigo_iner'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                throw new Exception("Campo requerido: " . $field);
            }
        }

        // ✅ VALIDAR DUPLICADOS (EXCLUYENDO LA ESTACIÓN ACTUAL)
        $nombre_nuevo = trim($_POST['nombre']);
        $query_check_nombre = "SELECT id_estacion FROM estaciones WHERE LOWER(nombre) = LOWER($1) AND id_estacion != $2";
        $result_check_nombre = pg_query_params($conn, $query_check_nombre, [$nombre_nuevo, $id_estacion]);
        if ($result_check_nombre && pg_num_rows($result_check_nombre) > 0) {
            throw new Exception("Ya existe otra estación con ese nombre");
        }

        // Validar código duplicado
        $codigo_nuevo = trim($_POST['codigo']);
        $query_check_codigo = "SELECT id_estacion FROM estaciones WHERE LOWER(codigo) = LOWER($1) AND id_estacion != $2";
        $result_check_codigo = pg_query_params($conn, $query_check_codigo, [$codigo_nuevo, $id_estacion]);
        if ($result_check_codigo && pg_num_rows($result_check_codigo) > 0) {
            throw new Exception("Ya existe otra estación con ese código");
        }

        // Validar TAG duplicado
        $tag_nuevo = trim($_POST['tag_codigo_iner']);
        $query_check_tag = "SELECT id_estacion FROM estaciones WHERE LOWER(tag_codigo_iner) = LOWER($1) AND id_estacion != $2";
        $result_check_tag = pg_query_params($conn, $query_check_tag, [$tag_nuevo, $id_estacion]);
        if ($result_check_tag && pg_num_rows($result_check_tag) > 0) {
            throw new Exception("Ya existe otra estación con ese TAG/Código INER");
        }

        // Validar extensiones de archivos si se envían
        if (isset($_FILES['fotografia']) && $_FILES['fotografia']['error'] === UPLOAD_ERR_OK) {
            $fotoExt = strtolower(pathinfo($_FILES['fotografia']['name'], PATHINFO_EXTENSION));
            if (!in_array($fotoExt, ['jpg', 'jpeg', 'png'])) {
                throw new Exception("La fotografía debe ser JPG, JPEG o PNG");
            }
        }

        if (isset($_FILES['mapa']) && $_FILES['mapa']['error'] === UPLOAD_ERR_OK) {
            $mapaExt = strtolower(pathinfo($_FILES['mapa']['name'], PATHINFO_EXTENSION));
            if (!in_array($mapaExt, ['jpg', 'jpeg', 'png'])) {
                throw new Exception("El mapa debe ser JPG, JPEG o PNG");
            }
        }

        try {
            // Limpiar buffer antes de procesar
            ob_clean();

            // Usar el método del modelo para actualizar la estación
            $actualizado = $modeloEstaciones->actualizarEstacion($id_estacion, $_POST, $_FILES);

            $response = [
                'success' => $actualizado,
                'message' => $actualizado ? 'Estación actualizada correctamente' : 'No se pudo actualizar la estación'
            ];

            error_log("✅ Estación actualizada exitosamente: " . $id_estacion);

            // Limpiar buffer y enviar respuesta
            ob_clean();
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit();

        } catch (Exception $e) {
            error_log("❌ Error al actualizar estación: " . $e->getMessage());
            throw $e;
        }
    }
    // ================================================================
    // PRIORIDAD 3: PROCESAR PETICIONES DELETE
    // ================================================================
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            throw new Exception("Datos JSON requeridos para operaciones DELETE");
        }

        if (!isset($input['action'])) {
            throw new Exception("Acción requerida en datos DELETE");
        }

        $action = $input['action'];

        switch ($action) {
            case 'delete_station':
                error_log("=== INICIO ELIMINACIÓN DE ESTACIÓN ===");

                if (!isset($input['id_estacion']) || empty($input['id_estacion'])) {
                    throw new Exception("ID de estación requerido");
                }

                $id_estacion = intval($input['id_estacion']);
                error_log("ID estación a eliminar: " . $id_estacion);

                try {
                    $eliminado = $modeloEstaciones->eliminarEstacion($id_estacion);

                    error_log("Resultado eliminación: " . ($eliminado ? 'EXITOSO' : 'FALLIDO'));

                    $response = [
                        'success' => $eliminado,
                        'message' => $eliminado ? 'Estación eliminada correctamente' : 'No se pudo eliminar la estación'
                    ];

                    error_log("Estación eliminada exitosamente: " . $id_estacion);
                    error_log("=== FIN ELIMINACIÓN DE ESTACIÓN ===");

                    ob_clean();
                    echo json_encode($response, JSON_UNESCAPED_UNICODE);
                    exit();

                } catch (Exception $e) {
                    error_log("❌ ERROR al eliminar estación: " . $e->getMessage());
                    error_log("Stack trace: " . $e->getTraceAsString());
                    throw new Exception("Error al eliminar estación: " . $e->getMessage());
                }
                break;

            case 'delete_sensor':
                if (!isset($input['id_sensor']) || empty($input['id_sensor'])) {
                    throw new Exception("ID de sensor requerido");
                }

                $id_sensor = intval($input['id_sensor']);
                $eliminado = $modeloEstaciones->eliminarSensor($id_sensor);

                $response = [
                    'success' => $eliminado,
                    'message' => $eliminado ? 'Sensor eliminado correctamente' : 'No se pudo eliminar el sensor'
                ];

                ob_clean();
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit();
                break;
            case 'delete_tipo_sensor':
                if (!isset($input['id']) || empty($input['id'])) {
                    throw new Exception("ID del tipo de sensor requerido");
                }

                $id_tipo = intval($input['id']);
                $eliminado = $modeloEstaciones->eliminarTipoSensor($id_tipo);

                $response = [
                    'success' => $eliminado,
                    'message' => $eliminado ? 'Tipo de sensor eliminado correctamente' : 'No se pudo eliminar'
                ];

                ob_clean();
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit();

            case 'delete_tipo_componente':
                if (!isset($input['id']) || empty($input['id'])) {
                    throw new Exception("ID del tipo de componente requerido");
                }

                $id_tipo = intval($input['id']);
                $eliminado = $modeloEstaciones->eliminarTipoComponente($id_tipo);

                $response = [
                    'success' => $eliminado,
                    'message' => $eliminado ? 'Tipo de componente eliminado correctamente' : 'No se pudo eliminar'
                ];

                ob_clean();
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit();

            case 'delete_variable':
                if (!isset($input['id']) || empty($input['id'])) {
                    throw new Exception("ID de la variable requerido");
                }

                $id = intval($input['id']);
                $eliminado = $modeloEstaciones->eliminarVariable($id);

                $response = [
                    'success' => $eliminado,
                    'message' => $eliminado ? 'Variable eliminada correctamente' : 'No se pudo eliminar'
                ];

                ob_clean();
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit();

            case 'delete_component':
                if (!isset($input['id_componente']) || empty($input['id_componente'])) {
                    throw new Exception("ID de componente requerido");
                }

                $id_componente = intval($input['id_componente']);
                $eliminado = $modeloEstaciones->eliminarComponente($id_componente);

                $response = [
                    'success' => $eliminado,
                    'message' => $eliminado ? 'Componente eliminado correctamente' : 'No se pudo eliminar el componente'
                ];

                ob_clean();
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit();
                break;

            default:
                throw new Exception("Acción DELETE no válida: " . $action);
        }
    }

    // ================================================================
    // PRIORIDAD 4: OBTENER INFORMACIÓN DE ESTACIÓN POR ID
    // ================================================================
    if (isset($_GET['id_estacion'])) {
        $id_estacion = intval($_GET['id_estacion']);

        if ($id_estacion <= 0) {
            throw new Exception("ID de estación inválido");
        }

        $estacion_completa = $modeloEstaciones->obtenerInformacionEstacionCompleta($id_estacion);

        $response = [
            'success' => true,
            'data' => [
                'estacion' => [
                    'id' => $estacion_completa['estacion']['id_estacion'],
                    'codigo' => $estacion_completa['estacion']['codigo'],
                    'nombre' => $estacion_completa['estacion']['nombre'],
                    'ubicacion' => $estacion_completa['estacion']['comunidad'] . ', ' .
                        $estacion_completa['estacion']['canton'] . ', ' .
                        $estacion_completa['estacion']['provincia'],
                    'elevacion' => $estacion_completa['estacion']['altura_terreno'] . 'm',
                    'coordenadas' => [
                        'lat' => $estacion_completa['estacion']['latitud'],
                        'lng' => $estacion_completa['estacion']['longitud']
                    ],
                    'tag' => $estacion_completa['estacion']['tag_codigo_iner'],
                    'instalacion' => $estacion_completa['estacion']['fecha_instalacion'],
                    'estado' => $estacion_completa['estacion']['estado_texto'],
                    'status' => $estacion_completa['estacion']['status']
                ],
                'componentes' => array_map(function ($comp) {
                    return [
                        'id' => $comp['id_componente'],
                        'tipo' => $comp['tipo_componente'],
                        'marca' => $comp['marca'],
                        'modelo' => $comp['modelo'],
                        'serie' => $comp['serie'],
                        'especificaciones' => $comp['especificaciones'],
                        'fecha_mantenimiento' => $comp['fecha_ultimo_mantenimiento'] ?: 'Pendiente',
                        'estado' => $comp['estado_componente'],
                        'observaciones' => $comp['observaciones']
                    ];
                }, $estacion_completa['componentes']),
                'sensores' => array_map(function ($sensor) {
                    return [
                        'id' => $sensor['id_sensor'],
                        'tipo' => $sensor['tipo_sensor'],
                        'marca' => $sensor['marca'],
                        'modelo' => $sensor['modelo'],
                        'serie' => $sensor['serie'],
                        'fecha_mantenimiento' => $sensor['fecha_ultimo_mantenimiento'] ?: 'Pendiente',
                        'estado' => $sensor['estado_sensor'],
                        'observaciones' => $sensor['observaciones']
                    ];
                }, $estacion_completa['sensores']),
                'imagenes' => [
                    'mapa' => $estacion_completa['imagenes']['mapa'],
                    'fotografia' => $estacion_completa['imagenes']['fotografia']
                ],
                'ultima_lectura' => $estacion_completa['ultimo_registro'] ? [
                    'fecha_hora' => $estacion_completa['ultimo_registro']['fecha_hora'],
                    'tipo_nivel' => $estacion_completa['ultimo_registro']['tipo_nivel']
                ] : null
            ]
        ];

        ob_clean();
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    // ================================================================
    // PRIORIDAD 5: ACCIONES GET
    // ================================================================
    if (isset($_GET['action'])) {
        $action = $_GET['action'];

        switch ($action) {
            case 'get_all_stations':
                $estaciones = $modeloEstaciones->obtenerTodasLasEstaciones();
                $response = [
                    'success' => true,
                    'data' => array_map(function ($est) use ($modeloEstaciones) {
                        $coords = $modeloEstaciones->formatearCoordenadas($est['latitud'], $est['longitud']);
                        return [
                            'id' => $est['id_estacion'],
                            'codigo' => $est['codigo'],
                            'nombre' => $est['nombre'],
                            'coords' => [$coords['lat'], $coords['lng']],
                            'location' => $est['comunidad'] . ', ' . $est['canton'] . ', ' . $est['provincia'],
                            'status' => $est['status'],
                            'elevation' => $est['altura_terreno'] . 'm'
                        ];
                    }, $estaciones)
                ];
                ob_clean();
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit();

            case 'get_stations_for_frontend':
                $estaciones = $modeloEstaciones->obtenerTodasLasEstaciones();
                $colors = [
                    'var(--primary-blue)',
                    'var(--accent-green)',
                    'var(--accent-orange)',
                    'var(--primary-cyan)',
                    'var(--accent-purple)',
                    'var(--accent-yellow)',
                    '#00ff40',
                    '#ff4000',
                    'var(--accent-pink)',
                    '#ff40ff',
                    'var(--accent-red)',
                    'var(--accent-green)',
                    'var(--accent-orange)',
                    'var(--primary-blue)',
                    'var(--accent-purple)'
                ];

                $response = [
                    'success' => true,
                    'data' => array_map(function ($est, $index) use ($colors) {
                        return [
                            'id' => intval($est['id_estacion']),
                            'nombre' => $est['nombre'],
                            'codigo' => $est['codigo'],
                            'coords' => [floatval($est['latitud']), floatval($est['longitud'])],
                            'color' => $colors[$index % count($colors)],
                            'isDefault' => $index === 3
                        ];
                    }, $estaciones, array_keys($estaciones))
                ];
                ob_clean();
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit();

            case 'get_sensor_types':
                $tipos = $modeloEstaciones->obtenerCatalogoSensores();

                // ✅ ASEGURAR que devuelve 'id' (no 'id_catalogo_sensor')
                $tipos_formateados = array_map(function ($tipo) {
                    return [
                        'id' => intval($tipo['id_catalogo_sensor']),
                        'nombre' => trim($tipo['nombre']),
                        'descripcion' => isset($tipo['descripcion']) ? trim($tipo['descripcion']) : null
                    ];
                }, $tipos);

                error_log("📤 get_sensor_types respuesta: " . count($tipos_formateados) . " tipos");

                $response = ['success' => true, 'data' => $tipos_formateados];
                ob_clean();
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit();

            case 'get_component_types':
                $tipos = $modeloEstaciones->obtenerTiposComponentes();

                // ✅ ASEGURAR que devuelve 'id' (no 'id_catalogo_componente')
                $tipos_formateados = array_map(function ($tipo) {
                    return [
                        'id' => intval($tipo['id_catalogo_componente']),
                        'nombre' => trim($tipo['nombre']),
                        'descripcion' => isset($tipo['descripcion']) ? trim($tipo['descripcion']) : null
                    ];
                }, $tipos);

                error_log("📤 get_component_types respuesta: " . count($tipos_formateados) . " tipos");

                $response = ['success' => true, 'data' => $tipos_formateados];
                ob_clean();
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit();

            case 'get_catalogo_sensores':
                $catalogo = $modeloEstaciones->obtenerCatalogoSensores();
                $response = ['success' => true, 'data' => $catalogo];
                ob_clean();
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit();

            case 'get_catalogo_componentes':
                $catalogo = $modeloEstaciones->obtenerCatalogoComponentes();
                $response = ['success' => true, 'data' => $catalogo];
                ob_clean();
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit();
            case 'get_catalogo_variables':
                $catalogo = $modeloEstaciones->obtenerCatalogoVariables();
                $response = ['success' => true, 'data' => $catalogo];
                ob_clean();
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit();

            case 'get_sensor':
                if (!isset($_GET['id_sensor'])) {
                    throw new Exception("ID de sensor requerido");
                }
                $id_sensor = intval($_GET['id_sensor']);
                $sensor = $modeloEstaciones->obtenerSensorPorId($id_sensor);

                if (!$sensor) {
                    throw new Exception("Sensor no encontrado");
                }

                $response = [
                    'success' => true,
                    'data' => [
                        'id' => $sensor['id_sensor'],
                        'id_estacion' => $sensor['id_estacion'],
                        'id_catalogo_sensor' => $sensor['id_catalogo_sensor'],
                        'tipo' => $sensor['tipo_sensor'],
                        'marca' => $sensor['marca'],
                        'modelo' => $sensor['modelo'],
                        'serie' => $sensor['serie'],
                        'fecha_mantenimiento' => $sensor['fecha_ultimo_mantenimiento'],
                        'estado' => $sensor['estado_sensor'],
                        'observaciones' => $sensor['observaciones']
                    ]
                ];
                ob_clean();
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit();

            case 'get_component':
                if (!isset($_GET['id_componente'])) {
                    throw new Exception("ID de componente requerido");
                }
                $id_componente = intval($_GET['id_componente']);
                $componente = $modeloEstaciones->obtenerComponentePorId($id_componente);

                if (!$componente) {
                    throw new Exception("Componente no encontrado");
                }

                $response = [
                    'success' => true,
                    'data' => [
                        'id' => $componente['id_componente'],
                        'id_estacion' => $componente['id_estacion'],
                        'id_catalogo_componente' => $componente['id_catalogo_componente'],
                        'tipo' => $componente['tipo_componente'],
                        'marca' => $componente['marca'],
                        'modelo' => $componente['modelo'],
                        'serie' => $componente['serie'],
                        'especificaciones' => $componente['especificaciones'],
                        'fecha_mantenimiento' => $componente['fecha_ultimo_mantenimiento'],
                        'estado' => $componente['estado_componente'],
                        'observaciones' => $componente['observaciones']
                    ]
                ];
                ob_clean();
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit();

            default:
                throw new Exception("Acción no válida: " . $action);
        }
    }

    // ================================================================
    // PRIORIDAD 6: PROCESAR PETICIONES POST (con JSON)
    // ================================================================
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            throw new Exception("Datos JSON requeridos para esta operación");
        }

        if (!isset($input['action'])) {
            throw new Exception("Acción requerida en los datos JSON");
        }

        $action = $input['action'];

        switch ($action) {
            case 'add_sensor':
                error_log("=== AGREGAR SENSOR ===");
                error_log("Input recibido: " . json_encode($input, JSON_UNESCAPED_UNICODE));

                $required = ['id_estacion', 'id_catalogo_sensor'];
                foreach ($required as $field) {
                    if (!isset($input[$field]) || empty($input[$field])) {
                        throw new Exception("Campo requerido: $field");
                    }
                }

                $datos_sensor = [
                    'id_estacion' => intval($input['id_estacion']),
                    'id_catalogo_sensor' => intval($input['id_catalogo_sensor']),
                    'marca' => !empty(trim($input['marca'] ?? '')) ? trim($input['marca']) : null,
                    'modelo' => !empty(trim($input['modelo'] ?? '')) ? trim($input['modelo']) : null,
                    'serie' => !empty(trim($input['serie'] ?? '')) ? trim($input['serie']) : null,
                    'fecha_ultimo_mantenimiento' => !empty(trim($input['fecha_mantenimiento'] ?? '')) ? $input['fecha_mantenimiento'] : null,
                    'estado_sensor' => !empty(trim($input['estado'] ?? '')) ? trim($input['estado']) : 'ACTIVO',
                    'observaciones' => !empty(trim($input['observaciones'] ?? '')) ? trim($input['observaciones']) : null
                ];

                $id_sensor = $modeloEstaciones->agregarSensor($datos_sensor);

                $response = [
                    'success' => true,
                    'message' => 'Sensor agregado correctamente',
                    'data' => ['id_sensor' => $id_sensor]
                ];

                ob_clean();
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit();

            case 'add_tipo_sensor':
                if (!isset($input['nombre']) || empty(trim($input['nombre']))) {
                    throw new Exception("El nombre del tipo de sensor es requerido");
                }

                $id_tipo = $modeloEstaciones->agregarTipoSensor(
                    trim($input['nombre']),
                    !empty($input['descripcion']) ? trim($input['descripcion']) : null
                );

                $response = [
                    'success' => true,
                    'message' => 'Tipo de sensor agregado correctamente',
                    'data' => ['id' => $id_tipo]
                ];

                ob_clean();
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit();

            case 'add_tipo_componente':
                if (!isset($input['nombre']) || empty(trim($input['nombre']))) {
                    throw new Exception("El nombre del tipo de componente es requerido");
                }

                $id_tipo = $modeloEstaciones->agregarTipoComponente(
                    trim($input['nombre']),
                    !empty($input['descripcion']) ? trim($input['descripcion']) : null
                );

                $response = [
                    'success' => true,
                    'message' => 'Tipo de componente agregado correctamente',
                    'data' => ['id' => $id_tipo]
                ];

                ob_clean();
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit();

            case 'add_component':
                error_log("=== AGREGAR COMPONENTE ===");
                error_log("Input recibido: " . json_encode($input, JSON_UNESCAPED_UNICODE));

                $required = ['id_estacion', 'id_catalogo_componente'];
                foreach ($required as $field) {
                    if (!isset($input[$field]) || empty($input[$field])) {
                        throw new Exception("Campo requerido: $field");
                    }
                }

                $datos_componente = [
                    'id_estacion' => intval($input['id_estacion']),
                    'id_catalogo_componente' => intval($input['id_catalogo_componente']),
                    'marca' => !empty(trim($input['marca'] ?? '')) ? trim($input['marca']) : null,
                    'modelo' => !empty(trim($input['modelo'] ?? '')) ? trim($input['modelo']) : null,
                    'serie' => !empty(trim($input['serie'] ?? '')) ? trim($input['serie']) : null,
                    'especificaciones' => !empty(trim($input['especificaciones'] ?? '')) ? trim($input['especificaciones']) : null,
                    'fecha_ultimo_mantenimiento' => !empty(trim($input['fecha_mantenimiento'] ?? '')) ? $input['fecha_mantenimiento'] : null,
                    'estado_componente' => !empty(trim($input['estado'] ?? '')) ? trim($input['estado']) : 'ACTIVO',
                    'observaciones' => !empty(trim($input['observaciones'] ?? '')) ? trim($input['observaciones']) : null
                ];

                $id_componente = $modeloEstaciones->agregarComponente($datos_componente);

                $response = [
                    'success' => true,
                    'message' => 'Componente agregado correctamente',
                    'data' => ['id_componente' => $id_componente]
                ];

                ob_clean();
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit();
            case 'add_variable':
                if (!isset($input['codigo_columna']) || empty(trim($input['codigo_columna']))) {
                    throw new Exception("El código de columna es requerido");
                }
                if (!isset($input['nombre_corto']) || empty(trim($input['nombre_corto']))) {
                    throw new Exception("El nombre corto es requerido");
                }
                if (!isset($input['unidad']) || empty(trim($input['unidad']))) {
                    throw new Exception("La unidad es requerida");
                }

                $id = $modeloEstaciones->agregarVariable(
                    trim($input['codigo_columna']),
                    trim($input['nombre_corto']),
                    !empty($input['descripcion']) ? trim($input['descripcion']) : null,
                    trim($input['unidad'])
                );

                $response = [
                    'success' => true,
                    'message' => 'Variable agregada correctamente',
                    'data' => ['id' => $id]
                ];

                ob_clean();
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit();

            default:
                throw new Exception("Acción POST no válida: " . $action);
        }
    }

    // ================================================================
    // PRIORIDAD 7: PROCESAR PETICIONES PUT
    // ================================================================
    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['action'])) {
            throw new Exception("Acción requerida");
        }

        $action = $input['action'];

        switch ($action) {
            case 'update_sensor':
                if (!isset($input['id_sensor']) || empty($input['id_sensor'])) {
                    throw new Exception("ID de sensor requerido");
                }

                $id_sensor = intval($input['id_sensor']);

                // Preparar datos limpiando valores vacíos a NULL
                $datos_actualizados = [
                    'marca' => !empty(trim($input['marca'] ?? '')) ? trim($input['marca']) : null,
                    'modelo' => !empty(trim($input['modelo'] ?? '')) ? trim($input['modelo']) : null,
                    'serie' => !empty(trim($input['serie'] ?? '')) ? trim($input['serie']) : null,
                    'fecha_ultimo_mantenimiento' => !empty(trim($input['fecha_mantenimiento'] ?? '')) ? $input['fecha_mantenimiento'] : null,
                    'estado_sensor' => !empty(trim($input['estado'] ?? '')) ? trim($input['estado']) : 'ACTIVO',
                    'observaciones' => !empty(trim($input['observaciones'] ?? '')) ? trim($input['observaciones']) : null
                ];

                $actualizado = $modeloEstaciones->actualizarSensor($id_sensor, $datos_actualizados);

                $response = [
                    'success' => $actualizado,
                    'message' => $actualizado ? 'Sensor actualizado correctamente' : 'No se pudo actualizar el sensor'
                ];

                ob_clean();
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit();

            case 'update_tipo_sensor':
                if (!isset($input['id']) || !isset($input['nombre'])) {
                    throw new Exception("ID y nombre del tipo de sensor son requeridos");
                }

                $actualizado = $modeloEstaciones->actualizarTipoSensor(
                    intval($input['id']),
                    trim($input['nombre']),
                    !empty($input['descripcion']) ? trim($input['descripcion']) : null
                );

                $response = [
                    'success' => $actualizado,
                    'message' => $actualizado ? 'Tipo de sensor actualizado correctamente' : 'No se pudo actualizar'
                ];

                ob_clean();
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit();

            case 'update_variable':
                if (!isset($input['id']) || !isset($input['codigo_columna']) || !isset($input['nombre_corto']) || !isset($input['unidad'])) {
                    throw new Exception("Todos los campos son requeridos");
                }

                $actualizado = $modeloEstaciones->actualizarVariable(
                    intval($input['id']),
                    trim($input['codigo_columna']),
                    trim($input['nombre_corto']),
                    !empty($input['descripcion']) ? trim($input['descripcion']) : null,
                    trim($input['unidad'])
                );

                $response = [
                    'success' => $actualizado,
                    'message' => $actualizado ? 'Variable actualizada correctamente' : 'No se pudo actualizar'
                ];

                ob_clean();
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit();

            case 'update_tipo_componente':
                if (!isset($input['id']) || !isset($input['nombre'])) {
                    throw new Exception("ID y nombre del tipo de componente son requeridos");
                }

                $actualizado = $modeloEstaciones->actualizarTipoComponente(
                    intval($input['id']),
                    trim($input['nombre']),
                    !empty($input['descripcion']) ? trim($input['descripcion']) : null
                );

                $response = [
                    'success' => $actualizado,
                    'message' => $actualizado ? 'Tipo de componente actualizado correctamente' : 'No se pudo actualizar'
                ];

                ob_clean();
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit();

            case 'update_component':
                if (!isset($input['id_componente']) || empty($input['id_componente'])) {
                    throw new Exception("ID de componente requerido");
                }

                $id_componente = intval($input['id_componente']);

                // Preparar datos limpiando valores vacíos a NULL
                $datos_actualizados = [
                    'marca' => !empty(trim($input['marca'] ?? '')) ? trim($input['marca']) : null,
                    'modelo' => !empty(trim($input['modelo'] ?? '')) ? trim($input['modelo']) : null,
                    'serie' => !empty(trim($input['serie'] ?? '')) ? trim($input['serie']) : null,
                    'especificaciones' => !empty(trim($input['especificaciones'] ?? '')) ? trim($input['especificaciones']) : null,
                    'fecha_ultimo_mantenimiento' => !empty(trim($input['fecha_mantenimiento'] ?? '')) ? $input['fecha_mantenimiento'] : null,
                    'estado_componente' => !empty(trim($input['estado'] ?? '')) ? trim($input['estado']) : 'ACTIVO',
                    'observaciones' => !empty(trim($input['observaciones'] ?? '')) ? trim($input['observaciones']) : null
                ];

                $actualizado = $modeloEstaciones->actualizarComponente($id_componente, $datos_actualizados);

                $response = [
                    'success' => $actualizado,
                    'message' => $actualizado ? 'Componente actualizado correctamente' : 'No se pudo actualizar el componente'
                ];

                ob_clean();
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit();

            default:
                throw new Exception("Acción PUT no válida: " . $action);
        }
    }

    throw new Exception("No se especificó ninguna acción válida");

} catch (Exception $e) {
    http_response_code(400);

    // Extraer mensaje de error más específico
    $error_message = $e->getMessage();

    // Mejorar mensajes de error de PostgreSQL
    if (strpos($error_message, 'duplicate key') !== false) {
        if (strpos($error_message, 'sensores_serie_key') !== false) {
            $error_message = "El número de serie del sensor ya existe. Por favor use un número de serie diferente o déjelo vacío.";
        } elseif (strpos($error_message, 'serie') !== false) {
            $error_message = "El número de serie ya existe en la base de datos.";
        } elseif (strpos($error_message, 'codigo') !== false) {
            $error_message = "El código de estación ya existe.";
        } elseif (strpos($error_message, 'tag_codigo_iner') !== false) {
            $error_message = "El TAG/Código INER ya existe.";
        } else {
            $error_message = "Ya existe un registro con esos datos. Por favor verifique los valores ingresados.";
        }
    } elseif (strpos($error_message, 'foreign key') !== false) {
        $error_message = "Error de relación: La estación especificada no existe.";
    } elseif (strpos($error_message, 'violates check constraint') !== false) {
        $error_message = "Valor no válido. Verifique que el estado sea: ACTIVO, INACTIVO, MANTENIMIENTO o FUERA_SERVICIO.";
    }

    $response = [
        'success' => false,
        'error' => $error_message
    ];

    error_log("ERROR EN CONTROLADOR: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    // Limpiar buffer y enviar error
    ob_clean();
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit();
}
?>