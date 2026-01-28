<?php

require_once('../config/database.php');

class MMapas
{
    private $conn;
    private $database;

    public function __construct($db = null)
    {
        if ($db) {
            $this->conn = $db;
        } else {
            $this->database = new Database();
            $this->conn = $this->database->getConnection();

            if (!$this->conn) {
                throw new Exception('Error de conexión a la base de datos');
            }
        }
    }

    public function __destruct()
    {
        if ($this->database) {
            $this->database->closeConnection();
        }
    }

    /**
     * 🏢 Obtener todas las estaciones que tienen datos de resúmenes climatológicos
     */
    public function obtenerEstacionesConDatos()
    {
        try {
            $query = "SELECT DISTINCT 
                    e.id_estacion,
                    e.codigo,
                    e.nombre,
                    e.comunidad,
                    e.provincia,
                    e.canton,
                    e.altura_terreno as altitud
                 FROM estaciones e
                 INNER JOIN resumenes_climatologicos r ON e.id_estacion = r.id_estacion
                 WHERE e.estado_activo = true
                 AND e.provincia = 'Chimborazo'
                 ORDER BY e.nombre ASC";

            $result = pg_query($this->conn, $query);

            if (!$result) {
                throw new Exception('Error al obtener estaciones: ' . pg_last_error($this->conn));
            }

            $estaciones = [];
            while ($row = pg_fetch_assoc($result)) {
                $estaciones[] = [
                    'id' => $row['id_estacion'],
                    'codigo' => $row['codigo'],
                    'nombre' => $row['nombre'],
                    'comunidad' => $row['comunidad'],
                    'provincia' => $row['provincia'],
                    'canton' => $row['canton'],
                    'altitud' => $row['altitud']
                ];
            }

            return $estaciones;

        } catch (Exception $e) {
            error_log("❌ Error en obtenerEstacionesConDatos: " . $e->getMessage());
            throw new Exception('Error al obtener estaciones: ' . $e->getMessage());
        }
    }

    /**
 * 📅 Obtener el rango de años disponible en TODAS las estaciones de Chimborazo
 * Retorna array con año_minimo y año_maximo
 */
public function obtenerRangoAniosGlobal()
{
    try {
        $query = "
            SELECT 
                MIN(rc.anio) as anio_minimo,
                MAX(rc.anio) as anio_maximo
            FROM resumenes_climatologicos rc
            INNER JOIN estaciones e ON rc.id_estacion = e.id_estacion
            WHERE e.estado_activo = true
              AND e.provincia = 'Chimborazo'
              AND e.latitud IS NOT NULL
              AND e.longitud IS NOT NULL
        ";

        $result = pg_query($this->conn, $query);

        if (!$result) {
            throw new Exception('Error al obtener rango de años: ' . pg_last_error($this->conn));
        }

        $row = pg_fetch_assoc($result);

        if (!$row || !$row['anio_minimo']) {
            return ['anio_minimo' => null, 'anio_maximo' => null];
        }

        return [
            'anio_minimo' => intval($row['anio_minimo']),
            'anio_maximo' => intval($row['anio_maximo'])
        ];

    } catch (Exception $e) {
        error_log("❌ Error en obtenerRangoAniosGlobal: " . $e->getMessage());
        throw new Exception('Error al obtener rango de años global: ' . $e->getMessage());
    }
}

/**
 * 📆 Obtener todos los meses disponibles para un año específico en TODAS las estaciones
 * Retorna array con números de mes (1-12) + 13 si existe en alguna estación
 */
public function obtenerMesesDisponiblesGlobal($anio)
{
    try {
        $query = "
            SELECT DISTINCT rc.mes
            FROM resumenes_climatologicos rc
            INNER JOIN estaciones e ON rc.id_estacion = e.id_estacion
            WHERE e.estado_activo = true
              AND e.provincia = 'Chimborazo'
              AND e.latitud IS NOT NULL
              AND e.longitud IS NOT NULL
              AND rc.anio = $1
            ORDER BY rc.mes ASC
        ";

        $result = pg_query_params($this->conn, $query, [$anio]);

        if (!$result) {
            throw new Exception('Error al obtener meses globales: ' . pg_last_error($this->conn));
        }

        $meses = [];
        while ($row = pg_fetch_assoc($result)) {
            $mes = intval($row['mes']);
            if ($mes >= 1 && $mes <= 13) {
                $meses[] = $mes;
            }
        }

        return $meses;

    } catch (Exception $e) {
        error_log("❌ Error en obtenerMesesDisponiblesGlobal: " . $e->getMessage());
        throw new Exception('Error al obtener meses globales: ' . $e->getMessage());
    }
}

/**
 * 🗺️ Obtener datos meteorológicos de TODAS las estaciones para un período específico
 * 
 * @param int $anio Año de consulta
 * @param int $mes Mes (1-12) o 13 para promedio anual
 * @param string $tipo_dato 'MAX', 'AVG' o 'MIN'
 * @return array Array con datos de todas las estaciones que tengan información
 */
public function obtenerDatosTodasEstaciones($anio, $mes, $tipo_dato)
{
    try {
        $query = "
            SELECT 
                e.id_estacion,
                e.codigo AS codigo_estacion,
                e.nombre AS nombre_estacion,
                e.comunidad,
                e.canton,
                e.provincia,
                e.latitud,
                e.longitud,
                e.altura_terreno AS altitud,
                rc.temperatura_aire,
                rc.humedad_relativa,
                rc.presion_atmosferica,
                rc.radiacion_global,
                rc.direccion_viento,
                rc.velocidad_viento,
                rc.anio,
                rc.mes,
                rc.tipo_estadistica
            FROM resumenes_climatologicos rc
            INNER JOIN estaciones e ON rc.id_estacion = e.id_estacion
            WHERE 
                rc.anio = $1
                AND rc.mes = $2
                AND rc.tipo_estadistica = $3
                AND e.estado_activo = true
                AND e.provincia = 'Chimborazo'
                AND e.latitud IS NOT NULL
                AND e.longitud IS NOT NULL
            ORDER BY e.nombre ASC
        ";

        $result = pg_query_params($this->conn, $query, [
            $anio,
            $mes,
            $tipo_dato
        ]);

        if (!$result) {
            throw new Exception('Error SQL: ' . pg_last_error($this->conn));
        }

        $estaciones = [];
        
        while ($row = pg_fetch_assoc($result)) {
            $estaciones[] = [
                'estacion' => [
                    'id' => intval($row['id_estacion']),
                    'codigo' => $row['codigo_estacion'],
                    'nombre' => $row['nombre_estacion'],
                    'comunidad' => $row['comunidad'],
                    'canton' => $row['canton'],
                    'provincia' => $row['provincia'],
                    'latitud' => floatval($row['latitud']),
                    'longitud' => floatval($row['longitud']),
                    'altitud' => intval($row['altitud'])
                ],
                'valores' => [
                    'temperatura_aire' => $this->formatearValor($row['temperatura_aire']),
                    'humedad_relativa' => $this->formatearValor($row['humedad_relativa']),
                    'presion_atmosferica' => $this->formatearValor($row['presion_atmosferica']),
                    'radiacion_global' => $this->formatearValor($row['radiacion_global']),
                    'direccion_viento' => $this->formatearValor($row['direccion_viento']),
                    'velocidad_viento' => $this->formatearValor($row['velocidad_viento'])
                ],
                'filtros' => [
                    'anio' => intval($row['anio']),
                    'mes' => intval($row['mes']),
                    'tipo_estadistica' => $row['tipo_estadistica']
                ]
            ];
        }

        return $estaciones;

    } catch (Exception $e) {
        error_log("❌ Error en obtenerDatosTodasEstaciones: " . $e->getMessage());
        throw new Exception('Error al obtener datos de todas las estaciones: ' . $e->getMessage());
    }
}

    /**
     * 📅 Obtener años disponibles para una estación específica
     */
    public function obtenerAniosDisponibles($id_estacion)
    {
        try {
            $query = "SELECT DISTINCT anio
                     FROM resumenes_climatologicos
                     WHERE id_estacion = $1
                     ORDER BY anio DESC";

            $result = pg_query_params($this->conn, $query, [$id_estacion]);

            if (!$result) {
                throw new Exception('Error al obtener años: ' . pg_last_error($this->conn));
            }

            $anios = [];
            while ($row = pg_fetch_assoc($result)) {
                $anios[] = intval($row['anio']);
            }

            return $anios;

        } catch (Exception $e) {
            error_log("❌ Error en obtenerAniosDisponibles: " . $e->getMessage());
            throw new Exception('Error al obtener años: ' . $e->getMessage());
        }
    }

    /**
     * 📆 Obtener meses disponibles para una estación y año específicos
     * Retorna array con números de mes (1-12) + 13 si existe TOTAL
     */
    public function obtenerMesesDisponibles($id_estacion, $anio)
    {
        try {
            $query = "SELECT DISTINCT mes
                     FROM resumenes_climatologicos
                     WHERE id_estacion = $1 AND anio = $2
                     ORDER BY mes ASC";

            $result = pg_query_params($this->conn, $query, [$id_estacion, $anio]);

            if (!$result) {
                throw new Exception('Error al obtener meses: ' . pg_last_error($this->conn));
            }

            $meses = [];
            while ($row = pg_fetch_assoc($result)) {
                $mes = intval($row['mes']);
                // Solo agregar meses válidos (1-12) o 13 (TOTAL)
                if ($mes >= 1 && $mes <= 13) {
                    $meses[] = $mes;
                }
            }

            return $meses;

        } catch (Exception $e) {
            error_log("❌ Error en obtenerMesesDisponibles: " . $e->getMessage());
            throw new Exception('Error al obtener meses: ' . $e->getMessage());
        }
    }

    /**
     * 🗺️ Obtener información detallada de una estación
     */
    public function obtenerInfoEstacion($id_estacion)
    {
        try {
            $query = "SELECT 
                    e.id_estacion,
                    e.codigo,
                    e.nombre,
                    e.comunidad,
                    e.provincia,
                    e.canton,
                    e.altura_terreno as altitud,
                    e.latitud,
                    e.longitud,
                    e.fecha_instalacion,
                    e.estado_activo,
                    e.ruta_fotografia as fotografia,
                    e.ruta_mapa as mapa
                 FROM estaciones e
                 WHERE e.id_estacion = $1";

            $result = pg_query_params($this->conn, $query, [$id_estacion]);

            if (!$result) {
                throw new Exception('Error al obtener info de estación: ' . pg_last_error($this->conn));
            }

            $row = pg_fetch_assoc($result);

            if (!$row) {
                return null;
            }

            return [
                'id' => $row['id_estacion'],
                'codigo' => $row['codigo'],
                'nombre' => $row['nombre'],
                'comunidad' => $row['comunidad'],
                'provincia' => $row['provincia'],
                'canton' => $row['canton'],
                'altitud' => $row['altitud'],
                'latitud' => $row['latitud'],
                'longitud' => $row['longitud'],
                'fecha_instalacion' => $row['fecha_instalacion'],
                'estado_activo' => $row['estado_activo'],
                'fotografia' => $row['fotografia'],
                'mapa' => $row['mapa']
            ];

        } catch (Exception $e) {
            error_log("❌ Error en obtenerInfoEstacion: " . $e->getMessage());
            throw new Exception('Error al obtener información de estación: ' . $e->getMessage());
        }
    }
    public function obtenerDatosMapas($id_estacion, $anio, $mes, $tipo_dato)
    {
        try {
            // Query para obtener los datos meteorológicos
            $query = "SELECT 
                    anio,
                    mes,
                    tipo,
                    temperatura,
                    humedad,
                    presion,
                    radiacion,
                    direccion_viento,
                    velocidad_viento
                FROM datos_meteorologicos
                WHERE id_estacion = :id_estacion
                  AND anio = :anio
                  AND mes = :mes
                  AND tipo = :tipo_dato
                LIMIT 1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_estacion', $id_estacion, PDO::PARAM_INT);
            $stmt->bindParam(':anio', $anio, PDO::PARAM_INT);
            $stmt->bindParam(':mes', $mes, PDO::PARAM_INT);
            $stmt->bindParam(':tipo_dato', $tipo_dato, PDO::PARAM_STR);

            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$resultado) {
                return null;
            }

            // Formatear respuesta
            return [
                'temperatura' => floatval($resultado['temperatura']),
                'humedad' => floatval($resultado['humedad']),
                'presion' => floatval($resultado['presion']),
                'radiacion' => floatval($resultado['radiacion']),
                'direccion_viento' => floatval($resultado['direccion_viento']),
                'velocidad_viento' => floatval($resultado['velocidad_viento']),
                'anio' => intval($resultado['anio']),
                'mes' => intval($resultado['mes']),
                'tipo' => $resultado['tipo']
            ];

        } catch (PDOException $e) {
            error_log("Error en obtenerDatosMapas: " . $e->getMessage());
            return null;
        }
    }

    /**
     * 📊 Obtener resumen de datos disponibles por estación
     */
    public function obtenerResumenDatosEstacion($id_estacion)
    {
        try {
            $query = "SELECT 
                        COUNT(DISTINCT anio) as total_anios,
                        COUNT(DISTINCT CASE WHEN mes BETWEEN 1 AND 12 THEN mes END) as total_meses,
                        MIN(anio) as primer_anio,
                        MAX(anio) as ultimo_anio,
                        COUNT(*) as total_registros
                     FROM resumenes_climatologicos
                     WHERE id_estacion = $1";

            $result = pg_query_params($this->conn, $query, [$id_estacion]);

            if (!$result) {
                throw new Exception('Error al obtener resumen: ' . pg_last_error($this->conn));
            }

            $row = pg_fetch_assoc($result);

            return [
                'total_anios' => intval($row['total_anios']),
                'total_meses' => intval($row['total_meses']),
                'primer_anio' => $row['primer_anio'] ? intval($row['primer_anio']) : null,
                'ultimo_anio' => $row['ultimo_anio'] ? intval($row['ultimo_anio']) : null,
                'total_registros' => intval($row['total_registros'])
            ];

        } catch (Exception $e) {
            error_log("❌ Error en obtenerResumenDatosEstacion: " . $e->getMessage());
            throw new Exception('Error al obtener resumen de datos: ' . $e->getMessage());
        }
    }
/**
 * 🗺️ Obtener datos meteorológicos + coordenadas de estación
 * 
 * @param int $id_estacion ID de la estación
 * @param int $anio Año de consulta
 * @param int $mes Mes (1-12) o 13 para promedio anual
 * @param string $tipo_dato 'MAX', 'AVG' o 'MIN'
 * @return array|null Datos formateados o null si no hay resultados
 */
public function obtenerDatosMapasConCoordenadas($id_estacion, $anio, $mes, $tipo_dato)
{
    try {
        $query = "
            SELECT 
                e.id_estacion,
                e.codigo AS codigo_estacion,
                e.nombre AS nombre_estacion,
                e.comunidad,
                e.canton,
                e.provincia,
                e.latitud,
                e.longitud,
                e.altura_terreno AS altitud,
                rc.temperatura_aire,
                rc.humedad_relativa,
                rc.presion_atmosferica,
                rc.radiacion_global,
                rc.direccion_viento,
                rc.velocidad_viento,
                rc.anio,
                rc.mes,
                rc.tipo_estadistica
            FROM resumenes_climatologicos rc
            INNER JOIN estaciones e ON rc.id_estacion = e.id_estacion
            WHERE 
                rc.id_estacion = $1
                AND rc.anio = $2
                AND rc.mes = $3
                AND rc.tipo_estadistica = $4
                AND e.estado_activo = true
                AND e.latitud IS NOT NULL
                AND e.longitud IS NOT NULL
            LIMIT 1
        ";

        $result = pg_query_params($this->conn, $query, [
            $id_estacion,
            $anio,
            $mes,
            $tipo_dato
        ]);

        if (!$result) {
            throw new Exception('Error SQL: ' . pg_last_error($this->conn));
        }

        $row = pg_fetch_assoc($result);

        if (!$row) {
            return null;
        }

        // Formatear respuesta
        return [
            'estacion' => [
                'id' => intval($row['id_estacion']),
                'codigo' => $row['codigo_estacion'],
                'nombre' => $row['nombre_estacion'],
                'comunidad' => $row['comunidad'],
                'canton' => $row['canton'],
                'provincia' => $row['provincia'],
                'latitud' => floatval($row['latitud']),
                'longitud' => floatval($row['longitud']),
                'altitud' => intval($row['altitud'])
            ],
            'valores' => [
                'temperatura_aire' => $this->formatearValor($row['temperatura_aire']),
                'humedad_relativa' => $this->formatearValor($row['humedad_relativa']),
                'presion_atmosferica' => $this->formatearValor($row['presion_atmosferica']),
                'radiacion_global' => $this->formatearValor($row['radiacion_global']),
                'direccion_viento' => $this->formatearValor($row['direccion_viento']),
                'velocidad_viento' => $this->formatearValor($row['velocidad_viento'])
            ],
            'filtros' => [
                'anio' => intval($row['anio']),
                'mes' => intval($row['mes']),
                'tipo_estadistica' => $row['tipo_estadistica']
            ]
        ];

    } catch (Exception $e) {
        error_log("❌ Error en obtenerDatosMapasConCoordenadas: " . $e->getMessage());
        throw new Exception('Error al obtener datos: ' . $e->getMessage());
    }
}


/**
 * 🔧 Formatear valor numérico (convierte NULL a null y limpia decimales)
 */
private function formatearValor($valor)
{
    if ($valor === null || $valor === '') {
        return null;
    }
    return round(floatval($valor), 2);
}
    /**
     * 🔍 Verificar si existen datos para combinación específica
     */
    public function verificarDatosDisponibles($id_estacion, $anio, $mes = null)
    {
        try {
            $query = "SELECT COUNT(*) as total
                     FROM resumenes_climatologicos
                     WHERE id_estacion = $1 AND anio = $2";

            $params = [$id_estacion, $anio];

            if ($mes !== null) {
                $query .= " AND mes = $3";
                $params[] = $mes;
            }

            $result = pg_query_params($this->conn, $query, $params);

            if (!$result) {
                throw new Exception('Error al verificar datos: ' . pg_last_error($this->conn));
            }

            $row = pg_fetch_assoc($result);
            return intval($row['total']) > 0;

        } catch (Exception $e) {
            error_log("❌ Error en verificarDatosDisponibles: " . $e->getMessage());
            return false;
        }
    }
}
?>