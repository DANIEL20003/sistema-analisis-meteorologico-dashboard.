<?php
// model/MNotificaciones.php
// MODELO PARA OBTENER NOTIFICACIONES DE ERRORES

require_once dirname(__DIR__) . '/config/conexion.php';

class MNotificaciones {
    private $conn;
    
    public function __construct() {
        global $conn;
        
        if (!$conn) {
            throw new Exception("No hay conexión a la base de datos");
        }
        
        $this->conn = $conn;
    }
    
    /**
     * Obtiene las últimas 15 notificaciones de errores del tiempo real
     * @return array Lista de notificaciones con datos completos
     */
    public function obtenerUltimasNotificaciones($limite = 15) {
        $query = "
            SELECT 
                l.id_log,
                l.id_carga,
                l.mensaje,
                l.fecha,
                l.tipo_nivel,
                e.nombre AS nombre_estacion,
                e.codigo AS codigo_estacion,
                c.nombre_archivo,
                c.tipo_nivel AS tipo_carga
            FROM logs_carga_archivos l
            INNER JOIN cargas_archivos c ON l.id_carga = c.id_carga
            INNER JOIN estaciones e ON c.id_estacion = e.id_estacion
            WHERE l.tipo_nivel = 'L2TR'
            ORDER BY l.fecha DESC
            LIMIT $1
        ";
        
        $result = pg_query_params($this->conn, $query, [$limite]);
        
        if (!$result) {
            throw new Exception("Error al obtener notificaciones: " . pg_last_error($this->conn));
        }
        
        $notificaciones = [];
        while ($row = pg_fetch_assoc($result)) {
            // Extraer información del mensaje
            $info = $this->extraerInfoMensaje($row['mensaje']);
            
            $notificaciones[] = [
                'id_log' => intval($row['id_log']),
                'id_carga' => intval($row['id_carga']),
                'nombre_estacion' => $row['nombre_estacion'],
                'codigo_estacion' => $row['codigo_estacion'],
                'mensaje_completo' => $row['mensaje'],
                'error_tipo' => $info['error_tipo'],
                'error_descripcion' => $info['error_descripcion'],
                'variables_faltantes' => $info['variables_faltantes'],
                'fecha' => $row['fecha'],
                'fecha_formateada' => $this->formatearFechaRelativa($row['fecha']),
                'tipo_nivel' => $row['tipo_nivel']
            ];
        }
        
        return $notificaciones;
    }
    
    /**
     * Cuenta el total de notificaciones de error
     * @return int Total de notificaciones
     */
    public function contarNotificaciones() {
        $query = "
            SELECT COUNT(*) as total
            FROM logs_carga_archivos
            WHERE tipo_nivel = 'L2TR'
        ";
        
        $result = pg_query($this->conn, $query);
        
        if (!$result) {
            return 0;
        }
        
        $row = pg_fetch_assoc($result);
        return intval($row['total']);
    }
    
    /**
     * Extrae información específica del mensaje de error
     * @param string $mensaje Mensaje completo del log
     * @return array Información estructurada del error
     */
    private function extraerInfoMensaje($mensaje) {
        $info = [
            'error_tipo' => 'ERROR_GENERAL',
            'error_descripcion' => 'Error no especificado',
            'variables_faltantes' => []
        ];
        
        // Detectar tipo de error
        if (strpos($mensaje, 'VARIABLES NO REGISTRADAS') !== false) {
            $info['error_tipo'] = 'VARIABLES_FALTANTES';
            $info['error_descripcion'] = 'Variables no registradas en el sistema';
            
            // Extraer lista de variables faltantes
            if (preg_match('/Variables faltantes: \[(.*?)\]/', $mensaje, $matches)) {
                $variables = explode(', ', $matches[1]);
                $info['variables_faltantes'] = array_map('trim', $variables);
            }
            
        } elseif (strpos($mensaje, 'DUPLICADO') !== false) {
            $info['error_tipo'] = 'DUPLICADO';
            $info['error_descripcion'] = 'Registros duplicados';
            
        } elseif (strpos($mensaje, 'SIN_DATOS') !== false || strpos($mensaje, 'sin registros válidos') !== false) {
            $info['error_tipo'] = 'SIN_DATOS';
            $info['error_descripcion'] = 'Archivo sin datos válidos';
            
        } elseif (strpos($mensaje, 'SIN_MAPEO') !== false) {
            $info['error_tipo'] = 'SIN_MAPEO';
            $info['error_descripcion'] = 'Sin columnas coincidentes';
            
        } else {
            $info['error_descripcion'] = 'Error en procesamiento';
        }
        
        return $info;
    }
    
    /**
     * Formatea la fecha en formato relativo (hace X minutos/horas/días)
     * @param string $fecha Fecha en formato timestamp
     * @return string Fecha formateada
     */
    private function formatearFechaRelativa($fecha) {
        $timestamp = strtotime($fecha);
        $diferencia = time() - $timestamp;
        
        if ($diferencia < 60) {
            return 'Hace ' . $diferencia . ' seg';
        } elseif ($diferencia < 3600) {
            $minutos = floor($diferencia / 60);
            return 'Hace ' . $minutos . ' min';
        } elseif ($diferencia < 86400) {
            $horas = floor($diferencia / 3600);
            return 'Hace ' . $horas . ' h';
        } elseif ($diferencia < 604800) {
            $dias = floor($diferencia / 86400);
            return 'Hace ' . $dias . ' día' . ($dias > 1 ? 's' : '');
        } else {
            return date('d/m/Y H:i', $timestamp);
        }
    }
}
?>