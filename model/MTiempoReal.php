<?php
// model/MTiempoReal.php
// MODELO PARA PROCESAR ARCHIVOS DE TIEMPO REAL CON MAPEO DINÁMICO

require_once dirname(__DIR__) . '/config/conexion.php';

class MTiempoReal
{
    private $conn;

    public function __construct()
    {
        global $conn;

        if (!$conn) {
            throw new Exception("No hay conexión a la base de datos");
        }

        $this->conn = $conn;

        // DEBUG: Verificar conexión y base de datos
        $db_info = pg_query($this->conn, "SELECT current_database(), current_user");
        if ($db_info) {
            $info = pg_fetch_assoc($db_info);
            error_log("DEBUG: Conectado a BD: " . $info['current_database'] . " como usuario: " . $info['current_user']);
        }
    }

    /**
     * Obtiene todos los metadatos activos de la tabla metadatos_variables
     * @return array [codigo_columna => ['id_metadato' => X, 'nombre_corto' => Y, 'unidad' => Z]]
     */
    private function obtenerMetadatosActivos()
    {
        $query = "SELECT id_metadato, codigo_columna, nombre_corto, descripcion, unidad 
                  FROM metadatos_variables 
                  ORDER BY codigo_columna";

        $result = pg_query($this->conn, $query);

        if (!$result) {
            // DEBUG: Mostrar error de PostgreSQL
            error_log("ERROR PG: " . pg_last_error($this->conn));
            throw new Exception("Error al consultar metadatos: " . pg_last_error($this->conn));
        }

        // DEBUG: Contar filas
        $num_rows = pg_num_rows($result);
        error_log("DEBUG: Metadatos encontrados en BD: {$num_rows}");

        $metadatos = [];
        while ($row = pg_fetch_assoc($result)) {
            error_log("DEBUG: Metadato encontrado: " . $row['codigo_columna']);
            $metadatos[$row['codigo_columna']] = [
                'id_metadato' => intval($row['id_metadato']),
                'nombre_corto' => $row['nombre_corto'],
                'unidad' => $row['unidad'],
                'descripcion' => $row['descripcion']
            ];
        }

        error_log("DEBUG: Total metadatos cargados: " . count($metadatos));

        return $metadatos;
    }

    /**
     * Mapea las columnas del CSV con los metadatos disponibles
     * @param array $cabecera Array con los nombres de columnas del CSV
     * @param array $metadatos Array de metadatos obtenidos de la BD
     * @return array [indice_csv => id_metadato]
     */
    private function mapearColumnasDinamicas($cabecera, $metadatos)
    {
        $mapeo = [];
        $variables_faltantes = [];

        // Empezar desde índice 1 (saltar la columna 'fecha')
        for ($i = 1; $i < count($cabecera); $i++) {
            $nombre_columna = trim($cabecera[$i]);

            // Ignorar columnas 'cdt' (códigos de calidad)
            if (strtolower($nombre_columna) === 'cdt') {
                continue;
            }

            // Verificar si la columna existe en los metadatos
            if (isset($metadatos[$nombre_columna])) {
                $mapeo[$i] = $metadatos[$nombre_columna]['id_metadato'];
            } else {
                // COLUMNA NO ENCONTRADA - agregar a la lista de faltantes
                $variables_faltantes[] = $nombre_columna;
            }
        }

        return [
            'mapeo' => $mapeo,
            'variables_faltantes' => $variables_faltantes
        ];
    }

    /**
     * Procesa un archivo de tiempo real completo
     * @param string $ruta_archivo Ruta completa al archivo
     * @param int $id_estacion ID de la estación en la BD
     * @param string $nombre_estacion Nombre de la estación (para logs)
     * @return array Resultado del procesamiento
     */
    public function procesarArchivoTiempoReal($ruta_archivo, $id_estacion, $nombre_estacion = '')
    {
        $nombre_archivo = basename($ruta_archivo);

        try {
            // 1. Obtener metadatos activos
            $metadatos = $this->obtenerMetadatosActivos();

            if (empty($metadatos)) {
                $mensaje_error = "No hay metadatos configurados en el sistema";
                return [
                    'exito' => false,
                    'tipo_resultado' => 'ERROR_PROCESAMIENTO',
                    'mensaje' => $mensaje_error,
                    'registros_insertados' => 0,
                    'id_carga' => null
                ];
            }

            // 2. Leer archivo
            $contenido = file_get_contents($ruta_archivo);
            if ($contenido === false) {
                throw new Exception("No se pudo leer el archivo");
            }

            // 3. Separar por líneas
            $lineas = explode("\n", $contenido);
            $lineas = array_filter(array_map('trim', $lineas));

            if (count($lineas) < 2) {
                // Crear carga para registrar el error
                $id_carga = $this->crearRegistroCarga($id_estacion, $nombre_archivo);
                $mensaje = "Estación: {$nombre_estacion} | Carpeta: TiempoR | Archivo: {$nombre_archivo} | Error: Archivo vacío o sin registros válidos";
                $this->registrarLog($id_carga, $mensaje, 'L2TR');

                return [
                    'exito' => false,
                    'tipo_resultado' => 'SIN_DATOS',
                    'mensaje' => 'Archivo vacío o sin registros válidos',
                    'registros_insertados' => 0,
                    'id_carga' => $id_carga
                ];
            }

            // 4. Extraer cabecera
            $cabecera = str_getcsv($lineas[0]);
            array_shift($lineas); // Quitar cabecera del array

            // 5. Mapear columnas dinámicamente
            $resultado_mapeo = $this->mapearColumnasDinamicas($cabecera, $metadatos);
            $mapeo_columnas = $resultado_mapeo['mapeo'];
            $variables_faltantes = $resultado_mapeo['variables_faltantes'];

            // 5.1 VALIDACIÓN ESTRICTA: Si hay variables faltantes, DETENER procesamiento
            if (!empty($variables_faltantes)) {
                // Crear carga para registrar el error
                $id_carga = $this->crearRegistroCarga($id_estacion, $nombre_archivo);

                // Crear mensaje detallado con las variables faltantes
                $lista_variables = implode(', ', $variables_faltantes);
                $mensaje = "Estación: {$nombre_estacion} | Carpeta: TiempoR | Archivo: {$nombre_archivo} | " .
                    "Error: VARIABLES NO REGISTRADAS | " .
                    "Variables faltantes: [{$lista_variables}] | " .
                    "Acción requerida: Debe agregar estas variables a la tabla 'metadatos_variables' antes de procesar este archivo";

                // Registrar en logs
                $this->registrarLog($id_carga, $mensaje, 'L2TR');

                error_log("❌ VALIDACIÓN FALLIDA: Archivo '{$nombre_archivo}' tiene variables no registradas: {$lista_variables}");

                return [
                    'exito' => false,
                    'tipo_resultado' => 'VARIABLES_FALTANTES',
                    'mensaje' => "El archivo contiene variables NO registradas: {$lista_variables}",
                    'variables_faltantes' => $variables_faltantes,
                    'registros_insertados' => 0,
                    'id_carga' => $id_carga,
                    'no_mover_archivo' => true  // FLAG IMPORTANTE para no mover el archivo
                ];
            }

            // 5.2 Si el mapeo quedó vacío (todas eran 'cdt' o 'fecha'), también detener
            if (empty($mapeo_columnas)) {
                $id_carga = $this->crearRegistroCarga($id_estacion, $nombre_archivo);
                $mensaje = "Estación: {$nombre_estacion} | Carpeta: TiempoR | Archivo: {$nombre_archivo} | Error: El archivo no tiene columnas válidas para procesar (solo contiene 'fecha' y/o 'cdt')";
                $this->registrarLog($id_carga, $mensaje, 'L2TR');

                return [
                    'exito' => false,
                    'tipo_resultado' => 'SIN_COLUMNAS_VALIDAS',
                    'mensaje' => 'El archivo no tiene columnas válidas para procesar',
                    'registros_insertados' => 0,
                    'id_carga' => $id_carga
                ];
            }

            // 6. Crear registro de carga
            $id_carga = $this->crearRegistroCarga($id_estacion, $nombre_archivo);

            // 7. Iniciar transacción
            pg_query($this->conn, "BEGIN");

            $registros_insertados = 0;
            $registros_duplicados = 0;
            $errores = [];

            // 8. Procesar cada línea de datos
            foreach ($lineas as $num_linea => $linea) {
                if (empty($linea))
                    continue;

                try {
                    $valores = str_getcsv($linea);

                    if (count($valores) < 2) {
                        $errores[] = "Línea " . ($num_linea + 2) . ": Datos insuficientes";
                        continue;
                    }

                    // Extraer fecha/hora del primer campo
                    $fecha_hora_str = trim($valores[0]);

                    // Formato esperado: YYYYMMDDHHMMSS (ej: 20230907161509)
                    if (strlen($fecha_hora_str) !== 14) {
                        $errores[] = "Línea " . ($num_linea + 2) . ": Formato de fecha/hora inválido: {$fecha_hora_str}";
                        continue;
                    }

                    $fecha = substr($fecha_hora_str, 0, 8); // YYYYMMDD
                    $hora = substr($fecha_hora_str, 8, 6);  // HHMMSS

                    // Convertir a formato PostgreSQL
                    $fecha_formateada = substr($fecha, 0, 4) . '-' . substr($fecha, 4, 2) . '-' . substr($fecha, 6, 2);
                    $hora_formateada = substr($hora, 0, 2) . ':' . substr($hora, 2, 2) . ':' . substr($hora, 4, 2);

                    // Crear o obtener registro
                    $resultado_registro = $this->crearObtenerRegistro(
                        $id_estacion,
                        $fecha_formateada,
                        $hora_formateada,
                        $id_carga
                    );

                    if ($resultado_registro['es_duplicado']) {
                        $registros_duplicados++;
                        continue;
                    }

                    $id_registro = $resultado_registro['id_registro'];

                    // Insertar datos por cada variable mapeada
                    $valores_insertados = 0;
                    foreach ($mapeo_columnas as $indice_csv => $id_metadato) {
                        $valor_raw = $valores[$indice_csv] ?? null;
                        $valor_limpio = $this->limpiarValor($valor_raw);

                        // Insertar dato individual
                        $insertado = $this->insertarDatoPorVariable(
                            $id_registro,
                            $fecha_formateada,
                            $hora_formateada,
                            $id_metadato,
                            $valor_limpio
                        );

                        if ($insertado) {
                            $valores_insertados++;
                        }
                    }

                    if ($valores_insertados > 0) {
                        $registros_insertados++;
                    }

                } catch (Exception $e) {
                    $errores[] = "Línea " . ($num_linea + 2) . ": " . $e->getMessage();
                }
            }

            // 9. Verificar si hubo inserciones exitosas
            if ($registros_insertados > 0) {
                pg_query($this->conn, "COMMIT");

                // Actualizar total de registros en cargas_archivos
                $this->actualizarTotalRegistros($id_carga, $registros_insertados);

                // NO registrar log de éxito - solo en consola
                $mensaje_log = "Procesado exitosamente: {$registros_insertados} registros insertados";
                if ($registros_duplicados > 0) {
                    $mensaje_log .= ", {$registros_duplicados} duplicados omitidos";
                }

                return [
                    'exito' => true,
                    'tipo_resultado' => 'EXITOSO',
                    'mensaje' => $mensaje_log,
                    'registros_insertados' => $registros_insertados,
                    'registros_duplicados' => $registros_duplicados,
                    'id_carga' => $id_carga,
                    'errores' => $errores
                ];

            } else {
                pg_query($this->conn, "ROLLBACK");

                $tipo_resultado = $registros_duplicados > 0 ? 'DUPLICADO' : 'SIN_DATOS';
                $mensaje = $registros_duplicados > 0
                    ? "Todos los registros ({$registros_duplicados}) ya existían en la base de datos"
                    : "No se insertó ningún registro válido";

                // Registrar en logs con información completa
                $mensaje_log = "Estación: {$nombre_estacion} | Carpeta: TiempoR | Archivo: {$nombre_archivo} | Error: {$mensaje}";
                $this->registrarLog($id_carga, $mensaje_log, 'L2TR');

                return [
                    'exito' => false,
                    'tipo_resultado' => $tipo_resultado,
                    'mensaje' => $mensaje,
                    'registros_insertados' => 0,
                    'registros_duplicados' => $registros_duplicados,
                    'id_carga' => $id_carga,
                    'errores' => $errores
                ];
            }

        } catch (Exception $e) {
            pg_query($this->conn, "ROLLBACK");

            $mensaje_error = "Error al procesar archivo: " . $e->getMessage();

            if (isset($id_carga)) {
                // Registrar en logs con información completa
                $mensaje_log = "Estación: {$nombre_estacion} | Carpeta: TiempoR | Archivo: {$nombre_archivo} | Error: {$mensaje_error}";
                $this->registrarLog($id_carga, $mensaje_log, 'L2TR');
            }

            return [
                'exito' => false,
                'tipo_resultado' => 'ERROR_PROCESAMIENTO',
                'mensaje' => $mensaje_error,
                'registros_insertados' => 0,
                'id_carga' => $id_carga ?? null
            ];
        }
    }

    /**
     * Crea un registro de carga en la tabla cargas_archivos
     */
    private function crearRegistroCarga($id_estacion, $nombre_archivo)
    {
        // Obtener el primer administrador
        $query_admin = "SELECT id_administrador FROM administradores ORDER BY id_administrador LIMIT 1";
        $result_admin = pg_query($this->conn, $query_admin);

        if (!$result_admin || pg_num_rows($result_admin) === 0) {
            throw new Exception("No se encontró ningún administrador en el sistema");
        }

        $row_admin = pg_fetch_assoc($result_admin);
        $id_administrador = $row_admin['id_administrador'];

        // Crear registro de carga
        $query_carga = "INSERT INTO cargas_archivos 
                        (id_administrador, id_estacion, nombre_archivo, tipo_nivel, estado_carga, total_registros)
                        VALUES ($1, $2, $3, 'L2', 'PROCESANDO', 0)
                        RETURNING id_carga";

        $result_carga = pg_query_params($this->conn, $query_carga, [
            $id_administrador,
            $id_estacion,
            'tiempo_real_automatico'
        ]);

        if (!$result_carga) {
            throw new Exception("Error al crear registro de carga: " . pg_last_error($this->conn));
        }

        $row_carga = pg_fetch_assoc($result_carga);
        return intval($row_carga['id_carga']);
    }

    /**
     * Crea o obtiene un registro existente
     * DETECTA DUPLICADOS POR ESTACIÓN + FECHA/HORA, independiente del id_carga
     */
    private function crearObtenerRegistro($id_estacion, $fecha, $hora, $id_carga)
    {
        $fecha_hora_completa = $fecha . ' ' . $hora;

        // PRIMERO: Verificar si ya existe un registro con esa estación + fecha/hora + tipo L2
        $query_check = "SELECT id_registro, id_carga FROM registros 
                       WHERE id_estacion = $1 
                       AND fecha_hora = $2 
                       AND tipo_nivel = 'L2'
                       LIMIT 1";

        $result_check = pg_query_params($this->conn, $query_check, [$id_estacion, $fecha_hora_completa]);

        if (!$result_check) {
            throw new Exception("Error al verificar duplicados: " . pg_last_error($this->conn));
        }

        // Si ya existe, es un DUPLICADO
        if (pg_num_rows($result_check) > 0) {
            $row_existente = pg_fetch_assoc($result_check);

            error_log("DEBUG: DUPLICADO detectado - Estación {$id_estacion}, Fecha/Hora {$fecha_hora_completa}");

            return [
                'id_registro' => intval($row_existente['id_registro']),
                'es_duplicado' => true
            ];
        }

        // Si NO existe, INSERTAR nuevo registro
        $query_insert = "INSERT INTO registros (id_estacion, fecha_hora, tipo_nivel, id_carga) 
                        VALUES ($1, $2, 'L2', $3) 
                        RETURNING id_registro";

        $result = pg_query_params($this->conn, $query_insert, [$id_estacion, $fecha_hora_completa, $id_carga]);

        if (!$result) {
            throw new Exception("Error al crear registro: " . pg_last_error($this->conn));
        }

        $row = pg_fetch_assoc($result);

        error_log("DEBUG: Registro NUEVO creado - ID: {$row['id_registro']}, Fecha/Hora: {$fecha_hora_completa}");

        return [
            'id_registro' => intval($row['id_registro']),
            'es_duplicado' => false
        ];
    }

    /**
     * Inserta un dato individual en datos_l2_tiempo_real
     */
    private function insertarDatoPorVariable($id_registro, $fecha, $hora, $id_metadato, $valor)
    {
        $query = "INSERT INTO datos_l2_tiempo_real (id_registro, fecha, hora, id_metadato, valor)
                 VALUES ($1, $2, $3, $4, $5)
                 ON CONFLICT (id_registro, id_metadato) DO NOTHING";

        $result = pg_query_params($this->conn, $query, [
            $id_registro,
            $fecha,
            $hora,
            $id_metadato,
            $valor
        ]);

        if (!$result) {
            throw new Exception("Error al insertar dato: " . pg_last_error($this->conn));
        }

        return pg_affected_rows($result) > 0;
    }

    /**
     * Limpia y valida un valor del CSV
     * Retorna valor con máximo 3 decimales
     */
    private function limpiarValor($valor)
    {
        if ($valor === null || $valor === '') {
            return 0;
        }

        $valor = trim($valor);

        // Valores inválidos que se convierten en 0
        if (in_array($valor, ['//////', '/////', 'NaN', 'NULL'])) {
            return 0;
        }

        // Si es numérico, retornar con máximo 3 decimales
        if (is_numeric($valor)) {
            return round(floatval($valor), 3);
        }

        return 0;
    }

    /**
     * Actualiza el total de registros en cargas_archivos
     */
    private function actualizarTotalRegistros($id_carga, $total)
    {
        $query = "UPDATE cargas_archivos 
                 SET total_registros = $1, estado_carga = 'COMPLETADO'
                 WHERE id_carga = $2";

        pg_query_params($this->conn, $query, [$total, $id_carga]);
    }

    /**
     * Registra un log en logs_carga_archivos
     */
    private function registrarLog($id_carga, $mensaje, $tipo_nivel = 'L2TR')
    {
        // Obtener el primer administrador
        $query_admin = "SELECT id_administrador FROM administradores ORDER BY id_administrador LIMIT 1";
        $result_admin = pg_query($this->conn, $query_admin);

        if (!$result_admin || pg_num_rows($result_admin) === 0) {
            return; // No fallar si no hay admin
        }

        $row_admin = pg_fetch_assoc($result_admin);
        $id_administrador = $row_admin['id_administrador'];

        $query = "INSERT INTO logs_carga_archivos (id_carga, id_administrador, mensaje, tipo_nivel)
                 VALUES ($1, $2, $3, $4)";

        pg_query_params($this->conn, $query, [$id_carga, $id_administrador, $mensaje, $tipo_nivel]);
    }
}
?>