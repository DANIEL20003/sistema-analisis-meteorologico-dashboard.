<?php
require_once '../config/conexion.php';

class MInformacionEstaciones {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
        
        if (!$this->conn) {
            error_log("❌ ERROR: No hay conexión a la base de datos en MInformacionEstaciones");
            throw new Exception("Error de conexión a la base de datos");
        }
        
        error_log("✅ MInformacionEstaciones inicializado correctamente");
    }
    
    /**
     * Obtiene todas las estaciones meteorológicas de Ecuador
     * @return array Array con todas las estaciones válidas
     */
    public function obtenerTodasLasEstaciones() {
        error_log("🔍 Iniciando obtenerTodasLasEstaciones()");
        
        if (!$this->conn) {
            throw new Exception("No hay conexión a la base de datos");
        }
        
        // Query principal para obtener estaciones con validaciones
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
                    e.ruta_fotografia,
                    CASE 
                        WHEN e.estado_activo = true THEN 'ACTIVA'
                        ELSE 'INACTIVA'
                    END as estado_texto,
                    CASE 
                        WHEN e.estado_activo = true THEN 'active'
                        ELSE 'offline'
                    END as status
                  FROM estaciones e 
                  WHERE e.latitud IS NOT NULL 
                    AND e.longitud IS NOT NULL 
                    AND e.latitud != 0 
                    AND e.longitud != 0
                  ORDER BY e.id_estacion";
        
        $result = pg_query($this->conn, $query);
        
        if (!$result) {
            $error_msg = pg_last_error($this->conn);
            error_log("❌ Error SQL: " . $error_msg);
            throw new Exception("Error al consultar estaciones: " . $error_msg);
        }
        
        $total_consulta = pg_num_rows($result);
        error_log("📊 Registros encontrados en BD: " . $total_consulta);
        
        $estaciones = array();
        $contador_validas = 0;
        
        while ($row = pg_fetch_assoc($result)) {
            $lat = floatval($row['latitud']);
            $lng = floatval($row['longitud']);
            
            error_log("📍 Procesando estación " . $row['id_estacion'] . " (" . $row['codigo'] . "): coords (" . $lat . ", " . $lng . ")");
            
            // Validar coordenadas dentro de Ecuador
            if ($this->validarCoordenadasEcuador($lat, $lng)) {
    // ✅ NORMALIZAR ruta_fotografia
    if (!empty($row['ruta_fotografia'])) {
        // Extraer solo el nombre del archivo sin extensión
        $ruta = $row['ruta_fotografia'];
        $filename = basename($ruta); // Ej: "F_Espoch.jpg"
        $nombre_sin_ext = pathinfo($filename, PATHINFO_FILENAME); // "F_Espoch"
        
        // Guardar para uso en frontend
        $row['imagen_base'] = $nombre_sin_ext;
    } else {
        $row['imagen_base'] = 'default';
    }
    
    $estaciones[] = $row;
    $contador_validas++;
}
        }
        
        error_log("📊 RESUMEN: " . $contador_validas . " estaciones válidas de " . $total_consulta . " total");
        
        if (empty($estaciones)) {
            error_log("⚠️  ADVERTENCIA: No se encontraron estaciones válidas");
            throw new Exception("No se encontraron estaciones meteorológicas con coordenadas válidas en Ecuador");
        }
        
        return $estaciones;
    }
    
    /**
     * Obtiene información detallada de una estación específica
     * @param int $id_estacion ID de la estación
     * @return array Información completa de la estación
     */
    public function obtenerInformacionEstacion($id_estacion) {
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
                            e.ruta_fotografia,
                            CASE 
                                WHEN e.estado_activo = true THEN 'ACTIVA'
                                ELSE 'INACTIVA'
                            END as estado_texto,
                            CASE 
                                WHEN e.estado_activo = true THEN 'active'
                                ELSE 'offline'
                            END as status
                          FROM estaciones e 
                          WHERE e.id_estacion = $id_estacion";
        
        $result_estacion = pg_query($this->conn, $query_estacion);
        
        if (!$result_estacion) {
            throw new Exception("Error al obtener información de estación: " . pg_last_error($this->conn));
        }
        
        $estacion = pg_fetch_assoc($result_estacion);
        
        if (!$estacion) {
            throw new Exception("Estación no encontrada");
        }
        
        // Obtener componentes de la estación
        $query_componentes = "SELECT DISTINCT
                                tipo_componente
                              FROM componentes 
                              WHERE id_estacion = $id_estacion 
                              ORDER BY tipo_componente";
        
        $result_componentes = pg_query($this->conn, $query_componentes);
        
        $componentes = array();
        if ($result_componentes) {
            while ($row = pg_fetch_assoc($result_componentes)) {
                $componentes[] = $row;
            }
        }
        
        // Obtener sensores de la estación
        $query_sensores = "SELECT DISTINCT
                             tipo_sensor
                           FROM sensores 
                           WHERE id_estacion = $id_estacion 
                           ORDER BY tipo_sensor";
        
        $result_sensores = pg_query($this->conn, $query_sensores);
        
        $sensores = array();
        if ($result_sensores) {
            while ($row = pg_fetch_assoc($result_sensores)) {
                $sensores[] = $row;
            }
        }
        
        // Combinar toda la información
        $estacion_completa = array(
            'estacion' => $estacion,
            'componentes' => $componentes,
            'sensores' => $sensores
        );
        
        return $estacion_completa;
    }
    
    /**
     * Obtiene estadísticas generales de las estaciones
     * @return array Estadísticas
     */
    public function obtenerEstadisticasGenerales() {
        $query = "SELECT 
                    COUNT(*) as total_estaciones,
                    COUNT(CASE WHEN estado_activo = true THEN 1 END) as estaciones_activas,
                    COUNT(CASE WHEN estado_activo = false THEN 1 END) as estaciones_inactivas,
                    COUNT(DISTINCT provincia) as provincias_con_estaciones
                  FROM estaciones";
        
        $result = pg_query($this->conn, $query);
        
        if (!$result) {
            throw new Exception("Error al obtener estadísticas: " . pg_last_error($this->conn));
        }
        
        return pg_fetch_assoc($result);
    }
    
    /**
     * Valida coordenadas dentro de Ecuador
     * @param float $lat Latitud
     * @param float $lng Longitud
     * @return bool True si están en Ecuador
     */
    public function validarCoordenadasEcuador($lat, $lng) {
        // Límites aproximados de Ecuador continental
        $lat_min = -5.0;
        $lat_max = 2.0;
        $lng_min = -92.0;
        $lng_max = -75.0;
        
        $es_valida = ($lat >= $lat_min && $lat <= $lat_max && $lng >= $lng_min && $lng <= $lng_max);
        error_log("🗺️  Validación coords (" . $lat . ", " . $lng . "): " . ($es_valida ? "VÁLIDA" : "INVÁLIDA"));
        
        return $es_valida;
    }
    
    /**
     * Formatea coordenadas para el mapa
     * @param float $lat Latitud
     * @param float $lng Longitud
     * @return array Coordenadas formateadas
     */
    public function formatearCoordenadas($lat, $lng) {
        return [
            'lat' => floatval($lat),
            'lng' => floatval($lng),
            'lat_str' => abs($lat) . '°' . ($lat < 0 ? 'S' : 'N'),
            'lng_str' => abs($lng) . '°' . ($lng < 0 ? 'W' : 'E')
        ];
    }
    
    /**
     * Formatea fecha para mostrar
     * @param string $fecha Fecha en formato SQL
     * @return string Fecha formateada
     */
    public function formatearFecha($fecha) {
        if (empty($fecha) || $fecha === null) {
            return 'No disponible';
        }
        
        $timestamp = strtotime($fecha);
        if ($timestamp === false) {
            return $fecha;
        }
        
        return date('Y-m-d H:i:s', $timestamp);
    }
    
    /**
     * Obtiene provincias con estaciones
     * @return array Lista de provincias
     */
    public function obtenerProvinciasConEstaciones() {
        $query = "SELECT DISTINCT 
                    provincia,
                    COUNT(*) as total_estaciones
                  FROM estaciones 
                  WHERE latitud IS NOT NULL 
                    AND longitud IS NOT NULL 
                    AND latitud != 0 
                    AND longitud != 0
                  GROUP BY provincia 
                  ORDER BY total_estaciones DESC";
        
        $result = pg_query($this->conn, $query);
        
        if (!$result) {
            error_log("❌ Error al obtener provincias: " . pg_last_error($this->conn));
            return array();
        }
        
        $provincias = array();
        while ($row = pg_fetch_assoc($result)) {
            $provincias[] = $row;
        }
        
        return $provincias;
    }
}
?>