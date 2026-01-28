<?php
// model/Mindex.php
require_once '../config/conexion.php';

class MIndex {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    /**
     * Obtener todas las estaciones meteorológicas con sus rutas de imágenes
     */
    public function obtenerEstacionesConImagenes() {
        $query = "SELECT 
                    id_estacion,
                    codigo,
                    nombre,
                    provincia,
                    canton,
                    ruta_fotografia,
                    ruta_mapa
                  FROM estaciones 
                  WHERE estado_activo = true 
                  ORDER BY nombre ASC";
        
        pg_prepare($this->conn, "obtener_estaciones_imagenes", $query);
        $result = pg_execute($this->conn, "obtener_estaciones_imagenes", []);
        
        if (!$result) {
            throw new Exception("Error al obtener estaciones con imágenes: " . pg_last_error($this->conn));
        }
        
        $estaciones = [];
        while ($row = pg_fetch_assoc($result)) {
            $estaciones[] = [
                'id_estacion' => intval($row['id_estacion']),
                'codigo' => $row['codigo'],
                'nombre' => $row['nombre'],
                'provincia' => $row['provincia'],
                'canton' => $row['canton'],
                'ruta_fotografia' => $row['ruta_fotografia'],
                'ruta_mapa' => $row['ruta_mapa']
            ];
        }
        
        return $estaciones;
    }
    
    /**
     * Obtener solo las fotografías válidas de las estaciones
     */
    public function obtenerFotografiasEstaciones() {
        $estaciones = $this->obtenerEstacionesConImagenes();
        $fotografias = [];
        
        foreach ($estaciones as $estacion) {
            $ruta_foto = $estacion['ruta_fotografia'];
            
            // Solo agregar si la ruta existe y no está vacía
            if (!empty($ruta_foto)) {
                // Verificar ruta completa desde la raíz del proyecto
                $ruta_completa = __DIR__ . '/../' . $ruta_foto;
                
                if (file_exists($ruta_completa) && is_readable($ruta_completa)) {
                    $fotografias[] = [
                        'imagen' => $ruta_foto,
                        'estacion' => $estacion['nombre'],
                        'ubicacion' => $estacion['canton'],
                        'codigo' => $estacion['codigo']
                    ];
                }
            }
        }
        
        return $fotografias;
    }
    
    /**
     * Obtener solo los mapas válidos de las estaciones
     */
    public function obtenerMapasEstaciones() {
        $estaciones = $this->obtenerEstacionesConImagenes();
        $mapas = [];
        
        foreach ($estaciones as $estacion) {
            $ruta_mapa = $estacion['ruta_mapa'];
            
            // Solo agregar si la ruta existe y no está vacía
            if (!empty($ruta_mapa)) {
                // Verificar ruta completa desde la raíz del proyecto
                $ruta_completa = __DIR__ . '/../' . $ruta_mapa;
                
                if (file_exists($ruta_completa) && is_readable($ruta_completa)) {
                    $mapas[] = [
                        'imagen' => $ruta_mapa,
                        'estacion' => $estacion['nombre'],
                        'ubicacion' => $estacion['canton'],
                        'codigo' => $estacion['codigo']
                    ];
                }
            }
        }
        
        return $mapas;
    }
    
    /**
     * Verificar conexión con la base de datos
     */
    public function verificarConexion() {
        try {
            $query = "SELECT 1";
            pg_prepare($this->conn, "test_connection", $query);
            $result = pg_execute($this->conn, "test_connection", []);
            
            return $result !== false;
        } catch (Exception $e) {
            error_log("Error de conexión a la base de datos: " . $e->getMessage());
            return false;
        }
    }
}
?>