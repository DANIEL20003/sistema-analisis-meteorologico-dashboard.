<?php

require_once('../config/database.php');

class MLoadDataL2
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

    public function insertLogCarga($idCarga, $idAdministrador, $mensaje, $tipoNivel = 'EL2')
    {
        try {
            $query = "INSERT INTO logs_carga_archivos (id_carga, id_administrador, mensaje, tipo_nivel, fecha) VALUES ($1, $2, $3, $4, NOW())";
            $result = pg_query_params($this->conn, $query, [$idCarga, $idAdministrador, $mensaje, $tipoNivel]);
            if (!$result) {
                error_log('Error insertando log de carga L2: ' . pg_last_error($this->conn));
                return false;
            }
            return true;
        } catch (Exception $e) {
            error_log('Exception insertLogCarga L2: ' . $e->getMessage());
            return false;
        }
    }

    public function insertL2Data($idCarga, $datosL2)
    {
        try {
            $cargaInfo = $this->getCargaArchivoInfo($idCarga);
            if (!$cargaInfo) {
                throw new Exception('Información de carga no encontrada');
            }

            $batchSize = 5000;
            $totalRegistros = count($datosL2);
            $registrosInsertados = 0;
            $registrosSaltados = 0;

            for ($offset = 0; $offset < $totalRegistros; $offset += $batchSize) {
                $lote = array_slice($datosL2, $offset, $batchSize);
                pg_query($this->conn, "BEGIN");
                try {
                    $values = [];
                    $params = [];
                    $paramIndex = 1;

                    foreach ($lote as $dato) {
                        $fechaHora = $dato['fecha_hora'];

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
                        $params[] = 'L2';
                    }

                    $queryRegistros = "INSERT INTO registros (id_carga, id_estacion, fecha_hora, tipo_nivel) 
                                   VALUES " . implode(', ', $values) . "
                                   ON CONFLICT (id_estacion, fecha_hora, tipo_nivel, id_carga) DO NOTHING
                                   RETURNING id_registro, fecha_hora";

                    $resultRegistros = pg_query_params($this->conn, $queryRegistros, $params);

                    if (!$resultRegistros) {
                        throw new Exception('Error insertando registros L2: ' . pg_last_error($this->conn));
                    }

                    $registrosInsertadosMap = [];
                    while ($row = pg_fetch_assoc($resultRegistros)) {
                        $registrosInsertadosMap[$row['fecha_hora']] = $row['id_registro'];
                    }

                    if (!empty($registrosInsertadosMap)) {
                        $valuesL2 = [];
                        $paramsL2 = [];
                        $paramIndexL2 = 1;

                        foreach ($lote as $dato) {
                            $fechaHora = $dato['fecha_hora'];

                            if (isset($registrosInsertadosMap[$fechaHora])) {
                                $idRegistro = $registrosInsertadosMap[$fechaHora];

                                $valuesL2[] = sprintf(
                                    "($%d, $%d, $%d, $%d, $%d, $%d, $%d, $%d, $%d)",
                                    $paramIndexL2++,
                                    $paramIndexL2++,
                                    $paramIndexL2++,
                                    $paramIndexL2++,
                                    $paramIndexL2++,
                                    $paramIndexL2++,
                                    $paramIndexL2++,
                                    $paramIndexL2++,
                                    $paramIndexL2++
                                );

                                $paramsL2[] = $idRegistro;
                                $paramsL2[] = $dato['fecha'];
                                $paramsL2[] = $dato['hora'];
                                $paramsL2[] = $dato['temp_promedio_hora'];
                                $paramsL2[] = $dato['humedad_promedio_hora'];
                                $paramsL2[] = $dato['presion_promedio_hora'];
                                $paramsL2[] = $dato['radiacion_total_hora'];
                                $paramsL2[] = $dato['viento_promedio_hora'];
                                $paramsL2[] = $dato['direccion_viento_hora'] ?? null;

                                $registrosInsertados++;
                            } else {
                                $registrosSaltados++;
                            }
                        }

                        if (!empty($valuesL2)) {
                            $queryL2 = "INSERT INTO datos_l2 (id_registro, fecha, hora, temp_promedio_hora, humedad_promedio_hora, presion_promedio_hora, radiacion_total_hora, velocidad_viento_promedio_hora, direccion_viento_promedio_hora) 
                                    VALUES " . implode(', ', $valuesL2) . "
                                    ON CONFLICT (id_registro) DO NOTHING";

                            $resultL2 = pg_query_params($this->conn, $queryL2, $paramsL2);

                            if (!$resultL2) {
                                throw new Exception('Error insertando datos L2: ' . pg_last_error($this->conn));
                            }
                        }
                    }

                    pg_query($this->conn, "COMMIT");

                } catch (Exception $e) {
                    pg_query($this->conn, "ROLLBACK");
                    throw $e;
                }
            }

            error_log("✅ Inserción L2 completada: $registrosInsertados insertados, $registrosSaltados saltados");
            return true;

        } catch (Exception $e) {
            throw new Exception('Error al insertar datos L2: ' . $e->getMessage());
        }
    }

    /**
     * Extrae el rango de fechas de un archivo CSV
     * @param string $filePath Ruta del archivo CSV
     * @return array|null Array con 'fecha_inicio' y 'fecha_fin' o null si no se puede determinar
     */
    public function extraerRangoFechasCSV($filePath)
    {
        try {
            if (!file_exists($filePath)) {
                return null;
            }

            $handle = fopen($filePath, 'r');
            if (!$handle) {
                return null;
            }

            // Leer header
            $header = fgetcsv($handle);
            if (!$header) {
                fclose($handle);
                return null;
            }

            // Buscar índices de columnas de fecha/hora
            $dateCol = null;
            $timeCol = null;
            foreach ($header as $idx => $col) {
                $colLower = strtolower(trim($col));
                if (in_array($colLower, ['date', 'fecha', 'unnamed: 0']) && $dateCol === null) {
                    $dateCol = $idx;
                } elseif (in_array($colLower, ['time', 'hora', 'unnamed: 1']) && $timeCol === null) {
                    $timeCol = $idx;
                }
            }

            if ($dateCol === null || $timeCol === null) {
                fclose($handle);
                return null;
            }

            // Leer primera fila de datos
            $firstRow = fgetcsv($handle);
            if (!$firstRow) {
                fclose($handle);
                return null;
            }

            // Leer última fila de datos
            $lastRow = $firstRow;
            while (($row = fgetcsv($handle)) !== false) {
                $lastRow = $row;
            }
            fclose($handle);

            // Parsear fechas
            $fechaInicio = $this->parseFechaHora($firstRow[$dateCol], $firstRow[$timeCol]);
            $fechaFin = $this->parseFechaHora($lastRow[$dateCol], $lastRow[$timeCol]);

            if ($fechaInicio && $fechaFin) {
                return [
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin
                ];
            }

            return null;

        } catch (Exception $e) {
            error_log('Error extrayendo rango de fechas: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Parsea fecha/hora en diferentes formatos comunes
     */
    private function parseFechaHora($fecha, $hora)
    {
        try {
            $combined = trim($fecha) . ' ' . trim($hora);

            // Intentar formato m/d/y H:i:s A
            $dt = date_create_from_format('m/d/y H:i:s A', $combined);
            if ($dt) {
                return $dt->format('Y-m-d H:i:s');
            }

            // Intentar otros formatos comunes
            $formats = [
                'Y-m-d H:i:s',
                'd/m/Y H:i:s',
                'm/d/Y H:i:s',
                'Y-m-d H:i',
            ];

            foreach ($formats as $format) {
                $dt = date_create_from_format($format, $combined);
                if ($dt) {
                    return $dt->format('Y-m-d H:i:s');
                }
            }

            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Verifica si hay datos duplicados para una estación en un rango de fechas
     * @param int $idEstacion ID de la estación
     * @param string $fechaInicio Fecha de inicio (Y-m-d H:i:s)
     * @param string $fechaFin Fecha de fin (Y-m-d H:i:s)
     * @return array Array con información sobre duplicados
     */
    public function verificarDatosDuplicados($idEstacion, $fechaInicio, $fechaFin)
    {
        try {
            // Consultar cuántos registros existen en ese rango
            $query = "SELECT COUNT(*) as total
                     FROM registros r
                     INNER JOIN datos_l2 d ON r.id_registro = d.id_registro
                     WHERE r.id_estacion = $1 
                     AND r.tipo_nivel = 'L2'
                     AND r.fecha_hora BETWEEN $2 AND $3";

            $result = pg_query_params($this->conn, $query, [$idEstacion, $fechaInicio, $fechaFin]);

            if (!$result) {
                throw new Exception('Error consultando duplicados: ' . pg_last_error($this->conn));
            }

            $row = pg_fetch_assoc($result);
            $totalExistentes = (int) $row['total'];

            // Consultar el primer y último registro existente en ese rango
            $queryRango = "SELECT MIN(r.fecha_hora) as primera_fecha, MAX(r.fecha_hora) as ultima_fecha
                          FROM registros r
                          INNER JOIN datos_l2 d ON r.id_registro = d.id_registro
                          WHERE r.id_estacion = $1 
                          AND r.tipo_nivel = 'L2'
                          AND r.fecha_hora BETWEEN $2 AND $3";

            $resultRango = pg_query_params($this->conn, $queryRango, [$idEstacion, $fechaInicio, $fechaFin]);
            $rangoExistente = pg_fetch_assoc($resultRango);

            return [
                'tiene_duplicados' => $totalExistentes > 0,
                'total_registros_existentes' => $totalExistentes,
                'rango_existente' => $rangoExistente,
                'fecha_inicio_nueva' => $fechaInicio,
                'fecha_fin_nueva' => $fechaFin
            ];

        } catch (Exception $e) {
            error_log('Error verificando duplicados: ' . $e->getMessage());
            return [
                'tiene_duplicados' => false,
                'total_registros_existentes' => 0,
                'error' => $e->getMessage()
            ];
        }
    }
}
