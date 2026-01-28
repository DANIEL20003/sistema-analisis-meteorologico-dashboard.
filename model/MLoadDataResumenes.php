<?php

require_once('../config/database.php');

class MLoadDataResumenes
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
            $query = "SELECT id_estacion, codigo, nombre, provincia, canton 
                     FROM estaciones 
                     WHERE id_estacion = $1 AND estado_activo = true";

            $result = pg_query_params($this->conn, $query, [$id_estacion]);

            if (!$result) {
                throw new Exception('Error en consulta: ' . pg_last_error($this->conn));
            }

            return pg_fetch_assoc($result);
        } catch (Exception $e) {
            throw new Exception('Error al obtener estación: ' . $e->getMessage());
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
                throw new Exception('Error al crear carga: ' . pg_last_error($this->conn));
            }

            $row = pg_fetch_assoc($result);
            return $row['id_carga'];
        } catch (Exception $e) {
            throw new Exception('Error al crear carga: ' . $e->getMessage());
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
                throw new Exception('Error al actualizar carga: ' . pg_last_error($this->conn));
            }

            return true;
        } catch (Exception $e) {
            throw new Exception('Error al actualizar carga: ' . $e->getMessage());
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
                throw new Exception('Error al actualizar estado: ' . pg_last_error($this->conn));
            }

            return true;
        } catch (Exception $e) {
            throw new Exception('Error al actualizar estado: ' . $e->getMessage());
        }
    }

    public function getCargaArchivoInfo($idCarga)
    {
        try {
            $query = "SELECT * FROM cargas_archivos WHERE id_carga = $1";
            $result = pg_query_params($this->conn, $query, [$idCarga]);

            if (!$result) {
                throw new Exception('Error al obtener info de carga: ' . pg_last_error($this->conn));
            }

            return pg_fetch_assoc($result);
        } catch (Exception $e) {
            throw new Exception('Error al obtener info de carga: ' . $e->getMessage());
        }
    }

    public function saveTempFilePath($idCarga, $tempFilePath)
    {
        try {
            $tempFileName = 'TEMP_RESUMENES_' . $idCarga . '_' . basename($tempFilePath);

            $query = "UPDATE cargas_archivos 
                     SET nombre_archivo = $1
                     WHERE id_carga = $2";

            $result = pg_query_params($this->conn, $query, [$tempFileName, $idCarga]);

            if (!$result) {
                throw new Exception('Error al guardar ruta temporal: ' . pg_last_error($this->conn));
            }

            return true;
        } catch (Exception $e) {
            throw new Exception('Error al guardar ruta temporal: ' . $e->getMessage());
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
            if ($row && strpos($row['nombre_archivo'], 'TEMP_RESUMENES_') === 0) {
                $pattern = '/^TEMP_RESUMENES_\d+_(.+)$/';
                if (preg_match($pattern, $row['nombre_archivo'], $matches)) {
                    return '../controller/uploads/temp/' . $matches[1];
                }
            }

            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    public function verificarAniosDuplicados($idEstacion, $anios)
    {
        try {
            if (empty($anios)) {
                return [];
            }

            $aniosStr = implode(',', array_map('intval', $anios));

            $query = "SELECT DISTINCT anio 
                     FROM resumenes_climatologicos 
                     WHERE id_estacion = $1 
                     AND anio IN ($aniosStr)";

            $result = pg_query_params($this->conn, $query, [$idEstacion]);

            if (!$result) {
                throw new Exception('Error verificando duplicados: ' . pg_last_error($this->conn));
            }

            $duplicados = [];
            while ($row = pg_fetch_assoc($result)) {
                $duplicados[] = $row['anio'];
            }

            return $duplicados;
        } catch (Exception $e) {
            error_log("Error verificando duplicados: " . $e->getMessage());
            return [];
        }
    }

    public function insertResumenesBatch($lote)
    {
        try {
            if (empty($lote)) {
                return true;
            }

            // Iniciar transacción
            pg_query($this->conn, "BEGIN");

            $insertados = 0;
            $errores = 0;

            foreach ($lote as $registro) {
                // ✅ REDONDEAR A 4 DECIMALES antes de insertar
                $registro['temperatura_aire'] = $registro['temperatura_aire'] !== null ? 
                    round($registro['temperatura_aire'], 4) : null;
                $registro['humedad_relativa'] = $registro['humedad_relativa'] !== null ? 
                    round($registro['humedad_relativa'], 4) : null;
                $registro['presion_atmosferica'] = $registro['presion_atmosferica'] !== null ? 
                    round($registro['presion_atmosferica'], 4) : null;
                $registro['radiacion_global'] = $registro['radiacion_global'] !== null ? 
                    round($registro['radiacion_global'], 4) : null;
                $registro['direccion_viento'] = $registro['direccion_viento'] !== null ? 
                    round($registro['direccion_viento'], 4) : null;
                $registro['velocidad_viento'] = $registro['velocidad_viento'] !== null ? 
                    round($registro['velocidad_viento'], 4) : null;

                $query = "INSERT INTO resumenes_climatologicos (
                        id_carga, 
                        id_estacion, 
                        anio, 
                        mes, 
                        tipo_estadistica, 
                        temperatura_aire, 
                        humedad_relativa, 
                        presion_atmosferica, 
                        radiacion_global, 
                        direccion_viento, 
                        velocidad_viento
                      ) VALUES (
                        $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11
                      )
                      ON CONFLICT (id_estacion, anio, mes, tipo_estadistica, id_carga) 
                      DO UPDATE SET
                        temperatura_aire = CASE 
                            WHEN EXCLUDED.temperatura_aire IS NOT NULL 
                            THEN EXCLUDED.temperatura_aire 
                            ELSE resumenes_climatologicos.temperatura_aire 
                        END,
                        humedad_relativa = CASE 
                            WHEN EXCLUDED.humedad_relativa IS NOT NULL 
                            THEN EXCLUDED.humedad_relativa 
                            ELSE resumenes_climatologicos.humedad_relativa 
                        END,
                        presion_atmosferica = CASE 
                            WHEN EXCLUDED.presion_atmosferica IS NOT NULL 
                            THEN EXCLUDED.presion_atmosferica 
                            ELSE resumenes_climatologicos.presion_atmosferica 
                        END,
                        radiacion_global = CASE 
                            WHEN EXCLUDED.radiacion_global IS NOT NULL 
                            THEN EXCLUDED.radiacion_global 
                            ELSE resumenes_climatologicos.radiacion_global 
                        END,
                        direccion_viento = CASE 
                            WHEN EXCLUDED.direccion_viento IS NOT NULL 
                            THEN EXCLUDED.direccion_viento 
                            ELSE resumenes_climatologicos.direccion_viento 
                        END,
                        velocidad_viento = CASE 
                            WHEN EXCLUDED.velocidad_viento IS NOT NULL 
                            THEN EXCLUDED.velocidad_viento 
                            ELSE resumenes_climatologicos.velocidad_viento 
                        END";

                $params = [
                    $registro['id_carga'],
                    $registro['id_estacion'],
                    $registro['anio'],
                    $registro['mes'],
                    $registro['tipo_estadistica'],
                    $registro['temperatura_aire'],
                    $registro['humedad_relativa'],
                    $registro['presion_atmosferica'],
                    $registro['radiacion_global'],
                    $registro['direccion_viento'],
                    $registro['velocidad_viento']
                ];

                $result = pg_query_params($this->conn, $query, $params);

                if ($result) {
                    $insertados++;
                } else {
                    $errores++;
                    error_log("⚠ Error insertando registro: " . pg_last_error($this->conn));
                }
            }

            // Confirmar transacción
            pg_query($this->conn, "COMMIT");
            
            error_log("  ✓ Lote procesado: $insertados insertados, $errores errores");
            
            return true;

        } catch (Exception $e) {
            // Revertir transacción en caso de error
            pg_query($this->conn, "ROLLBACK");
            error_log("❌ Error en insertResumenesBatch: " . $e->getMessage());
            throw new Exception('Error en insertResumenesBatch: ' . $e->getMessage());
        }
    }
}
?>