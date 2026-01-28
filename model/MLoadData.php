<?php
// model/MLoadData.php
// --- CORRECCIÓN AQUÍ: Usar __DIR__ para ruta absoluta ---
require_once __DIR__ . '/../config/conexion.php';
class MLoadData {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn; 
    }
    
    // =======================================================
    // 1. OBTENER ESTACIONES
    // =======================================================
    public function obtenerEstaciones() {
        $query = "SELECT id_estacion, codigo, nombre, provincia, canton
                  FROM estaciones 
                  WHERE estado_activo = true 
                  ORDER BY nombre ASC";
        
        pg_prepare($this->conn, "obtener_estaciones", $query);
        $result = pg_execute($this->conn, "obtener_estaciones", []);
        
        if (!$result) {
            throw new Exception("Error al obtener estaciones: " . pg_last_error($this->conn));
        }
        
        $estaciones = [];
        while ($row = pg_fetch_assoc($result)) {
            $estaciones[] = [
                'id_estacion' => intval($row['id_estacion']),
                'codigo' => $row['codigo'],
                'nombre' => $row['nombre'],
                'provincia' => $row['provincia'],
                'canton' => $row['canton']
            ];
        }
        return $estaciones;
    }
    
    // =======================================================
    // 2. VERIFICAR CONEXIÓN
    // =======================================================
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
    
    // =======================================================
    // 3. REGISTRO DE DESCARGA
    // =======================================================
    public function registrarDescarga($data) {
        // Asegurarse de que el id_estacion está presente, si no, buscarlo por id_carga
        if (empty($data['id_estacion'])) {
             // Lógica opcional para buscar estación si no viene en el formulario
             // Por defecto usaremos 1 si no se encuentra
             $data['id_estacion'] = 1; 
        }

        $query = "INSERT INTO registro_descargas (
                      nombre_solicitante, institucion, cedula_pasaporte, motivo, 
                      id_estacion, tipo_nivel, tipo_archivo
                  ) VALUES ($1, $2, $3, $4, $5, $6, $7) 
                  RETURNING id_registro_descarga";
        
        $params = [
            $data['nombre_solicitante'], 
            $data['institucion'], 
            $data['cedula_pasaporte'], 
            $data['motivo'], 
            $data['id_estacion'], 
            $data['tipo_nivel'], 
            $data['tipo_archivo']
        ];
        
        // Usamos un nombre único para el prepare statement para evitar colisiones
        $stmtName = "insert_descarga_" . time();
        pg_prepare($this->conn, $stmtName, $query);
        $result = pg_execute($this->conn, $stmtName, $params);
        
        if (!$result) {
            throw new Exception("Error al registrar la descarga: " . pg_last_error($this->conn));
        }
        
        return pg_fetch_result($result, 0, 'id_registro_descarga');
    }
    
    // =======================================================
    // 4. OBTENER RUTA DEL ARCHIVO (Legacy/Backup)
    // =======================================================
    public function get_ruta_archivo_por_id_carga($id_carga) {
        // Esta función se mantiene por compatibilidad, aunque ahora usamos get_datos_meteorologicos
        $tipo_nivel = 'L1'; 
        $nombre_archivo_simulado = "datos_L1_Estacion_". $id_carga .".csv";
        $id_estacion_simulada = 4;
        
        $ruta_real = dirname(__DIR__) . "/uploads/" . strtolower($tipo_nivel) . "/" . $nombre_archivo_simulado;
        
        return [
            'ruta' => $ruta_real,
            'nombre_archivo' => $nombre_archivo_simulado,
            'id_estacion' => $id_estacion_simulada
        ];
    }
    
   // =======================================================
    // 5. OBTENER ARCHIVOS (LÓGICA HÍBRIDA: DB vs CARPETA)
    // =======================================================
    public function get_archivos_cargados($id_estacion, $nivel, $tipo_seleccion) {
        try {
            // --- CASO 1: DATOS CRUDOS (Escanear carpeta física) ---
            if ($tipo_seleccion === 'CRUDOS') {
                // Definir ruta: ../uploads/L0/
                $folder = __DIR__ . '/../uploads/' . strtoupper($nivel) . '/';
                
                if (!is_dir($folder)) {
                    return []; // La carpeta no existe
                }

                // Buscar todos los archivos .csv
                $archivos_fisicos = glob($folder . "*.csv");
                $resultados = [];
                $id_ficticio = 1;

                foreach ($archivos_fisicos as $ruta_completa) {
                    $nombre_archivo = basename($ruta_completa);
                    
                    // Opcional: Filtro rápido de "Status Invalid"
                    // Si el archivo es muy pequeño (<1KB) o tiene nombre de error, lo saltamos
                    if (filesize($ruta_completa) < 100) continue; 

                    $resultados[] = [
                        // Generamos un ID temporal o usamos el nombre como identificador
                        'id_carga' => 'FILE_' . $id_ficticio++, 
                        'nombre_archivo' => $nombre_archivo,
                        'tipo_nivel' => $nivel,
                        'fecha_carga' => date("Y-m-d H:i:s", filemtime($ruta_completa)), // Fecha del archivo
                        'total_registros' => 'N/A', // No calculado para no abrir todos los archivos
                        'tipo_dato' => 'CRUDO',
                        'origen' => 'FISICO' // Marca para el controlador
                    ];
                }
                
                // Ordenar por fecha (más reciente primero)
                usort($resultados, function($a, $b) {
                    return strtotime($b['fecha_carga']) - strtotime($a['fecha_carga']);
                });

                return $resultados;
            } 
            
            // --- CASO 2: DATOS LIMPIOS (Consulta a Base de Datos) ---
            else {
                $conectar = $this->conn;
                $datos_table = "datos_" . strtolower($nivel);
                
                $sql = "SELECT c.id_carga, c.nombre_archivo, c.tipo_nivel, c.fecha_carga, c.total_registros, 'LIMPIO' as tipo_dato
                        FROM cargas_archivos c
                        WHERE c.tipo_nivel = $1 AND c.estado_carga = 'COMPLETADO' AND
                        EXISTS (SELECT 1 FROM registros r JOIN $datos_table dl ON r.id_registro = dl.id_registro WHERE r.id_carga = c.id_carga)
                        ORDER BY c.fecha_carga DESC";
                
                // Usar un nombre único para evitar colisiones de prepared statements
                $stmtName = "get_files_" . $nivel . "_" . ($tipo_seleccion);
                
                // Verificar si ya existe el statement (en pgsql puro es difícil, mejor usar query directo o nombre único)
                // Para simplificar y evitar errores de "statement already exists", usamos pg_query_params
                $result = pg_query_params($conectar, $sql, [$nivel]);

                if (!$result) throw new Exception("Error SQL: " . pg_last_error($conectar));
                return pg_fetch_all($result) ?: [];
            }

        } catch (Exception $e) {
            error_log("Error en get_archivos_cargados: " . $e->getMessage());
            return [];
        }
    }

    // =======================================================
    // 6. OBTENER DATOS REALES (Para Preview y Descarga - NUEVO)
    // =======================================================
    public function get_datos_meteorologicos($id_carga, $limit = null) {
        // 1. Obtener info de la carga
        $queryInfo = "SELECT tipo_nivel, nombre_archivo FROM cargas_archivos WHERE id_carga = $1";
        // Usar pg_query_params directamente evita problemas de nombres de statements repetidos
        $resInfo = pg_query_params($this->conn, $queryInfo, [$id_carga]);
        
        if (!$resInfo || pg_num_rows($resInfo) === 0) {
            throw new Exception("Carga no encontrada.");
        }
        
        $info = pg_fetch_assoc($resInfo);
        $nivel = strtolower($info['tipo_nivel']); // l0, l1, l2
        $tabla_datos = "datos_" . $nivel;
        
        // 2. Construir consulta dinámica
        $limitClause = $limit ? "LIMIT $limit" : "";
        
        $sql = "SELECT r.fecha_hora, d.* FROM $tabla_datos d
                JOIN registros r ON d.id_registro = r.id_registro
                WHERE r.id_carga = $1
                ORDER BY r.fecha_hora ASC
                $limitClause";

        $result = pg_query_params($this->conn, $sql, [$id_carga]);
        
        if (!$result) {
            throw new Exception("Error al consultar datos: " . pg_last_error($this->conn));
        }
        
        $datos = pg_fetch_all($result);
        
        // Limpieza de columnas ID
        if ($datos) {
            foreach ($datos as &$fila) {
                unset($fila['id_registro']);
                foreach ($fila as $key => $val) {
                    if (strpos($key, 'id_dato') === 0) unset($fila[$key]);
                }
            }
        }
        
        return $datos ?: [];
    }
    // =======================================================
    // 7. LEER CSV FÍSICO (OPTIMIZADO: LIMITE DE COLUMNAS Y FILAS)
    // =======================================================
    public function previewCsvContent($file_path, $max_rows = 10) {
        if (!file_exists($file_path)) {
            throw new Exception("Archivo no encontrado: " . basename($file_path));
        }

        $datos_preview = [];
        $file_handle = fopen($file_path, 'r');
        
        if ($file_handle === FALSE) throw new Exception("No se puede leer el archivo.");

        // 1. Detectar separador (Excel usa ';' en regiones latinas, estándar es ',')
        $primera_linea = fgets($file_handle);
        rewind($file_handle); // Volver al inicio
        $delimitador = (substr_count($primera_linea, ';') > substr_count($primera_linea, ',')) ? ';' : ',';

        // 2. Leer encabezado
        $header = fgetcsv($file_handle, 1000, $delimitador);
        
        // OPTIMIZACIÓN: Solo las primeras 5 columnas
        $limit_cols = 5;
        $header_cut = array_slice($header, 0, $limit_cols); 

        // 3. Leer filas
        $row_count = 0;
        while (($row = fgetcsv($file_handle, 1000, $delimitador)) !== FALSE && $row_count < $max_rows) {
            // Cortar la fila también a 5 columnas
            $row_cut = array_slice($row, 0, $limit_cols);
            
            // Combinar con encabezado si es posible
            if (count($header_cut) === count($row_cut)) {
                $datos_preview[] = array_combine($header_cut, $row_cut);
            } else {
                $datos_preview[] = $row_cut;
            }
            $row_count++;
        }

        fclose($file_handle);
        
        return [
            'header' => $header_cut, // Solo enviamos las cabeceras cortadas
            'data' => $datos_preview
        ];
    }
    // =======================================================
    // 8. OBTENER LOGS (Para VADUser)
    // =======================================================
    public function get_logs_descargas() {
        $sql = "SELECT * FROM registro_descargas ORDER BY fecha_descarga DESC LIMIT 200";
        $res = pg_query($this->conn, $sql);
        return pg_fetch_all($res) ?: [];
    }

    // =======================================================
    // 9. ELIMINAR ARCHIVO (NUEVO)
    // =======================================================
    public function eliminarArchivo($id, $nombre, $nivel, $origen) {
        if ($origen === 'FISICO') {
            // Eliminar archivo físico de la carpeta uploads
            $ruta = __DIR__ . '/../uploads/' . strtoupper($nivel) . '/' . basename($nombre);
            if (file_exists($ruta)) {
                return unlink($ruta); // Borra el archivo del disco
            }
            return false; // No existía
        } else {
            // Eliminar de BD (Limpios)
            // PostgreSQL con ON DELETE CASCADE borrará automáticamente los datos en datos_l0/l1...
            $query = "DELETE FROM cargas_archivos WHERE id_carga = $1";
            pg_prepare($this->conn, "del_file_".$id, $query);
            $res = pg_execute($this->conn, "del_file_".$id, [$id]);
            return $res !== false;
        }
    }

} // <--- FIN DE LA CLASE (Llave de cierre correcta)
?>