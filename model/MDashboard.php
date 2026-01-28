<?php
// model/MDashboard.php
require_once '../config/conexion.php';

class MDashboard
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
    }

    /**
     * Obtiene todas las estaciones disponibles
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
            END as status,
            e.ruta_fotografia,
            e.ruta_mapa
          FROM estaciones e 
          WHERE e.estado_activo = true
          ORDER BY e.nombre";

        $result = pg_query($this->conn, $query);

        if (!$result) {
            error_log("Error SQL al obtener estaciones: " . pg_last_error($this->conn));
            throw new Exception("Error al obtener estaciones: " . pg_last_error($this->conn));
        }

        $estaciones = array();
        while ($row = pg_fetch_assoc($result)) {
            // ✅ SOLUCIÓN: DEVOLVER LA RUTA TAL COMO ESTÁ EN LA BD
            // El JavaScript se encargará de construir la ruta correcta
            $ruta_fotografia = $row['ruta_fotografia'];
            $ruta_mapa = $row['ruta_mapa'];

            // Solo validar que no estén vacías
            if (empty($ruta_fotografia)) {
                $ruta_fotografia = 'public/img/default.png';
            }

            if (empty($ruta_mapa)) {
                $ruta_mapa = 'public/img/default.png';
            }

            $row['ruta_fotografia'] = $ruta_fotografia;
            $row['ruta_mapa'] = $ruta_mapa;

            $estaciones[] = $row;
        }

        error_log("Se encontraron " . count($estaciones) . " estaciones activas");
        return $estaciones;
    }

    /**
     * Obtiene los años disponibles para una estación específica
     */
    public function obtenerAniosDisponibles($id_estacion, $tipo_nivel = 'L0')
    {
        $query = "SELECT DISTINCT 
                    EXTRACT(YEAR FROM r.fecha_hora) as anio
                  FROM registros r
                  WHERE r.id_estacion = $1 
                    AND r.tipo_nivel = $2
                  ORDER BY anio DESC";

        $result = pg_query_params($this->conn, $query, [$id_estacion, $tipo_nivel]);

        if (!$result) {
            error_log("Error SQL al obtener años: " . pg_last_error($this->conn));
            throw new Exception("Error al obtener años: " . pg_last_error($this->conn));
        }

        $anios = array();
        while ($row = pg_fetch_assoc($result)) {
            $anios[] = intval($row['anio']);
        }

        error_log("Se encontraron " . count($anios) . " años para estación $id_estacion nivel $tipo_nivel");
        return $anios;
    }

    /**
     * Obtiene los meses disponibles para una estación, año y nivel específico
     */
    public function obtenerMesesDisponibles($id_estacion, $anio, $tipo_nivel = 'L0')
    {
        $query = "SELECT DISTINCT 
                    EXTRACT(MONTH FROM r.fecha_hora) as mes
                  FROM registros r
                  WHERE r.id_estacion = $1 
                    AND EXTRACT(YEAR FROM r.fecha_hora) = $2
                    AND r.tipo_nivel = $3
                  ORDER BY mes ASC";

        $result = pg_query_params($this->conn, $query, [$id_estacion, $anio, $tipo_nivel]);

        if (!$result) {
            error_log("Error SQL al obtener meses: " . pg_last_error($this->conn));
            throw new Exception("Error al obtener meses: " . pg_last_error($this->conn));
        }

        $meses = array();
        while ($row = pg_fetch_assoc($result)) {
            $meses[] = intval($row['mes']);
        }

        error_log("Se encontraron " . count($meses) . " meses para estación $id_estacion año $anio nivel $tipo_nivel");
        return $meses;
    }

    /**
     * Obtiene información completa de una estación específica
     */
    public function obtenerInformacionEstacion($id_estacion)
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
            e.ruta_fotografia,
            e.ruta_mapa,
            COUNT(DISTINCT r.id_registro) as total_registros,
            COUNT(DISTINCT CASE WHEN r.tipo_nivel = 'L0' THEN r.id_registro END) as registros_l0,
            COUNT(DISTINCT CASE WHEN r.tipo_nivel = 'L1' THEN r.id_registro END) as registros_l1,
            COUNT(DISTINCT CASE WHEN r.tipo_nivel = 'L2' THEN r.id_registro END) as registros_l2,
            MAX(r.fecha_hora) as ultima_lectura
          FROM estaciones e 
          LEFT JOIN registros r ON e.id_estacion = r.id_estacion
          WHERE e.id_estacion = $1
          GROUP BY e.id_estacion";

        $result = pg_query_params($this->conn, $query, [$id_estacion]);

        if (!$result) {
            throw new Exception("Error al obtener información de estación: " . pg_last_error($this->conn));
        }

        $estacion = pg_fetch_assoc($result);

        if (!$estacion) {
            throw new Exception("Estación no encontrada");
        }

        // ✅ SOLUCIÓN: DEVOLVER LA RUTA TAL COMO ESTÁ EN LA BD
        if (empty($estacion['ruta_fotografia'])) {
            $estacion['ruta_fotografia'] = 'public/img/default.png';
        }

        if (empty($estacion['ruta_mapa'])) {
            $estacion['ruta_mapa'] = 'public/img/default.png';
        }

        // Estructurar estadísticas
        $estacion['estadisticas'] = [
            'total_registros' => intval($estacion['total_registros']),
            'registros_l0' => intval($estacion['registros_l0']),
            'registros_l1' => intval($estacion['registros_l1']),
            'registros_l2' => intval($estacion['registros_l2']),
            'ultima_lectura' => $estacion['ultima_lectura']
        ];

        unset(
            $estacion['total_registros'],
            $estacion['registros_l0'],
            $estacion['registros_l1'],
            $estacion['registros_l2']
        );

        return $estacion;
    }

    // ============================================
// MÉTODO OPTIMIZADO 2: Años + Meses en UNA sola consulta
// ============================================
    public function obtenerAniosYMesesDisponibles($id_estacion, $tipo_nivel = 'L0')
    {
        $query = "SELECT 
                EXTRACT(YEAR FROM r.fecha_hora)::INTEGER as anio,
                EXTRACT(MONTH FROM r.fecha_hora)::INTEGER as mes
              FROM registros r
              WHERE r.id_estacion = $1 
                AND r.tipo_nivel = $2
              GROUP BY anio, mes
              ORDER BY anio DESC, mes ASC";

        $result = pg_query_params($this->conn, $query, [$id_estacion, $tipo_nivel]);

        if (!$result) {
            throw new Exception("Error al obtener años y meses: " . pg_last_error($this->conn));
        }

        $data = ['anios' => [], 'meses_por_anio' => []];
        $anios_set = [];

        while ($row = pg_fetch_assoc($result)) {
            $anio = intval($row['anio']);
            $mes = intval($row['mes']);

            if (!in_array($anio, $anios_set)) {
                $anios_set[] = $anio;
                $data['anios'][] = $anio;
                $data['meses_por_anio'][$anio] = [];
            }

            $data['meses_por_anio'][$anio][] = $mes;
        }

        return $data;
    }

    /**
     * NUEVA: Obtiene datos L0 formateados para gráfica Chart.js
     */
    public function obtenerDatosL0ParaGrafica($id_estacion, $anio = null, $mes = null)
    {
        $where_conditions = ["r.id_estacion = $1", "r.tipo_nivel = 'L0'"];
        $params = [$id_estacion];
        $param_count = 1;

        if ($anio !== null) {
            $param_count++;
            $where_conditions[] = "EXTRACT(YEAR FROM r.fecha_hora) = $" . $param_count;
            $params[] = $anio;
        }

        if ($mes !== null) {
            $param_count++;
            $where_conditions[] = "EXTRACT(MONTH FROM r.fecha_hora) = $" . $param_count;
            $params[] = $mes;
        }

        $where_clause = implode(" AND ", $where_conditions);

        // ✅ OPTIMIZACIÓN: Agregamos LIMIT y ORDER BY optimizado
        $query = "SELECT 
            TO_CHAR(r.fecha_hora, 'DD/MM HH24:MI') as etiqueta,
            dl0.velocidad_viento,
            dl0.direccion_viento
          FROM registros r
          INNER JOIN datos_l0 dl0 ON r.id_registro = dl0.id_registro
          WHERE $where_clause
            AND dl0.velocidad_viento IS NOT NULL
            AND dl0.direccion_viento IS NOT NULL
          ORDER BY r.fecha_hora ASC
          LIMIT 2000";

        error_log("🔍 SQL Query OPTIMIZADO: " . $query);
        error_log("🔍 Parámetros: " . json_encode($params));

        $result = pg_query_params($this->conn, $query, $params);

        if (!$result) {
            error_log("❌ Error SQL: " . pg_last_error($this->conn));
            throw new Exception("Error al obtener datos L0: " . pg_last_error($this->conn));
        }

        $labels = array();
        $velocidades = array();
        $direcciones = array();

        while ($row = pg_fetch_assoc($result)) {
            $labels[] = $row['etiqueta'];
            $velocidades[] = floatval($row['velocidad_viento']);
            $direcciones[] = floatval($row['direccion_viento']);
        }

        $total = count($labels);
        error_log("✅ Datos L0 obtenidos: {$total} registros");

        return [
            'labels' => $labels,
            'velocidades' => $velocidades,
            'direcciones' => $direcciones,
            'total_registros' => $total
        ];
    }

    /**
     * NUEVA: Obtiene direcciones del viento agrupadas por hora
     */
    public function obtenerDireccionesVientoPorHora($id_estacion, $anio = null, $mes = null)
    {
        $where_conditions = ["r.id_estacion = $1", "r.tipo_nivel = 'L0'"];
        $params = [$id_estacion];
        $param_count = 1;

        if ($anio !== null) {
            $param_count++;
            $where_conditions[] = "EXTRACT(YEAR FROM r.fecha_hora) = $" . $param_count;
            $params[] = $anio;
        }

        if ($mes !== null) {
            $param_count++;
            $where_conditions[] = "EXTRACT(MONTH FROM r.fecha_hora) = $" . $param_count;
            $params[] = $mes;
        }

        $where_clause = implode(" AND ", $where_conditions);

        // Agrupar por hora y calcular promedios
        $query = "SELECT 
                EXTRACT(HOUR FROM r.fecha_hora) as hora,
                AVG(dl0.direccion_viento) as direccion_promedio,
                AVG(dl0.velocidad_viento) as velocidad_promedio,
                COUNT(*) as cantidad
              FROM registros r
              INNER JOIN datos_l0 dl0 ON r.id_registro = dl0.id_registro
              WHERE $where_clause
              GROUP BY EXTRACT(HOUR FROM r.fecha_hora)
              ORDER BY hora ASC";

        error_log("🔍 SQL Query Direcciones: " . $query);
        error_log("🔍 Parámetros: " . json_encode($params));

        $result = pg_query_params($this->conn, $query, $params);

        if (!$result) {
            error_log("❌ Error SQL: " . pg_last_error($this->conn));
            throw new Exception("Error al obtener direcciones por hora: " . pg_last_error($this->conn));
        }

        $labels = array();
        $direcciones = array();
        $velocidades = array();

        while ($row = pg_fetch_assoc($result)) {
            $hora = str_pad($row['hora'], 2, '0', STR_PAD_LEFT);
            $labels[] = $hora . ':00';
            $direcciones[] = round(floatval($row['direccion_promedio']), 1);
            $velocidades[] = round(floatval($row['velocidad_promedio']), 3);
        }

        $total = count($labels);
        error_log("✅ Direcciones por hora obtenidas: {$total} registros");

        return [
            'labels' => $labels,
            'direcciones' => $direcciones,
            'velocidades' => $velocidades,
            'total_horas' => $total
        ];
    }

    /**
     * Obtiene datos L0 agrupados por HORA (profesional para gráficas)
     */
    public function obtenerDatosL0PorHora($id_estacion, $anio = null, $mes = null)
    {
        $where_conditions = ["id_estacion = $1"];
        $params = [$id_estacion];
        $param_count = 1;

        if ($anio !== null) {
            $param_count++;
            $where_conditions[] = "anio = $" . $param_count;
            $params[] = $anio;
        }

        if ($mes !== null) {
            $param_count++;
            $where_conditions[] = "mes = $" . $param_count;
            $params[] = $mes;
        }

        $where_clause = implode(" AND ", $where_conditions);

        // ✅ USA VISTA MATERIALIZADA
        $query = "SELECT 
            etiqueta,
            velocidad_promedio,
            direccion_promedio,
            velocidad_maxima,
            velocidad_minima
          FROM mv_l0_por_hora
          WHERE $where_clause
          ORDER BY fecha_hora_agrupada ASC";

        $result = pg_query_params($this->conn, $query, $params);

        if (!$result) {
            error_log("❌ Error SQL: " . pg_last_error($this->conn));
            throw new Exception("Error al obtener datos L0 agrupados: " . pg_last_error($this->conn));
        }

        $labels = array();
        $velocidades = array();
        $direcciones = array();
        $maximas = array();
        $minimas = array();

        while ($row = pg_fetch_assoc($result)) {
            $labels[] = $row['etiqueta'];
            $velocidades[] = round(floatval($row['velocidad_promedio']), 2);
            $direcciones[] = round(floatval($row['direccion_promedio']), 1);
            $maximas[] = round(floatval($row['velocidad_maxima']), 2);
            $minimas[] = round(floatval($row['velocidad_minima']), 2);
        }

        return [
            'labels' => $labels,
            'velocidades' => $velocidades,
            'direcciones' => $direcciones,
            'maximas' => $maximas,
            'minimas' => $minimas,
            'total_horas' => count($labels)
        ];
    }

    /**
     * Obtiene distribución de direcciones por sectores (para Rosa de Vientos)
     */
    public function obtenerDistribucionDirecciones($id_estacion, $anio = null, $mes = null)
    {
        $where_conditions = ["id_estacion = $1"];
        $params = [$id_estacion];
        $param_count = 1;

        if ($anio !== null) {
            $param_count++;
            $where_conditions[] = "anio = $" . $param_count;
            $params[] = $anio;
        }

        if ($mes !== null) {
            $param_count++;
            $where_conditions[] = "mes = $" . $param_count;
            $params[] = $mes;
        }

        $where_clause = implode(" AND ", $where_conditions);

        // ✅ USA VISTA MATERIALIZADA
        $query = "SELECT 
            sector,
            velocidad_promedio,
            frecuencia
          FROM mv_l0_sectores
          WHERE $where_clause
            AND sector IS NOT NULL
          ORDER BY 
            CASE sector
                WHEN 'N' THEN 1 WHEN 'NE' THEN 2 WHEN 'E' THEN 3 WHEN 'SE' THEN 4
                WHEN 'S' THEN 5 WHEN 'SW' THEN 6 WHEN 'W' THEN 7 WHEN 'NW' THEN 8
            END";

        $result = pg_query_params($this->conn, $query, $params);

        if (!$result) {
            error_log("❌ Error SQL: " . pg_last_error($this->conn));
            throw new Exception("Error al obtener distribución de direcciones: " . pg_last_error($this->conn));
        }

        // Inicializar todos los sectores en 0
        $sectores = ['N' => 0, 'NE' => 0, 'E' => 0, 'SE' => 0, 'S' => 0, 'SW' => 0, 'W' => 0, 'NW' => 0];
        $velocidades = ['N' => 0, 'NE' => 0, 'E' => 0, 'SE' => 0, 'S' => 0, 'SW' => 0, 'W' => 0, 'NW' => 0];

        while ($row = pg_fetch_assoc($result)) {
            $sector = $row['sector'];
            if ($sector && isset($sectores[$sector])) {
                $sectores[$sector] = intval($row['frecuencia']);
                $velocidades[$sector] = round(floatval($row['velocidad_promedio']), 2);
            }
        }

        return [
            'sectores' => array_keys($sectores),
            'frecuencias' => array_values($sectores),
            'velocidades' => array_values($velocidades)
        ];
    }
    /**
     * Obtiene los datos específicos para el nivel L0 (viento) - ORIGINAL
     */
    public function obtenerDatosL0($id_estacion, $anio = null, $mes = null)
    {
        $where_conditions = ["r.id_estacion = $1", "r.tipo_nivel = 'L0'"];
        $params = [$id_estacion];
        $param_count = 1;

        if ($anio) {
            $param_count++;
            $where_conditions[] = "EXTRACT(YEAR FROM r.fecha_hora) = $" . $param_count;
            $params[] = $anio;
        }

        if ($mes) {
            $param_count++;
            $where_conditions[] = "EXTRACT(MONTH FROM r.fecha_hora) = $" . $param_count;
            $params[] = $mes;
        }

        $where_clause = implode(" AND ", $where_conditions);

        $query = "SELECT 
                    r.fecha_hora,
                    dl0.direccion_viento,
                    dl0.velocidad_viento,
                    EXTRACT(HOUR FROM r.fecha_hora) as hora,
                    EXTRACT(DAY FROM r.fecha_hora) as dia
                  FROM registros r
                  INNER JOIN datos_l0 dl0 ON r.id_registro = dl0.id_registro
                  WHERE $where_clause
                  ORDER BY r.fecha_hora ASC";

        $result = pg_query_params($this->conn, $query, $params);

        if (!$result) {
            throw new Exception("Error al obtener datos L0: " . pg_last_error($this->conn));
        }

        $datos = array();
        while ($row = pg_fetch_assoc($result)) {
            $datos[] = $row;
        }

        return $datos;
    }

    /**
     * Obtiene los datos específicos para el nivel L1 (climático)
     */
    public function obtenerDatosL1($id_estacion, $anio = null, $mes = null)
    {
        $where_conditions = ["r.id_estacion = $1", "r.tipo_nivel = 'L1'"];
        $params = [$id_estacion];
        $param_count = 1;

        if ($anio) {
            $param_count++;
            $where_conditions[] = "EXTRACT(YEAR FROM r.fecha_hora) = $" . $param_count;
            $params[] = $anio;
        }

        if ($mes) {
            $param_count++;
            $where_conditions[] = "EXTRACT(MONTH FROM r.fecha_hora) = $" . $param_count;
            $params[] = $mes;
        }

        $where_clause = implode(" AND ", $where_conditions);

        $query = "SELECT 
                    r.fecha_hora,
                    dl1.temperatura_aire,
                    dl1.humedad_relativa,
                    dl1.presion_barometrica,
                    dl1.radiacion_solar,
                    dl1.velocidad_viento,
                    EXTRACT(HOUR FROM r.fecha_hora) as hora,
                    EXTRACT(DAY FROM r.fecha_hora) as dia
                  FROM registros r
                  INNER JOIN datos_l1 dl1 ON r.id_registro = dl1.id_registro
                  WHERE $where_clause
                  ORDER BY r.fecha_hora ASC";

        $result = pg_query_params($this->conn, $query, $params);

        if (!$result) {
            throw new Exception("Error al obtener datos L1: " . pg_last_error($this->conn));
        }

        $datos = array();
        while ($row = pg_fetch_assoc($result)) {
            $datos[] = $row;
        }

        return $datos;
    }

    /**
     * Obtiene los datos específicos para el nivel L2 (estadístico)
     */
    public function obtenerDatosL2($id_estacion, $anio = null, $mes = null)
    {
        $where_conditions = ["r.id_estacion = $1", "r.tipo_nivel = 'L2'"];
        $params = [$id_estacion];
        $param_count = 1;

        if ($anio) {
            $param_count++;
            $where_conditions[] = "EXTRACT(YEAR FROM r.fecha_hora) = $" . $param_count;
            $params[] = $anio;
        }

        if ($mes) {
            $param_count++;
            $where_conditions[] = "EXTRACT(MONTH FROM r.fecha_hora) = $" . $param_count;
            $params[] = $mes;
        }

        $where_clause = implode(" AND ", $where_conditions);

        $query = "SELECT 
                r.fecha_hora,
                dl2.temp_promedio_hora,
                dl2.humedad_promedio_hora,
                dl2.presion_promedio_hora,
                dl2.radiacion_total_hora,
                dl2.velocidad_viento_promedio_hora,
                dl2.direccion_viento_promedio_hora,
                EXTRACT(HOUR FROM r.fecha_hora) as hora,
                EXTRACT(DAY FROM r.fecha_hora) as dia
              FROM registros r
              INNER JOIN datos_l2 dl2 ON r.id_registro = dl2.id_registro
              WHERE $where_clause
              ORDER BY r.fecha_hora ASC";

        $result = pg_query_params($this->conn, $query, $params);

        if (!$result) {
            throw new Exception("Error al obtener datos L2: " . pg_last_error($this->conn));
        }

        $datos = array();
        while ($row = pg_fetch_assoc($result)) {
            $datos[] = $row;
        }

        return $datos;
    }

    /**
     * Obtiene estadísticas generales del dashboard
     */
    private $cache_stats = null;
    private $cache_stats_time = null;
    private $cache_duration = 300; // 5 minutos
    public function obtenerEstadisticasGenerales()
    {
        // Verificar caché en memoria
        if (
            $this->cache_stats !== null &&
            $this->cache_stats_time !== null &&
            (time() - $this->cache_stats_time) < $this->cache_duration
        ) {
            error_log("✅ Estadísticas devueltas desde caché");
            return $this->cache_stats;
        }

        $query = "SELECT 
                (SELECT COUNT(*) FROM estaciones WHERE estado_activo = true) as total_estaciones,
                (SELECT COUNT(*) FROM registros) as total_registros,
                (SELECT COUNT(DISTINCT id_estacion) FROM registros) as estaciones_con_datos,
                (SELECT MAX(fecha_hora) FROM registros) as ultima_actualizacion";

        $result = pg_query($this->conn, $query);

        if (!$result) {
            throw new Exception("Error al obtener estadísticas: " . pg_last_error($this->conn));
        }

        $stats = pg_fetch_assoc($result);

        // Guardar en caché
        $this->cache_stats = $stats;
        $this->cache_stats_time = time();

        error_log("✅ Estadísticas calculadas y guardadas en caché");
        return $stats;
    }

    /**
     * Obtiene estadísticas específicas para una estación y nivel
     */
    public function obtenerEstadisticasEstacionNivel($id_estacion, $tipo_nivel = 'L0')
    {
        $query = "SELECT 
                COUNT(*) as total_registros,
                MAX(r.fecha_hora) as ultima_actualizacion
              FROM registros r
              WHERE r.id_estacion = $1 
                AND r.tipo_nivel = $2";

        $result = pg_query_params($this->conn, $query, [$id_estacion, $tipo_nivel]);

        if (!$result) {
            throw new Exception("Error al obtener estadísticas: " . pg_last_error($this->conn));
        }

        $stats = pg_fetch_assoc($result);

        // Agregar estaciones activas desde caché
        $general_stats = $this->obtenerEstadisticasGenerales();

        return [
            'total_registros' => intval($stats['total_registros']),
            'estaciones_activas' => intval($general_stats['estaciones_con_datos']),
            'ultima_actualizacion' => $stats['ultima_actualizacion']
        ];
    }

    /**
     * Obtiene datos para gráficas comparativas entre estaciones
     */
    public function obtenerDatosComparativos($estaciones_ids, $anio, $mes, $tipo_nivel = 'L1')
    {
        $placeholders = implode(',', array_fill(0, count($estaciones_ids), '$'));

        $query = "SELECT 
                    e.nombre as estacion,
                    r.fecha_hora,
                    CASE 
                        WHEN $tipo_nivel = 'L0' THEN dl0.velocidad_viento
                        WHEN $tipo_nivel = 'L1' THEN dl1.temperatura_aire
                        WHEN $tipo_nivel = 'L2' THEN dl2.temp_promedio_hora
                    END as valor
                  FROM registros r
                  INNER JOIN estaciones e ON r.id_estacion = e.id_estacion
                  LEFT JOIN datos_l0 dl0 ON r.id_registro = dl0.id_registro AND '$tipo_nivel' = 'L0'
                  LEFT JOIN datos_l1 dl1 ON r.id_registro = dl1.id_registro AND '$tipo_nivel' = 'L1'
                  LEFT JOIN datos_l2 dl2 ON r.id_registro = dl2.id_registro AND '$tipo_nivel' = 'L2'
                  WHERE r.id_estacion IN ($placeholders)
                    AND EXTRACT(YEAR FROM r.fecha_hora) = $" . (count($estaciones_ids) + 1) . "
                    AND EXTRACT(MONTH FROM r.fecha_hora) = $" . (count($estaciones_ids) + 2) . "
                    AND r.tipo_nivel = '$tipo_nivel'
                  ORDER BY r.fecha_hora ASC";

        $params = array_merge($estaciones_ids, [$anio, $mes]);
        $result = pg_query_params($this->conn, $query, $params);

        if (!$result) {
            throw new Exception("Error al obtener datos comparativos: " . pg_last_error($this->conn));
        }

        $datos = array();
        while ($row = pg_fetch_assoc($result)) {
            $datos[] = $row;
        }

        return $datos;
    }

    /**
     * Verifica si una estación tiene datos para un nivel específico
     */
    public function verificarDatosEstacion($id_estacion, $tipo_nivel = 'L0')
    {
        $query = "SELECT COUNT(*) as total 
                  FROM registros 
                  WHERE id_estacion = $1 AND tipo_nivel = $2";

        $result = pg_query_params($this->conn, $query, [$id_estacion, $tipo_nivel]);

        if (!$result) {
            return false;
        }

        $row = pg_fetch_assoc($result);
        return intval($row['total']) > 0;
    }
    /**
     * Obtiene datos agrupados por MINUTO para gráfica Urbina Tipo 1
     * Barras: Velocidad promedio | Línea: Dirección predominante
     */
    public function obtenerDatosUrbinaHora($id_estacion, $anio = null, $mes = null)
    {
        $where_conditions = ["r.id_estacion = $1", "r.tipo_nivel = 'L0'"];
        $params = [$id_estacion];
        $param_count = 1;

        if ($anio !== null) {
            $param_count++;
            $where_conditions[] = "EXTRACT(YEAR FROM r.fecha_hora) = $" . $param_count;
            $params[] = $anio;
        }

        if ($mes !== null) {
            $param_count++;
            $where_conditions[] = "EXTRACT(MONTH FROM r.fecha_hora) = $" . $param_count;
            $params[] = $mes;
        }

        $where_clause = implode(" AND ", $where_conditions);

        // Agrupar por HORA (máximo 24-744 puntos según si es día/mes/año)
        $query = "SELECT 
                DATE_TRUNC('hour', r.fecha_hora) as fecha_hora_agrupada,
                TO_CHAR(DATE_TRUNC('hour', r.fecha_hora), 'DD/MM HH24:00') as etiqueta,
                AVG(dl0.velocidad_viento) as vel_promedio,
                MAX(dl0.velocidad_viento) as vel_maxima,
                AVG(dl0.direccion_viento) as dir_promedio,
                COUNT(*) as frecuencia
              FROM registros r
              INNER JOIN datos_l0 dl0 ON r.id_registro = dl0.id_registro
              WHERE $where_clause
              GROUP BY DATE_TRUNC('hour', r.fecha_hora)
              ORDER BY fecha_hora_agrupada ASC";

        error_log("🔍 SQL Query Urbina Hora: " . $query);

        $result = pg_query_params($this->conn, $query, $params);

        if (!$result) {
            error_log("❌ Error SQL: " . pg_last_error($this->conn));
            throw new Exception("Error al obtener datos Urbina por hora: " . pg_last_error($this->conn));
        }

        $etiquetas = array();
        $vel_promedio = array();
        $vel_maxima = array();
        $dir_promedio = array();

        while ($row = pg_fetch_assoc($result)) {
            $etiquetas[] = $row['etiqueta'];
            $vel_promedio[] = round(floatval($row['vel_promedio']), 2);
            $vel_maxima[] = round(floatval($row['vel_maxima']), 2);
            $dir_promedio[] = round(floatval($row['dir_promedio']), 1);
        }

        error_log("✅ Datos Urbina Hora: " . count($etiquetas) . " registros");

        return [
            'etiquetas' => $etiquetas,
            'vel_promedio' => $vel_promedio,
            'vel_maxima' => $vel_maxima,
            'dir_promedio' => $dir_promedio,
            'total_horas' => count($etiquetas)
        ];
    }

    /**
     * Obtiene datos para Heatmap: Velocidad del viento por día y hora
     * Retorna matriz de velocidades para visualizar patrones temporales
     */
    public function obtenerDatosHeatmapVelocidad($id_estacion, $anio = null, $mes = null)
    {
        $where_conditions = ["id_estacion = $1"];
        $params = [$id_estacion];
        $param_count = 1;

        if ($anio !== null) {
            $param_count++;
            $where_conditions[] = "anio = $" . $param_count;
            $params[] = $anio;
        }

        $agrupar_por_mes = ($mes === null);

        if (!$agrupar_por_mes) {
            $param_count++;
            $where_conditions[] = "mes = $" . $param_count;
            $params[] = $mes;
        }

        $where_clause = implode(" AND ", $where_conditions);

        if ($agrupar_por_mes) {
            $query = "SELECT 
            mes as periodo,
            hora,
            AVG(velocidad_promedio) as velocidad_promedio,
            MAX(velocidad_maxima) as velocidad_maxima,
            MIN(velocidad_minima) as velocidad_minima,
            SUM(num_lecturas) as num_lecturas
          FROM mv_l0_heatmap_viento
          WHERE $where_clause
          GROUP BY mes, hora
          ORDER BY mes, hora";

            $max_periodo = 12;
            $etiquetas_periodo = [
                'Ene',
                'Feb',
                'Mar',
                'Abr',
                'May',
                'Jun',
                'Jul',
                'Ago',
                'Sep',
                'Oct',
                'Nov',
                'Dic'
            ];
        } else {
            $query = "SELECT 
            dia as periodo,
            hora,
            velocidad_promedio,
            velocidad_maxima,
            velocidad_minima,
            num_lecturas
          FROM mv_l0_heatmap_viento
          WHERE $where_clause
          ORDER BY dia, hora";

            $max_periodo = 31;
            $etiquetas_periodo = array();
            for ($i = 1; $i <= 31; $i++) {
                $etiquetas_periodo[] = 'Día ' . $i;
            }
        }

        error_log("🔍 SQL Heatmap (vista materializada): " . $query);

        $result = pg_query_params($this->conn, $query, $params);

        if (!$result) {
            error_log("❌ Error SQL: " . pg_last_error($this->conn));
            throw new Exception("Error al obtener heatmap: " . pg_last_error($this->conn));
        }

        $matriz_velocidades = array_fill(0, $max_periodo, array_fill(0, 24, null));
        $datos_raw = array();

        while ($row = pg_fetch_assoc($result)) {
            $periodo = intval($row['periodo']) - 1;
            $hora = intval($row['hora']);
            $velocidad = round(floatval($row['velocidad_promedio']), 2);

            if ($periodo >= 0 && $periodo < $max_periodo && $hora >= 0 && $hora < 24) {
                $matriz_velocidades[$periodo][$hora] = $velocidad;

                $datos_raw[] = [
                    'periodo' => $periodo + 1,
                    'hora' => $hora,
                    'velocidad' => $velocidad,
                    'maxima' => round(floatval($row['velocidad_maxima']), 2),
                    'minima' => round(floatval($row['velocidad_minima']), 2),
                    'lecturas' => intval($row['num_lecturas'])
                ];
            }
        }

        $etiquetas_horas = array();
        for ($h = 0; $h < 24; $h++) {
            $etiquetas_horas[] = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
        }

        error_log("✅ Heatmap desde vista materializada: " . count($datos_raw) . " puntos");

        return [
            'matriz_velocidades' => $matriz_velocidades,
            'etiquetas_periodo' => $etiquetas_periodo,
            'etiquetas_horas' => $etiquetas_horas,
            'datos_raw' => $datos_raw,
            'total_puntos' => count($datos_raw),
            'modo' => $agrupar_por_mes ? 'anual' : 'mensual'
        ];
    }
    /**
     * Obtiene datos para Detección de Rachas de Viento
     * Identifica eventos extremos usando percentil 75 como umbral
     */
    public function obtenerDatosRachasViento($id_estacion, $anio = null, $mes = null)
    {
        $where_conditions = ["id_estacion = $1"];
        $params = [$id_estacion];
        $param_count = 1;

        if ($anio !== null) {
            $param_count++;
            $where_conditions[] = "anio = $" . $param_count;
            $params[] = $anio;
        }

        if ($mes !== null) {
            $param_count++;
            $where_conditions[] = "mes = $" . $param_count;
            $params[] = $mes;
        }

        $where_clause = implode(" AND ", $where_conditions);

        $query = "SELECT 
        etiqueta,
        velocidad_promedio,
        es_racha,
        umbral_p75,
        vel_maxima,
        vel_promedio_total,
        total_registros
      FROM mv_l0_rachas_viento
      WHERE $where_clause
      ORDER BY fecha_hora_agrupada ASC";

        $result = pg_query_params($this->conn, $query, $params);

        if (!$result) {
            error_log("❌ Error SQL: " . pg_last_error($this->conn));
            throw new Exception("Error al obtener rachas de viento: " . pg_last_error($this->conn));
        }

        $labels = array();
        $velocidades = array();
        $labels_rachas = array();
        $velocidades_rachas = array();

        $umbral_p75 = 0;
        $vel_maxima = 0;
        $vel_promedio = 0;
        $total_registros = 0;
        $total_rachas = 0;

        while ($row = pg_fetch_assoc($result)) {
            $etiqueta = $row['etiqueta'];
            $velocidad = round(floatval($row['velocidad_promedio']), 2);
            $es_racha = ($row['es_racha'] === 't' || $row['es_racha'] === true);

            $labels[] = $etiqueta;
            $velocidades[] = $velocidad;

            if ($es_racha) {
                $labels_rachas[] = $etiqueta;
                $velocidades_rachas[] = $velocidad;
                $total_rachas++;
            }

            if ($umbral_p75 === 0) {
                $umbral_p75 = round(floatval($row['umbral_p75']), 2);
                $vel_maxima = round(floatval($row['vel_maxima']), 2);
                $vel_promedio = round(floatval($row['vel_promedio_total']), 2);
                $total_registros = intval($row['total_registros']);
            }
        }

        $porcentaje_rachas = count($labels) > 0
            ? round(($total_rachas / count($labels)) * 100, 1)
            : 0;

        return [
            'labels' => $labels,
            'velocidades' => $velocidades,
            'labels_rachas' => $labels_rachas,
            'velocidades_rachas' => $velocidades_rachas,
            'umbral_p75' => $umbral_p75,
            'vel_maxima' => $vel_maxima,
            'vel_promedio' => $vel_promedio,
            'total_registros' => count($labels),
            'total_rachas' => $total_rachas,
            'porcentaje_rachas' => $porcentaje_rachas
        ];
    }

    /**
     * Obtiene datos por SECTOR de dirección para gráfica Urbina Tipo 2
     * Barras: Frecuencia por sector | Línea: Velocidad promedio por sector
     */
    public function obtenerDatosUrbinaSector($id_estacion, $anio = null, $mes = null)
    {
        $where_conditions = ["r.id_estacion = $1", "r.tipo_nivel = 'L0'"];
        $params = [$id_estacion];
        $param_count = 1;

        if ($anio !== null) {
            $param_count++;
            $where_conditions[] = "EXTRACT(YEAR FROM r.fecha_hora) = $" . $param_count;
            $params[] = $anio;
        }

        if ($mes !== null) {
            $param_count++;
            $where_conditions[] = "EXTRACT(MONTH FROM r.fecha_hora) = $" . $param_count;
            $params[] = $mes;
        }

        $where_clause = implode(" AND ", $where_conditions);

        // ✅ SOLUCIÓN: Usar subconsulta para poder filtrar por el alias
        $query = "SELECT 
                sector,
                velocidad_promedio,
                frecuencia
              FROM (
                SELECT 
                    CASE 
                        WHEN dl0.direccion_viento >= 337.5 OR dl0.direccion_viento < 22.5 THEN 'N'
                        WHEN dl0.direccion_viento >= 22.5 AND dl0.direccion_viento < 67.5 THEN 'NE'
                        WHEN dl0.direccion_viento >= 67.5 AND dl0.direccion_viento < 112.5 THEN 'E'
                        WHEN dl0.direccion_viento >= 112.5 AND dl0.direccion_viento < 157.5 THEN 'SE'
                        WHEN dl0.direccion_viento >= 157.5 AND dl0.direccion_viento < 202.5 THEN 'S'
                        WHEN dl0.direccion_viento >= 202.5 AND dl0.direccion_viento < 247.5 THEN 'SW'
                        WHEN dl0.direccion_viento >= 247.5 AND dl0.direccion_viento < 292.5 THEN 'W'
                        WHEN dl0.direccion_viento >= 292.5 AND dl0.direccion_viento < 337.5 THEN 'NW'
                    END as sector,
                    AVG(dl0.velocidad_viento) as velocidad_promedio,
                    COUNT(*) as frecuencia
                  FROM registros r
                  INNER JOIN datos_l0 dl0 ON r.id_registro = dl0.id_registro
                  WHERE $where_clause
                    AND dl0.direccion_viento IS NOT NULL
                    AND dl0.velocidad_viento IS NOT NULL
                  GROUP BY sector
              ) AS sectores_data
              WHERE sector IS NOT NULL
              ORDER BY 
                CASE sector
                    WHEN 'N' THEN 1 WHEN 'NE' THEN 2 WHEN 'E' THEN 3 WHEN 'SE' THEN 4
                    WHEN 'S' THEN 5 WHEN 'SW' THEN 6 WHEN 'W' THEN 7 WHEN 'NW' THEN 8
                END";

        error_log("🔍 SQL Query Urbina Sector: " . $query);
        error_log("🔍 Parámetros: " . json_encode($params));

        $result = pg_query_params($this->conn, $query, $params);

        if (!$result) {
            error_log("❌ Error SQL: " . pg_last_error($this->conn));
            throw new Exception("Error al obtener datos Urbina por sector: " . pg_last_error($this->conn));
        }

        // ✅ Inicializar todos los sectores en 0
        $sectores = array('N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW');
        $velocidades = array_fill(0, 8, 0);
        $frecuencias = array_fill(0, 8, 0);

        $total_rows = 0;
        while ($row = pg_fetch_assoc($result)) {
            $sector = $row['sector'];
            $index = array_search($sector, $sectores);
            if ($index !== false) {
                $velocidades[$index] = round(floatval($row['velocidad_promedio']), 2);
                $frecuencias[$index] = intval($row['frecuencia']);
                $total_rows++;
            }
        }

        error_log("✅ Datos Urbina Sector procesados:");
        error_log("   - Filas obtenidas: " . $total_rows);
        error_log("   - Frecuencias: " . json_encode($frecuencias));
        error_log("   - Velocidades: " . json_encode($velocidades));

        return [
            'sectores' => $sectores,
            'velocidades' => $velocidades,
            'frecuencias' => $frecuencias,
            'total_sectores' => $total_rows
        ];
    }

    /**
     * Obtiene datos L1 agrupados por HORA para gráfica Temperatura Diaria
     * Similar a obtenerDatosL0PorHora pero para temperatura
     */
    public function obtenerDatosL1TemperaturaPorHora($id_estacion, $anio = null, $mes = null)
    {
        $where_conditions = ["id_estacion = $1"];
        $params = [$id_estacion];
        $param_count = 1;

        if ($anio !== null) {
            $param_count++;
            $where_conditions[] = "anio = $" . $param_count;
            $params[] = $anio;
        }

        if ($mes !== null) {
            $param_count++;
            $where_conditions[] = "mes = $" . $param_count;
            $params[] = $mes;
        }

        $where_clause = implode(" AND ", $where_conditions);

        // ✅ USA VISTA MATERIALIZADA
        $query = "SELECT 
            etiqueta,
            temp_promedio,
            temp_maxima,
            temp_minima,
            num_lecturas
          FROM mv_l1_temperatura_hora
          WHERE $where_clause
          ORDER BY fecha_hora_agrupada ASC";

        $result = pg_query_params($this->conn, $query, $params);

        if (!$result) {
            error_log("❌ Error SQL: " . pg_last_error($this->conn));
            throw new Exception("Error al obtener datos de temperatura: " . pg_last_error($this->conn));
        }

        $labels = array();
        $temp_promedio = array();
        $temp_maxima = array();
        $temp_minima = array();

        while ($row = pg_fetch_assoc($result)) {
            $labels[] = $row['etiqueta'];
            $temp_promedio[] = round(floatval($row['temp_promedio']), 2);
            $temp_maxima[] = round(floatval($row['temp_maxima']), 2);
            $temp_minima[] = round(floatval($row['temp_minima']), 2);
        }

        error_log("✅ Temperatura L1: " . count($labels) . " horas obtenidas desde vista materializada");

        return [
            'labels' => $labels,
            'temp_promedio' => $temp_promedio,
            'temp_maxima' => $temp_maxima,
            'temp_minima' => $temp_minima,
            'total_horas' => count($labels)
        ];
    }
    /**
     * Obtiene distribución de humedad relativa por rangos para gráfica Radar L1
     * Similar a la distribución de direcciones de viento pero para humedad
     */
    public function obtenerDistribucionHumedadL1($id_estacion, $anio = null, $mes = null)
    {
        $where_conditions = ["id_estacion = $1"];
        $params = [$id_estacion];
        $param_count = 1;

        if ($anio !== null) {
            $param_count++;
            $where_conditions[] = "anio = $" . $param_count;
            $params[] = $anio;
        }

        if ($mes !== null) {
            $param_count++;
            $where_conditions[] = "mes = $" . $param_count;
            $params[] = $mes;
        }

        $where_clause = implode(" AND ", $where_conditions);

        // ✅ USA VISTA MATERIALIZADA
        $query = "SELECT 
            rango_humedad,
            humedad_promedio,
            frecuencia
          FROM mv_l1_humedad_rangos
          WHERE $where_clause
            AND rango_humedad IS NOT NULL
          ORDER BY 
            CASE rango_humedad
                WHEN 'Muy Seca (<30%)' THEN 1
                WHEN 'Seca (30-45%)' THEN 2
                WHEN 'Normal (45-60%)' THEN 3
                WHEN 'Húmeda (60-75%)' THEN 4
                WHEN 'Muy Húmeda (≥75%)' THEN 5
            END";

        error_log("🔍 SQL Query Humedad L1: " . $query);
        error_log("🔍 Parámetros: " . json_encode($params));

        $result = pg_query_params($this->conn, $query, $params);

        if (!$result) {
            error_log("❌ Error SQL: " . pg_last_error($this->conn));
            throw new Exception("Error al obtener distribución de humedad: " . pg_last_error($this->conn));
        }

        // Inicializar todos los rangos en 0
        $rangos = [
            'Muy Seca (<30%)' => 0,
            'Seca (30-45%)' => 0,
            'Normal (45-60%)' => 0,
            'Húmeda (60-75%)' => 0,
            'Muy Húmeda (≥75%)' => 0
        ];

        $humedades_promedio = [
            'Muy Seca (<30%)' => 0,
            'Seca (30-45%)' => 0,
            'Normal (45-60%)' => 0,
            'Húmeda (60-75%)' => 0,
            'Muy Húmeda (≥75%)' => 0
        ];

        while ($row = pg_fetch_assoc($result)) {
            $rango = $row['rango_humedad'];
            if ($rango && isset($rangos[$rango])) {
                $rangos[$rango] = intval($row['frecuencia']);
                $humedades_promedio[$rango] = round(floatval($row['humedad_promedio']), 2);
            }
        }

        error_log("✅ Distribución de humedad obtenida desde vista materializada");

        return [
            'rangos' => array_keys($rangos),
            'frecuencias' => array_values($rangos),
            'humedades_promedio' => array_values($humedades_promedio),
            'total_rangos' => count(array_filter($rangos))
        ];
    }

    /**
     * Obtiene datos de presión barométrica agrupados por día para gráfica de barras L1
     * Muestra presión promedio, máxima y mínima por día
     */
    public function obtenerPresionBarometricaPorDia($id_estacion, $anio = null, $mes = null)
    {
        $where_conditions = ["id_estacion = $1"];
        $params = [$id_estacion];
        $param_count = 1;

        if ($anio !== null) {
            $param_count++;
            $where_conditions[] = "anio = $" . $param_count;
            $params[] = $anio;
        }

        if ($mes !== null) {
            $param_count++;
            $where_conditions[] = "mes = $" . $param_count;
            $params[] = $mes;
        }

        $where_clause = implode(" AND ", $where_conditions);

        // ✅ USA VISTA MATERIALIZADA
        $query = "SELECT 
            etiqueta,
            presion_promedio,
            presion_maxima,
            presion_minima,
            num_lecturas
          FROM mv_l1_presion_dia
          WHERE $where_clause
          ORDER BY fecha ASC";

        error_log("🔍 SQL Query Presión L1: " . $query);
        error_log("🔍 Parámetros: " . json_encode($params));

        $result = pg_query_params($this->conn, $query, $params);

        if (!$result) {
            error_log("❌ Error SQL: " . pg_last_error($this->conn));
            throw new Exception("Error al obtener datos de presión: " . pg_last_error($this->conn));
        }

        $labels = array();
        $presion_promedio = array();
        $presion_maxima = array();
        $presion_minima = array();

        while ($row = pg_fetch_assoc($result)) {
            $labels[] = $row['etiqueta'];
            $presion_promedio[] = round(floatval($row['presion_promedio']), 2);
            $presion_maxima[] = round(floatval($row['presion_maxima']), 2);
            $presion_minima[] = round(floatval($row['presion_minima']), 2);
        }

        $total = count($labels);
        error_log("✅ Presión Barométrica L1: {$total} días desde vista materializada");

        return [
            'labels' => $labels,
            'presion_promedio' => $presion_promedio,
            'presion_maxima' => $presion_maxima,
            'presion_minima' => $presion_minima,
            'total_dias' => $total
        ];
    }
    /**
     * Obtiene datos L1 de velocidad del viento y temperatura por hora
     * Para gráfica mixta (barras + línea con doble eje Y)
     */
    public function obtenerVientoTemperaturaPorHora($id_estacion, $anio = null, $mes = null)
    {
        $where_conditions = ["id_estacion = $1"];
        $params = [$id_estacion];
        $param_count = 1;

        if ($anio !== null) {
            $param_count++;
            $where_conditions[] = "anio = $" . $param_count;
            $params[] = $anio;
        }

        if ($mes !== null) {
            $param_count++;
            $where_conditions[] = "mes = $" . $param_count;
            $params[] = $mes;
        }

        $where_clause = implode(" AND ", $where_conditions);

        // ✅ USA VISTA MATERIALIZADA
        $query = "SELECT 
            etiqueta,
            temp_promedio,
            humedad_promedio,
            presion_promedio,
            radiacion_promedio,
            num_lecturas
          FROM mv_l1_multivariable_hora
          WHERE $where_clause
          ORDER BY fecha_hora_agrupada ASC";

        $result = pg_query_params($this->conn, $query, $params);

        if (!$result) {
            error_log("❌ Error SQL: " . pg_last_error($this->conn));
            throw new Exception("Error al obtener datos multivariable: " . pg_last_error($this->conn));
        }

        $etiquetas = array();
        $temp_promedio = array();
        $humedad_promedio = array();
        $presion_promedio = array();
        $radiacion_promedio = array();

        while ($row = pg_fetch_assoc($result)) {
            $etiquetas[] = $row['etiqueta'];
            $temp_promedio[] = round(floatval($row['temp_promedio']), 2);
            $humedad_promedio[] = round(floatval($row['humedad_promedio']), 2);
            $presion_promedio[] = round(floatval($row['presion_promedio']), 2);
            $radiacion_promedio[] = round(floatval($row['radiacion_promedio']), 2);
        }

        error_log("✅ Multivariable L1: " . count($etiquetas) . " horas desde vista materializada");

        return [
            'etiquetas' => $etiquetas,
            'temp_promedio' => $temp_promedio,
            'humedad_promedio' => $humedad_promedio,
            'presion_promedio' => $presion_promedio,
            'radiacion_promedio' => $radiacion_promedio,
            'total_horas' => count($etiquetas)
        ];
    }
    /**
     * Obtiene datos de presión atmosférica en formato MATRIZ para Heatmap
     * Eje X: Días del mes (o meses del año)
     * Eje Y: Horas del día (0-23)
     */
    public function obtenerHeatmapPresionAtmosferica($id_estacion, $anio = null, $mes = null)
    {
        $where_conditions = ["id_estacion = $1"];
        $params = [$id_estacion];
        $param_count = 1;

        if ($anio !== null) {
            $param_count++;
            $where_conditions[] = "anio = $" . $param_count;
            $params[] = $anio;
        }

        // Determinar si agrupar por día o por mes
        $agrupar_por_mes = ($mes === null);

        if (!$agrupar_por_mes) {
            $param_count++;
            $where_conditions[] = "mes = $" . $param_count;
            $params[] = $mes;
        }

        $where_clause = implode(" AND ", $where_conditions);

        // ✅ USA VISTA MATERIALIZADA con agrupación dinámica
        if ($agrupar_por_mes) {
            // MODO ANUAL: Agrupar por MES
            $query = "SELECT 
                mes as periodo,
                hora,
                AVG(presion_promedio) as presion_promedio,
                MAX(presion_maxima) as presion_maxima,
                MIN(presion_minima) as presion_minima,
                SUM(num_lecturas) as num_lecturas
              FROM mv_l1_heatmap_presion
              WHERE $where_clause
              GROUP BY mes, hora
              ORDER BY mes, hora";

            $max_periodo = 12;
            $etiquetas_periodo = [
                'Ene',
                'Feb',
                'Mar',
                'Abr',
                'May',
                'Jun',
                'Jul',
                'Ago',
                'Sep',
                'Oct',
                'Nov',
                'Dic'
            ];
        } else {
            // MODO MENSUAL: Agrupar por DÍA
            $query = "SELECT 
                dia as periodo,
                hora,
                presion_promedio,
                presion_maxima,
                presion_minima,
                num_lecturas
              FROM mv_l1_heatmap_presion
              WHERE $where_clause
              ORDER BY dia, hora";

            $max_periodo = 31;
            $etiquetas_periodo = array();
            for ($i = 1; $i <= 31; $i++) {
                $etiquetas_periodo[] = 'Día ' . $i;
            }
        }

        error_log("🔍 SQL Heatmap Presión (vista materializada): " . $query);

        $result = pg_query_params($this->conn, $query, $params);

        if (!$result) {
            error_log("❌ Error SQL: " . pg_last_error($this->conn));
            throw new Exception("Error al obtener heatmap presión: " . pg_last_error($this->conn));
        }

        // Inicializar matriz
        $matriz_presion = array_fill(0, $max_periodo, array_fill(0, 24, null));
        $datos_raw = array();

        while ($row = pg_fetch_assoc($result)) {
            $periodo = intval($row['periodo']) - 1;
            $hora = intval($row['hora']);
            $presion = round(floatval($row['presion_promedio']), 2);

            if ($periodo >= 0 && $periodo < $max_periodo && $hora >= 0 && $hora < 24) {
                $matriz_presion[$periodo][$hora] = $presion;

                $datos_raw[] = [
                    'periodo' => $periodo + 1,
                    'hora' => $hora,
                    'presion' => $presion,
                    'maxima' => round(floatval($row['presion_maxima']), 2),
                    'minima' => round(floatval($row['presion_minima']), 2),
                    'lecturas' => intval($row['num_lecturas'])
                ];
            }
        }

        $etiquetas_horas = array();
        for ($h = 0; $h < 24; $h++) {
            $etiquetas_horas[] = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
        }

        error_log("✅ Heatmap Presión: " . count($datos_raw) . " puntos desde vista materializada");

        return [
            'matriz_presion' => $matriz_presion,
            'etiquetas_periodo' => $etiquetas_periodo,
            'etiquetas_horas' => $etiquetas_horas,
            'datos_raw' => $datos_raw,
            'total_puntos' => count($datos_raw),
            'modo' => $agrupar_por_mes ? 'anual' : 'mensual'
        ];
    }

    /**
     * Obtiene datos de radiación solar en formato MATRIZ para Heatmap
     * Eje X: Días del mes (o meses del año)
     * Eje Y: Horas del día (0-23)
     * ¡PERFECTA para visualización por su patrón día/noche extremo!
     */
    public function obtenerHeatmapRadiacionSolar($id_estacion, $anio = null, $mes = null)
    {
        $where_conditions = ["id_estacion = $1"];
        $params = [$id_estacion];
        $param_count = 1;

        if ($anio !== null) {
            $param_count++;
            $where_conditions[] = "anio = $" . $param_count;
            $params[] = $anio;
        }

        $agrupar_por_mes = ($mes === null);

        if (!$agrupar_por_mes) {
            $param_count++;
            $where_conditions[] = "mes = $" . $param_count;
            $params[] = $mes;
        }

        $where_clause = implode(" AND ", $where_conditions);

        // ✅ USA VISTA MATERIALIZADA con agrupación dinámica
        if ($agrupar_por_mes) {
            $query = "SELECT 
                mes as periodo,
                hora,
                AVG(radiacion_promedio) as radiacion_promedio,
                MAX(radiacion_maxima) as radiacion_maxima,
                MIN(radiacion_minima) as radiacion_minima,
                SUM(num_lecturas) as num_lecturas
              FROM mv_l1_heatmap_radiacion
              WHERE $where_clause
              GROUP BY mes, hora
              ORDER BY mes, hora";

            $max_periodo = 12;
            $etiquetas_periodo = [
                'Ene',
                'Feb',
                'Mar',
                'Abr',
                'May',
                'Jun',
                'Jul',
                'Ago',
                'Sep',
                'Oct',
                'Nov',
                'Dic'
            ];
        } else {
            $query = "SELECT 
                dia as periodo,
                hora,
                radiacion_promedio,
                radiacion_maxima,
                radiacion_minima,
                num_lecturas
              FROM mv_l1_heatmap_radiacion
              WHERE $where_clause
              ORDER BY dia, hora";

            $max_periodo = 31;
            $etiquetas_periodo = array();
            for ($i = 1; $i <= 31; $i++) {
                $etiquetas_periodo[] = 'Día ' . $i;
            }
        }

        error_log("🔍 SQL Heatmap Radiación (vista materializada): " . $query);

        $result = pg_query_params($this->conn, $query, $params);

        if (!$result) {
            error_log("❌ Error SQL: " . pg_last_error($this->conn));
            throw new Exception("Error al obtener heatmap radiación: " . pg_last_error($this->conn));
        }

        $matriz_radiacion = array_fill(0, $max_periodo, array_fill(0, 24, null));
        $datos_raw = array();

        while ($row = pg_fetch_assoc($result)) {
            $periodo = intval($row['periodo']) - 1;
            $hora = intval($row['hora']);
            $radiacion = round(floatval($row['radiacion_promedio']), 2);

            if ($periodo >= 0 && $periodo < $max_periodo && $hora >= 0 && $hora < 24) {
                $matriz_radiacion[$periodo][$hora] = $radiacion;

                $datos_raw[] = [
                    'periodo' => $periodo + 1,
                    'hora' => $hora,
                    'radiacion' => $radiacion,
                    'maxima' => round(floatval($row['radiacion_maxima']), 2),
                    'minima' => round(floatval($row['radiacion_minima']), 2),
                    'lecturas' => intval($row['num_lecturas'])
                ];
            }
        }

        $etiquetas_horas = array();
        for ($h = 0; $h < 24; $h++) {
            $etiquetas_horas[] = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
        }

        error_log("✅ Heatmap Radiación: " . count($datos_raw) . " puntos desde vista materializada");

        return [
            'matriz_radiacion' => $matriz_radiacion,
            'etiquetas_periodo' => $etiquetas_periodo,
            'etiquetas_horas' => $etiquetas_horas,
            'datos_raw' => $datos_raw,
            'total_puntos' => count($datos_raw),
            'modo' => $agrupar_por_mes ? 'anual' : 'mensual'
        ];
    }

    /**
     * Obtiene datos para Climograma Integral L2-2
     * Combina temperatura promedio (barras) + velocidad del viento (línea)
     */
    public function obtenerClimogramaL2($id_estacion, $anio = null, $mes = null)
    {
        $where_conditions = ["id_estacion = $1"];
        $params = [$id_estacion];
        $param_count = 1;

        if ($anio !== null) {
            $param_count++;
            $where_conditions[] = "anio = $" . $param_count;
            $params[] = $anio;
        }

        if ($mes !== null) {
            $param_count++;
            $where_conditions[] = "mes = $" . $param_count;
            $params[] = $mes;
        }

        $where_clause = implode(" AND ", $where_conditions);

        // ✅ USA VISTA MATERIALIZADA
        $query = "SELECT 
            etiqueta,
            temp_promedio,
            viento_promedio
          FROM mv_l2_consolidado
          WHERE $where_clause
          ORDER BY fecha_hora_agrupada ASC";

        $result = pg_query_params($this->conn, $query, $params);

        if (!$result) {
            error_log("❌ Error SQL: " . pg_last_error($this->conn));
            throw new Exception("Error al obtener climograma: " . pg_last_error($this->conn));
        }

        $labels = array();
        $temperatura_promedio = array();
        $viento_promedio = array();

        while ($row = pg_fetch_assoc($result)) {
            $labels[] = $row['etiqueta'];
            $temperatura_promedio[] = round(floatval($row['temp_promedio']), 2);
            $viento_promedio[] = round(floatval($row['viento_promedio']), 2);
        }

        return [
            'labels' => $labels,
            'temperatura_promedio' => $temperatura_promedio,
            'viento_promedio' => $viento_promedio,
            'total_periodos' => count($labels),
            'modo' => ($mes === null) ? 'anual' : 'mensual'
        ];
    }
    /**
     * Obtiene tendencia anual multivariable para L2-1
     * Muestra evolución mensual (o diaria) de 4 variables principales
     * REFACTORIZADO: Usa mv_l2_consolidado (igual que climograma)
     */
    public function obtenerTendenciaAnualL2($id_estacion, $anio = null, $mes = null)
    {
        $where_conditions = ["id_estacion = $1"];
        $params = [$id_estacion];
        $param_count = 1;

        if ($anio !== null) {
            $param_count++;
            $where_conditions[] = "anio = $" . $param_count;
            $params[] = $anio;
        }

        if ($mes !== null) {
            $param_count++;
            $where_conditions[] = "mes = $" . $param_count;
            $params[] = $mes;
        }

        $where_clause = implode(" AND ", $where_conditions);

        // ✅ USA VISTA MATERIALIZADA (igual que climograma)
        $query = "SELECT 
            etiqueta,
            temp_promedio as temperatura_promedio,
            humedad_promedio,
            presion_promedio,
            radiacion_total,
            viento_promedio
          FROM mv_l2_consolidado
          WHERE $where_clause
          ORDER BY fecha_hora_agrupada ASC";

        error_log("🔍 SQL Query Tendencia Anual L2: " . $query);
        error_log("🔍 Parámetros: " . json_encode($params));

        $result = pg_query_params($this->conn, $query, $params);

        if (!$result) {
            error_log("❌ Error SQL: " . pg_last_error($this->conn));
            throw new Exception("Error al obtener tendencia anual: " . pg_last_error($this->conn));
        }

        $labels = array();
        $temperatura_promedio = array();
        $humedad_promedio = array();
        $presion_promedio = array();
        $radiacion_total = array();
        $viento_promedio = array();

        while ($row = pg_fetch_assoc($result)) {
            $labels[] = $row['etiqueta'];
            $temperatura_promedio[] = round(floatval($row['temperatura_promedio']), 2);
            $humedad_promedio[] = round(floatval($row['humedad_promedio'] ?? 0), 2);
            $presion_promedio[] = round(floatval($row['presion_promedio'] ?? 0), 2);
            $radiacion_total[] = round(floatval($row['radiacion_total'] ?? 0), 2);
            $viento_promedio[] = round(floatval($row['viento_promedio'] ?? 0), 2);
        }

        $total = count($labels);
        error_log("✅ Datos Tendencia Anual L2: {$total} períodos, ID estación: {$id_estacion}, Año: {$anio}, Mes: " . ($mes ?? 'NULL'));

        return [
            'labels' => $labels,
            'temperatura_promedio' => $temperatura_promedio,
            'humedad_promedio' => $humedad_promedio,
            'presion_promedio' => $presion_promedio,
            'radiacion_total' => $radiacion_total,
            'viento_promedio' => $viento_promedio,
            'total_periodos' => $total,
            'modo' => ($mes === null) ? 'anual' : 'mensual'
        ];
    }

    /**
     * Obtiene datos de balance energético por hora
     * Para gráfica L1-6: Balance Energético (Radiación + Temperatura + Viento)
     */
    public function obtenerBalanceEnergeticoPorHora($id_estacion, $anio = null, $mes = null)
    {
        $where_conditions = ["id_estacion = $1"];
        $params = [$id_estacion];
        $param_count = 1;

        if ($anio !== null) {
            $param_count++;
            $where_conditions[] = "anio = $" . $param_count;
            $params[] = $anio;
        }

        if ($mes !== null) {
            $param_count++;
            $where_conditions[] = "mes = $" . $param_count;
            $params[] = $mes;
        }

        $where_clause = implode(" AND ", $where_conditions);

        // ✅ USA VISTA MATERIALIZADA
        $query = "SELECT 
            etiqueta,
            hora_del_dia,
            radiacion_promedio,
            temperatura_promedio,
            velocidad_viento,
            num_lecturas
          FROM mv_l1_balance_energetico
          WHERE $where_clause
          ORDER BY fecha_hora_agrupada ASC";

        error_log("🔍 SQL Balance Energético (vista materializada): " . $query);

        $result = pg_query_params($this->conn, $query, $params);

        if (!$result) {
            error_log("❌ Error SQL: " . pg_last_error($this->conn));
            throw new Exception("Error al obtener balance energético: " . pg_last_error($this->conn));
        }

        $labels = array();
        $radiacion_promedio = array();
        $temperatura_promedio = array();
        $velocidad_viento = array();
        $horas_del_dia = array();

        while ($row = pg_fetch_assoc($result)) {
            $labels[] = $row['etiqueta'];
            $radiacion_promedio[] = round(floatval($row['radiacion_promedio']), 2);
            $temperatura_promedio[] = round(floatval($row['temperatura_promedio']), 2);
            $velocidad_viento[] = round(floatval($row['velocidad_viento']), 2);
            $horas_del_dia[] = intval($row['hora_del_dia']);
        }

        $total = count($labels);
        error_log("✅ Balance Energético: {$total} horas desde vista materializada");

        return [
            'labels' => $labels,
            'radiacion_promedio' => $radiacion_promedio,
            'temperatura_promedio' => $temperatura_promedio,
            'velocidad_viento' => $velocidad_viento,
            'horas_del_dia' => $horas_del_dia,
            'total_horas' => $total
        ];
    }

    /**
     * Obtiene datos de punto de rocío calculado por hora
     * Para gráfica L1-7: Punto de Rocío (Dew Point)
     * Fórmula Magnus-Tetens para cálculo de Td
     */
    public function obtenerPuntoRocioPorHora($id_estacion, $anio = null, $mes = null)
    {
        $where_conditions = ["id_estacion = $1"];
        $params = [$id_estacion];
        $param_count = 1;

        if ($anio !== null) {
            $param_count++;
            $where_conditions[] = "anio = $" . $param_count;
            $params[] = $anio;
        }

        if ($mes !== null) {
            $param_count++;
            $where_conditions[] = "mes = $" . $param_count;
            $params[] = $mes;
        }

        $where_clause = implode(" AND ", $where_conditions);

        // ✅ USA VISTA MATERIALIZADA con punto de rocío pre-calculado
        $query = "SELECT 
            etiqueta,
            temperatura_promedio,
            humedad_promedio,
            punto_rocio,
            num_lecturas
          FROM mv_l1_punto_rocio
          WHERE $where_clause
          ORDER BY fecha_hora_agrupada ASC";

        error_log("🔍 SQL Punto de Rocío (vista materializada): " . $query);

        $result = pg_query_params($this->conn, $query, $params);

        if (!$result) {
            error_log("❌ Error SQL: " . pg_last_error($this->conn));
            throw new Exception("Error al obtener punto de rocío: " . pg_last_error($this->conn));
        }

        $labels = array();
        $punto_rocio = array();
        $temperatura_aire = array();

        $labels_alertas_heladas = array();
        $valores_alertas_heladas = array();
        $labels_alertas_humedad = array();
        $valores_alertas_humedad = array();

        while ($row = pg_fetch_assoc($result)) {
            $label = $row['etiqueta'];
            $td = round(floatval($row['punto_rocio']), 2);
            $temp = round(floatval($row['temperatura_promedio']), 2);

            $labels[] = $label;
            $punto_rocio[] = $td;
            $temperatura_aire[] = $temp;

            if ($td < 0) {
                $labels_alertas_heladas[] = $label;
                $valores_alertas_heladas[] = $td;
            }

            if ($td > 20) {
                $labels_alertas_humedad[] = $label;
                $valores_alertas_humedad[] = $td;
            }
        }

        $total = count($labels);
        $total_alertas_heladas = count($labels_alertas_heladas);
        $total_alertas_humedad = count($labels_alertas_humedad);

        error_log("✅ Punto de Rocío: {$total} horas desde vista materializada");
        error_log("   - Alertas heladas: {$total_alertas_heladas}");
        error_log("   - Alertas humedad: {$total_alertas_humedad}");

        return [
            'labels' => $labels,
            'punto_rocio' => $punto_rocio,
            'temperatura_aire' => $temperatura_aire,
            'labels_alertas_heladas' => $labels_alertas_heladas,
            'valores_alertas_heladas' => $valores_alertas_heladas,
            'labels_alertas_humedad' => $labels_alertas_humedad,
            'valores_alertas_humedad' => $valores_alertas_humedad,
            'total_horas' => $total,
            'total_alertas_heladas' => $total_alertas_heladas,
            'total_alertas_humedad' => $total_alertas_humedad
        ];
    }
    /**
     * Obtiene Análisis de Tendencias L2 (Comparativo Año Actual vs Promedio Histórico)
     */
    public function obtenerAnalisisTendenciasL2($id_estacion, $anio)
    {
        // 1. Obtener datos del año seleccionado
        $queryCurrent = "SELECT 
            EXTRACT(MONTH FROM fecha_hora_agrupada)::INTEGER as mes,
            AVG(temp_promedio) as temp_actual
        FROM mv_l2_consolidado
        WHERE id_estacion = $1 AND anio = $2
        GROUP BY mes";

        // 2. Obtener promedio histórico (todos los años excepto el actual opcionalmente, o todos)
        $queryHistoric = "SELECT 
            EXTRACT(MONTH FROM fecha_hora_agrupada)::INTEGER as mes,
            AVG(temp_promedio) as temp_historica
        FROM mv_l2_consolidado
        WHERE id_estacion = $1
        GROUP BY mes";

        $resultCurrent = pg_query_params($this->conn, $queryCurrent, [$id_estacion, $anio]);
        $resultHistoric = pg_query_params($this->conn, $queryHistoric, [$id_estacion]);

        if (!$resultCurrent || !$resultHistoric) {
            throw new Exception("Error al obtener análisis de tendencias");
        }

        $dataCurrent = [];
        while ($row = pg_fetch_assoc($resultCurrent)) {
            $dataCurrent[intval($row['mes'])] = floatval($row['temp_actual']);
        }

        $dataHistoric = [];
        while ($row = pg_fetch_assoc($resultHistoric)) {
            $dataHistoric[intval($row['mes'])] = floatval($row['temp_historica']);
        }

        $meses = [];
        $tempActual = [];
        $tempHistorica = [];
        $nombresMeses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        for ($i = 1; $i <= 12; $i++) {
            $meses[] = $nombresMeses[$i - 1];
            $tempActual[] = isset($dataCurrent[$i]) ? round($dataCurrent[$i], 2) : 0; // null?
            $tempHistorica[] = isset($dataHistoric[$i]) ? round($dataHistoric[$i], 2) : 0;
        }

        return [
            'labels' => $meses,
            'actual' => $tempActual,
            'historico' => $tempHistorica
        ];
    }

    /**
     * Obtiene Distribución Estacional L2 (Promedios por estación del año)
     */
    public function obtenerDistribucionEstacionalL2($id_estacion)
    {
        // Agrupar por trimestres o estaciones meteorológicas locales
        // Ecuador: Invierno (Lluvioso: Dic-May), Verano (Seco: Jun-Nov)
        // Simplificación: Agrupar por mes histórico
        $query = "SELECT 
            EXTRACT(MONTH FROM fecha_hora_agrupada)::INTEGER as mes,
            AVG(temp_promedio) as temperatura,
            AVG(lluvia_promedio) as precipitacion, -- Asumiendo columna lluvia_promedio en MV
            AVG(humedad_promedio) as humedad
        FROM mv_l2_consolidado
        WHERE id_estacion = $1
        GROUP BY mes
        ORDER BY mes ASC";

        $result = pg_query_params($this->conn, $query, [$id_estacion]);

        if (!$result) {
            // Fallback si no existe lluvia_promedio en MV, usar datos_l1
            $query = "SELECT 
                EXTRACT(MONTH FROM r.fecha_hora)::INTEGER as mes,
                AVG(dl1.temperatura_aire) as temperatura,
                AVG(dl1.lluvia) as precipitacion,
                AVG(dl1.humedad_relativa) as humedad
            FROM registros r
            JOIN datos_l1 dl1 ON r.id_registro = dl1.id_registro
            WHERE r.id_estacion = $1 AND r.tipo_nivel = 'L1'
            GROUP BY mes
            ORDER BY mes ASC";
            $result = pg_query_params($this->conn, $query, [$id_estacion]);
        }

        if (!$result)
            throw new Exception("Error al obtener distribución estacional");

        $temperatura = [];
        $precipitacion = [];
        $etiquetas = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic']; // Placeholder para 12 meses

        // Inicializar arrays
        for ($i = 0; $i < 12; $i++) {
            $temperatura[$i] = 0;
            $precipitacion[$i] = 0;
        }

        while ($row = pg_fetch_assoc($result)) {
            $idx = intval($row['mes']) - 1;
            if ($idx >= 0 && $idx < 12) {
                $temperatura[$idx] = round(floatval($row['temperatura']), 2);
                $precipitacion[$idx] = round(floatval($row['precipitacion'] ?? 0), 2);
            }
        }

        // Agrupar en Estaciones si se desea, o devolver los 12 meses como "Perfil Estacional"
        // Devolveremos los 12 meses para que el frontend pueda pintar áreas
        return [
            'labels' => $etiquetas,
            'temperatura' => $temperatura,
            'precipitacion' => $precipitacion
        ];
    }

    /**
     * Obtiene Análisis de Precipitaciones L2
     */
    /**
     * Obtiene Análisis de Precipitaciones L2
     * Usando mv_l2_consolidado para asegurar datos
     */
    public function obtenerAnalisisPrecipitacionesL2($id_estacion, $anio)
    {
        $query = "SELECT 
            EXTRACT(MONTH FROM fecha_hora_agrupada)::INTEGER as mes,
            SUM(lluvia_promedio) as total_lluvia,
            COUNT(DISTINCT CASE WHEN lluvia_promedio > 0 THEN DATE(fecha_hora_agrupada) END) as dias_lluvia
        FROM mv_l2_consolidado
        WHERE id_estacion = $1 
          AND anio = $2
        GROUP BY mes
        ORDER BY mes ASC";

        error_log("🔍 SQL Precipitaciones L2 (MV): " . $query . " Params: $id_estacion, $anio");

        $result = pg_query_params($this->conn, $query, [$id_estacion, $anio]);
        if (!$result)
            throw new Exception("Error al obtener precipitaciones: " . pg_last_error($this->conn));

        $lluvia = array_fill(0, 12, 0);
        $dias = array_fill(0, 12, 0);
        $labels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        while ($row = pg_fetch_assoc($result)) {
            $idx = intval($row['mes']) - 1;
            if ($idx >= 0 && $idx < 12) {
                $lluvia[$idx] = round(floatval($row['total_lluvia']), 1);
                $dias[$idx] = intval($row['dias_lluvia']);
            }
        }

        return [
            'labels' => $labels,
            'precipitacion' => $lluvia,
            'dias_lluvia' => $dias
        ];
    }

    /**
     * Obtiene Índice de Aridez de De Martonne L2
     * Usando mv_l2_consolidado
     */
    public function obtenerIndiceAridezL2($id_estacion, $anio)
    {
        $query = "SELECT 
            EXTRACT(MONTH FROM fecha_hora_agrupada)::INTEGER as mes,
            SUM(lluvia_promedio) as precipitacion,
            AVG(temp_promedio) as temperatura
        FROM mv_l2_consolidado
        WHERE id_estacion = $1 
          AND anio = $2
        GROUP BY mes
        ORDER BY mes ASC";

        error_log("🔍 SQL Aridez L2 (MV): " . $query);

        $result = pg_query_params($this->conn, $query, [$id_estacion, $anio]);
        if (!$result)
            throw new Exception("Error al obtener índice de aridez");

        $aridez = array_fill(0, 12, 0);
        $labels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        while ($row = pg_fetch_assoc($result)) {
            $idx = intval($row['mes']) - 1;
            if ($idx >= 0 && $idx < 12) {
                $P = floatval($row['precipitacion']);
                $T = floatval($row['temperatura']);
                if ($T + 10 != 0) {
                    $I = $P / ($T + 10);
                    $aridez[$idx] = round($I, 2);
                }
            }
        }

        return [
            'labels' => $labels,
            'indice' => $aridez
        ];
    }

    /**
     * Obtiene Análisis de Heladas L2
     * Usando mv_l2_consolidado (aproximación con temp promedio horaria)
     */
    public function obtenerAnalisisHeladasL2($id_estacion, $anio)
    {
        // Contar días donde al menos una hora tuvo temperatura < 0
        $query = "SELECT 
            EXTRACT(MONTH FROM fecha_hora_agrupada)::INTEGER as mes,
            COUNT(DISTINCT DATE(fecha_hora_agrupada)) as dias_helada,
            MIN(temp_promedio) as temp_minima_absoluta
        FROM mv_l2_consolidado
        WHERE id_estacion = $1 
          AND anio = $2
          AND temp_promedio < 0
        GROUP BY mes
        ORDER BY mes ASC";

        error_log("🔍 SQL Heladas L2 (MV): " . $query);

        $result = pg_query_params($this->conn, $query, [$id_estacion, $anio]);
        if (!$result)
            throw new Exception("Error al obtener análisis de heladas");

        $diasHelada = array_fill(0, 12, 0);
        $minimas = array_fill(0, 12, 0);
        $labels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        while ($row = pg_fetch_assoc($result)) {
            $idx = intval($row['mes']) - 1;
            if ($idx >= 0 && $idx < 12) {
                $diasHelada[$idx] = intval($row['dias_helada']);
                $minimas[$idx] = round(floatval($row['temp_minima_absoluta']), 2);
            }
        }

        return [
            'labels' => $labels,
            'dias_helada' => $diasHelada,
            'temp_minima' => $minimas
        ];
    }
}
?>