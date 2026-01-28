<?php

require_once('../config/database.php');

class MLoadTorres
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
     * Obtiene información de estación por ID
     */
    public function obtenerEstacionPorId($id_estacion)
    {
        try {
            $query = "SELECT id_estacion, codigo, nombre, provincia, canton, parroquia, comunidad 
                     FROM estaciones 
                     WHERE id_estacion = $1 AND estado_activo = true";

            $result = pg_query_params($this->conn, $query, [$id_estacion]);

            if (!$result) {
                throw new Exception('Error en consulta: ' . pg_last_error($this->conn));
            }

            return pg_fetch_assoc($result);
        } catch (Exception $e) {
            throw new Exception('Error al obtener información de estación: ' . $e->getMessage());
        }
    }

    /**
     * Valida encabezados del archivo CSV
     * Debe tener al menos 5 variables válidas de Torres
     */
    public function validateCSVHeaders($filePath)
{
    try {
        if (!file_exists($filePath)) {
            return ['valid' => false, 'message' => 'Archivo no encontrado'];
        }

        $fileContent = file_get_contents($filePath);
        $lines = explode("\n", $fileContent);

        if (count($lines) < 2) {
            return ['valid' => false, 'message' => 'El archivo debe tener al menos 2 líneas de encabezado'];
        }

        error_log("=== VALIDACIÓN DE ENCABEZADOS TORRES ===");

        // 🔹 PASO 1: Leer SOLO la segunda línea (índice 1)
        $headerLine = strtolower(trim($lines[1]));

        if (empty($headerLine)) {
            return ['valid' => false, 'message' => 'La segunda línea de encabezados está vacía'];
        }

        error_log("Segunda línea: " . substr($headerLine, 0, 200));

        // 🔹 PASO 2: Detectar separador
        $separador = (substr_count($headerLine, ',') > substr_count($headerLine, ';')) ? ',' : ';';
        error_log("Separador detectado: '$separador'");

        // 🔹 PASO 3: Extraer todas las columnas
        $columnas = array_map('trim', array_map('strtolower', explode($separador, $headerLine)));
        $totalColumnas = count($columnas);

        error_log("Total columnas en segunda línea: $totalColumnas");
        error_log("Primeras 10 columnas: " . implode(', ', array_slice($columnas, 0, 10)));

        // 🔹 PASO 4: Lista de variables OBLIGATORIAS (comunes a todas las torres)
        $variablesObligatorias = [
            'date',        // Fecha
            'time',        // Hora
            'avg',         // Promedio (temperatura, humedad)
            'max',         // Máximo (temperatura, humedad)
            'min',         // Mínimo (temperatura, humedad)
            'diravg',      // Dirección promedio (viento)
            'dirmax',      // Dirección máxima (viento)
            'dirmin',      // Dirección mínima (viento)
            'spdavg',      // Velocidad promedio (viento)
            'spdmax',      // Velocidad máxima (viento)
            'spdmin',      // Velocidad mínima (viento)
            'external_dc'  // Voltaje externo (energía)
        ];

        // 🔹 PASO 5: Buscar cuántas variables obligatorias están presentes
        $variablesEncontradas = [];

        foreach ($variablesObligatorias as $varObligatoria) {
            foreach ($columnas as $columna) {
                // Buscar la variable en la columna (puede contener más texto)
                if (strpos($columna, $varObligatoria) !== false) {
                    if (!in_array($varObligatoria, $variablesEncontradas)) {
                        $variablesEncontradas[] = $varObligatoria;
                        error_log("✓ Variable encontrada: $varObligatoria");
                    }
                }
            }
        }

        $totalEncontradas = count($variablesEncontradas);

        error_log("📊 Total variables obligatorias encontradas: $totalEncontradas de " . count($variablesObligatorias));
        error_log("📋 Variables presentes: " . implode(', ', $variablesEncontradas));

        // 🔹 PASO 6: Validar mínimo 10 variables
        if ($totalEncontradas < 10) {
            $faltantes = array_diff($variablesObligatorias, $variablesEncontradas);
            
            return [
                'valid' => false,
                'message' => "El archivo debe contener al menos 10 de las 12 variables obligatorias de Torres. Solo se encontraron: $totalEncontradas.\n\n" .
                            "✓ Presentes: " . implode(', ', $variablesEncontradas) . "\n" .
                            "✗ Faltantes: " . implode(', ', $faltantes)
            ];
        }

        error_log("✅ ENCABEZADOS TORRES VÁLIDOS: $totalEncontradas/12 variables obligatorias presentes");

        return [
            'valid' => true,
            'message' => "Encabezados válidos: $totalEncontradas variables obligatorias encontradas"
        ];

    } catch (Exception $e) {
        error_log("❌ Error en validación de encabezados: " . $e->getMessage());
        return ['valid' => false, 'message' => 'Error al validar encabezados: ' . $e->getMessage()];
    }
}

    /**
     * Valida calidad de datos del archivo
     * Debe tener al menos 5% de datos válidos
     */
    public function validateDataQuality($filePath)
    {
        try {
            if (!file_exists($filePath)) {
                return ['valid' => false, 'message' => 'Archivo no encontrado'];
            }

            $fileContent = file_get_contents($filePath);
            $lines = explode("\n", $fileContent);

            $separador = null;
            foreach ($lines as $line) {
                if (empty(trim($line))) continue;
                $separador = (substr_count($line, ';') > substr_count($line, ',')) ? ';' : ',';
                break;
            }

            if (!$separador) {
                return ['valid' => false, 'message' => 'No se pudo detectar el formato del archivo'];
            }

            $totalLineasDatos = 0;
            $lineasConDatosValidos = 0;

            error_log("=== VALIDACIÓN DE CALIDAD DE DATOS TORRES ===");

            foreach ($lines as $index => $line) {
                $line = trim($line);
                if (empty($line)) continue;

                $lineLower = strtolower($line);

                // Saltar encabezados
                if (strpos($lineLower, 'stat_ta') !== false ||
                    strpos($lineLower, 'date') !== false ||
                    strpos($lineLower, 'status') !== false) {
                    continue;
                }

                $campos = array_map('trim', explode($separador, $line));

                if (count($campos) < 10) continue;

                $totalLineasDatos++;

                $tieneValorValido = false;

                foreach ($campos as $valor) {
                    if (is_numeric($valor)) {
                        $num = floatval($valor);

                        // Dirección: 0-360
                        if ($num > 0.0 && $num < 360.0) {
                            $tieneValorValido = true;
                            break;
                        }

                        // Temperatura: -100 a 100
                        if ($num > -100 && $num < 100 && $num != 0) {
                            $tieneValorValido = true;
                            break;
                        }
                    }
                }

                if ($tieneValorValido) {
                    $lineasConDatosValidos++;
                }
            }

            error_log("📊 RESULTADO VALIDACIÓN TORRES:");
            error_log("  Total líneas de datos: $totalLineasDatos");
            error_log("  Líneas con datos válidos: $lineasConDatosValidos");

            $porcentajeValidos = $totalLineasDatos > 0
                ? ($lineasConDatosValidos / $totalLineasDatos) * 100
                : 0;

            error_log("  Porcentaje de datos válidos: " . round($porcentajeValidos, 2) . "%");

            if ($lineasConDatosValidos == 0) {
                return [
                    'valid' => false,
                    'message' => 'El archivo NO contiene datos válidos de torres.'
                ];
            }

            if ($porcentajeValidos < 5) {
                return [
                    'valid' => false,
                    'message' => "El archivo tiene muy pocos datos válidos (" . round($porcentajeValidos, 1) . "%). Se requiere al menos 5% de datos válidos."
                ];
            }

            return [
                'valid' => true,
                'message' => 'Archivo con datos válidos'
            ];

        } catch (Exception $e) {
            error_log("❌ Error en validación de calidad Torres: " . $e->getMessage());
            return ['valid' => false, 'message' => 'Error al validar calidad de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Cuenta líneas de datos en el archivo
     */
    public function countDataLines($filePath)
    {
        try {
            if (!file_exists($filePath)) {
                return 0;
            }

            $fileContent = file_get_contents($filePath);
            $lines = explode("\n", $fileContent);

            $totalLineasDatos = 0;

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                $lineLower = strtolower($line);
                if (strpos($lineLower, 'stat_ta') !== false ||
                    strpos($lineLower, 'date') !== false ||
                    strpos($lineLower, 'status') !== false) {
                    continue;
                }

                $totalLineasDatos++;
            }

            error_log("📊 Total líneas de datos Torres encontradas: $totalLineasDatos");
            return $totalLineasDatos;

        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Extrae fecha inicio y fin del archivo
     */
    public function extraerFechaInicioFin($filePath)
    {
        try {
            if (!file_exists($filePath)) {
                error_log("❌ Archivo no encontrado");
                return null;
            }

            $fileContent = file_get_contents($filePath);
            $lines = explode("\n", $fileContent);

            $separador = null;
            foreach ($lines as $line) {
                if (empty(trim($line))) continue;
                $separador = (substr_count($line, ';') > substr_count($line, ',')) ? ';' : ',';
                break;
            }

            if (!$separador) {
                error_log("❌ No se pudo detectar separador");
                return null;
            }

            $deteccion = $this->detectarColumnasParaFechas($lines, $separador);

            if (!$deteccion) {
                error_log("❌ No se pudieron detectar columnas");
                return null;
            }

            $primeraFecha = null;
            $ultimaFecha = null;

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                $lineLower = strtolower($line);
                if (strpos($lineLower, 'stat_ta') !== false ||
                    strpos($lineLower, 'date') !== false ||
                    strpos($lineLower, 'status') !== false) {
                    continue;
                }

                $campos = array_map('trim', explode($separador, $line));

                $maxCol = max($deteccion['colFecha'], $deteccion['colHora']);
                if (count($campos) <= $maxCol) {
                    continue;
                }

                $fechaTexto = $campos[$deteccion['colFecha']];
                $horaTexto = $campos[$deteccion['colHora']];

                $ampm = '';
                if (preg_match('/^(.+?)\s*(AM|PM)$/i', $horaTexto, $matches)) {
                    $horaTexto = trim($matches[1]);
                    $ampm = strtoupper($matches[2]);
                }

                $fechaHora = $this->parseDateTimeFromParts($fechaTexto, $horaTexto, $ampm);

                if ($fechaHora) {
                    if ($primeraFecha === null) {
                        $primeraFecha = $fechaHora;
                    }
                    $ultimaFecha = $fechaHora;
                }
            }

            if ($primeraFecha && $ultimaFecha) {
                error_log("✓ Primera fecha: $primeraFecha");
                error_log("✓ Última fecha: $ultimaFecha");

                return [
                    'inicio' => min($primeraFecha, $ultimaFecha),
                    'fin' => max($primeraFecha, $ultimaFecha)
                ];
            }

            error_log("❌ No se encontraron fechas válidas");
            return null;

        } catch (Exception $e) {
            error_log("Error extrayendo fechas: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Detecta columnas de fecha y hora
     */
    private function detectarColumnasParaFechas($lines, $separador)
    {
        if (empty($lines)) {
            return null;
        }

        $lineasDatos = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $lineLower = strtolower($line);
            if (strpos($lineLower, 'stat_ta') !== false ||
                strpos($lineLower, 'date') !== false ||
                strpos($lineLower, 'status') !== false) {
                continue;
            }

            $lineasDatos[] = $line;
            if (count($lineasDatos) >= 50) break;
        }

        if (count($lineasDatos) < 3) {
            error_log("❌ Pocas líneas de datos: " . count($lineasDatos));
            return null;
        }

        $primeraLinea = array_map('trim', explode($separador, $lineasDatos[0]));
        $totalColumnas = count($primeraLinea);

        $puntuacionFecha = array_fill(0, $totalColumnas, 0);
        $puntuacionHora = array_fill(0, $totalColumnas, 0);

        foreach ($lineasDatos as $linea) {
            $campos = array_map('trim', explode($separador, $linea));

            foreach ($campos as $colIndex => $valor) {
                if (empty($valor)) continue;

                if (preg_match('/^\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4}$/', $valor)) {
                    $puntuacionFecha[$colIndex] += 5;
                }

                if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?\s*(AM|PM)$/i', $valor)) {
                    $puntuacionHora[$colIndex] += 10;
                }
            }
        }

        $colFecha = array_search(max($puntuacionFecha), $puntuacionFecha);
        $colHora = array_search(max($puntuacionHora), $puntuacionHora);

        if ($colFecha === false || $colHora === false) {
            error_log("❌ No se detectaron columnas de fecha/hora");
            return null;
        }

        error_log("✓ Columnas detectadas: Fecha=$colFecha, Hora=$colHora");

        return [
            'separador' => $separador,
            'colFecha' => $colFecha,
            'colHora' => $colHora
        ];
    }

    /**
     * Parsea fecha y hora
     */
    private function parseDateTimeFromParts($fechaTexto, $horaTexto, $ampm = '')
    {
        $separador = '/';
        if (strpos($fechaTexto, '-') !== false) {
            $separador = '-';
        }

        $fechaComponentes = explode($separador, $fechaTexto);
        if (count($fechaComponentes) != 3) {
            return false;
        }

        $mes = intval($fechaComponentes[0]);
        $dia = intval($fechaComponentes[1]);
        $anio = intval($fechaComponentes[2]);

        if ($anio < 100) {
            $anio = 2000 + $anio;
        }

        if (!checkdate($mes, $dia, $anio)) {
            return false;
        }

        $horaComponentes = explode(':', $horaTexto);
        if (count($horaComponentes) < 2) {
            return false;
        }

        $hora = intval($horaComponentes[0]);
        $minuto = intval($horaComponentes[1]);
        $segundo = isset($horaComponentes[2]) ? intval($horaComponentes[2]) : 0;

        if (!empty($ampm)) {
            $ampm = strtoupper(trim($ampm));
            if ($ampm == 'PM' && $hora < 12) {
                $hora += 12;
            } elseif ($ampm == 'AM' && $hora == 12) {
                $hora = 0;
            }
        }

        if ($hora < 0 || $hora > 23 || $minuto < 0 || $minuto > 59 || $segundo < 0 || $segundo > 59) {
            return false;
        }

        return sprintf('%04d-%02d-%02d %02d:%02d:%02d', $anio, $mes, $dia, $hora, $minuto, $segundo);
    }
}
?>