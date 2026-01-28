<?php

require_once('../config/database.php');

class MLoadDataL0
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


    public function insertL0Data($idCarga, $datosL0)
    {
        try {
            $cargaInfo = $this->getCargaArchivoInfo($idCarga);
            if (!$cargaInfo) {
                throw new Exception('Información de carga no encontrada');
            }

            $batchSize = 5000;
            $totalRegistros = count($datosL0);
            $registrosInsertados = 0;
            $registrosSaltados = 0;

            error_log("📊 Insertando $totalRegistros registros en lotes de $batchSize");

            for ($offset = 0; $offset < $totalRegistros; $offset += $batchSize) {
                $lote = array_slice($datosL0, $offset, $batchSize);

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
                        $params[] = 'L0';
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

                    // ✅ INSERTAR DATOS_L0 EN BATCH
                    if (!empty($registrosInsertadosMap)) {
                        $valuesL0 = [];
                        $paramsL0 = [];
                        $paramIndexL0 = 1;

                        foreach ($lote as $dato) {
                            $fechaHora = $dato['fecha_hora'];

                            if (isset($registrosInsertadosMap[$fechaHora])) {
                                $idRegistro = $registrosInsertadosMap[$fechaHora];

                                $valuesL0[] = sprintf(
                                    "($%d, $%d, $%d, $%d, $%d)",
                                    $paramIndexL0++,
                                    $paramIndexL0++,
                                    $paramIndexL0++,
                                    $paramIndexL0++,
                                    $paramIndexL0++
                                );

                                $paramsL0[] = $idRegistro;
                                $paramsL0[] = $dato['fecha'];
                                $paramsL0[] = $dato['hora'];
                                $paramsL0[] = $dato['direccion_viento'];
                                $paramsL0[] = $dato['velocidad_viento'];

                                $registrosInsertados++;
                            } else {
                                $registrosSaltados++;
                            }
                        }

                        if (!empty($valuesL0)) {
                            $queryL0 = "INSERT INTO datos_l0 (id_registro, fecha, hora, direccion_viento, velocidad_viento) 
                                    VALUES " . implode(', ', $valuesL0) . "
                                    ON CONFLICT (id_registro) DO NOTHING";

                            $resultL0 = pg_query_params($this->conn, $queryL0, $paramsL0);

                            if (!$resultL0) {
                                throw new Exception('Error insertando datos L0: ' . pg_last_error($this->conn));
                            }
                        }
                    }

                    pg_query($this->conn, "COMMIT");

                    // ✅ LOG cada 10,000 registros
                    if ($registrosInsertados % 10000 == 0 || ($registrosInsertados + $registrosSaltados) == $totalRegistros) {
                        $porcentaje = round((($registrosInsertados + $registrosSaltados) / $totalRegistros) * 100, 1);
                        error_log("✓ Progreso: " . ($registrosInsertados + $registrosSaltados) . "/$totalRegistros ($porcentaje%)");
                    }

                } catch (Exception $e) {
                    pg_query($this->conn, "ROLLBACK");
                    throw $e;
                }
            }

            error_log("✅ Inserción completada: $registrosInsertados insertados, $registrosSaltados saltados");
            return true;

        } catch (Exception $e) {
            throw new Exception('Error al insertar datos L0: ' . $e->getMessage());
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


    public function insertDatoL0($idRegistro, $dato)
    {
        try {
            $query = "INSERT INTO datos_l0 (
                    id_registro,
                    fecha,
                    hora,
                    direccion_viento,
                    velocidad_viento
                  ) VALUES (
                    $1, $2, $3, $4, $5
                  ) ON CONFLICT (id_registro) DO NOTHING";

            $result = pg_query_params($this->conn, $query, [
                $idRegistro,
                $dato['fecha'],           // ✅ Solo fecha (DATE)
                $dato['hora'],            // ✅ Solo hora (TIME)
                $dato['direccion_viento'],
                $dato['velocidad_viento']
            ]);

            if (!$result) {
                throw new Exception('Error al insertar dato L0: ' . pg_last_error($this->conn));
            }

            return true;
        } catch (Exception $e) {
            throw new Exception('Error al insertar dato L0: ' . $e->getMessage());
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


    public function validateDateRangeForStation($idEstacion, $tempFilePath) {
    try {
        if (!file_exists($tempFilePath)) {
            return ['valid' => false, 'reason' => 'Archivo no encontrado'];
        }
        
        error_log("=== VALIDACIÓN DE DUPLICADOS ===");
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
                      AND tipo_nivel = 'L0'
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
                error_log("❌ DUPLICADO encontrado: $fechaHora");
                
                // Si encuentra 3+ duplicados, rechazar inmediatamente
                if ($duplicadosEncontrados >= 3) {
                    error_log("🚫 ARCHIVO RECHAZADO: $duplicadosEncontrados duplicados detectados");
                    return [
                        'valid' => false,
                        'reason' => "El archivo contiene datos duplicados. Se encontraron al menos $duplicadosEncontrados registros que ya existen (ejemplo: " . date('d/m/Y H:i:s', strtotime($primeraFechaDuplicada)) . ")"
                    ];
                }
            } else {
                error_log("✓ Nueva: $fechaHora");
            }
        }
        
        // ✅ Resultado final
        if ($duplicadosEncontrados > 0) {
            error_log("⚠️ Se encontraron $duplicadosEncontrados duplicados de $fechasValidadas validadas");
            return [
                'valid' => false,
                'reason' => "El archivo contiene $duplicadosEncontrados registros duplicados de $fechasValidadas validados"
            ];
        }
        
        error_log("✅ SIN DUPLICADOS - Archivo válido ($fechasValidadas fechas verificadas)");
        return ['valid' => true, 'reason' => 'Sin duplicados'];
        
    } catch (Exception $e) {
        error_log("❌ Error en validación: " . $e->getMessage());
        return ['valid' => false, 'reason' => 'Error: ' . $e->getMessage()];
    }
}

    private function extraerFechasMuestraParaValidacion($filePath) {
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
            if (empty($line)) continue;
            
            $lineLower = strtolower($line);
            
            // Saltar encabezados
            if (strpos($lineLower, 'genwind') !== false || 
                strpos($lineLower, 'time') !== false || 
                strpos($lineLower, 'date') !== false ||
                strpos($lineLower, 'status') !== false) {
                continue;
            }
            
            // Detectar separador en primera línea de datos
            if ($separador === null) {
                $separador = (substr_count($line, ';') > substr_count($line, ',')) ? ';' : ',';
                error_log("Separador detectado: '$separador'");
            }
            
            $campos = explode($separador, $line);
            if (count($campos) >= 5) {
                $todasLasLineas[] = $line;
            }
        }
        
        fclose($handle);
        
        $totalLineas = count($todasLasLineas);
        
        if ($totalLineas == 0) {
            error_log("❌ No hay líneas de datos en el archivo");
            return [];
        }
        
        error_log("Total líneas de datos: $totalLineas");
        
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
        $deteccion = $this->detectarColumnasRapido(array_slice($todasLasLineas, 0, 50), $separador);
        
        if (!$deteccion) {
            error_log("❌ No se pudieron detectar columnas");
            return [];
        }
        
        error_log("Columnas detectadas: Fecha={$deteccion['colFecha']}, Hora={$deteccion['colHora']}, AM/PM=" . ($deteccion['colAmPm'] ?? 'incluido'));
        
        // ✅ PASO 4: Extraer fechas de las líneas seleccionadas
        $fechasMuestra = [];
        
        foreach ($indicesMuestra as $idx) {
            if (!isset($todasLasLineas[$idx])) continue;
            
            $fechaHora = $this->extraerFechaDeLinea($todasLasLineas[$idx], $deteccion);
            
            if ($fechaHora) {
                $fechasMuestra[] = $fechaHora;
            } else {
                error_log("⚠️ No se pudo parsear línea $idx");
            }
        }
        
        error_log("✓ Fechas extraídas exitosamente: " . count($fechasMuestra));
        
        return $fechasMuestra;
        
    } catch (Exception $e) {
        error_log("Error extrayendo fechas: " . $e->getMessage());
        return [];
    }
}
    private function extraerFechaDeLinea($linea, $deteccion) {
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
        error_log("Error extrayendo fecha: " . $e->getMessage());
        return null;
    }
}

    private function detectarColumnasRapido($lineas, $separador) {
    if (empty($lineas)) return null;
    
    $totalColumnas = count(explode($separador, $lineas[0]));
    
    $puntuacionFecha = array_fill(0, $totalColumnas, 0);
    $puntuacionHora = array_fill(0, $totalColumnas, 0);
    $puntuacionAmPm = array_fill(0, $totalColumnas, 0);
    
    foreach ($lineas as $linea) {
        $campos = array_map('trim', explode($separador, $linea));
        
        foreach ($campos as $colIndex => $valor) {
            if (empty($valor)) continue;
            
            // Fecha: M/D/YY o MM/DD/YYYY
            if (preg_match('/^\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4}$/', $valor)) {
                $puntuacionFecha[$colIndex] += 5;
            }
            
            // Hora con AM/PM incluido: "12:00:11 AM"
            if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?\s*(AM|PM)$/i', $valor)) {
                $puntuacionHora[$colIndex] += 10;
                $puntuacionAmPm[$colIndex] += 10; // Marca que AM/PM está incluido
            }
            // Hora sin AM/PM: "12:00:11"
            else if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $valor)) {
                $puntuacionHora[$colIndex] += 3;
            }
            
            // AM/PM en columna separada
            if (strtoupper($valor) == 'AM' || strtoupper($valor) == 'PM') {
                $puntuacionAmPm[$colIndex] += 5;
            }
        }
    }
    
    // Determinar columnas
    $colFecha = array_search(max($puntuacionFecha), $puntuacionFecha);
    $colHora = array_search(max($puntuacionHora), $puntuacionHora);
    
    // ¿AM/PM está en la misma columna que la hora?
    $horaIncluyeAmPm = ($puntuacionAmPm[$colHora] > 5);
    $colAmPm = null;
    
    if (!$horaIncluyeAmPm) {
        // Buscar columna separada de AM/PM
        foreach ($puntuacionAmPm as $col => $puntaje) {
            if ($col != $colHora && $puntaje > 3) {
                $colAmPm = $col;
                break;
            }
        }
    }
    
    if ($colFecha === false || $colHora === false) {
        error_log("❌ No se detectaron columnas de fecha/hora");
        return null;
    }
    
    return [
        'separador' => $separador,
        'colFecha' => $colFecha,
        'colHora' => $colHora,
        'colAmPm' => $colAmPm,
        'horaIncluyeAmPm' => $horaIncluyeAmPm
    ];
}


    private function extraerTresFechasClaveParaValidacion($filePath)
    {
        try {
            $handle = fopen($filePath, 'r');
            if (!$handle) {
                error_log("❌ No se pudo abrir archivo");
                return null;
            }

            // ✅ PASO 1: Detectar separador leyendo primeras líneas
            $separador = null;
            $lineasDatos = [];
            $lineNumber = 0;

            rewind($handle);

            while (($line = fgets($handle)) !== false && count($lineasDatos) < 100) {
                $lineNumber++;
                $line = trim($line);

                if (empty($line))
                    continue;

                $lineLower = strtolower($line);

                // Saltar encabezados
                if (
                    strpos($lineLower, 'genwind') !== false ||
                    strpos($lineLower, 'time') !== false ||
                    strpos($lineLower, 'date') !== false
                ) {
                    continue;
                }

                // ✅ Detectar separador en PRIMERA línea de datos
                if ($separador === null) {
                    $contadorPuntoComa = substr_count($line, ';');
                    $contadorComa = substr_count($line, ',');
                    $separador = ($contadorPuntoComa > $contadorComa) ? ';' : ',';
                    error_log("Separador detectado: '$separador'");
                }

                $campos = array_map('trim', explode($separador, $line));

                // ✅ Validar que tenga suficientes campos
                if (count($campos) >= 3) {
                    $lineasDatos[] = ['line' => $line, 'numero' => $lineNumber, 'campos' => $campos];
                }
            }

            if (count($lineasDatos) < 3) {
                error_log("❌ Pocas líneas de datos: " . count($lineasDatos));
                fclose($handle);
                return null;
            }

            error_log("✓ Encontradas " . count($lineasDatos) . " líneas de datos");

            // ✅ PASO 2: Extraer fechas de 3 puntos
            $totalLineas = count($lineasDatos);
            $indicePrimera = 0;
            $indiceMitad = intval($totalLineas / 2);
            $indiceUltima = $totalLineas - 1;

            $fechaPrimera = $this->extraerFechaDelRenglon($lineasDatos[$indicePrimera], $separador);
            $fechaMitad = $this->extraerFechaDelRenglon($lineasDatos[$indiceMitad], $separador);
            $fechaUltima = $this->extraerFechaDelRenglon($lineasDatos[$indiceUltima], $separador);

            fclose($handle);

            if (!$fechaPrimera || !$fechaMitad || !$fechaUltima) {
                error_log("❌ No se pudieron extraer las 3 fechas");
                return null;
            }

            return [
                'primera' => $fechaPrimera,
                'mitad' => $fechaMitad,
                'ultima' => $fechaUltima
            ];

        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            return null;
        }
    }
    private function extraerFechaDelRenglon($renglon, $separador)
    {
        try {
            $campos = $renglon['campos'];

            // ✅ Buscar columnas de fecha (patrón DD/MM/YY o M/D/YY)
            $colFecha = null;
            $colHora = null;

            foreach ($campos as $idx => $valor) {
                // FECHA: Patrón número/número/número
                if (preg_match('/^\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4}$/', $valor)) {
                    $colFecha = $idx;
                }
                // HORA: Patrón número:número con opcional AM/PM
                if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?\s*(AM|PM)?$/i', $valor)) {
                    $colHora = $idx;
                }
            }

            if ($colFecha === null || $colHora === null) {
                error_log("⚠️ No se encontraron fecha/hora en línea {$renglon['numero']}");
                return null;
            }

            $fechaTexto = $campos[$colFecha];
            $horaTexto = $campos[$colHora];

            // ✅ Extraer AM/PM si está incluido en la hora
            $ampm = '';
            if (preg_match('/\s*(AM|PM)$/i', $horaTexto, $matches)) {
                $ampm = $matches[1];
                $horaTexto = preg_replace('/\s*(AM|PM)$/i', '', $horaTexto);
            }

            // ✅ PARSEAR CON DETECCIÓN INTELIGENTE
            return $this->parseDateTimeFromPartsInteligente($fechaTexto, trim($horaTexto), $ampm);

        } catch (Exception $e) {
            return null;
        }
    }
    private function extraerTresFechasClave($filePath, $deteccion)
    {
        try {
            $separador = $deteccion['separador'];
            $colFecha = $deteccion['colFecha'];
            $colHora = $deteccion['colHora'];
            $colAmPm = $deteccion['colAmPm'];

            // ✅ CONTAR LÍNEAS TOTALES (sin cargar en memoria)
            $totalLineas = $this->contarLineasDatos($filePath);

            if ($totalLineas < 3) {
                error_log("❌ Archivo muy pequeño: $totalLineas líneas");
                return null;
            }

            error_log("📊 Total líneas de datos: $totalLineas");

            // ✅ CALCULAR ÍNDICES
            $indicePrimera = 1;  // Primera línea de datos
            $indiceMitad = intval($totalLineas / 2);
            $indiceUltima = $totalLineas;

            error_log("Índices: Primera=$indicePrimera, Mitad=$indiceMitad, Última=$indiceUltima");

            // ✅ LEER SOLO LAS 3 LÍNEAS NECESARIAS
            $fechaPrimera = $this->leerFechaDeLinea($filePath, $indicePrimera, $deteccion);
            $fechaMitad = $this->leerFechaDeLinea($filePath, $indiceMitad, $deteccion);
            $fechaUltima = $this->leerFechaDeLinea($filePath, $indiceUltima, $deteccion);

            if (!$fechaPrimera || !$fechaMitad || !$fechaUltima) {
                error_log("❌ No se pudieron extraer las 3 fechas");
                return null;
            }

            return [
                'primera' => $fechaPrimera,
                'mitad' => $fechaMitad,
                'ultima' => $fechaUltima
            ];

        } catch (Exception $e) {
            error_log("Error extrayendo fechas: " . $e->getMessage());
            return null;
        }
    }

    private function leerFechaDeLinea($filePath, $numeroLinea, $deteccion)
    {
        try {
            $handle = fopen($filePath, 'r');
            if (!$handle) {
                return null;
            }

            $separador = $deteccion['separador'];
            $colFecha = $deteccion['colFecha'];
            $colHora = $deteccion['colHora'];
            $colAmPm = $deteccion['colAmPm'];
            $horaIncluyeAmPm = $deteccion['horaIncluyeAmPm'] ?? false; // ✅ NUEVO

            $lineCount = 0;
            $fechaEncontrada = null;

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
                    strpos($lineLower, 'status') !== false
                ) {
                    continue;
                }

                $lineCount++;

                // ✅ SOLO procesar la línea que buscamos
                if ($lineCount == $numeroLinea) {
                    $campos = array_map('trim', explode($separador, $line));

                    $maxCol = max($colFecha, $colHora);
                    if ($colAmPm !== null) {
                        $maxCol = max($maxCol, $colAmPm);
                    }

                    if (count($campos) > $maxCol) {
                        $fechaTexto = $campos[$colFecha];
                        $horaTexto = $campos[$colHora];

                        // ✅ MANEJAR AM/PM (igual que en processCSVFile)
                        $ampm = '';
                        if ($horaIncluyeAmPm) {
                            // AM/PM está en la hora (ej: "12:00:11 AM")
                            if (preg_match('/^(.+?)\s*(AM|PM)$/i', $horaTexto, $matches)) {
                                $horaTexto = trim($matches[1]);
                                $ampm = strtoupper($matches[2]);
                            }
                        } else if ($colAmPm !== null) {
                            // AM/PM en columna separada
                            $ampm = $campos[$colAmPm];
                        }

                        $fechaEncontrada = $this->parseDateTimeFromPartsInteligente($fechaTexto, $horaTexto, $ampm);

                        error_log("Línea $numeroLinea: F='$fechaTexto', H='$horaTexto', AP='$ampm' → $fechaEncontrada");
                    }

                    break;
                }
            }

            fclose($handle);
            return $fechaEncontrada;

        } catch (Exception $e) {
            error_log("Error leyendo línea $numeroLinea: " . $e->getMessage());
            return null;
        }
    }


    private function contarLineasDatos($filePath)
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return 0;
        }

        $count = 0;

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
                strpos($lineLower, 'status') !== false
            ) {
                continue;
            }

            $count++;
        }

        fclose($handle);
        return $count;
    }

    private function parsearLineaFecha($linea, $deteccion)
    {
        try {
            $separador = $deteccion['separador'];
            $colFecha = $deteccion['colFecha'];
            $colHora = $deteccion['colHora'];
            $colAmPm = $deteccion['colAmPm'];

            $campos = array_map('trim', explode($separador, $linea));

            $maxCol = max($colFecha, $colHora);
            if ($colAmPm !== null) {
                $maxCol = max($maxCol, $colAmPm);
            }

            if (count($campos) <= $maxCol) {
                return null;
            }

            $fechaTexto = $campos[$colFecha];
            $horaTexto = $campos[$colHora];
            $ampm = ($colAmPm !== null) ? $campos[$colAmPm] : '';

            // ✅ USAR PARSER INTELIGENTE
            return $this->parseDateTimeFromPartsInteligente($fechaTexto, $horaTexto, $ampm);

        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Extrae SOLO la primera y última fecha del archivo (optimizado)
     */
    private function extraerPrimeraYUltimaFecha($filePath, $deteccion)
    {
        try {
            $separador = $deteccion['separador'];
            $colFecha = $deteccion['colFecha'];
            $colHora = $deteccion['colHora'];
            $colAmPm = $deteccion['colAmPm'];

            $handle = fopen($filePath, 'r');
            if (!$handle) {
                return null;
            }

            $primeraFecha = null;
            $ultimaFecha = null;
            $lineNumber = 0;

            // ✅ BUSCAR PRIMERA FECHA VÁLIDA
            while (($line = fgets($handle)) !== false && $primeraFecha === null) {
                $lineNumber++;
                $line = trim($line);

                if (empty($line))
                    continue;

                $lineLower = strtolower($line);

                // Saltar encabezados
                if (
                    strpos($lineLower, 'genwind') !== false ||
                    strpos($lineLower, 'time') !== false ||
                    strpos($lineLower, 'date') !== false ||
                    strpos($lineLower, 'status') !== false
                ) {
                    continue;
                }

                $campos = array_map('trim', explode($separador, $line));

                $maxCol = max($colFecha, $colHora);
                if ($colAmPm !== null) {
                    $maxCol = max($maxCol, $colAmPm);
                }

                if (count($campos) <= $maxCol) {
                    continue;
                }

                $fechaTexto = $campos[$colFecha];
                $horaTexto = $campos[$colHora];
                $ampm = ($colAmPm !== null) ? $campos[$colAmPm] : '';

                $fechaHora = $this->parseDateTimeFromPartsInteligente($fechaTexto, $horaTexto, $ampm);

                if ($fechaHora) {
                    $primeraFecha = $fechaHora;
                    error_log("Primera fecha encontrada en línea $lineNumber: $primeraFecha");
                }
            }

            // ✅ BUSCAR ÚLTIMA FECHA VÁLIDA (desde el final)
            $file = new SplFileObject($filePath, 'r');
            $file->seek(PHP_INT_MAX);
            $totalLineas = $file->key();

            for ($i = $totalLineas; $i >= 0 && $ultimaFecha === null; $i--) {
                try {
                    $file->seek($i);
                    $line = trim($file->current());

                    if (empty($line))
                        continue;

                    $lineLower = strtolower($line);

                    // Saltar encabezados
                    if (
                        strpos($lineLower, 'genwind') !== false ||
                        strpos($lineLower, 'time') !== false ||
                        strpos($lineLower, 'date') !== false
                    ) {
                        continue;
                    }

                    $campos = array_map('trim', explode($separador, $line));

                    $maxCol = max($colFecha, $colHora);
                    if ($colAmPm !== null) {
                        $maxCol = max($maxCol, $colAmPm);
                    }

                    if (count($campos) <= $maxCol) {
                        continue;
                    }

                    $fechaTexto = $campos[$colFecha];
                    $horaTexto = $campos[$colHora];
                    $ampm = ($colAmPm !== null) ? $campos[$colAmPm] : '';

                    $fechaHora = $this->parseDateTimeFromPartsInteligente($fechaTexto, $horaTexto, $ampm);

                    if ($fechaHora) {
                        $ultimaFecha = $fechaHora;
                        error_log("Última fecha encontrada en línea $i: $ultimaFecha");
                    }

                } catch (Exception $e) {
                    continue;
                }
            }

            fclose($handle);

            if ($primeraFecha && $ultimaFecha) {
                return [
                    'primera' => min($primeraFecha, $ultimaFecha),
                    'ultima' => max($primeraFecha, $ultimaFecha)
                ];
            }

            return null;

        } catch (Exception $e) {
            error_log("Error extrayendo fechas: " . $e->getMessage());
            return null;
        }
    }
    /**
     * Extrae todas las fechas_hora del archivo
     */
    private function extraerFechasDelArchivo($filePath)
    {
        $fechas = [];

        try {
            $deteccion = $this->detectarColumnasParaValidacion($filePath);

            if (!$deteccion) {
                return [];
            }

            $separador = $deteccion['separador'];
            $colFecha = $deteccion['colFecha'];
            $colHora = $deteccion['colHora'];
            $colAmPm = $deteccion['colAmPm'];

            $handle = fopen($filePath, 'r');
            if (!$handle) {
                return [];
            }

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
                    strpos($lineLower, 'status') !== false
                ) {
                    continue;
                }

                $campos = array_map('trim', explode($separador, $line));

                $maxCol = max($colFecha, $colHora);
                if ($colAmPm !== null) {
                    $maxCol = max($maxCol, $colAmPm);
                }

                if (count($campos) <= $maxCol) {
                    continue;
                }

                $fechaTexto = $campos[$colFecha];
                $horaTexto = $campos[$colHora];
                $ampm = ($colAmPm !== null) ? $campos[$colAmPm] : '';

                $fechaHora = $this->parseDateTimeFromPartsInteligente($fechaTexto, $horaTexto, $ampm);

                if ($fechaHora) {
                    $fechas[] = $fechaHora;
                }
            }

            fclose($handle);

            return $fechas;

        } catch (Exception $e) {
            error_log("Error extrayendo fechas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Detecta columnas para validación (versión simplificada)
     */
    private function detectarColumnasParaValidacion($filePath)
    {
        try {
            $handle = fopen($filePath, 'r');
            if (!$handle) {
                error_log("❌ No se pudo abrir archivo para detectar columnas");
                return null;
            }

            error_log("=== DETECCIÓN PARA VALIDACIÓN ===");

            $separador = null;
            $lineasDatos = [];
            $lineNumber = 0;
            $maxLineas = 100; // Leer más líneas

            // ✅ LEER PRIMERAS 100 LÍNEAS DE DATOS
            while (($line = fgets($handle)) !== false && count($lineasDatos) < $maxLineas) {
                $lineNumber++;
                $line = trim($line);

                if (empty($line))
                    continue;

                $lineLower = strtolower($line);

                // Saltar encabezados
                if (
                    strpos($lineLower, 'genwind') !== false ||
                    (strpos($lineLower, 'time') !== false && strpos($lineLower, 'status') !== false) ||
                    (strpos($lineLower, 'date') !== false && strpos($lineLower, 'status') !== false)
                ) {
                    continue;
                }

                // Detectar separador
                if ($separador === null) {
                    $separador = (substr_count($line, ';') > substr_count($line, ',')) ? ';' : ',';
                }

                // ✅ SOLO líneas con valores reales
                if ($this->lineaTieneValoresRealesValidacion($line, $separador)) {
                    $lineasDatos[] = $line;
                }
            }

            fclose($handle);

            if (count($lineasDatos) < 3) {
                error_log("❌ Pocas líneas con datos: " . count($lineasDatos));
                return null;
            }

            error_log("✓ Encontradas " . count($lineasDatos) . " líneas válidas");

            // ✅ ANALIZAR COLUMNAS
            return $this->analizarColumnasValidacion($lineasDatos, $separador);

        } catch (Exception $e) {
            error_log("❌ Error en detección: " . $e->getMessage());
            return null;
        }
    }

    private function lineaTieneValoresRealesValidacion($line, $separador)
    {
        $campos = array_map('trim', explode($separador, $line));

        foreach ($campos as $valor) {
            if (is_numeric($valor)) {
                $num = floatval($valor);
                // Valores significativos
                if (($num > 0.1 && $num < 50) || ($num > 1 && $num < 359)) {
                    return true;
                }
            }
        }

        return false;
    }
    private function analizarColumnasValidacion($lineas, $separador)
    {
        if (empty($lineas)) {
            return null;
        }

        $primeraLinea = array_map('trim', explode($separador, $lineas[0]));
        $totalColumnas = count($primeraLinea);

        error_log("Analizando " . count($lineas) . " líneas con $totalColumnas columnas");

        // Puntuaciones
        $puntuacionFecha = array_fill(0, $totalColumnas, 0);
        $puntuacionHora = array_fill(0, $totalColumnas, 0);
        $puntuacionAmPm = array_fill(0, $totalColumnas, 0);

        foreach ($lineas as $index => $linea) {
            $campos = array_map('trim', explode($separador, $linea));

            if ($index == 0) {
                error_log("Primera línea:");
                for ($i = 0; $i < min(8, count($campos)); $i++) {
                    error_log("  Col[$i] = '{$campos[$i]}'");
                }
            }

            foreach ($campos as $colIndex => $valor) {
                if (empty($valor))
                    continue;

                // ✅ FECHA (solo fecha, sin hora)
                if (preg_match('/^\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4}$/', $valor)) {
                    $puntuacionFecha[$colIndex] += 5;
                }

                // ✅ HORA CON AM/PM INCLUIDO (formato: "12:00:11 AM")
                if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?\s*(AM|PM)$/i', $valor)) {
                    $puntuacionHora[$colIndex] += 10; // MUY alta prioridad
                    $puntuacionAmPm[$colIndex] += 10; // Marcar que incluye AM/PM
                }
                // ✅ HORA SIN AM/PM (formato: "6:41:37")
                else if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $valor)) {
                    $puntuacionHora[$colIndex] += 3;
                }

                // ✅ AM/PM SEPARADO
                if (strtoupper($valor) == 'AM' || strtoupper($valor) == 'PM') {
                    $puntuacionAmPm[$colIndex] += 5;
                }
            }
        }

        // ✅ DETERMINAR COLUMNAS
        $colFecha = array_search(max($puntuacionFecha), $puntuacionFecha);
        $colHora = array_search(max($puntuacionHora), $puntuacionHora);

        // ✅ DETECTAR SI AM/PM ESTÁ EN LA MISMA COLUMNA
        $horaIncluyeAmPm = ($puntuacionAmPm[$colHora] > 5);
        $colAmPm = null;

        if (!$horaIncluyeAmPm) {
            // Buscar columna AM/PM separada
            foreach ($puntuacionAmPm as $col => $puntaje) {
                if ($col != $colHora && $puntaje > 3) {
                    $colAmPm = $col;
                    break;
                }
            }
        }

        // ✅ LOGS
        error_log("📊 RESULTADO:");
        error_log("  Separador: '$separador'");
        error_log("  Fecha: Col[$colFecha] = {$puntuacionFecha[$colFecha]} pts");
        error_log("  Hora: Col[$colHora] = {$puntuacionHora[$colHora]} pts " . ($horaIncluyeAmPm ? "(incluye AM/PM)" : ""));
        if ($colAmPm !== null) {
            error_log("  AM/PM: Col[$colAmPm] = {$puntuacionAmPm[$colAmPm]} pts");
        }

        // ✅ VALIDAR
        if ($colFecha === false || $colHora === false) {
            error_log("❌ Detección incompleta");
            return null;
        }

        error_log("✅ COLUMNAS DETECTADAS");

        return [
            'separador' => $separador,
            'colFecha' => $colFecha,
            'colHora' => $colHora,
            'colAmPm' => $colAmPm,
            'horaIncluyeAmPm' => $horaIncluyeAmPm
        ];
    }
    /**
     * Parsea fecha y hora desde partes separadas
     */
    private function parseDateTimeFromParts($fechaTexto, $horaTexto, $ampm = '')
    {
        // Parsear fecha (DD/MM/YYYY o DD/MM/YY)
        $separador = '/';
        if (strpos($fechaTexto, '-') !== false) {
            $separador = '-';
        }

        $fechaComponentes = explode($separador, $fechaTexto);
        if (count($fechaComponentes) != 3) {
            return false;
        }

        $dia = intval($fechaComponentes[0]);
        $mes = intval($fechaComponentes[1]);
        $anio = intval($fechaComponentes[2]);

        // Convertir año corto a completo
        if ($anio < 100) {
            $anio = 2000 + $anio;
        }

        if (!checkdate($mes, $dia, $anio)) {
            return false;
        }

        // Parsear hora (HH:MM:SS o H:MM:SS)
        $horaComponentes = explode(':', $horaTexto);
        $hora = intval($horaComponentes[0]);
        $minuto = isset($horaComponentes[1]) ? intval($horaComponentes[1]) : 0;
        $segundo = isset($horaComponentes[2]) ? intval($horaComponentes[2]) : 0;

        // Convertir AM/PM a formato 24 horas
        if (!empty($ampm)) {
            $ampm = strtoupper($ampm);
            if ($ampm == 'PM' && $hora < 12) {
                $hora += 12;
            } elseif ($ampm == 'AM' && $hora == 12) {
                $hora = 0;
            }
        }

        return sprintf('%04d-%02d-%02d %02d:%02d:%02d', $anio, $mes, $dia, $hora, $minuto, $segundo);
    }


    /**
     * Parsea fecha y hora con detección inteligente de formato
     */
    private $formatoFechaDetectado = null;

  private function parseDateTimeFromPartsInteligente($fechaTexto, $horaTexto, $ampm = '') {
    // Parsear fecha
    $separador = '/';
    if (strpos($fechaTexto, '-') !== false) {
        $separador = '-';
    }
    
    $fechaComponentes = explode($separador, $fechaTexto);
    if (count($fechaComponentes) != 3) {
        error_log("❌ Formato inválido: $fechaTexto");
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
        error_log("❌ Fecha inválida: mes=$mes, día=$dia, año=$anio (original: $fechaTexto)");
        return false;
    }
    
    // Parsear hora
    $horaComponentes = explode(':', $horaTexto);
    if (count($horaComponentes) < 2) {
        error_log("❌ Hora inválida: $horaTexto");
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
        error_log("❌ Hora fuera de rango: $hora:$minuto:$segundo");
        return false;
    }
    
    $resultado = sprintf('%04d-%02d-%02d %02d:%02d:%02d', $anio, $mes, $dia, $hora, $minuto, $segundo);
    
    return $resultado;
}
private function detectarFormatoFechaGlobal() {
    try {
        // Obtener archivo temporal actual
        $tempDir = '../controller/uploads/temp/';
        $files = glob($tempDir . 'temp_*');
        
        if (empty($files)) {
            error_log("⚠️ No hay archivo temporal, usando MM/DD/YY por defecto");
            return 'MM/DD/YY';
        }
        
        // Usar el más reciente
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        $filePath = $files[0];
        
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return 'MM/DD/YY';
        }
        
        $contadorParte1Mayor12 = 0; // DD/MM
        $contadorParte2Mayor12 = 0; // MM/DD
        $fechasAnalizadas = 0;
        $maxFechas = 200; // Analizar hasta 200 fechas
        
        while (($line = fgets($handle)) !== false && $fechasAnalizadas < $maxFechas) {
            $line = trim($line);
            if (empty($line)) continue;
            
            $lineLower = strtolower($line);
            
            // Saltar encabezados
            if (strpos($lineLower, 'genwind') !== false || 
                strpos($lineLower, 'time') !== false || 
                strpos($lineLower, 'date') !== false) {
                continue;
            }
            
            // Detectar separador
            $separador = (substr_count($line, ';') > substr_count($line, ',')) ? ';' : ',';
            $campos = array_map('trim', explode($separador, $line));
            
            // Buscar primera fecha en la línea
            foreach ($campos as $valor) {
                if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-]\d{2,4}$/', $valor, $matches)) {
                    $parte1 = intval($matches[1]);
                    $parte2 = intval($matches[2]);
                    
                    // ✅ Contar evidencias
                    if ($parte1 > 12) {
                        $contadorParte1Mayor12++; // Solo puede ser DD/MM
                    }
                    if ($parte2 > 12) {
                        $contadorParte2Mayor12++; // Solo puede ser MM/DD
                    }
                    
                    $fechasAnalizadas++;
                    break; // Solo analizar primera fecha de cada línea
                }
            }
        }
        
        fclose($handle);
        
        error_log("📊 Análisis de formato:");
        error_log("  Fechas analizadas: $fechasAnalizadas");
        error_log("  Parte 1 > 12 (DD/MM): $contadorParte1Mayor12");
        error_log("  Parte 2 > 12 (MM/DD): $contadorParte2Mayor12");
        
        // ✅ DECISIÓN BASADA EN EVIDENCIA
        if ($contadorParte2Mayor12 > 0) {
            // Si la segunda parte es > 12, definitivamente es MM/DD
            error_log("✓ Formato: MM/DD/YY (evidencia clara)");
            return 'MM/DD/YY';
        } else if ($contadorParte1Mayor12 > 0) {
            // Si la primera parte es > 12, definitivamente es DD/MM
            error_log("✓ Formato: DD/MM/YY (evidencia clara)");
            return 'DD/MM/YY';
        } else {
            // Ambiguo: analizar el rango de valores
            // Si hay muchas fechas con parte1 > parte2, probablemente sea DD/MM
            // En archivos meteorológicos, MM/DD es más común en USA
            error_log("⚠️ Formato ambiguo, asumiendo MM/DD/YY (estándar USA)");
            return 'MM/DD/YY';
        }
        
    } catch (Exception $e) {
        error_log("Error detectando formato: " . $e->getMessage());
        return 'MM/DD/YY';
    }
}

    /**
     * Parsea fecha y hora desde texto
     */
    private function parseDateTimeString($dateTimeText)
    {
        $dateTimeText = trim($dateTimeText);

        $formats = [
            'Y-m-d H:i:s',
            'd/m/Y H:i:s',
            'd-m-Y H:i:s',
            'Y/m/d H:i:s',
            'Y-m-d H:i',
            'd/m/Y H:i',
            'd-m-Y H:i',
            'Y/m/d H:i'
        ];

        foreach ($formats as $format) {
            $date = DateTime::createFromFormat($format, $dateTimeText);
            if ($date !== false) {
                return $date->format('Y-m-d H:i:s');
            }
        }

        $timestamp = strtotime($dateTimeText);
        if ($timestamp !== false) {
            return date('Y-m-d H:i:s', $timestamp);
        }

        return false;
    }
}
?>