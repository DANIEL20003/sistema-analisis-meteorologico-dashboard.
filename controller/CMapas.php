<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', '0');
ob_start();

require_once('../config/database.php');
require_once('../model/MMapas.php');

class CMapas
{
    private $db;
    private $model;
    private $database;

    public function __construct()
    {
        $this->database = new Database();
        $this->db = $this->database->getConnection();
        $this->model = new MMapas($this->db);
    }

    public function handleRequest()
{
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');

    $action = $_GET['action'] ?? '';

    try {
        switch ($action) {
            case 'get_estaciones':
                $this->getEstaciones();
                break;

            case 'get_anios':
                $this->getAnios();
                break;

            case 'get_meses':
                $this->getMeses();
                break;

            case 'get_datos_mapas':
                $this->getDatosMapasConCoordenadas();
                break;

            // ✅ NUEVO: Rango de años global
            case 'get_rango_anios_global':
                $this->getRangoAniosGlobal();
                break;

            // ✅ NUEVO: Meses disponibles globalmente
            case 'get_meses_global':
                $this->getMesesGlobal();
                break;

            // ✅ NUEVO: Datos de todas las estaciones
            case 'get_datos_todas_estaciones':
                $this->getDatosTodasEstaciones();
                break;

            default:
                throw new Exception('Acción no válida');
        }

    } catch (Exception $e) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * 📅 GET: Obtener rango de años global (mínimo y máximo)
 */
private function getRangoAniosGlobal()
{
    try {
        $rango = $this->model->obtenerRangoAniosGlobal();

        ob_clean();
        echo json_encode([
            'success' => true,
            'data' => $rango
        ]);

    } catch (Exception $e) {
        throw new Exception('Error al obtener rango de años: ' . $e->getMessage());
    }
}
/**
 * 📆 GET: Obtener meses disponibles globalmente para un año
 * Parámetros: anio
 */
private function getMesesGlobal()
{
    $anio = $_GET['anio'] ?? '';

    if (empty($anio)) {
        throw new Exception('Debe especificar un año');
    }

    try {
        $meses = $this->model->obtenerMesesDisponiblesGlobal($anio);

        // Convertir números a nombres
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
            12 => 'Diciembre',
            13 => 'Total Anual'
        ];

        $mesesDetalle = [];
        foreach ($meses as $mes) {
            $mesesDetalle[] = [
                'numero' => $mes,
                'nombre' => $nombres_meses[$mes] ?? "Mes $mes"
            ];
        }

        ob_clean();
        echo json_encode([
            'success' => true,
            'data' => $mesesDetalle,
            'total' => count($meses)
        ]);

    } catch (Exception $e) {
        throw new Exception('Error al obtener meses globales: ' . $e->getMessage());
    }
}
/**
 * 🗺️ POST: Obtener datos meteorológicos de TODAS las estaciones
 * Body JSON: {anio, mes, tipo_dato}
 */
private function getDatosTodasEstaciones()
{
    try {
        // Leer JSON del body
        $json = file_get_contents('php://input');
        $params = json_decode($json, true);

        // Validar parámetros obligatorios
        if (
            !isset($params['anio']) || 
            !isset($params['mes']) || 
            !isset($params['tipo_dato'])
        ) {
            throw new Exception('Faltan parámetros obligatorios');
        }

        $anio = intval($params['anio']);
        $mes = intval($params['mes']);
        $tipo_dato = strtoupper($params['tipo_dato']);

        // Validaciones
        if (!in_array($tipo_dato, ['MAX', 'AVG', 'MIN'])) {
            throw new Exception('Tipo de dato inválido');
        }

        if ($anio < 1900 || $anio > 2100) {
            throw new Exception('Año fuera de rango válido');
        }

        if ($mes < 1 || $mes > 13) {
            throw new Exception('Mes inválido');
        }

        // Llamar al modelo
        $estaciones = $this->model->obtenerDatosTodasEstaciones($anio, $mes, $tipo_dato);

        if (empty($estaciones)) {
            throw new Exception('No se encontraron datos para los filtros seleccionados');
        }

        // Respuesta exitosa
        ob_clean();
        echo json_encode([
            'success' => true,
            'data' => $estaciones,
            'total_estaciones' => count($estaciones)
        ]);

    } catch (Exception $e) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}


    /**
     * 🏢 GET: Obtener todas las estaciones con datos disponibles
     */
    private function getEstaciones()
    {
        try {
            $estaciones = $this->model->obtenerEstacionesConDatos();

            ob_clean();
            echo json_encode([
                'success' => true,
                'data' => $estaciones,
                'total' => count($estaciones)
            ]);

        } catch (Exception $e) {
            throw new Exception('Error al obtener estaciones: ' . $e->getMessage());
        }
    }

    /**
 * 🗺️ POST: Obtener datos meteorológicos CON coordenadas de estación
 * Body JSON: {id_estacion, anio, mes, tipo_dato}
 */
private function getDatosMapasConCoordenadas()
{
    try {
        // Leer JSON del body
        $json = file_get_contents('php://input');
        $params = json_decode($json, true);

        // Validar parámetros obligatorios
        if (
            !isset($params['id_estacion']) || 
            !isset($params['anio']) || 
            !isset($params['mes']) || 
            !isset($params['tipo_dato'])
        ) {
            throw new Exception('Faltan parámetros obligatorios');
        }

        $id_estacion = intval($params['id_estacion']);
        $anio = intval($params['anio']);
        $mes = intval($params['mes']);
        $tipo_dato = strtoupper($params['tipo_dato']);

        // Validaciones
        if (!in_array($tipo_dato, ['MAX', 'AVG', 'MIN'])) {
            throw new Exception('Tipo de dato inválido');
        }

        if ($anio < 1900 || $anio > 2100) {
            throw new Exception('Año fuera de rango válido');
        }

        if ($mes < 1 || $mes > 13) {
            throw new Exception('Mes inválido');
        }

        // Llamar al modelo
        $datos = $this->model->obtenerDatosMapasConCoordenadas(
            $id_estacion, 
            $anio, 
            $mes, 
            $tipo_dato
        );

        if (!$datos) {
            throw new Exception('No se encontraron datos para los filtros seleccionados');
        }

        // Respuesta exitosa
        ob_clean();
        echo json_encode([
            'success' => true,
            'data' => $datos
        ]);

    } catch (Exception $e) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

    /**
     * 📅 GET: Obtener años disponibles para una estación
     * Parámetros: id_estacion
     */
    private function getAnios()
    {
        $id_estacion = $_GET['id_estacion'] ?? '';

        if (empty($id_estacion)) {
            throw new Exception('Debe especificar una estación');
        }

        try {
            $anios = $this->model->obtenerAniosDisponibles($id_estacion);

            ob_clean();
            echo json_encode([
                'success' => true,
                'data' => $anios,
                'total' => count($anios)
            ]);

        } catch (Exception $e) {
            throw new Exception('Error al obtener años: ' . $e->getMessage());
        }
    }

    /**
     * 📆 GET: Obtener meses disponibles para una estación y año
     * Parámetros: id_estacion, anio
     */
    private function getMeses()
    {
        $id_estacion = $_GET['id_estacion'] ?? '';
        $anio = $_GET['anio'] ?? '';

        if (empty($id_estacion) || empty($anio)) {
            throw new Exception('Debe especificar estación y año');
        }

        try {
            $meses = $this->model->obtenerMesesDisponibles($id_estacion, $anio);

            // Convertir números a nombres para facilitar uso
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
                12 => 'Diciembre',
                13 => 'Total Anual'
            ];

            $mesesDetalle = [];
            foreach ($meses as $mes) {
                $mesesDetalle[] = [
                    'numero' => $mes,
                    'nombre' => $nombres_meses[$mes] ?? "Mes $mes"
                ];
            }

            ob_clean();
            echo json_encode([
                'success' => true,
                'data' => $mesesDetalle,
                'meses_numeros' => $meses,
                'total' => count($meses)
            ]);

        } catch (Exception $e) {
            throw new Exception('Error al obtener meses: ' . $e->getMessage());
        }
    }

    /**
     * 🗺️ GET: Obtener información detallada de una estación
     * Parámetros: id_estacion
     */
    private function getInfoEstacion()
    {
        $id_estacion = $_GET['id_estacion'] ?? '';

        if (empty($id_estacion)) {
            throw new Exception('Debe especificar una estación');
        }

        try {
            $info = $this->model->obtenerInfoEstacion($id_estacion);

            if (!$info) {
                throw new Exception('Estación no encontrada');
            }

            ob_clean();
            echo json_encode([
                'success' => true,
                'data' => $info
            ]);

        } catch (Exception $e) {
            throw new Exception('Error al obtener información: ' . $e->getMessage());
        }
    }

    /**
     * 📊 GET: Obtener resumen de datos disponibles
     * Parámetros: id_estacion
     */
    private function getResumenDatos()
    {
        $id_estacion = $_GET['id_estacion'] ?? '';

        if (empty($id_estacion)) {
            throw new Exception('Debe especificar una estación');
        }

        try {
            $resumen = $this->model->obtenerResumenDatosEstacion($id_estacion);

            ob_clean();
            echo json_encode([
                'success' => true,
                'data' => $resumen
            ]);

        } catch (Exception $e) {
            throw new Exception('Error al obtener resumen: ' . $e->getMessage());
        }
    }

    public function getDatosMapas()
    {
        try {
            // Leer JSON del body
            $json = file_get_contents('php://input');
            $params = json_decode($json, true);

            // Validar parámetros obligatorios
            if (
                !isset($params['id_estacion']) ||
                !isset($params['anio']) ||
                !isset($params['mes']) ||
                !isset($params['tipo_dato'])
            ) {
                throw new Exception('Faltan parámetros obligatorios');
            }

            $id_estacion = intval($params['id_estacion']);
            $anio = intval($params['anio']);
            $mes = intval($params['mes']);
            $tipo_dato = strtoupper($params['tipo_dato']);

            // Validar tipo_dato
            if (!in_array($tipo_dato, ['MAX', 'AVG', 'MIN'])) {
                throw new Exception('Tipo de dato inválido. Debe ser MAX, AVG o MIN');
            }

            // Validar año
            if ($anio < 1900 || $anio > 2100) {
                throw new Exception('Año fuera de rango válido');
            }

            // Validar mes
            if ($mes < 1 || $mes > 13) {
                throw new Exception('Mes inválido. Debe estar entre 1-13');
            }

            // Llamar al modelo
            $datos = $this->model->obtenerDatosMapasConCoordenadas(
                $id_estacion,
                $anio,
                $mes,
                $tipo_dato
            );

            if (!$datos) {
                throw new Exception('No se encontraron datos para los filtros seleccionados');
            }

            // Respuesta exitosa
            ob_clean();
            echo json_encode([
                'success' => true,
                'data' => $datos,
                'mensaje' => 'Datos obtenidos correctamente'
            ]);

        } catch (Exception $e) {
            ob_clean();
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'code' => 'ERROR_GET_DATOS_MAPAS'
            ]);
        }
    }
    /**
     * 🔍 GET: Verificar si existen datos para combinación específica
     * Parámetros: id_estacion, anio, mes (opcional)
     */
    private function verificarDatos()
    {
        $id_estacion = $_GET['id_estacion'] ?? '';
        $anio = $_GET['anio'] ?? '';
        $mes = $_GET['mes'] ?? null;

        if (empty($id_estacion) || empty($anio)) {
            throw new Exception('Debe especificar estación y año');
        }

        try {
            $existe = $this->model->verificarDatosDisponibles($id_estacion, $anio, $mes);

            ob_clean();
            echo json_encode([
                'success' => true,
                'existe' => $existe,
                'mensaje' => $existe ? 'Datos disponibles' : 'No hay datos para esta combinación'
            ]);

        } catch (Exception $e) {
            throw new Exception('Error al verificar datos: ' . $e->getMessage());
        }
    }
}

// Inicializar controlador
if ($_SERVER['REQUEST_METHOD'] === 'GET' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new CMapas();
    $controller->handleRequest();
}
?>