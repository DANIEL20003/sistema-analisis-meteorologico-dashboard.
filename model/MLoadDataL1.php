<?php

require_once('../config/database.php');

class MLoadDataL1
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


    public function createCargaArchivo($idAdministrador, $idEstacion, $nombreArchivo, $tipoNivel)
    {
        try {
            $query = "INSERT INTO cargas_archivos (
                        id_administrador,
                        id_estacion,
                        nombre_archivo,
                        tipo_nivel,
                        fecha_carga,
                        total_registros,
                        estado_carga
                      ) VALUES (
                        $1, $2, $3, $4, NOW(), 0, 'PROCESANDO'
                      ) RETURNING id_carga";

            $result = pg_query_params($this->conn, $query, [$idAdministrador, $idEstacion, $nombreArchivo, $tipoNivel]);

            if (!$result) {
                throw new Exception('Error al crear registro de carga: ' . pg_last_error($this->conn));
            }

            $row = pg_fetch_assoc($result);
            return $row['id_carga'];
        } catch (Exception $e) {
            throw new Exception('Error al crear registro de carga: ' . $e->getMessage());
        }
    }


    public function updateCargaArchivo($idCarga, $totalRegistros)
    {
        try {
            $query = "UPDATE cargas_archivos 
                     SET total_registros = $1 
                     WHERE id_carga = $2";

            $result = pg_query_params($this->conn, $query, [$totalRegistros, $idCarga]);

            if (!$result) {
                throw new Exception('Error al actualizar registro de carga: ' . pg_last_error($this->conn));
            }

            return true;
        } catch (Exception $e) {
            throw new Exception('Error al actualizar registro de carga: ' . $e->getMessage());
        }
    }

    public function updateCargaArchivoEstado($idCarga, $estado)
    {
        try {
            $query = "UPDATE cargas_archivos 
                     SET estado_carga = $1 
                     WHERE id_carga = $2";

            $result = pg_query_params($this->conn, $query, [$estado, $idCarga]);

            if (!$result) {
                throw new Exception('Error al actualizar estado de carga: ' . pg_last_error($this->conn));
            }

            return true;
        } catch (Exception $e) {
            throw new Exception('Error al actualizar estado de carga: ' . $e->getMessage());
        }
    }


    public function updateCargaArchivoFinalWithOriginalName($idCarga, $archivoFinal, $registrosCargados, $nombreOriginal)
    {
        try {
            $query = "UPDATE cargas_archivos 
                     SET nombre_archivo = $1,
                         total_registros = $2
                     WHERE id_carga = $3";

            $result = pg_query_params($this->conn, $query, [$archivoFinal, $registrosCargados, $idCarga]);

            if (!$result) {
                throw new Exception('Error al actualizar información final: ' . pg_last_error($this->conn));
            }

            return true;
        } catch (Exception $e) {
            throw new Exception('Error al actualizar información final: ' . $e->getMessage());
        }
    }


    public function getCargaArchivoInfo($idCarga)
    {
        try {
            $query = "SELECT * FROM cargas_archivos WHERE id_carga = $1";

            $result = pg_query_params($this->conn, $query, [$idCarga]);

            if (!$result) {
                throw new Exception('Error al obtener información de carga: ' . pg_last_error($this->conn));
            }

            return pg_fetch_assoc($result);
        } catch (Exception $e) {
            throw new Exception('Error al obtener información de carga: ' . $e->getMessage());
        }
    }


    public function saveTempFilePath($idCarga, $tempFilePath)
    {
        try {
            $tempFileName = 'TEMP_' . $idCarga . '_' . basename($tempFilePath);

            $query = "UPDATE cargas_archivos 
                     SET nombre_archivo = $1
                     WHERE id_carga = $2";

            $result = pg_query_params($this->conn, $query, [$tempFileName, $idCarga]);

            if (!$result) {
                throw new Exception('Error al guardar ruta de archivo temporal: ' . pg_last_error($this->conn));
            }

            return true;
        } catch (Exception $e) {
            throw new Exception('Error al guardar ruta de archivo temporal: ' . $e->getMessage());
        }
    }

    public function getTempFilePath($idCarga)
    {
        try {
            $query = "SELECT nombre_archivo FROM cargas_archivos WHERE id_carga = $1";

            $result = pg_query_params($this->conn, $query, [$idCarga]);

            if (!$result) {
                return null;
            }

            $row = pg_fetch_assoc($result);
            if ($row && strpos($row['nombre_archivo'], 'TEMP_') === 0) {
                // Extraer nombre real del archivo después de TEMP_{id}_
                $pattern = '/^TEMP_\d+_(.+)$/';
                if (preg_match($pattern, $row['nombre_archivo'], $matches)) {
                    return '../controller/uploads/temp/' . $matches[1];
                }
            }

            return null;
        } catch (Exception $e) {
            return null;
        }
    }


    public function insertL1Data($idCarga, $datosL1)
    {
        try {
            set_time_limit(0);
            $cargaInfo = $this->getCargaArchivoInfo($idCarga);
            if (!$cargaInfo) {
                throw new Exception('Información de carga no encontrada');
            }

            $batchSize = 10000;
            $totalRegistros = count($datosL1);
            $registrosInsertados = 0;
            $registrosSaltados = 0;

            error_log("📊 Insertando $totalRegistros registros L1 en lotes de $batchSize");

            for ($offset = 0; $offset < $totalRegistros; $offset += $batchSize) {
                $lote = array_slice($datosL1, $offset, $batchSize);

                pg_query($this->conn, "BEGIN");

                try {
                    // ✅ INSERCIÓN MASIVA CON UN SOLO QUERY
                    $values = [];
                    $params = [];
                    $paramIndex = 1;

                    foreach ($lote as $dato) {
                        $fechaHora = $dato['fecha_hora'];

                        // ✅ CORREGIDO: Solo 4 columnas (created_at se genera automáticamente)
                        $values[] = sprintf(
                            "($%d, $%d, $%d, $%d)",
                            $paramIndex++,
                            $paramIndex++,
                            $paramIndex++,
                            $paramIndex++
                        );

                        $params[] = $idCarga;
                        $params[] = $cargaInfo['id_estacion'];
                        $params[] = $fechaHora;
                        $params[] = 'L1';
                    }

                    // ✅ CORREGIDO: Sin created_at en columnas (se genera automático)
                    $queryRegistros = "INSERT INTO registros (id_carga, id_estacion, fecha_hora, tipo_nivel) 
                                   VALUES " . implode(', ', $values) . "
                                   ON CONFLICT (id_estacion, fecha_hora, tipo_nivel, id_carga) DO NOTHING
                                   RETURNING id_registro, fecha_hora";

                    $resultRegistros = pg_query_params($this->conn, $queryRegistros, $params);

                    if (!$resultRegistros) {
                        throw new Exception('Error insertando registros: ' . pg_last_error($this->conn));
                    }

                    // ✅ OBTENER IDs insertados
                    $registrosInsertadosMap = [];
                    while ($row = pg_fetch_assoc($resultRegistros)) {
                        $registrosInsertadosMap[$row['fecha_hora']] = $row['id_registro'];
                    }

                    // ✅ INSERTAR DATOS_L1 EN BATCH
                    if (!empty($registrosInsertadosMap)) {
                        $valuesL1 = [];
                        $paramsL1 = [];
                        $paramIndexL1 = 1;

                        foreach ($lote as $dato) {
                            $fechaHora = $dato['fecha_hora'];

                            if (isset($registrosInsertadosMap[$fechaHora])) {
                                $idRegistro = $registrosInsertadosMap[$fechaHora];

                                $valuesL1[] = sprintf(
                                    "($%d, $%d, $%d, $%d, $%d, $%d, $%d, $%d, $%d, $%d)",
                                    $paramIndexL1++,
                                    $paramIndexL1++,
                                    $paramIndexL1++,
                                    $paramIndexL1++,
                                    $paramIndexL1++,
                                    $paramIndexL1++,
                                    $paramIndexL1++,
                                    $paramIndexL1++,
                                    $paramIndexL1++,
                                    $paramIndexL1++
                                );

                                $paramsL1[] = $idRegistro;
                                $paramsL1[] = $dato['fecha'];
                                $paramsL1[] = $dato['hora'];
                                $paramsL1[] = $dato['temperatura_aire'];
                                $paramsL1[] = $dato['humedad_relativa'];
                                $paramsL1[] = $dato['presion_barometrica'];
                                $paramsL1[] = $dato['radiacion_global'];
                                $paramsL1[] = $dato['lluvia'];
                                $paramsL1[] = $dato['velocidad_viento'];
                                $paramsL1[] = $dato['direccion_viento'];

                                $registrosInsertados++;
                            } else {
                                $registrosSaltados++;
                            }
                        }

                        if (!empty($valuesL1)) {
                            $queryL1 = "INSERT INTO datos_l1 (id_registro, fecha, hora, temperatura_aire, humedad_relativa, presion_barometrica, radiacion_global, lluvia, velocidad_viento, direccion_viento) 
                                    VALUES " . implode(', ', $valuesL1) . "
                                    ON CONFLICT (id_registro) DO NOTHING";

                            $resultL1 = pg_query_params($this->conn, $queryL1, $paramsL1);

                            if (!$resultL1) {
                                throw new Exception('Error insertando datos L1: ' . pg_last_error($this->conn));
                            }
                        }
                    }

                    pg_query($this->conn, "COMMIT");

                    // ✅ LOG cada 10,000 registros
                    if ($registrosInsertados % 10000 == 0 || ($registrosInsertados + $registrosSaltados) == $totalRegistros) {
                        $porcentaje = round((($registrosInsertados + $registrosSaltados) / $totalRegistros) * 100, 1);
                        error_log("✓ Progreso L1: " . ($registrosInsertados + $registrosSaltados) . "/$totalRegistros ($porcentaje%)");
                    }

                } catch (Exception $e) {
                    pg_query($this->conn, "ROLLBACK");
                    throw $e;
                }
            }

            error_log("✅ Inserción L1 completada: $registrosInsertados insertados, $registrosSaltados saltados");
            return true;

        } catch (Exception $e) {
            throw new Exception('Error al insertar datos L1: ' . $e->getMessage());
        }
    }


    public function createRegistro($idCarga, $idEstacion, $fechaHora, $tipoNivel)
    {
        try {
            // ✅ PRIMERO VERIFICAR SI YA EXISTE
            $queryCheck = "SELECT id_registro FROM registros 
                      WHERE id_estacion = $1 
                      AND fecha_hora = $2 
                      AND tipo_nivel = $3";

            $resultCheck = pg_query_params($this->conn, $queryCheck, [$idEstacion, $fechaHora, $tipoNivel]);

            if ($resultCheck && pg_num_rows($resultCheck) > 0) {
                // Ya existe, retornar NULL para saltarlo
                return null;
            }

            // ✅ NO EXISTE, INSERTAR
            $query = "INSERT INTO registros (
                    id_carga,
                    id_estacion,
                    fecha_hora,
                    tipo_nivel,
                    created_at
                  ) VALUES (
                    $1, $2, $3, $4, NOW()
                  ) RETURNING id_registro";

            $result = pg_query_params($this->conn, $query, [$idCarga, $idEstacion, $fechaHora, $tipoNivel]);

            if (!$result) {
                $error = pg_last_error($this->conn);
                // Si es error de duplicado, retornar NULL
                if (strpos($error, 'unique') !== false || strpos($error, 'duplicate') !== false) {
                    return null;
                }
                throw new Exception('Error al crear registro: ' . $error);
            }

            $row = pg_fetch_assoc($result);
            return $row['id_registro'];

        } catch (Exception $e) {
            // Si es error de duplicado, retornar NULL
            if (strpos($e->getMessage(), 'unique') !== false || strpos($e->getMessage(), 'duplicate') !== false) {
                return null;
            }
            throw new Exception('Error al crear registro: ' . $e->getMessage());
        }
    }

    private function getExistingRegistro($idCarga, $idEstacion, $fechaHora, $tipoNivel)
    {
        try {
            $query = "SELECT id_registro FROM registros 
                     WHERE id_carga = $1 
                     AND id_estacion = $2 
                     AND fecha_hora = $3 
                     AND tipo_nivel = $4";

            $result = pg_query_params($this->conn, $query, [$idCarga, $idEstacion, $fechaHora, $tipoNivel]);

            if (!$result) {
                return null;
            }

            $row = pg_fetch_assoc($result);
            return $row ? $row['id_registro'] : null;
        } catch (Exception $e) {
            return null;
        }
    }


    public function insertDatoL1($idRegistro, $dato)
    {
        try {
            $query = "INSERT INTO datos_l1 (
                id_registro,
                fecha,
                hora,
                temperatura_aire,
                humedad_relativa,
                presion_barometrica,
                radiacion_global,
                lluvia,
                velocidad_viento,
                direccion_viento
            ) VALUES (
                $1, $2, $3, $4, $5, $6, $7, $8, $9, $10
            ) ON CONFLICT (id_registro) DO NOTHING";

            $result = pg_query_params($this->conn, $query, [
                $idRegistro,
                $dato['fecha'],
                $dato['hora'],
                $dato['temperatura_aire'],
                $dato['humedad_relativa'],
                $dato['presion_barometrica'],
                $dato['radiacion_global'],
                $dato['lluvia'],
                $dato['velocidad_viento'],
                $dato['direccion_viento']
            ]);

            if (!$result) {
                throw new Exception('Error al insertar dato L1: ' . pg_last_error($this->conn));
            }

            return true;
        } catch (Exception $e) {
            throw new Exception('Error al insertar dato L1: ' . $e->getMessage());
        }
    }

    public function checkDuplicateFile($idEstacion, $nombreArchivo)
    {
        try {
            $query = "SELECT COUNT(*) as count 
                     FROM cargas_archivos 
                     WHERE id_estacion = $1 
                     AND nombre_archivo = $2 
                     AND estado_carga != 'ERROR'";

            $result = pg_query_params($this->conn, $query, [$idEstacion, $nombreArchivo]);

            if (!$result) {
                throw new Exception('Error en consulta: ' . pg_last_error($this->conn));
            }

            $row = pg_fetch_assoc($result);
            return $row['count'] > 0;
        } catch (Exception $e) {
            throw new Exception('Error al verificar duplicados de archivo: ' . $e->getMessage());
        }
    }


    public function validateDateRangeForStation($idEstacion, $tempFilePath)
    {
        try {
            if (!file_exists($tempFilePath)) {
                return ['valid' => false, 'reason' => 'Archivo no encontrado'];
            }

            error_log("=== VALIDACIÓN DE DUPLICADOS L1 ===");
            error_log("Estación ID: $idEstacion");
            error_log("Archivo: " . basename($tempFilePath));

            // ✅ Extraer fechas de muestra del archivo
            $fechasMuestra = $this->extraerFechasMuestraParaValidacion($tempFilePath);

            if (empty($fechasMuestra)) {
                error_log("❌ No se pudieron extraer fechas del archivo");
                return ['valid' => false, 'reason' => 'No se encontraron fechas válidas en el archivo'];
            }

            error_log("📊 Total fechas a validar: " . count($fechasMuestra));
            error_log("📅 Primera: {$fechasMuestra[0]}");
            error_log("📅 Última: " . end($fechasMuestra));

            // ✅ Verificar duplicados en base de datos
            $duplicadosEncontrados = 0;
            $fechasValidadas = 0;
            $primeraFechaDuplicada = null;

            foreach ($fechasMuestra as $fechaHora) {
                $query = "SELECT COUNT(*) as existe 
                          FROM registros 
                          WHERE id_estacion = $1 
                          AND tipo_nivel = 'L1'
                          AND fecha_hora = $2";

                $result = pg_query_params($this->conn, $query, [$idEstacion, $fechaHora]);

                if (!$result) {
                    error_log("❌ Error en consulta: " . pg_last_error($this->conn));
                    continue;
                }

                $row = pg_fetch_assoc($result);
                $fechasValidadas++;

                if ($row && $row['existe'] > 0) {
                    $duplicadosEncontrados++;
                    if ($primeraFechaDuplicada === null) {
                        $primeraFechaDuplicada = $fechaHora;
                    }
                    error_log("❌ DUPLICADO L1 encontrado: $fechaHora");

                    // Si encuentra 3+ duplicados, rechazar inmediatamente
                    if ($duplicadosEncontrados >= 3) {
                        error_log("🚫 ARCHIVO L1 RECHAZADO: $duplicadosEncontrados duplicados detectados");
                        return [
                            'valid' => false,
                            'reason' => "El archivo contiene datos duplicados. Se encontraron al menos $duplicadosEncontrados registros que ya existen (ejemplo: " . date('d/m/Y H:i:s', strtotime($primeraFechaDuplicada)) . ")"
                        ];
                    }
                } else {
                    error_log("✓ Nueva L1: $fechaHora");
                }
            }

            // ✅ Resultado final
            if ($duplicadosEncontrados > 0) {
                error_log("⚠️ Se encontraron $duplicadosEncontrados duplicados L1 de $fechasValidadas validados");
                return [
                    'valid' => false,
                    'reason' => "El archivo contiene $duplicadosEncontrados registros duplicados L1 de $fechasValidadas validados"
                ];
            }

            error_log("✅ SIN DUPLICADOS L1 - Archivo válido ($fechasValidadas fechas verificadas)");
            return ['valid' => true, 'reason' => 'Sin duplicados'];

        } catch (Exception $e) {
            error_log("❌ Error en validación: " . $e->getMessage());
            return ['valid' => false, 'reason' => 'Error: ' . $e->getMessage()];
        }
    }

    private function extraerFechasMuestraParaValidacion($filePath)
    {
        try {
            $handle = fopen($filePath, 'r');
            if (!$handle) {
                error_log("❌ No se pudo abrir archivo");
                return [];
            }

            // ✅ PASO 1: Leer todas las líneas de datos
            $todasLasLineas = [];
            $separador = null;

            while (($line = fgets($handle)) !== false) {
                $line = trim($line);
                if (empty($line))
                    continue;

                $lineLower = strtolower($line);

                // Saltar encabezados
                if (
                    strpos($lineLower, 'genwind') !== false ||
                    strpos($lineLower, 'time') !== false ||
                    strpos($lineLower, 'date') !== false ||
                    strpos($lineLower, 'status') !== false ||
                    strpos($lineLower, 'temperature') !== false ||
                    strpos($lineLower, 'humidity') !== false ||
                    strpos($lineLower, 'pressure') !== false ||
                    strpos($lineLower, 'radiation') !== false
                ) {
                    continue;
                }

                // Detectar separador en primera línea de datos
                if ($separador === null) {
                    $separador = (substr_count($line, ';') > substr_count($line, ',')) ? ';' : ',';
                    error_log("Separador detectado: '$separador'");
                }

                $campos = explode($separador, $line);
                if (count($campos) >= 6) { // L1 necesita al menos 6 columnas
                    $todasLasLineas[] = $line;
                }
            }

            fclose($handle);

            $totalLineas = count($todasLasLineas);

            if ($totalLineas == 0) {
                error_log("❌ No hay líneas de datos en el archivo");
                return [];
            }

            error_log("Total líneas de datos L1: $totalLineas");

            // ✅ PASO 2: Seleccionar 20 líneas distribuidas uniformemente
            $numMuestras = min(20, $totalLineas);
            $intervalo = floor($totalLineas / $numMuestras);

            $indicesMuestra = [];
            for ($i = 0; $i < $numMuestras; $i++) {
                $indicesMuestra[] = $i * $intervalo;
            }

            // Asegurar que incluye la última línea
            if (!in_array($totalLineas - 1, $indicesMuestra)) {
                $indicesMuestra[] = $totalLineas - 1;
            }

            error_log("Índices seleccionados: " . implode(', ', $indicesMuestra));

            // ✅ PASO 3: Detectar columnas
            $deteccion = $this->detectarColumnasRapidoL1(array_slice($todasLasLineas, 0, 50), $separador);

            if (!$deteccion) {
                error_log("❌ No se pudieron detectar columnas L1");
                return [];
            }

            error_log("Columnas L1 detectadas: Fecha={$deteccion['colFecha']}, Hora={$deteccion['colHora']}");
            // ✅ PASO 4: Extraer fechas de las líneas seleccionadas
            $fechasMuestra = [];

            foreach ($indicesMuestra as $idx) {
                if (!isset($todasLasLineas[$idx]))
                    continue;

                $fechaHora = $this->extraerFechaDeLineaL1($todasLasLineas[$idx], $deteccion);

                if ($fechaHora) {
                    $fechasMuestra[] = $fechaHora;
                } else {
                    error_log("⚠️ No se pudo parsear línea $idx");
                }
            }

            error_log("✓ Fechas L1 extraídas exitosamente: " . count($fechasMuestra));

            return $fechasMuestra;

        } catch (Exception $e) {
            error_log("Error extrayendo fechas L1: " . $e->getMessage());
            return [];
        }
    }

    private function extraerFechaDeLineaL1($linea, $deteccion)
    {
        try {
            $separador = $deteccion['separador'];
            $campos = array_map('trim', explode($separador, $linea));

            $colFecha = $deteccion['colFecha'];
            $colHora = $deteccion['colHora'];
            $colAmPm = $deteccion['colAmPm'];
            $horaIncluyeAmPm = $deteccion['horaIncluyeAmPm'];

            if (!isset($campos[$colFecha]) || !isset($campos[$colHora])) {
                return null;
            }

            $fechaTexto = $campos[$colFecha];
            $horaTexto = $campos[$colHora];

            // Extraer AM/PM
            $ampm = '';
            if ($horaIncluyeAmPm) {
                // AM/PM está en la hora: "12:00:11 AM"
                if (preg_match('/^(.+?)\s*(AM|PM)$/i', $horaTexto, $matches)) {
                    $horaTexto = trim($matches[1]);
                    $ampm = strtoupper($matches[2]);
                }
            } else if ($colAmPm !== null && isset($campos[$colAmPm])) {
                // AM/PM en columna separada
                $ampm = $campos[$colAmPm];
            }

            return $this->parseDateTimeFromPartsInteligente($fechaTexto, $horaTexto, $ampm);

        } catch (Exception $e) {
            error_log("Error extrayendo fecha L1: " . $e->getMessage());
            return null;
        }
    }

    private function detectarColumnasRapidoL1($lineas, $separador)
    {
        if (empty($lineas))
            return null;

        $totalColumnas = count(explode($separador, $lineas[0]));

        $puntuacionFecha = array_fill(0, $totalColumnas, 0);
        $puntuacionHora = array_fill(0, $totalColumnas, 0);
        $puntuacionAmPm = array_fill(0, $totalColumnas, 0);
        $puntuacionLluvia = array_fill(0, $totalColumnas, 0);
        $puntuacionVelocidadViento = array_fill(0, $totalColumnas, 0);
        $puntuacionDireccionViento = array_fill(0, $totalColumnas, 0);

        foreach ($lineas as $linea) {
            $campos = array_map('trim', explode($separador, $linea));

            foreach ($campos as $colIndex => $valor) {
                if (empty($valor))
                    continue;

                // Fecha: M/D/YY o MM/DD/YYYY
                if (preg_match('/^\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4}$/', $valor)) {
                    $puntuacionFecha[$colIndex] += 5;
                }

                // Hora con AM/PM incluido: "12:00:11 AM"
                if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?\s*(AM|PM)$/i', $valor)) {
                    $puntuacionHora[$colIndex] += 10;
                    $puntuacionAmPm[$colIndex] += 10;
                }
                // Hora sin AM/PM: "12:00:11"
                else if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $valor)) {
                    $puntuacionHora[$colIndex] += 3;
                }

                // AM/PM en columna separada
                if (strtoupper($valor) == 'AM' || strtoupper($valor) == 'PM') {
                    $puntuacionAmPm[$colIndex] += 5;
                }

                // ✅ DETECCIÓN DE LLUVIA
                if (is_numeric($valor)) {
                    $num = floatval($valor);

                    // Lluvia: 0 a 10 mm (valores típicamente bajos, muchos ceros)
                    if ($num >= 0 && $num <= 10 && strpos($valor, '.') !== false) {
                        if ($num == 0.0) {
                            $puntuacionLluvia[$colIndex] += 2; // Muchos ceros
                        } else if ($num > 0 && $num <= 10) {
                            $puntuacionLluvia[$colIndex] += 8; // Valores de lluvia
                        }
                    }

                    // Velocidad Viento: 0 a 20 m/s
                    if ($num >= 0 && $num <= 20) {
                        if ($num == 0.0) {
                            $puntuacionVelocidadViento[$colIndex] += 1;
                        } else if ($num > 0 && $num < 20) {
                            $puntuacionVelocidadViento[$colIndex] += 6;
                        }
                    }

                    // Dirección Viento: 0 a 360 grados
                    $decimales = isset(explode('.', $valor)[1]) ? strlen(explode('.', $valor)[1]) : 0;
                    if ($num >= 0 && $num <= 360 && $decimales <= 2) {
                        if ($num == 360.0 || $num == 0.0) {
                            $puntuacionDireccionViento[$colIndex] += 2;
                        } else if ($num > 0 && $num < 360) {
                            $puntuacionDireccionViento[$colIndex] += 7;
                        }
                    }
                }
            }
        }

        // Determinar columnas
        $colFecha = array_search(max($puntuacionFecha), $puntuacionFecha);
        $colHora = array_search(max($puntuacionHora), $puntuacionHora);

        $horaIncluyeAmPm = ($puntuacionAmPm[$colHora] > 5);
        $colAmPm = null;

        if (!$horaIncluyeAmPm) {
            foreach ($puntuacionAmPm as $col => $puntaje) {
                if ($col != $colHora && $puntaje > 3) {
                    $colAmPm = $col;
                    break;
                }
            }
        }

        if ($colFecha === false || $colHora === false) {
            error_log("❌ No se detectaron columnas de fecha/hora L1");
            return null;
        }

        // ✅ BUSCAR COLUMNAS DE LLUVIA Y VIENTO (independientemente del formato)
        $cols = [$colFecha, $colHora];
        if ($colAmPm !== null)
            $cols[] = $colAmPm;

        // Solo asignar si hay puntuación significativa (>= 10 puntos)
        $colLluvia = null;
        $maxLluvia = max($puntuacionLluvia);
        if ($maxLluvia >= 10) {
            $colLluvia = array_search($maxLluvia, $puntuacionLluvia);
        }

        $colVelocidadViento = null;
        $maxVelViento = max($puntuacionVelocidadViento);
        if ($maxVelViento >= 10) {
            $colVelocidadViento = array_search($maxVelViento, $puntuacionVelocidadViento);
        }

        $colDireccionViento = null;
        $maxDirViento = max($puntuacionDireccionViento);
        if ($maxDirViento >= 10) {
            $colDireccionViento = array_search($maxDirViento, $puntuacionDireccionViento);
        }

        error_log("📊 DETECCIÓN LLUVIA Y VIENTO:");
        error_log("  Lluvia: Col[" . ($colLluvia ?? 'null') . "] = $maxLluvia pts");
        error_log("  Velocidad Viento: Col[" . ($colVelocidadViento ?? 'null') . "] = $maxVelViento pts");
        error_log("  Dirección Viento: Col[" . ($colDireccionViento ?? 'null') . "] = $maxDirViento pts");

        return [
            'separador' => $separador,
            'colFecha' => $colFecha,
            'colHora' => $colHora,
            'colAmPm' => $colAmPm,
            'horaIncluyeAmPm' => $horaIncluyeAmPm,
            'colLluvia' => $colLluvia,
            'colVelocidadViento' => $colVelocidadViento,
            'colDireccionViento' => $colDireccionViento
        ];
    }

    private function parseDateTimeFromPartsInteligente($fechaTexto, $horaTexto, $ampm = '')
    {
        // Parsear fecha
        $separador = '/';
        if (strpos($fechaTexto, '-') !== false) {
            $separador = '-';
        }

        $fechaComponentes = explode($separador, $fechaTexto);
        if (count($fechaComponentes) != 3) {
            error_log("❌ Formato inválido L1: $fechaTexto");
            return false;
        }

        // ✅ SIEMPRE MM/DD/YY
        $mes = intval($fechaComponentes[0]);
        $dia = intval($fechaComponentes[1]);
        $anio = intval($fechaComponentes[2]);

        // ✅ Convertir año corto a 4 dígitos
        if ($anio < 100) {
            $anio = 2000 + $anio;  // 17 → 2017
        }

        // ✅ Validar fecha
        if (!checkdate($mes, $dia, $anio)) {
            error_log("❌ Fecha L1 inválida: mes=$mes, día=$dia, año=$anio (original: $fechaTexto)");
            return false;
        }

        // Parsear hora
        $horaComponentes = explode(':', $horaTexto);
        if (count($horaComponentes) < 2) {
            error_log("❌ Hora L1 inválida: $horaTexto");
            return false;
        }

        $hora = intval($horaComponentes[0]);
        $minuto = intval($horaComponentes[1]);
        $segundo = isset($horaComponentes[2]) ? intval($horaComponentes[2]) : 0;

        // ✅ Convertir AM/PM a formato 24 horas
        if (!empty($ampm)) {
            $ampm = strtoupper(trim($ampm));
            if ($ampm == 'PM' && $hora < 12) {
                $hora += 12;
            } elseif ($ampm == 'AM' && $hora == 12) {
                $hora = 0;
            }
        }

        // Validar hora
        if ($hora < 0 || $hora > 23 || $minuto < 0 || $minuto > 59 || $segundo < 0 || $segundo > 59) {
            error_log("❌ Hora L1 fuera de rango: $hora:$minuto:$segundo");
            return false;
        }

        $resultado = sprintf('%04d-%02d-%02d %02d:%02d:%02d', $anio, $mes, $dia, $hora, $minuto, $segundo);

        return $resultado;
    }
}
?>