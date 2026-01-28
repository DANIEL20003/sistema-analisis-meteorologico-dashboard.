<?php
/**
 * MEstacion.php
 * Modelo para gestión de estaciones y archivos desde el sistema de archivos
 * Responsable de leer carpetas y archivos CSV de las estaciones
 */

class MEstacion
{

    private $uploadsBasePath;

    public function __construct()
    {
        // Establecer la ruta base de uploads
        $this->uploadsBasePath = $this->detectarRutaUploads();
    }

    /**
     * Detecta automáticamente la ruta correcta del directorio uploads
     */
    private function detectarRutaUploads()
    {
        $possiblePaths = [
            __DIR__ . '/../uploads/',
            dirname(__DIR__) . '/uploads/',
            dirname(dirname(__DIR__)) . '/uploads/',
            $_SERVER['DOCUMENT_ROOT'] . '/uploads/'
        ];

        foreach ($possiblePaths as $path) {
            if (is_dir($path)) {
                return realpath($path) . '/';
            }
        }

        throw new Exception("No se encontró el directorio uploads en ninguna ubicación");
    }

    /**
     * Obtiene la lista de estaciones disponibles (carpetas en uploads)
     * @return array Lista de nombres de carpetas/estaciones
     */
    public function obtenerEstacionesDisponibles()
    {
        try {
            if (!is_dir($this->uploadsBasePath)) {
                throw new Exception("Directorio uploads no accesible: " . $this->uploadsBasePath);
            }

            $items = scandir($this->uploadsBasePath);

            if ($items === false) {
                throw new Exception("No se puede leer el directorio uploads");
            }

            $estaciones = [];

            foreach ($items as $item) {
                $fullPath = $this->uploadsBasePath . $item;

                // Solo directorios, excluir . y .. y carpetas del sistema
                if (
                    is_dir($fullPath) && $item !== '.' && $item !== '..' &&
                    $item[0] !== '.' && $item !== '__MACOSX' && $item !== 'temp' &&
                    $item !== 'Limpios' && $item !== 'Crudos'
                ) {
                    $estaciones[] = $item;
                }
            }

            // Ordenar alfabéticamente
            sort($estaciones);

            return $estaciones;

        } catch (Exception $e) {
            error_log("Error en obtenerEstacionesDisponibles: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene los archivos CSV de una estación específica por nivel y tipo
     * @param string $estacion Nombre de la estación
     * @param string $nivel L0, L1 o L2
     * @param string $tipo "Crudos" o "Limpios"
     * @param int|null $anio Año opcional para filtrar archivos
     * @return array Lista de archivos con sus metadatos
     */
    public function obtenerArchivosPorTipo($estacion, $nivel, $tipo, $anio = null)
    {
        try {
            if (empty($estacion)) {
                throw new Exception("El nombre de la estación es requerido");
            }

            if (!in_array($nivel, ['L0', 'L1', 'L2'])) {
                throw new Exception("Nivel no válido. Debe ser L0, L1 o L2");
            }

            if (!in_array($tipo, ['Crudos', 'Limpios'])) {
                throw new Exception("Tipo no válido. Debe ser Crudos o Limpios");
            }

            $dirPath = $this->uploadsBasePath . $estacion . '/' . $tipo . '/' . $nivel . '/';

            if (!is_dir($dirPath)) {
                // No es error, simplemente no hay archivos
                return [];
            }

            $archivos = [];
            $items = scandir($dirPath);

            if ($items === false) {
                error_log("No se puede leer directorio: " . $dirPath);
                return [];
            }

            foreach ($items as $item) {
                if ($item === '.' || $item === '..')
                    continue;

                $filePath = $dirPath . $item;

                // Solo archivos .csv
                if (is_file($filePath) && strtolower(pathinfo($item, PATHINFO_EXTENSION)) === 'csv') {

                    // Si se especificó un año, filtrar por ese año
                    if ($anio !== null) {
                        $anioArchivo = $this->extraerAnioDeNombreArchivo($item);

                        // Solo incluir si el año coincide
                        if ($anioArchivo !== $anio) {
                            continue;
                        }
                    }

                    $archivos[] = $this->obtenerMetadatosArchivo($filePath, $estacion, $nivel, $tipo);
                }
            }

            // Ordenar por fecha de modificación (más reciente primero)
            usort($archivos, function ($a, $b) {
                return $b['fecha_modificacion'] - $a['fecha_modificacion'];
            });

            return $archivos;

        } catch (Exception $e) {
            error_log("Error en obtenerArchivosPorTipo: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene todos los archivos de una estación y nivel (Crudos y Limpios)
     * @param string $estacion Nombre de la estación
     * @param string $nivel L0, L1 o L2
     * @return array ['crudos' => [...], 'limpios' => [...]]
     */
    /**
     * Obtiene todos los archivos de una estación y nivel (Crudos y Limpios)
     * @param string $estacion Nombre de la estación
     * @param string $nivel L0, L1 o L2
     * @param int|null $anio Año opcional para filtrar
     * @return array ['crudos' => [...], 'limpios' => [...]]
     */
    public function obtenerArchivosCompletos($estacion, $nivel, $anio = null)
    {
        try {
            $archivosCrudos = $this->obtenerArchivosPorTipo($estacion, $nivel, 'Crudos', $anio);
            $archivosLimpios = $this->obtenerArchivosPorTipo($estacion, $nivel, 'Limpios', $anio);

            return [
                'crudos' => $archivosCrudos,
                'limpios' => $archivosLimpios,
                'estacion' => $estacion,
                'nivel' => $nivel,
                'anio' => $anio,
                'total_crudos' => count($archivosCrudos),
                'total_limpios' => count($archivosLimpios)
            ];

        } catch (Exception $e) {
            error_log("Error en obtenerArchivosCompletos: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene los archivos CSV de la carpeta Torres de una estación
     * @param string $estacion Nombre de la estación
     * @param string $nivel L0, L1 o L2
     * @return array Lista de archivos con sus metadatos
     */
    public function obtenerArchivosTorres($estacion, $nivel, $anio = null)
    {
        try {
            if (empty($estacion)) {
                throw new Exception("El nombre de la estación es requerido");
            }

            if (!in_array($nivel, ['L0', 'L1', 'L2'])) {
                throw new Exception("Nivel no válido. Debe ser L0, L1 o L2");
            }

            // Ruta: uploads/ESTACION/Torres/NIVEL/
            $dirPath = $this->uploadsBasePath . $estacion . '/Torres/' . $nivel . '/';

            if (!is_dir($dirPath)) {
                return [];
            }

            $archivos = [];
            $items = scandir($dirPath);

            if ($items === false) {
                error_log("No se puede leer directorio de torres: " . $dirPath);
                return [];
            }

            foreach ($items as $item) {
                if ($item === '.' || $item === '..')
                    continue;

                $filePath = $dirPath . $item;

                if (is_file($filePath) && strtolower(pathinfo($item, PATHINFO_EXTENSION)) === 'csv') {

                    // Si se especificó un año, filtrar por ese año
                    if ($anio !== null) {
                        $anioArchivo = $this->extraerAnioDeNombreArchivo($item);

                        if ($anioArchivo !== $anio) {
                            continue;
                        }
                    }

                    $archivos[] = $this->obtenerMetadatosArchivo($filePath, $estacion, $nivel, 'Torres');
                }
            }

            // Ordenar por fecha de modificación (más reciente primero)
            usort($archivos, function ($a, $b) {
                return $b['fecha_modificacion'] - $a['fecha_modificacion'];
            });

            return $archivos;

        } catch (Exception $e) {
            error_log("Error en obtenerArchivosTorres: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene los años disponibles de archivos de Torres
     * @param string $estacion Nombre de la estación
     * @param string $nivel L0, L1 o L2
     * @return array Lista de años únicos ordenados descendentemente
     */
    public function obtenerAniosDisponiblesTorres($estacion, $nivel)
    {
        try {
            if (empty($estacion)) {
                throw new Exception("El nombre de la estación es requerido");
            }

            if (!in_array($nivel, ['L0', 'L1', 'L2'])) {
                throw new Exception("Nivel no válido. Debe ser L0, L1 o L2");
            }

            $dirTorres = $this->uploadsBasePath . $estacion . '/Torres/' . $nivel . '/';

            if (!is_dir($dirTorres)) {
                return [];
            }

            $anios = $this->extraerAniosDeDirectorio($dirTorres);

            // Obtener años únicos y ordenar descendentemente
            $anios = array_unique($anios);
            rsort($anios);

            return $anios;

        } catch (Exception $e) {
            error_log("Error en obtenerAniosDisponiblesTorres: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Elimina un archivo del sistema de archivos
     * @param string $rutaRelativa Ruta relativa desde la raíz (ej: uploads/Estacion/Crudos/L0/archivo.csv)
     * @return bool True si se eliminó correctamente
     */
    public function eliminarArchivo($rutaRelativa)
    {
        try {
            // Construir ruta absoluta
            $rutaAbsoluta = dirname($this->uploadsBasePath) . '/' . $rutaRelativa;

            error_log("🗑️ Intentando eliminar: $rutaAbsoluta");

            // Validar que el archivo existe
            if (!file_exists($rutaAbsoluta)) {
                throw new Exception("El archivo no existe: $rutaRelativa");
            }

            // Validar que es un archivo (no directorio)
            if (!is_file($rutaAbsoluta)) {
                throw new Exception("La ruta no corresponde a un archivo válido");
            }

            // Validar que está dentro de uploads/ (seguridad)
            $realPath = realpath($rutaAbsoluta);
            $uploadsRealPath = realpath($this->uploadsBasePath);

            if (strpos($realPath, $uploadsRealPath) !== 0) {
                throw new Exception("Acceso denegado: el archivo está fuera del directorio permitido");
            }

            // Intentar eliminar
            if (unlink($rutaAbsoluta)) {
                error_log("✅ Archivo eliminado exitosamente: $rutaRelativa");
                return true;
            } else {
                throw new Exception("No se pudo eliminar el archivo (permisos insuficientes)");
            }

        } catch (Exception $e) {
            error_log("❌ Error en eliminarArchivo: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verifica si existe la carpeta Torres en una estación
     * @param string $estacion Nombre de la estación
     * @return bool
     */
    public function existeCarpetaTorres($estacion)
    {
        $torresPath = $this->uploadsBasePath . $estacion . '/Torres/';
        return is_dir($torresPath);
    }

    /**
     * Obtiene los metadatos de un archivo específico
     * @param string $filePath Ruta completa del archivo
     * @param string $estacion Nombre de la estación
     * @param string $nivel Nivel del archivo
     * @param string $tipo Tipo (Crudos/Limpios)
     * @return array Metadatos del archivo
     */
    private function obtenerMetadatosArchivo($filePath, $estacion, $nivel, $tipo)
    {
        $fileName = basename($filePath);
        $fileSize = filesize($filePath);
        $fileDate = filemtime($filePath); // Fecha de última modificación del archivo (UTC)

        // Configurar zona horaria de Ecuador (UTC-5)
        $timezone = new DateTimeZone('America/Guayaquil');
        $datetime = new DateTime('@' . $fileDate); // Crear desde timestamp UTC
        $datetime->setTimezone($timezone); // Convertir a hora de Ecuador

        // Ruta relativa desde la raíz del proyecto
        $rutaRelativa = str_replace($this->uploadsBasePath, 'uploads/', $filePath);

        return [
            'nombre' => $fileName,
            'ruta_relativa' => $rutaRelativa,
            'ruta_completa' => $filePath,
            'tamanio' => $fileSize,
            'tamanio_formateado' => $this->formatearTamanio($fileSize),
            'fecha_subida' => $fileDate, // Timestamp UTC original
            'fecha_modificacion' => $fileDate, // Para ordenamiento
            'fecha_formateada' => $datetime->format('d/m/Y H:i'), // Formato en hora de Ecuador
            'estacion' => $estacion,
            'nivel' => $nivel,
            'tipo' => $tipo,
            'extension' => 'csv'
        ];
    }

    /**
     * Cuenta las líneas de un archivo CSV (limitado para performance)
     * @param string $filePath Ruta del archivo
     * @param int $maxLines Máximo de líneas a contar
     * @return int Número de líneas
     */
    private function contarLineasCSV($filePath, $maxLines = 10000)
    {
        $count = 0;
        $handle = @fopen($filePath, 'r');

        if ($handle) {
            while (!feof($handle) && $count < $maxLines) {
                $line = fgets($handle);
                if ($line !== false) {
                    $count++;
                }
            }
            fclose($handle);
        }

        return $count;
    }

    /**
     * Formatea el tamaño de un archivo en formato legible
     * @param int $bytes Tamaño en bytes
     * @return string Tamaño formateado
     */
    private function formatearTamanio($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Verifica si una estación existe
     * @param string $estacion Nombre de la estación
     * @return bool
     */
    public function existeEstacion($estacion)
    {
        $estacionPath = $this->uploadsBasePath . $estacion;
        return is_dir($estacionPath);
    }

    /**
     * Obtiene la estructura completa de una estación
     * @param string $estacion Nombre de la estación
     * @return array Estructura con niveles disponibles
     */
    public function obtenerEstructuraEstacion($estacion)
    {
        if (!$this->existeEstacion($estacion)) {
            throw new Exception("La estación '$estacion' no existe");
        }

        $estructura = [
            'estacion' => $estacion,
            'niveles' => []
        ];

        foreach (['L0', 'L1', 'L2'] as $nivel) {
            $estructura['niveles'][$nivel] = [
                'crudos' => count($this->obtenerArchivosPorTipo($estacion, $nivel, 'Crudos')),
                'limpios' => count($this->obtenerArchivosPorTipo($estacion, $nivel, 'Limpios'))
            ];
        }

        return $estructura;
    }

    /**
     * Registra una descarga en la base de datos
     * @param array $datos Datos del formulario
     * @return int ID del registro creado
     */
    public function registrarDescarga($datos)
    {
        try {
            // Cargar conexión si no existe
            if (!isset($GLOBALS['conn'])) {
                require_once __DIR__ . '../config/conexion.php';
            }

            $conn = $GLOBALS['conn'];

            // Validar conexión
            if (!$conn) {
                throw new Exception("No hay conexión a la base de datos");
            }

            // Validar datos requeridos
            if (empty($datos['nombre_solicitante']) || empty($datos['cedula_pasaporte'])) {
                throw new Exception("Faltan datos obligatorios");
            }

            // Buscar id_estacion por nombre de estación
            $id_estacion = $this->obtenerIdEstacionPorNombre($datos['estacion']);

            // Preparar consulta SQL
            $query = "INSERT INTO registro_descargas (
                    nombre_solicitante, 
                    institucion, 
                    cedula_pasaporte, 
                    motivo, 
                    fecha_descarga,
                    id_estacion, 
                    tipo_nivel, 
                    tipo_archivo
                  ) VALUES ($1, $2, $3, $4, CURRENT_DATE, $5, $6, $7) 
                  RETURNING id_registro_descarga";

            $params = [
                $datos['nombre_solicitante'],
                $datos['institucion'] ?? null,
                $datos['cedula_pasaporte'],
                $datos['motivo'] ?? null,
                $id_estacion,
                $datos['tipo_nivel'] ?? 'L0',
                'CSV'
            ];

            // Log para debug
            error_log("🔍 Ejecutando INSERT con params: " . json_encode($params));

            // Ejecutar consulta
            $result = pg_query_params($conn, $query, $params);

            if (!$result) {
                $error = pg_last_error($conn);
                error_log("❌ Error SQL: " . $error);
                throw new Exception("Error al registrar descarga: " . $error);
            }

            // Obtener ID generado
            $row = pg_fetch_assoc($result);
            $id_registro = intval($row['id_registro_descarga']);

            error_log("✅ Descarga registrada con ID: $id_registro");

            return $id_registro;

        } catch (Exception $e) {
            error_log("❌ Error en registrarDescarga: " . $e->getMessage());
            throw $e;
        }
    }
    /**
     * Obtiene los años disponibles de archivos en una estación y nivel específico
     * Extrae el año de la fecha de inicio del nombre del archivo
     * @param string $estacion Nombre de la estación
     * @param string $nivel L0, L1 o L2
     * @return array Lista de años únicos ordenados descendentemente
     */
    public function obtenerAniosDisponibles($estacion, $nivel)
    {
        try {
            if (empty($estacion)) {
                throw new Exception("El nombre de la estación es requerido");
            }

            if (!in_array($nivel, ['L0', 'L1', 'L2'])) {
                throw new Exception("Nivel no válido. Debe ser L0, L1 o L2");
            }

            $anios = [];

            // Buscar en Crudos
            $dirCrudos = $this->uploadsBasePath . $estacion . '/Crudos/' . $nivel . '/';
            if (is_dir($dirCrudos)) {
                $anios = array_merge($anios, $this->extraerAniosDeDirectorio($dirCrudos));
            }

            // Buscar en Limpios
            $dirLimpios = $this->uploadsBasePath . $estacion . '/Limpios/' . $nivel . '/';
            if (is_dir($dirLimpios)) {
                $anios = array_merge($anios, $this->extraerAniosDeDirectorio($dirLimpios));
            }

            // Obtener años únicos y ordenar descendentemente
            $anios = array_unique($anios);
            rsort($anios); // Más reciente primero

            return $anios;

        } catch (Exception $e) {
            error_log("Error en obtenerAniosDisponibles: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Extrae los años de los archivos CSV en un directorio
     * Formato esperado: NOMBRE_ESTACION_NIVEL_DD-MM-AAAA_DD-MM-AAAA.csv
     * @param string $dirPath Ruta del directorio
     * @return array Lista de años encontrados
     */
    private function extraerAniosDeDirectorio($dirPath)
    {
        $anios = [];

        if (!is_dir($dirPath)) {
            return $anios;
        }

        $items = scandir($dirPath);

        if ($items === false) {
            return $anios;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..')
                continue;

            $filePath = $dirPath . $item;

            // Solo archivos .csv
            if (is_file($filePath) && strtolower(pathinfo($item, PATHINFO_EXTENSION)) === 'csv') {
                // Extraer año del nombre del archivo
                $anio = $this->extraerAnioDeNombreArchivo($item);
                if ($anio !== null) {
                    $anios[] = $anio;
                }
            }
        }

        return $anios;
    }

    /**
     * Extrae el año de la fecha de inicio del nombre del archivo
     * Formato: NOMBRE_ESTACION_NIVEL_DD-MM-AAAA_DD-MM-AAAA.csv
     * @param string $nombreArchivo Nombre del archivo
     * @return int|null Año extraído o null si no se encuentra
     */
    private function extraerAnioDeNombreArchivo($nombreArchivo)
    {
        try {
            // Ejemplo: E.M_Alao222_L0_29-07-2024_31-12-2024.csv
            // Dividir por guion bajo
            $partes = explode('_', $nombreArchivo);

            // La fecha de inicio está en la posición 3 (índice 3)
            if (count($partes) >= 4) {
                $fechaInicio = $partes[3]; // 29-07-2024

                // Dividir la fecha por guion
                $fechaPartes = explode('-', $fechaInicio);

                // El año está en la tercera posición (índice 2)
                if (count($fechaPartes) === 3) {
                    $anio = intval($fechaPartes[2]);

                    // Validar que sea un año razonable
                    if ($anio >= 2000 && $anio <= 2100) {
                        return $anio;
                    }
                }
            }

            return null;

        } catch (Exception $e) {
            error_log("Error al extraer año de '$nombreArchivo': " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene el ID de una estación por su nombre
     */
    private function obtenerIdEstacionPorNombre($nombreEstacion)
    {
        try {
            // Cargar conexión si no existe
            if (!isset($GLOBALS['conn'])) {
                require_once __DIR__ . '../config/conexion.php';
            }

            $conn = $GLOBALS['conn'];

            if (!$conn) {
                error_log("⚠️ No hay conexión para buscar estación");
                return null;
            }

            // Buscar por código o nombre
            $query = "SELECT id_estacion FROM estaciones 
                  WHERE LOWER(codigo) = LOWER($1) 
                  OR LOWER(nombre) LIKE '%' || LOWER($1) || '%'
                  LIMIT 1";

            $result = pg_query_params($conn, $query, [$nombreEstacion]);

            if ($result && pg_num_rows($result) > 0) {
                $row = pg_fetch_assoc($result);
                $id = intval($row['id_estacion']);
                error_log("✅ Estación encontrada: $nombreEstacion -> ID: $id");
                return $id;
            }

            // Si no encuentra, devolver NULL
            error_log("⚠️ No se encontró id_estacion para: $nombreEstacion");
            return null;

        } catch (Exception $e) {
            error_log("❌ Error al buscar estación: " . $e->getMessage());
            return null;
        }
    }


}
?>