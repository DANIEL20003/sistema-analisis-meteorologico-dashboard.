<?php
// model/MInforSensor.php
require_once '../config/conexion.php';

class MInforSensor
{
    private $conn;

    public function __construct()
    {
        global $conn;

        if (!$conn) {
            error_log("❌ CRÍTICO: No hay conexión a la base de datos");
            throw new Exception("Error: No se pudo establecer conexión con la base de datos");
        }

        $this->conn = $conn;
        error_log("✅ Conexión a BD establecida correctamente");
        $uploadDir = dirname(__DIR__) . '/uploads';
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                error_log("⚠️ ADVERTENCIA: No se pudo crear la carpeta uploads/");
            } else {
                error_log("✅ Carpeta uploads/ creada exitosamente");
            }
        }
    }
    /**
     * Crea la estructura de carpetas para una nueva estación en uploads/
     */
    private function crearEstructuraCarpetasEstacion($nombreEstacion)
    {
        error_log("=== INICIO crearEstructuraCarpetasEstacion ===");
        error_log("Nombre estación original: " . $nombreEstacion);

        // ✅ Usar el método normalizarNombreParaCarpeta para limpiar el nombre
        $nombreCarpeta = $this->normalizarNombreParaCarpeta($nombreEstacion);

        error_log("Nombre estación normalizado: " . $nombreCarpeta);

        // Ruta base de uploads (subir un nivel desde model/)
        $rutaBase = dirname(__DIR__) . '/uploads/' . $nombreCarpeta;

        error_log("Ruta base a crear: " . $rutaBase);

        // Estructura de carpetas a crear
        $estructura = [
            'Limpios/L0',
            'Limpios/L1',
            'Limpios/L2',
            'Crudos/L0',
            'Crudos/L1',
            'Crudos/L2',
            'TiempoR',
            'Torres/L0',
            'Torres/L1',
            'Torres/L2',
            'TiempoRPr'
        ];

        try {
            // Crear carpeta principal de la estación si no existe
            if (!file_exists($rutaBase)) {
                if (!mkdir($rutaBase, 0755, true)) {
                    throw new Exception("No se pudo crear la carpeta principal: " . $rutaBase);
                }
                error_log("✅ Carpeta principal creada: " . $rutaBase);
            } else {
                error_log("⚠️ La carpeta principal ya existe: " . $rutaBase);
            }

            // Crear cada subcarpeta
            foreach ($estructura as $subcarpeta) {
                $rutaCompleta = $rutaBase . '/' . $subcarpeta;

                if (!file_exists($rutaCompleta)) {
                    if (!mkdir($rutaCompleta, 0755, true)) {
                        throw new Exception("No se pudo crear la carpeta: " . $rutaCompleta);
                    }
                    error_log("✅ Subcarpeta creada: " . $rutaCompleta);
                } else {
                    error_log("⚠️ La subcarpeta ya existe: " . $rutaCompleta);
                }
            }

            error_log("=== FIN crearEstructuraCarpetasEstacion EXITOSO ===");
            return $rutaBase; // ✅ Retornar la ruta creada

        } catch (Exception $e) {
            error_log("❌ Error al crear estructura de carpetas: " . $e->getMessage());
            throw new Exception("Error al crear estructura de carpetas: " . $e->getMessage());
        }
    }

    /**
     * Renombra la carpeta de una estación cuando se actualiza su nombre
     */
    private function renombrarCarpetaEstacion($nombreAntiguo, $nombreNuevo)
    {
        error_log("=== INICIO renombrarCarpetaEstacion ===");
        error_log("Nombre antiguo: " . $nombreAntiguo);
        error_log("Nombre nuevo: " . $nombreNuevo);

        // Normalizar ambos nombres
        $nombreCarpetaAntigua = $this->normalizarNombreParaCarpeta($nombreAntiguo);
        $nombreCarpetaNueva = $this->normalizarNombreParaCarpeta($nombreNuevo);

        error_log("Carpeta antigua normalizada: " . $nombreCarpetaAntigua);
        error_log("Carpeta nueva normalizada: " . $nombreCarpetaNueva);

        // Si los nombres normalizados son iguales, no hacer nada
        if ($nombreCarpetaAntigua === $nombreCarpetaNueva) {
            error_log("⚠️ Los nombres normalizados son iguales, no se requiere renombrar");
            return true;
        }

        $rutaBase = dirname(__DIR__) . '/uploads/';
        $rutaAntigua = $rutaBase . $nombreCarpetaAntigua;
        $rutaNueva = $rutaBase . $nombreCarpetaNueva;

        error_log("Ruta antigua: " . $rutaAntigua);
        error_log("Ruta nueva: " . $rutaNueva);

        try {
            // Verificar si la carpeta antigua existe
            if (!file_exists($rutaAntigua)) {
                error_log("⚠️ La carpeta antigua no existe, creando nueva estructura");
                // Si no existe la carpeta antigua, crear la nueva estructura
                return $this->crearEstructuraCarpetasEstacion($nombreNuevo);
            }

            // Verificar si la carpeta nueva ya existe
            if (file_exists($rutaNueva)) {
                error_log("⚠️ La carpeta nueva ya existe, no se puede renombrar");
                throw new Exception("La carpeta destino ya existe: " . $rutaNueva);
            }

            // Renombrar la carpeta
            if (!rename($rutaAntigua, $rutaNueva)) {
                throw new Exception("No se pudo renombrar la carpeta de " . $rutaAntigua . " a " . $rutaNueva);
            }

            error_log("✅ Carpeta renombrada exitosamente");
            error_log("=== FIN renombrarCarpetaEstacion EXITOSO ===");
            return true;

        } catch (Exception $e) {
            error_log("❌ Error al renombrar carpeta: " . $e->getMessage());
            // No lanzar excepción para no afectar la actualización de la BD
            error_log("⚠️ ADVERTENCIA: No se pudo renombrar la carpeta pero la BD se actualizó");
            return false;
        }
    }

    /**
     * Obtiene todas las estaciones con su información básica
     */
    public function obtenerTodasLasEstaciones()
    {
        $query = "SELECT 
                    e.id_estacion,
                    e.codigo,
                    e.nombre,
                    e.provincia,
                    e.canton,
                    e.parroquia,
                    e.comunidad,
                    e.latitud,
                    e.longitud,
                    e.altura_terreno,
                    e.tag_codigo_iner,
                    e.fecha_instalacion,
                    e.estado_activo,
                    CASE 
                        WHEN e.estado_activo = true THEN 'ACTIVA'
                        ELSE 'INACTIVA'
                    END as estado_texto,
                    CASE 
                        WHEN e.estado_activo = true THEN 'active'
                        ELSE 'offline'
                    END as status
                  FROM estaciones e 
                  ORDER BY e.id_estacion";

        $result = pg_query($this->conn, $query);

        if (!$result) {
            error_log("Error SQL al obtener estaciones: " . pg_last_error($this->conn));
            throw new Exception("Error al obtener estaciones: " . pg_last_error($this->conn));
        }

        $estaciones = array();
        while ($row = pg_fetch_assoc($result)) {
            if (!empty($row['latitud']) && !empty($row['longitud'])) {
                $estaciones[] = $row;
            }
        }

        error_log("Se encontraron " . count($estaciones) . " estaciones válidas");
        return $estaciones;
    }

    /**
     * Obtiene información completa de una estación específica
     */
    public function obtenerInformacionEstacionCompleta($id_estacion)
    {
        // Obtener información básica de la estación
        $query_estacion = "SELECT 
                            e.id_estacion,
                            e.codigo,
                            e.nombre,
                            e.provincia,
                            e.canton,
                            e.parroquia,
                            e.comunidad,
                            e.latitud,
                            e.longitud,
                            e.altura_terreno,
                            e.tag_codigo_iner,
                            e.fecha_instalacion,
                            e.estado_activo,
                            CASE 
                                WHEN e.estado_activo = true THEN 'ACTIVA'
                                ELSE 'INACTIVA'
                            END as estado_texto,
                            CASE 
                                WHEN e.estado_activo = true THEN 'active'
                                ELSE 'offline'
                            END as status
                          FROM estaciones e 
                          WHERE e.id_estacion = $1";

        $result_estacion = pg_query_params($this->conn, $query_estacion, [$id_estacion]);

        if (!$result_estacion) {
            throw new Exception("Error al obtener información de estación: " . pg_last_error($this->conn));
        }

        $estacion = pg_fetch_assoc($result_estacion);

        if (!$estacion) {
            throw new Exception("Estación no encontrada");
        }

        // Obtener componentes
        $query_componentes = "SELECT 
                        c.id_componente,
                        c.id_catalogo_componente,
                        cc.nombre as tipo_componente,
                        c.marca,
                        c.modelo,
                        c.serie,
                        c.especificaciones,
                        c.fecha_ultimo_mantenimiento,
                        c.estado_componente,
                        c.observaciones
                      FROM componentes c
                      INNER JOIN catalogo_componentes cc ON c.id_catalogo_componente = cc.id_catalogo_componente
                      WHERE c.id_estacion = $1 
                      ORDER BY cc.nombre, c.marca, c.modelo";

        $result_componentes = pg_query_params($this->conn, $query_componentes, [$id_estacion]);

        $componentes = array();
        if ($result_componentes) {
            while ($row = pg_fetch_assoc($result_componentes)) {
                $componentes[] = $row;
            }
        }

        // Obtener sensores
        $query_sensores = "SELECT 
                     s.id_sensor,
                     s.id_catalogo_sensor,
                     cs.nombre as tipo_sensor,
                     s.marca,
                     s.modelo,
                     s.serie,
                     s.fecha_ultimo_mantenimiento,
                     s.estado_sensor,
                     s.observaciones
                   FROM sensores s
                   INNER JOIN catalogo_sensores cs ON s.id_catalogo_sensor = cs.id_catalogo_sensor
                   WHERE s.id_estacion = $1 
                   ORDER BY cs.nombre, s.marca, s.modelo";

        $result_sensores = pg_query_params($this->conn, $query_sensores, [$id_estacion]);

        $sensores = array();
        if ($result_sensores) {
            while ($row = pg_fetch_assoc($result_sensores)) {
                $sensores[] = $row;
            }
        }

        // Obtener imágenes
        $imagenes = $this->obtenerImagenesEstacion($id_estacion, $estacion['nombre']);

        return array(
            'estacion' => $estacion,
            'componentes' => $componentes,
            'sensores' => $sensores,
            'imagenes' => $imagenes,
            'ultimo_registro' => null
        );
    }

    /**
     * Obtiene las rutas de imágenes para una estación específica
     */
    private function obtenerImagenesEstacion($id_estacion, $nombre_estacion)
    {
        $query = "SELECT ruta_fotografia, ruta_mapa FROM estaciones WHERE id_estacion = $1";
        $result = pg_query_params($this->conn, $query, [$id_estacion]);

        if ($result && pg_num_rows($result) > 0) {
            $row = pg_fetch_assoc($result);
            $ruta_foto = $row['ruta_fotografia'];
            $ruta_mapa = $row['ruta_mapa'];

            $fotografia = $ruta_foto ? basename($ruta_foto) : 'default.png';
            $mapa = $ruta_mapa ? basename($ruta_mapa) : 'default.png';

            return [
                'mapa' => $mapa,
                'fotografia' => $fotografia
            ];
        }

        return [
            'mapa' => 'default.png',
            'fotografia' => 'default.png'
        ];
    }

    public function formatearCoordenadas($lat, $lng)
    {
        return [
            'lat' => floatval($lat),
            'lng' => floatval($lng),
            'lat_str' => abs($lat) . '°' . ($lat < 0 ? 'S' : 'N'),
            'lng_str' => abs($lng) . '°' . ($lng < 0 ? 'W' : 'E')
        ];
    }

    // =================================================================
    // MÉTODOS PARA ADMINISTRACIÓN DE SENSORES
    // =================================================================

    /**
     * Agrega un nuevo sensor a la estación
     */
    public function agregarSensor($datos_sensor)
    {
        error_log("[MODELO] ===== INICIO agregarSensor =====");
        error_log("[MODELO] Datos recibidos: " . json_encode($datos_sensor, JSON_UNESCAPED_UNICODE));

        // Validar que id_catalogo_sensor sea un número válido
        if (!isset($datos_sensor['id_catalogo_sensor']) || intval($datos_sensor['id_catalogo_sensor']) <= 0) {
            error_log("[MODELO] ❌ ERROR: id_catalogo_sensor inválido o no proporcionado");
            throw new Exception("Error: El tipo de sensor es requerido (id_catalogo_sensor inválido)");
        }

        $query = "INSERT INTO sensores (
                    id_estacion, 
                    id_catalogo_sensor, 
                    marca, 
                    modelo, 
                    serie, 
                    fecha_ultimo_mantenimiento, 
                    estado_sensor, 
                    observaciones
                  ) VALUES (
                    $1, $2, $3, $4, $5, $6, $7, $8
                  ) RETURNING id_sensor";

        $params = [
            $datos_sensor['id_estacion'],
            intval($datos_sensor['id_catalogo_sensor']),
            $datos_sensor['marca'] ?? null,
            $datos_sensor['modelo'] ?? null,
            $datos_sensor['serie'] ?? null,
            $datos_sensor['fecha_ultimo_mantenimiento'] ?? null,
            $datos_sensor['estado_sensor'] ?? 'ACTIVO',
            $datos_sensor['observaciones'] ?? null
        ];

        error_log("[MODELO] Query a ejecutar: " . $query);
        error_log("[MODELO] Params: " . json_encode($params, JSON_UNESCAPED_UNICODE));

        $result = pg_query_params($this->conn, $query, $params);

        if (!$result) {
            $error_msg = pg_last_error($this->conn);
            error_log("[MODELO] ❌ Error PostgreSQL al agregar sensor: " . $error_msg);
            throw new Exception("Error al agregar sensor: " . $error_msg);
        }

        $row = pg_fetch_assoc($result);
        if (!$row) {
            throw new Exception("Error: No se pudo confirmar la inserción del sensor");
        }

        $id_sensor = intval($row['id_sensor']);
        error_log("[MODELO] ✅ Sensor agregado exitosamente con ID: " . $id_sensor);
        return $id_sensor;
    }

    /**
     * Actualiza un sensor existente
     */
    public function actualizarSensor($id_sensor, $datos_sensor)
    {
        error_log("[MODELO] Actualizando sensor ID: $id_sensor");
        error_log("[MODELO] Datos recibidos: " . json_encode($datos_sensor, JSON_UNESCAPED_UNICODE));

        $query = "UPDATE sensores SET 
                    marca = $1,
                    modelo = $2,
                    serie = $3,
                    fecha_ultimo_mantenimiento = $4,
                    estado_sensor = $5,
                    observaciones = $6,
                    updated_at = CURRENT_TIMESTAMP
                  WHERE id_sensor = $7";

        $params = [
            $datos_sensor['marca'] ?? null,
            $datos_sensor['modelo'] ?? null,
            $datos_sensor['serie'] ?? null,
            $datos_sensor['fecha_ultimo_mantenimiento'] ?? null,
            $datos_sensor['estado_sensor'] ?? 'ACTIVO',
            $datos_sensor['observaciones'] ?? null,
            $id_sensor
        ];

        error_log("[MODELO] Params procesados: " . json_encode($params, JSON_UNESCAPED_UNICODE));

        $result = pg_query_params($this->conn, $query, $params);

        if (!$result) {
            $error_msg = pg_last_error($this->conn);
            error_log("[MODELO] Error al actualizar sensor: " . $error_msg);
            throw new Exception("Error al actualizar sensor: " . $error_msg);
        }

        $affected = pg_affected_rows($result);
        error_log("[MODELO] Sensor actualizado. Filas afectadas: " . $affected);
        return $affected > 0;
    }

    /**
     * Elimina un sensor
     */
    public function eliminarSensor($id_sensor)
    {
        $query = "DELETE FROM sensores WHERE id_sensor = $1";
        $result = pg_query_params($this->conn, $query, [$id_sensor]);

        if (!$result) {
            throw new Exception("Error al eliminar sensor: " . pg_last_error($this->conn));
        }

        return pg_affected_rows($result) > 0;
    }

    /**
     * Obtiene un sensor por su ID
     */
    public function obtenerSensorPorId($id_sensor)
    {
        $query = "SELECT 
                    s.id_sensor,
                    s.id_estacion,
                    s.id_catalogo_sensor,
                    cs.nombre as tipo_sensor,
                    s.marca,
                    s.modelo,
                    s.serie,
                    s.fecha_ultimo_mantenimiento,
                    s.estado_sensor,
                    s.observaciones
                  FROM sensores s
                  INNER JOIN catalogo_sensores cs ON s.id_catalogo_sensor = cs.id_catalogo_sensor
                  WHERE s.id_sensor = $1";

        $result = pg_query_params($this->conn, $query, [$id_sensor]);

        if (!$result) {
            throw new Exception("Error al obtener sensor: " . pg_last_error($this->conn));
        }

        return pg_fetch_assoc($result) ?: null;
    }

    // =================================================================
    // MÉTODOS PARA ADMINISTRACIÓN DE COMPONENTES
    // =================================================================

    /**
     * Agrega un nuevo componente a la estación
     */
    public function agregarComponente($datos_componente)
    {
        error_log("[MODELO] ===== INICIO agregarComponente =====");
        error_log("[MODELO] Datos recibidos: " . json_encode($datos_componente, JSON_UNESCAPED_UNICODE));

        // Validar que id_catalogo_componente sea un número válido
        if (!isset($datos_componente['id_catalogo_componente']) || intval($datos_componente['id_catalogo_componente']) <= 0) {
            error_log("[MODELO] ❌ ERROR: id_catalogo_componente inválido o no proporcionado");
            throw new Exception("Error: El tipo de componente es requerido (id_catalogo_componente inválido)");
        }

        $query = "INSERT INTO componentes (
                    id_estacion, 
                    id_catalogo_componente, 
                    marca, 
                    modelo, 
                    serie, 
                    especificaciones,
                    fecha_ultimo_mantenimiento, 
                    estado_componente, 
                    observaciones
                  ) VALUES (
                    $1, $2, $3, $4, $5, $6, $7, $8, $9
                  ) RETURNING id_componente";

        $params = [
            $datos_componente['id_estacion'],
            intval($datos_componente['id_catalogo_componente']),
            $datos_componente['marca'] ?? null,
            $datos_componente['modelo'] ?? null,
            $datos_componente['serie'] ?? null,
            $datos_componente['especificaciones'] ?? null,
            $datos_componente['fecha_ultimo_mantenimiento'] ?? null,
            $datos_componente['estado_componente'] ?? 'ACTIVO',
            $datos_componente['observaciones'] ?? null
        ];

        error_log("[MODELO] Query a ejecutar: " . $query);
        error_log("[MODELO] Params: " . json_encode($params, JSON_UNESCAPED_UNICODE));

        $result = pg_query_params($this->conn, $query, $params);

        if (!$result) {
            $error_msg = pg_last_error($this->conn);
            error_log("[MODELO] ❌ Error PostgreSQL al agregar componente: " . $error_msg);
            throw new Exception("Error al agregar componente: " . $error_msg);
        }

        $row = pg_fetch_assoc($result);
        if (!$row) {
            throw new Exception("Error: No se pudo confirmar la inserción del componente");
        }

        $id_componente = intval($row['id_componente']);
        error_log("[MODELO] ✅ Componente agregado exitosamente con ID: " . $id_componente);
        return $id_componente;
    }

    /**
     * Actualiza un componente existente
     */
    public function actualizarComponente($id_componente, $datos_componente)
    {
        error_log("[MODELO] Actualizando componente ID: $id_componente");
        error_log("[MODELO] Datos recibidos: " . json_encode($datos_componente, JSON_UNESCAPED_UNICODE));

        $query = "UPDATE componentes SET 
                    marca = $1,
                    modelo = $2,
                    serie = $3,
                    especificaciones = $4,
                    fecha_ultimo_mantenimiento = $5,
                    estado_componente = $6,
                    observaciones = $7,
                    updated_at = CURRENT_TIMESTAMP
                  WHERE id_componente = $8";

        $params = [
            $datos_componente['marca'] ?? null,
            $datos_componente['modelo'] ?? null,
            $datos_componente['serie'] ?? null,
            $datos_componente['especificaciones'] ?? null,
            $datos_componente['fecha_ultimo_mantenimiento'] ?? null,
            $datos_componente['estado_componente'] ?? 'ACTIVO',
            $datos_componente['observaciones'] ?? null,
            $id_componente
        ];

        error_log("[MODELO] Params procesados: " . json_encode($params, JSON_UNESCAPED_UNICODE));

        $result = pg_query_params($this->conn, $query, $params);

        if (!$result) {
            $error_msg = pg_last_error($this->conn);
            error_log("[MODELO] Error al actualizar componente: " . $error_msg);
            throw new Exception("Error al actualizar componente: " . $error_msg);
        }

        $affected = pg_affected_rows($result);
        error_log("[MODELO] Componente actualizado. Filas afectadas: " . $affected);
        return $affected > 0;
    }

    /**
     * Elimina un componente
     */
    public function eliminarComponente($id_componente)
    {
        $query = "DELETE FROM componentes WHERE id_componente = $1";
        $result = pg_query_params($this->conn, $query, [$id_componente]);

        if (!$result) {
            throw new Exception("Error al eliminar componente: " . pg_last_error($this->conn));
        }

        return pg_affected_rows($result) > 0;
    }

    /**
     * Obtiene un componente por su ID
     */
    public function obtenerComponentePorId($id_componente)
    {
        $query = "SELECT 
                    c.id_componente,
                    c.id_estacion,
                    c.id_catalogo_componente,
                    cc.nombre as tipo_componente,
                    c.marca,
                    c.modelo,
                    c.serie,
                    c.especificaciones,
                    c.fecha_ultimo_mantenimiento,
                    c.estado_componente,
                    c.observaciones
                  FROM componentes c
                  INNER JOIN catalogo_componentes cc ON c.id_catalogo_componente = cc.id_catalogo_componente
                  WHERE c.id_componente = $1";

        $result = pg_query_params($this->conn, $query, [$id_componente]);

        if (!$result) {
            throw new Exception("Error al obtener componente: " . pg_last_error($this->conn));
        }

        return pg_fetch_assoc($result) ?: null;
    }

    public function obtenerTiposSensores()
    {
        return $this->obtenerCatalogoSensores();
    }

    public function obtenerTiposComponentes()
    {
        return $this->obtenerCatalogoComponentes();
    }

    public function obtenerEstados()
    {
        return ['ACTIVO', 'INACTIVO', 'MANTENIMIENTO', 'FUERA_SERVICIO'];
    }
    /**
     * Normaliza un nombre para usarlo como nombre de carpeta
     * Convierte tildes a letras sin tilde y caracteres especiales a guiones bajos
     */
    private function normalizarNombreParaCarpeta($nombre)
    {
        // Convertir a UTF-8 si no lo está
        if (!mb_check_encoding($nombre, 'UTF-8')) {
            $nombre = mb_convert_encoding($nombre, 'UTF-8');
        }

        // Reemplazar caracteres con tilde por su equivalente sin tilde
        $nombre = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ', 'ü', 'Ü'],
            ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', 'n', 'N', 'u', 'U'],
            $nombre
        );

        // Eliminar cualquier carácter especial restante y reemplazar espacios por guiones bajos
        $nombre = preg_replace('/[^a-zA-Z0-9_-]/', '_', $nombre);

        // Eliminar guiones bajos múltiples consecutivos
        $nombre = preg_replace('/_+/', '_', $nombre);

        // Eliminar guiones bajos al inicio y al final
        $nombre = trim($nombre, '_');

        return $nombre;
    }
    // =================================================================
    // MÉTODOS PARA ADMINISTRACIÓN COMPLETA DE ESTACIONES
    // =================================================================

    /**
     * Actualiza una estación completa incluyendo imágenes
     */
    public function actualizarEstacion($id_estacion, $datos_estacion, $files_data = null)
    {
        error_log("=== INICIO actualizarEstacion ===");
        error_log("ID Estación: " . $id_estacion);
        error_log("Datos recibidos: " . print_r($datos_estacion, true));

        // ✅ VALIDAR QUE NOMBRE ESTÉ PRESENTE
        if (!isset($datos_estacion['nombre']) || empty(trim($datos_estacion['nombre']))) {
            throw new Exception("El nombre de la estación es requerido para actualizar");
        }

        // ✅ NORMALIZAR EL NOMBRE NUEVO (eliminar tildes)
        $nombre_nuevo_original = trim($datos_estacion['nombre']);
        $nombre_nuevo_normalizado = $this->normalizarNombreParaCarpeta($nombre_nuevo_original);

        error_log("Nombre nuevo original: " . $nombre_nuevo_original);
        error_log("Nombre nuevo normalizado: " . $nombre_nuevo_normalizado);

        $estacion_actual = $this->obtenerInformacionEstacionCompleta($id_estacion);
        if (!$estacion_actual) {
            throw new Exception("Estación no encontrada");
        }

        // ✅ OBTENER EL NOMBRE ANTIGUO (ya está normalizado en BD)
        $nombre_antiguo = $estacion_actual['estacion']['nombre'];

        error_log("Nombre antiguo (de BD): " . $nombre_antiguo);

        pg_query($this->conn, "BEGIN");

        try {
            $imagenes_actualizadas = false;
            $rutas_imagenes = null;

            // ✅ PROCESAR IMÁGENES SI SE ENVIARON
            if ($files_data && (isset($files_data['fotografia']) || isset($files_data['mapa']))) {
                $imagenes_actualizadas = true;

                // ✅ USAR EL NOMBRE NORMALIZADO PARA LAS IMÁGENES
                $nombre_para_imagenes = $nombre_nuevo_normalizado;

                $rutas_imagenes = $this->procesarImagenesActualizacion(
                    $files_data,
                    $nombre_para_imagenes,
                    $estacion_actual['imagenes']
                );
            }

            // ✅ QUERY COMPLETO CON NOMBRE NORMALIZADO
            $query = "UPDATE estaciones SET 
                nombre = $1,
                codigo = $2,
                provincia = $3,
                canton = $4,
                parroquia = $5,
                comunidad = $6,
                latitud = $7,
                longitud = $8,
                altura_terreno = $9,
                tag_codigo_iner = $10,
                fecha_instalacion = $11";

            $params = [
                $nombre_nuevo_normalizado,                          // $1 ✅ USAR NORMALIZADO
                trim($datos_estacion['codigo']),                    // $2
                trim($datos_estacion['provincia']),                 // $3
                trim($datos_estacion['canton']),                    // $4
                !empty($datos_estacion['parroquia']) ? trim($datos_estacion['parroquia']) : null,   // $5
                !empty($datos_estacion['comunidad']) ? trim($datos_estacion['comunidad']) : null,   // $6
                floatval($datos_estacion['latitud']),               // $7
                floatval($datos_estacion['longitud']),              // $8
                !empty($datos_estacion['altura_terreno']) ? intval($datos_estacion['altura_terreno']) : null,  // $9
                trim($datos_estacion['tag_codigo_iner']),           // $10
                !empty($datos_estacion['fecha_instalacion']) ? $datos_estacion['fecha_instalacion'] : null     // $11
            ];

            if ($imagenes_actualizadas) {
                $query .= ", ruta_fotografia = $12, ruta_mapa = $13 WHERE id_estacion = $14";
                $params[] = $rutas_imagenes['ruta_fotografia'];  // $12
                $params[] = $rutas_imagenes['ruta_mapa'];        // $13
                $params[] = $id_estacion;                         // $14
            } else {
                $query .= " WHERE id_estacion = $12";
                $params[] = $id_estacion;                         // $12
            }

            error_log("Query a ejecutar: " . $query);
            error_log("Parámetros: " . print_r($params, true));

            $result = pg_query_params($this->conn, $query, $params);

            if (!$result) {
                $error = pg_last_error($this->conn);
                error_log("❌ Error PostgreSQL: " . $error);
                throw new Exception("Error al actualizar estación: " . $error);
            }

            $affected = pg_affected_rows($result);
            error_log("✅ Filas afectadas: " . $affected);

            if ($affected === 0) {
                error_log("⚠️ Advertencia: No se actualizó ninguna fila");
            }

            pg_query($this->conn, "COMMIT");
            error_log("✅ COMMIT exitoso");

            // ✅ RENOMBRAR CARPETA SI EL NOMBRE CAMBIÓ (DESPUÉS DEL COMMIT)
            // Ahora comparamos nombres normalizados
            if ($nombre_antiguo !== $nombre_nuevo_normalizado) {
                error_log("📁 Renombrando carpeta de estación...");
                $this->renombrarCarpetaEstacion($nombre_antiguo, $nombre_nuevo_normalizado);
            } else {
                error_log("ℹ️ El nombre no cambió, no se requiere renombrar carpeta");
            }

            error_log("=== FIN actualizarEstacion EXITOSO ===");
            return true;

        } catch (Exception $e) {
            pg_query($this->conn, "ROLLBACK");
            error_log("❌ ROLLBACK ejecutado");
            error_log("=== FIN actualizarEstacion CON ERROR ===");
            throw new Exception("Error al actualizar estación: " . $e->getMessage());
        }
    }

    /**
     * Elimina una estación completa en cascada
     */
    public function eliminarEstacion($id_estacion)
    {
        error_log("=== INICIO eliminarEstacion OPTIMIZADO ===");
        error_log("ID estación a eliminar: " . $id_estacion);

        // ✅ PASO 1: Verificar que la estación existe y obtener su nombre
        $query_check = "SELECT nombre, ruta_fotografia, ruta_mapa FROM estaciones WHERE id_estacion = $1";
        $result_check = pg_query_params($this->conn, $query_check, [$id_estacion]);

        if (!$result_check || pg_num_rows($result_check) === 0) {
            error_log("❌ ERROR: Estación no encontrada con ID: " . $id_estacion);
            throw new Exception("Estación no encontrada");
        }

        $row = pg_fetch_assoc($result_check);
        $nombre_estacion = $row['nombre'];
        $ruta_fotografia = $row['ruta_fotografia'];
        $ruta_mapa = $row['ruta_mapa'];

        error_log("✅ Estación encontrada: " . $nombre_estacion);

        // ✅ PASO 2: Iniciar transacción
        $begin = pg_query($this->conn, "BEGIN");
        if (!$begin) {
            throw new Exception("Error al iniciar transacción: " . pg_last_error($this->conn));
        }
        error_log("✅ Transacción iniciada");

        try {
            // ✅ PASO 3: Eliminar SENSORES primero
            $query_sensores = "DELETE FROM sensores WHERE id_estacion = $1";
            $result_sensores = pg_query_params($this->conn, $query_sensores, [$id_estacion]);

            if (!$result_sensores) {
                throw new Exception("Error al eliminar sensores: " . pg_last_error($this->conn));
            }

            $sensores_eliminados = pg_affected_rows($result_sensores);
            error_log("✅ Sensores eliminados: " . $sensores_eliminados);

            // ✅ PASO 4: Eliminar COMPONENTES
            $query_componentes = "DELETE FROM componentes WHERE id_estacion = $1";
            $result_componentes = pg_query_params($this->conn, $query_componentes, [$id_estacion]);

            if (!$result_componentes) {
                throw new Exception("Error al eliminar componentes: " . pg_last_error($this->conn));
            }

            $componentes_eliminados = pg_affected_rows($result_componentes);
            error_log("✅ Componentes eliminados: " . $componentes_eliminados);

            // ✅ PASO 5: Eliminar la ESTACIÓN
            $query_estacion = "DELETE FROM estaciones WHERE id_estacion = $1";
            $result_estacion = pg_query_params($this->conn, $query_estacion, [$id_estacion]);

            if (!$result_estacion) {
                throw new Exception("Error al eliminar estación: " . pg_last_error($this->conn));
            }

            $estaciones_eliminadas = pg_affected_rows($result_estacion);
            error_log("✅ Estación eliminada de BD: " . $estaciones_eliminadas . " fila(s)");

            // ✅ PASO 6: COMMIT inmediato
            $commit = pg_query($this->conn, "COMMIT");
            if (!$commit) {
                throw new Exception("Error al hacer commit: " . pg_last_error($this->conn));
            }
            error_log("✅ COMMIT exitoso");

            // ✅ PASO 7: Eliminar archivos de imágenes (DESPUÉS del commit)
            $directorio_imagenes = dirname(__DIR__) . '/public/img/';

            if ($ruta_fotografia && $ruta_fotografia !== 'public/img/default.png') {
                $archivo_foto = $directorio_imagenes . basename($ruta_fotografia);
                if (file_exists($archivo_foto)) {
                    if (@unlink($archivo_foto)) {
                        error_log("✅ Fotografía eliminada: " . basename($ruta_fotografia));
                    } else {
                        error_log("⚠️ No se pudo eliminar fotografía: " . basename($ruta_fotografia));
                    }
                }
            }

            if ($ruta_mapa && $ruta_mapa !== 'public/img/default.png') {
                $archivo_mapa = $directorio_imagenes . basename($ruta_mapa);
                if (file_exists($archivo_mapa)) {
                    if (@unlink($archivo_mapa)) {
                        error_log("✅ Mapa eliminado: " . basename($ruta_mapa));
                    } else {
                        error_log("⚠️ No se pudo eliminar mapa: " . basename($ruta_mapa));
                    }
                }
            }

            // ✅ PASO 8: Eliminar la CARPETA COMPLETA de la estación en uploads/
            error_log("📁 Eliminando carpeta completa de la estación...");
            $this->eliminarCarpetaEstacion($nombre_estacion);

            error_log("=== FIN eliminarEstacion EXITOSO ===");
            return true;

        } catch (Exception $e) {
            // ✅ ROLLBACK en caso de error
            $rollback = pg_query($this->conn, "ROLLBACK");
            error_log("❌ ROLLBACK ejecutado");
            error_log("❌ Error: " . $e->getMessage());
            error_log("=== FIN eliminarEstacion CON ERROR ===");
            throw new Exception("Error al eliminar estación: " . $e->getMessage());
        }
    }

    /**
     * Procesa las imágenes para actualización
     */
    private function procesarImagenesActualizacion($files_data, $nombre_estacion, $imagenes_actuales)
    {
        $directorio_imagenes = dirname(__DIR__) . '/public/img/';
        $ruta_fotografia = null;
        $ruta_mapa = null;

        error_log("=== PROCESAMIENTO DE IMÁGENES ===");
        error_log("Directorio de imágenes: " . $directorio_imagenes);

        // ✅ Normalizar el nombre de la estación para los archivos
        $nombre_limpio = $this->normalizarNombreParaCarpeta($nombre_estacion);
        error_log("Nombre normalizado para imágenes: " . $nombre_limpio);

        // Procesar fotografía si se envió
        if (isset($files_data['fotografia']) && $files_data['fotografia']['error'] === UPLOAD_ERR_OK) {
            error_log("📸 Procesando nueva fotografía...");

            $foto_extension = strtolower(pathinfo($files_data['fotografia']['name'], PATHINFO_EXTENSION));
            $nombre_foto = "F_{$nombre_limpio}.{$foto_extension}";
            $ruta_foto_completa = $directorio_imagenes . $nombre_foto;

            // Eliminar imagen anterior SI EXISTE y NO es default.png
            if (
                isset($imagenes_actuales['fotografia']) &&
                $imagenes_actuales['fotografia'] !== 'default.png' &&
                !empty($imagenes_actuales['fotografia'])
            ) {
                $imagen_anterior = $directorio_imagenes . $imagenes_actuales['fotografia'];
                if (file_exists($imagen_anterior)) {
                    @unlink($imagen_anterior);
                    error_log("🗑️ Fotografía anterior eliminada: " . basename($imagen_anterior));
                }
            }

            // Guardar nueva imagen
            if (!move_uploaded_file($files_data['fotografia']['tmp_name'], $ruta_foto_completa)) {
                throw new Exception("Error al guardar la nueva fotografía");
            }

            $ruta_fotografia = "public/img/{$nombre_foto}";
            error_log("✅ Nueva fotografía guardada: " . $nombre_foto);
        } else {
            // Mantener imagen actual
            $ruta_fotografia = "public/img/" . $imagenes_actuales['fotografia'];
            error_log("ℹ️ Manteniendo fotografía actual");
        }

        // Procesar mapa si se envió
        if (isset($files_data['mapa']) && $files_data['mapa']['error'] === UPLOAD_ERR_OK) {
            error_log("🗺️ Procesando nuevo mapa...");

            $mapa_extension = strtolower(pathinfo($files_data['mapa']['name'], PATHINFO_EXTENSION));
            $nombre_mapa = "M_{$nombre_limpio}.{$mapa_extension}";
            $ruta_mapa_completa = $directorio_imagenes . $nombre_mapa;

            // Eliminar imagen anterior SI EXISTE y NO es default.png
            if (
                isset($imagenes_actuales['mapa']) &&
                $imagenes_actuales['mapa'] !== 'default.png' &&
                !empty($imagenes_actuales['mapa'])
            ) {
                $imagen_anterior = $directorio_imagenes . $imagenes_actuales['mapa'];
                if (file_exists($imagen_anterior)) {
                    @unlink($imagen_anterior);
                    error_log("🗑️ Mapa anterior eliminado: " . basename($imagen_anterior));
                }
            }

            // Guardar nueva imagen
            if (!move_uploaded_file($files_data['mapa']['tmp_name'], $ruta_mapa_completa)) {
                throw new Exception("Error al guardar el nuevo mapa");
            }

            $ruta_mapa = "public/img/{$nombre_mapa}";
            error_log("✅ Nuevo mapa guardado: " . $nombre_mapa);
        } else {
            // Mantener imagen actual
            $ruta_mapa = "public/img/" . $imagenes_actuales['mapa'];
            error_log("ℹ️ Manteniendo mapa actual");
        }

        error_log("=== FIN PROCESAMIENTO DE IMÁGENES ===");

        return [
            'ruta_fotografia' => $ruta_fotografia,
            'ruta_mapa' => $ruta_mapa
        ];
    }

    /**
     * Elimina archivos de imagen
     */
    private function eliminarArchivosImagen($imagenes)
    {
        $directorio_imagenes = dirname(__DIR__) . '/public/img/';

        if (isset($imagenes['fotografia']) && $imagenes['fotografia'] !== 'default.png') {
            $archivo_foto = $directorio_imagenes . $imagenes['fotografia'];
            if (file_exists($archivo_foto)) {
                @unlink($archivo_foto);
            }
        }

        if (isset($imagenes['mapa']) && $imagenes['mapa'] !== 'default.png') {
            $archivo_mapa = $directorio_imagenes . $imagenes['mapa'];
            if (file_exists($archivo_mapa)) {
                @unlink($archivo_mapa);
            }
        }
    }

    // =================================================================
    // MÉTODOS PARA CREAR ESTACIÓN - CON VALIDACIONES MEJORADAS
    // =================================================================

    /**
     * Crea una nueva estación completa
     */
    public function crearEstacion($post_data, $files_data = null)
    {
        error_log("=== INICIO MÉTODO crearEstacion ===");
        $nombre_normalizado = $this->normalizarNombreParaCarpeta($post_data['nombre'] ?? '');

        error_log("Nombre original recibido: " . ($post_data['nombre'] ?? ''));
        error_log("Nombre normalizado (sin tildes): " . $nombre_normalizado);

        $datos_estacion = [
            'codigo' => trim($post_data['codigo'] ?? ''),
            'nombre' => $nombre_normalizado,
            'provincia' => trim($post_data['provincia'] ?? ''),
            'canton' => trim($post_data['canton'] ?? ''),
            'parroquia' => !empty($post_data['parroquia']) ? trim($post_data['parroquia']) : null,
            'comunidad' => !empty($post_data['comunidad']) ? trim($post_data['comunidad']) : null,
            'latitud' => floatval($post_data['latitud'] ?? 0),
            'longitud' => floatval($post_data['longitud'] ?? 0),
            'altura_terreno' => !empty($post_data['altura_terreno']) ? intval($post_data['altura_terreno']) : null,
            'tag_codigo_iner' => trim($post_data['tag_codigo_iner'] ?? ''),
            'fecha_instalacion' => !empty($post_data['fecha_instalacion']) ? $post_data['fecha_instalacion'] : null
        ];

        // Validaciones básicas
        if (empty($datos_estacion['nombre']))
            throw new Exception("El nombre de la estación es requerido");
        if (empty($datos_estacion['codigo']))
            throw new Exception("El código de la estación es requerido");
        if (empty($datos_estacion['tag_codigo_iner']))
            throw new Exception("El TAG código INER es requerido");
        if (empty($datos_estacion['provincia']))
            throw new Exception("La provincia es requerida");
        if (empty($datos_estacion['canton']))
            throw new Exception("El cantón es requerido");
        if (empty($datos_estacion['latitud']) || empty($datos_estacion['longitud'])) {
            throw new Exception("Las coordenadas son requeridas");
        }

        // Validar duplicados
        // ✅ VALIDAR DUPLICADOS POR NOMBRE
        $query_check_nombre = "SELECT id_estacion FROM estaciones WHERE LOWER(nombre) = LOWER($1)";
        $result_check_nombre = pg_query_params($this->conn, $query_check_nombre, [$datos_estacion['nombre']]);
        if ($result_check_nombre && pg_num_rows($result_check_nombre) > 0) {
            throw new Exception("Ya existe una estación con ese nombre. No se puede crear.");
        }

        // ✅ VALIDAR DUPLICADOS POR CÓDIGO
        $query_check_codigo = "SELECT id_estacion FROM estaciones WHERE LOWER(codigo) = LOWER($1)";
        $result_check_codigo = pg_query_params($this->conn, $query_check_codigo, [$datos_estacion['codigo']]);
        if ($result_check_codigo && pg_num_rows($result_check_codigo) > 0) {
            throw new Exception("Ya existe una estación con ese código. Debe usar un código único.");
        }

        // ✅ VALIDAR DUPLICADOS POR TAG INER
        $query_check_tag = "SELECT id_estacion FROM estaciones WHERE LOWER(tag_codigo_iner) = LOWER($1)";
        $result_check_tag = pg_query_params($this->conn, $query_check_tag, [$datos_estacion['tag_codigo_iner']]);
        if ($result_check_tag && pg_num_rows($result_check_tag) > 0) {
            throw new Exception("Ya existe una estación con ese TAG/Código INER. Debe usar un TAG único.");
        }

        // Procesar imágenes
        if (!$files_data || !isset($files_data['fotografia']) || !isset($files_data['mapa'])) {
            throw new Exception("Los archivos de fotografía y mapa son requeridos");
        }

        $directorio_imagenes = dirname(__DIR__) . '/public/img/';

        if (!is_dir($directorio_imagenes)) {
            mkdir($directorio_imagenes, 0755, true);
        }

        $nombre_limpio = $nombre_normalizado;
        $foto_extension = strtolower(pathinfo($files_data['fotografia']['name'], PATHINFO_EXTENSION));
        $nombre_foto = "F_{$nombre_limpio}.{$foto_extension}";
        $ruta_foto_completa = $directorio_imagenes . $nombre_foto;

        if (!move_uploaded_file($files_data['fotografia']['tmp_name'], $ruta_foto_completa)) {
            throw new Exception("Error al guardar la fotografía");
        }

        // Procesar mapa
        $mapa_extension = strtolower(pathinfo($files_data['mapa']['name'], PATHINFO_EXTENSION));
        $nombre_mapa = "M_{$nombre_limpio}.{$mapa_extension}";
        $ruta_mapa_completa = $directorio_imagenes . $nombre_mapa;

        if (!move_uploaded_file($files_data['mapa']['tmp_name'], $ruta_mapa_completa)) {
            @unlink($ruta_foto_completa);
            throw new Exception("Error al guardar el mapa");
        }

        $rutas_imagenes = [
            'ruta_fotografia' => "public/img/{$nombre_foto}",
            'ruta_mapa' => "public/img/{$nombre_mapa}"
        ];

        // ✅ CRÍTICO: Extraer sensores con validación estricta
        $datos_sensores = [];
        if (isset($post_data['sensor_tipo']) && is_array($post_data['sensor_tipo'])) {
            error_log("[MODELO] Procesando sensores. Total enviados: " . count($post_data['sensor_tipo']));

            foreach ($post_data['sensor_tipo'] as $index => $tipo) {
                error_log("[MODELO] Sensor[$index]: tipo='$tipo', vacio=" . (empty($tipo) ? 'SI' : 'NO'));

                // ✅ VALIDACIÓN ESTRICTA: Solo agregar si el valor es válido
                if (!empty($tipo) && $tipo !== '' && $tipo !== 'null' && intval($tipo) > 0) {
                    $datos_sensores[] = [
                        'id_catalogo_sensor' => intval($tipo),
                        'marca' => !empty($post_data['sensor_marca'][$index]) ? trim($post_data['sensor_marca'][$index]) : null,
                        'modelo' => !empty($post_data['sensor_modelo'][$index]) ? trim($post_data['sensor_modelo'][$index]) : null,
                        'serie' => !empty($post_data['sensor_serie'][$index]) ? trim($post_data['sensor_serie'][$index]) : null,
                        'estado' => !empty($post_data['sensor_estado'][$index]) ? $post_data['sensor_estado'][$index] : 'ACTIVO',
                        'fecha_mantenimiento' => !empty($post_data['sensor_mantenimiento'][$index]) ? $post_data['sensor_mantenimiento'][$index] : null,
                        'observaciones' => !empty($post_data['sensor_observaciones'][$index]) ? trim($post_data['sensor_observaciones'][$index]) : null
                    ];
                    error_log("[MODELO] ✅ Sensor[$index] AGREGADO con id_catalogo_sensor=" . intval($tipo));
                } else {
                    error_log("[MODELO] ❌ Sensor[$index] IGNORADO (tipo vacío o inválido)");
                }
            }
        }

        error_log("[MODELO] Total sensores válidos procesados: " . count($datos_sensores));

        // ✅ CRÍTICO: Extraer componentes con validación estricta
        $datos_componentes = [];
        if (isset($post_data['componente_tipo']) && is_array($post_data['componente_tipo'])) {
            error_log("[MODELO] Procesando componentes. Total enviados: " . count($post_data['componente_tipo']));

            foreach ($post_data['componente_tipo'] as $index => $tipo) {
                error_log("[MODELO] Componente[$index]: tipo='$tipo', vacio=" . (empty($tipo) ? 'SI' : 'NO'));

                // ✅ VALIDACIÓN ESTRICTA: Solo agregar si el valor es válido
                if (!empty($tipo) && $tipo !== '' && $tipo !== 'null' && intval($tipo) > 0) {
                    $datos_componentes[] = [
                        'id_catalogo_componente' => intval($tipo),
                        'marca' => !empty($post_data['componente_marca'][$index]) ? trim($post_data['componente_marca'][$index]) : null,
                        'modelo' => !empty($post_data['componente_modelo'][$index]) ? trim($post_data['componente_modelo'][$index]) : null,
                        'serie' => !empty($post_data['componente_serie'][$index]) ? trim($post_data['componente_serie'][$index]) : null,
                        'estado' => !empty($post_data['componente_estado'][$index]) ? $post_data['componente_estado'][$index] : 'ACTIVO',
                        'fecha_mantenimiento' => !empty($post_data['componente_mantenimiento'][$index]) ? $post_data['componente_mantenimiento'][$index] : null,
                        'especificaciones' => !empty($post_data['componente_especificaciones'][$index]) ? trim($post_data['componente_especificaciones'][$index]) : null,
                        'observaciones' => !empty($post_data['componente_observaciones'][$index]) ? trim($post_data['componente_observaciones'][$index]) : null
                    ];
                    error_log("[MODELO] ✅ Componente[$index] AGREGADO con id_catalogo_componente=" . intval($tipo));
                } else {
                    error_log("[MODELO] ❌ Componente[$index] IGNORADO (tipo vacío o inválido)");
                }
            }
        }

        error_log("[MODELO] Total componentes válidos procesados: " . count($datos_componentes));

        if (count($datos_sensores) === 0)
            throw new Exception("Debe agregar al menos 1 sensor válido");
        if (count($datos_componentes) < 3)
            throw new Exception("Debe agregar al menos 3 componentes válidos");

        // Iniciar transacción
        pg_query($this->conn, "BEGIN");

        try {
            // Crear estación
            $query_estacion = "INSERT INTO estaciones (
                                codigo, nombre, provincia, canton, parroquia, comunidad,
                                latitud, longitud, altura_terreno, tag_codigo_iner,
                                fecha_instalacion, estado_activo, ruta_fotografia, ruta_mapa
                              ) VALUES (
                                $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14
                              ) RETURNING id_estacion";

            $params_estacion = [
                $datos_estacion['codigo'],
                $datos_estacion['nombre'],
                $datos_estacion['provincia'],
                $datos_estacion['canton'],
                $datos_estacion['parroquia'],
                $datos_estacion['comunidad'],
                $datos_estacion['latitud'],
                $datos_estacion['longitud'],
                $datos_estacion['altura_terreno'],
                $datos_estacion['tag_codigo_iner'],
                $datos_estacion['fecha_instalacion'],
                true,
                $rutas_imagenes['ruta_fotografia'],
                $rutas_imagenes['ruta_mapa']
            ];

            error_log("[MODELO] Ejecutando query INSERT estación...");
            $result_estacion = pg_query_params($this->conn, $query_estacion, $params_estacion);

            if (!$result_estacion) {
                throw new Exception("Error al crear estación: " . pg_last_error($this->conn));
            }

            $row_estacion = pg_fetch_assoc($result_estacion);
            $id_estacion = intval($row_estacion['id_estacion']);
            error_log("[MODELO] ✅ Estación creada con ID: $id_estacion");

            // Crear sensores
            foreach ($datos_sensores as $index => $sensor) {
                error_log("[MODELO] Insertando sensor #$index con id_catalogo_sensor=" . $sensor['id_catalogo_sensor']);

                $query_sensor = "INSERT INTO sensores (
                                   id_estacion, id_catalogo_sensor, marca, modelo, serie,
                                   fecha_ultimo_mantenimiento, estado_sensor, observaciones
                                 ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8)";

                $params_sensor = [
                    $id_estacion,
                    intval($sensor['id_catalogo_sensor']),
                    $sensor['marca'],
                    $sensor['modelo'],
                    $sensor['serie'],
                    $sensor['fecha_mantenimiento'],
                    $sensor['estado'],
                    $sensor['observaciones']
                ];

                $result_sensor = pg_query_params($this->conn, $query_sensor, $params_sensor);

                if (!$result_sensor) {
                    error_log("[MODELO] ❌ Error al crear sensor #$index: " . pg_last_error($this->conn));
                    throw new Exception("Error al crear sensor: " . pg_last_error($this->conn));
                }

                error_log("[MODELO] ✅ Sensor #$index insertado correctamente");
            }

            // Crear componentes
            foreach ($datos_componentes as $index => $componente) {
                error_log("[MODELO] Insertando componente #$index con id_catalogo_componente=" . $componente['id_catalogo_componente']);

                $query_componente = "INSERT INTO componentes (
                                       id_estacion, id_catalogo_componente, marca, modelo, serie,
                                       especificaciones, fecha_ultimo_mantenimiento, 
                                       estado_componente, observaciones
                                     ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9)";

                $params_componente = [
                    $id_estacion,
                    intval($componente['id_catalogo_componente']),
                    $componente['marca'],
                    $componente['modelo'],
                    $componente['serie'],
                    $componente['especificaciones'],
                    $componente['fecha_mantenimiento'],
                    $componente['estado'],
                    $componente['observaciones']
                ];

                $result_componente = pg_query_params($this->conn, $query_componente, $params_componente);

                if (!$result_componente) {
                    error_log("[MODELO] ❌ Error al crear componente #$index: " . pg_last_error($this->conn));
                    throw new Exception("Error al crear componente: " . pg_last_error($this->conn));
                }

                error_log("[MODELO] ✅ Componente #$index insertado correctamente");
            }

            pg_query($this->conn, "COMMIT");
            error_log("=== FIN crearEstacion EXITOSO ===");

            // ✅ CREAR ESTRUCTURA DE CARPETAS EN uploads/
            try {
                $this->crearEstructuraCarpetasEstacion($datos_estacion['nombre']);
                error_log("✅ Estructura de carpetas creada exitosamente para: " . $datos_estacion['nombre']);
            } catch (Exception $e) {
                // ⚠️ IMPORTANTE: No hacer rollback aquí porque la estación ya se guardó
                // Solo registrar el error para que el administrador lo sepa
                error_log("⚠️ ADVERTENCIA: Estación creada pero falló la creación de carpetas: " . $e->getMessage());
                // Podríamos retornar un mensaje de advertencia al frontend
            }

            return $id_estacion;

        } catch (Exception $e) {
            pg_query($this->conn, "ROLLBACK");
            error_log("=== FIN crearEstacion CON ERROR: " . $e->getMessage() . " ===");
            throw new Exception("Error al crear estación completa: " . $e->getMessage());
        }
    }
    /**
     * Elimina una carpeta y todo su contenido de forma recursiva
     */
    private function eliminarCarpetaRecursivamente($ruta)
    {
        error_log("=== INICIO eliminarCarpetaRecursivamente ===");
        error_log("Ruta a eliminar: " . $ruta);

        // Verificar que la ruta existe
        if (!file_exists($ruta)) {
            error_log("⚠️ La ruta no existe, no hay nada que eliminar");
            return true;
        }

        // Verificar que es un directorio
        if (!is_dir($ruta)) {
            error_log("⚠️ La ruta no es un directorio");
            return false;
        }

        try {
            // Obtener todos los archivos y carpetas dentro
            $archivos = scandir($ruta);

            if ($archivos === false) {
                throw new Exception("No se pudo leer el contenido del directorio: " . $ruta);
            }

            foreach ($archivos as $archivo) {
                // Saltar . y ..
                if ($archivo === '.' || $archivo === '..') {
                    continue;
                }

                $ruta_completa = $ruta . '/' . $archivo;

                // Si es un directorio, llamar recursivamente
                if (is_dir($ruta_completa)) {
                    error_log("📁 Eliminando subdirectorio: " . $archivo);
                    if (!$this->eliminarCarpetaRecursivamente($ruta_completa)) {
                        throw new Exception("No se pudo eliminar el subdirectorio: " . $ruta_completa);
                    }
                } else {
                    // Si es un archivo, eliminarlo
                    error_log("📄 Eliminando archivo: " . $archivo);
                    if (!@unlink($ruta_completa)) {
                        error_log("⚠️ No se pudo eliminar el archivo: " . $ruta_completa);
                    }
                }
            }

            // Finalmente, eliminar el directorio vacío
            if (!@rmdir($ruta)) {
                throw new Exception("No se pudo eliminar el directorio: " . $ruta);
            }

            error_log("✅ Carpeta eliminada exitosamente: " . $ruta);
            error_log("=== FIN eliminarCarpetaRecursivamente EXITOSO ===");
            return true;

        } catch (Exception $e) {
            error_log("❌ Error al eliminar carpeta: " . $e->getMessage());
            // No lanzar excepción para no afectar la eliminación de la BD
            error_log("⚠️ ADVERTENCIA: No se pudo eliminar la carpeta pero la BD se eliminó");
            return false;
        }
    }
    /**
     * Elimina la carpeta completa de una estación en uploads/
     */
    private function eliminarCarpetaEstacion($nombreEstacion)
    {
        error_log("=== INICIO eliminarCarpetaEstacion ===");
        error_log("Nombre estación: " . $nombreEstacion);

        // Normalizar el nombre para obtener el nombre de la carpeta
        $nombreCarpeta = $this->normalizarNombreParaCarpeta($nombreEstacion);

        error_log("Nombre carpeta normalizado: " . $nombreCarpeta);

        // Ruta completa de la carpeta
        $rutaCarpeta = dirname(__DIR__) . '/uploads/' . $nombreCarpeta;

        error_log("Ruta completa a eliminar: " . $rutaCarpeta);

        // Verificar si la carpeta existe
        if (!file_exists($rutaCarpeta)) {
            error_log("⚠️ La carpeta no existe, no hay nada que eliminar");
            return true;
        }

        // Eliminar la carpeta y todo su contenido
        $resultado = $this->eliminarCarpetaRecursivamente($rutaCarpeta);

        if ($resultado) {
            error_log("✅ Carpeta de estación eliminada exitosamente");
        } else {
            error_log("⚠️ No se pudo eliminar completamente la carpeta de estación");
        }

        error_log("=== FIN eliminarCarpetaEstacion ===");
        return $resultado;
    }

    // ============================================================
    // MÉTODOS PARA GESTIÓN DE CATÁLOGOS DE SENSORES Y COMPONENTES
    // ============================================================

    /**
     * Obtiene todos los tipos de sensores del catálogo
     */
    public function obtenerCatalogoSensores()
    {
        $query = "SELECT id_catalogo_sensor, nombre, descripcion 
                  FROM catalogo_sensores 
                  ORDER BY nombre ASC";

        $result = pg_query($this->conn, $query);

        if (!$result) {
            error_log("[MODELO] ❌ Error al obtener catálogo de sensores: " . pg_last_error($this->conn));
            throw new Exception("Error al obtener catálogo de sensores: " . pg_last_error($this->conn));
        }

        $catalogo = [];
        while ($row = pg_fetch_assoc($result)) {
            $catalogo[] = $row;
        }

        error_log("[MODELO] ✅ Catálogo de sensores obtenido: " . count($catalogo) . " tipos");
        return $catalogo;
    }

    /**
     * Obtiene todos los tipos de componentes del catálogo
     */
    public function obtenerCatalogoComponentes()
    {
        $query = "SELECT id_catalogo_componente, nombre, descripcion 
                  FROM catalogo_componentes 
                  ORDER BY nombre ASC";

        $result = pg_query($this->conn, $query);

        if (!$result) {
            error_log("[MODELO] ❌ Error al obtener catálogo de componentes: " . pg_last_error($this->conn));
            throw new Exception("Error al obtener catálogo de componentes: " . pg_last_error($this->conn));
        }

        $catalogo = [];
        while ($row = pg_fetch_assoc($result)) {
            $catalogo[] = $row;
        }

        error_log("[MODELO] ✅ Catálogo de componentes obtenido: " . count($catalogo) . " tipos");
        return $catalogo;
    }

    /**
     * Agrega un nuevo tipo de sensor al catálogo
     */
    public function agregarTipoSensor($nombre, $descripcion = null)
    {
        $query = "INSERT INTO catalogo_sensores (nombre, descripcion) 
                  VALUES ($1, $2) 
                  RETURNING id_catalogo_sensor";

        $result = pg_query_params($this->conn, $query, [trim($nombre), $descripcion]);

        if (!$result) {
            throw new Exception("Error al agregar tipo de sensor: " . pg_last_error($this->conn));
        }

        $row = pg_fetch_assoc($result);
        return intval($row['id_catalogo_sensor']);
    }

    /**
     * Agrega un nuevo tipo de componente al catálogo
     */
    public function agregarTipoComponente($nombre, $descripcion = null)
    {
        $query = "INSERT INTO catalogo_componentes (nombre, descripcion) 
                  VALUES ($1, $2) 
                  RETURNING id_catalogo_componente";

        $result = pg_query_params($this->conn, $query, [trim($nombre), $descripcion]);

        if (!$result) {
            throw new Exception("Error al agregar tipo de componente: " . pg_last_error($this->conn));
        }

        $row = pg_fetch_assoc($result);
        return intval($row['id_catalogo_componente']);
    }

    /**
     * Actualiza un tipo de sensor en el catálogo
     */
    public function actualizarTipoSensor($id_catalogo_sensor, $nombre, $descripcion = null)
    {
        $query = "UPDATE catalogo_sensores 
                  SET nombre = $1, descripcion = $2 
                  WHERE id_catalogo_sensor = $3";

        $result = pg_query_params($this->conn, $query, [
            trim($nombre),
            $descripcion,
            $id_catalogo_sensor
        ]);

        if (!$result) {
            throw new Exception("Error al actualizar tipo de sensor: " . pg_last_error($this->conn));
        }

        return pg_affected_rows($result) > 0;
    }

    /**
     * Actualiza un tipo de componente en el catálogo
     */
    public function actualizarTipoComponente($id_catalogo_componente, $nombre, $descripcion = null)
    {
        $query = "UPDATE catalogo_componentes 
                  SET nombre = $1, descripcion = $2 
                  WHERE id_catalogo_componente = $3";

        $result = pg_query_params($this->conn, $query, [
            trim($nombre),
            $descripcion,
            $id_catalogo_componente
        ]);

        if (!$result) {
            throw new Exception("Error al actualizar tipo de componente: " . pg_last_error($this->conn));
        }

        return pg_affected_rows($result) > 0;
    }

    /**
     * Elimina un tipo de sensor del catálogo
     */
    public function eliminarTipoSensor($id_catalogo_sensor)
    {
        // Verificar si está en uso
        $query_check = "SELECT COUNT(*) as total FROM sensores WHERE id_catalogo_sensor = $1";
        $result_check = pg_query_params($this->conn, $query_check, [$id_catalogo_sensor]);

        if ($result_check) {
            $row = pg_fetch_assoc($result_check);
            if ($row['total'] > 0) {
                throw new Exception("No se puede eliminar: Este tipo de sensor está siendo usado por " . $row['total'] . " sensor(es)");
            }
        }

        $query = "DELETE FROM catalogo_sensores WHERE id_catalogo_sensor = $1";
        $result = pg_query_params($this->conn, $query, [$id_catalogo_sensor]);

        if (!$result) {
            throw new Exception("Error al eliminar tipo de sensor: " . pg_last_error($this->conn));
        }

        return pg_affected_rows($result) > 0;
    }
    public function obtenerCatalogoVariables()
    {
        $query = "SELECT id_metadato, codigo_columna, nombre_corto, descripcion, unidad 
              FROM metadatos_variables 
              ORDER BY codigo_columna ASC";

        $result = pg_query($this->conn, $query);

        if (!$result) {
            throw new Exception("Error al obtener catálogo de variables: " . pg_last_error($this->conn));
        }

        $catalogo = [];
        while ($row = pg_fetch_assoc($result)) {
            $catalogo[] = $row;
        }

        return $catalogo;
    }

    public function agregarVariable($codigo, $nombre, $descripcion, $unidad)
    {
        $query = "INSERT INTO metadatos_variables (codigo_columna, nombre_corto, descripcion, unidad) 
              VALUES ($1, $2, $3, $4) 
              RETURNING id_metadato";

        $result = pg_query_params($this->conn, $query, [
            trim($codigo),
            trim($nombre),
            trim($descripcion),
            $unidad
        ]);

        if (!$result) {
            throw new Exception("Error al agregar variable: " . pg_last_error($this->conn));
        }

        $row = pg_fetch_assoc($result);
        return intval($row['id_metadato']);
    }

    public function actualizarVariable($id, $codigo, $nombre, $descripcion, $unidad)
    {
        $query = "UPDATE metadatos_variables 
              SET codigo_columna = $1, nombre_corto = $2, descripcion = $3, unidad = $4 
              WHERE id_metadato = $5";

        $result = pg_query_params($this->conn, $query, [
            trim($codigo),
            trim($nombre),
            trim($descripcion),
            $unidad,
            $id
        ]);

        if (!$result) {
            throw new Exception("Error al actualizar variable: " . pg_last_error($this->conn));
        }

        return pg_affected_rows($result) > 0;
    }

    public function eliminarVariable($id)
    {
        $query_check = "SELECT COUNT(*) as total FROM datos_l2_tiempo_real WHERE id_metadato = $1";
        $result_check = pg_query_params($this->conn, $query_check, [$id]);

        if ($result_check) {
            $row = pg_fetch_assoc($result_check);
            if ($row['total'] > 0) {
                throw new Exception("No se puede eliminar: Esta variable está siendo usada por " . $row['total'] . " registro(s) de datos");
            }
        }

        $query = "DELETE FROM metadatos_variables WHERE id_metadato = $1";
        $result = pg_query_params($this->conn, $query, [$id]);

        if (!$result) {
            throw new Exception("Error al eliminar variable: " . pg_last_error($this->conn));
        }

        return pg_affected_rows($result) > 0;
    }

    /**
     * Elimina un tipo de componente del catálogo
     */
    public function eliminarTipoComponente($id_catalogo_componente)
    {
        // Verificar si está en uso
        $query_check = "SELECT COUNT(*) as total FROM componentes WHERE id_catalogo_componente = $1";
        $result_check = pg_query_params($this->conn, $query_check, [$id_catalogo_componente]);

        if ($result_check) {
            $row = pg_fetch_assoc($result_check);
            if ($row['total'] > 0) {
                throw new Exception("No se puede eliminar: Este tipo de componente está siendo usado por " . $row['total'] . " componente(s)");
            }
        }

        $query = "DELETE FROM catalogo_componentes WHERE id_catalogo_componente = $1";
        $result = pg_query_params($this->conn, $query, [$id_catalogo_componente]);

        if (!$result) {
            throw new Exception("Error al eliminar tipo de componente: " . pg_last_error($this->conn));
        }

        return pg_affected_rows($result) > 0;
    }
}
?>