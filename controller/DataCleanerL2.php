<?php
/**
 * DataCleanerL2 - Clase para limpieza y procesamiento de datos meteorológicos L2 en PHP puro
 * 
 * Elimina la dependencia de Python y procesa CSV/TXT directamente
 * Basado en la lógica de CLoadDataL1 pero adaptado para datos L2
 */

class DataCleanerL2
{
    // Umbrales para datos L2
    private $umbrales = [
        'temperatura' => ['min' => -80.0, 'max' => 60.0],
        'humedad' => ['min' => 0.0, 'max' => 100.0],
        'presion' => ['min' => 500.0, 'max' => 1080.0],
        'radiacion' => ['min' => 0.0, 'max' => 1373.0],
        'viento_velocidad' => ['min' => 0.0, 'max' => 75.0],
        'viento_direccion' => ['min' => 0.0, 'max' => 360.0]
    ];

    // Mapeo de columnas del dataset ESP OCH a nombres estándar
    private $mappingColumnas = [
        'Stat_TA_1h' => 'temp_promedio_hora',
        'Stat_RH_1h' => 'humedad_promedio_hora',
        'Stat_PA_1h' => 'presion_promedio_hora',
        'Sum_SR_Glob_1h' => 'radiacion_total_hora',
        'GenWind_1h' => 'velocidad_viento_promedio_hora',
        'GenWind_Dir_1h' => 'direccion_viento_promedio_hora'
    ];

    /**
     * Procesa archivo CSV/TXT y retorna datos limpios para L2
     */
    public function processCSVFile($filePath, $estacionInfo, $idCarga)
    {
        set_time_limit(0);
        ini_set('memory_limit', '2048M');

        $registrosValidos = 0;
        $registrosRechazados = 0;
        $fechaInicio = null;
        $fechaFin = null;
        $datosCompletos = []; // Todas las columnas originales limpias
        $datosL2 = []; // Solo columnas para BD

        try {
            if (!file_exists($filePath)) {
                throw new Exception('Archivo no encontrado');
            }

            // Detectar columnas y estructura
            $deteccion = $this->detectarColumnas($filePath);
            if (!$deteccion) {
                throw new Exception('No se pudieron detectar las columnas de datos');
            }

            $separador = $deteccion['separador'];
            $handle = fopen($filePath, 'r');
            if (!$handle) {
                throw new Exception('No se pudo abrir el archivo');
            }

            $lineNumber = 0;
            $headerOriginal = null;

            while (($line = fgets($handle)) !== false) {
                $lineNumber++;
                $line = trim($line);

                if (empty($line))
                    continue;

                $campos = array_map('trim', explode($separador, $line));

                // Guardar header original (primera línea con datos)
                if ($headerOriginal === null && $this->esLineaHeader($line, $separador)) {
                    $headerOriginal = $campos;
                    continue;
                }

                // Saltar líneas de encabezado o etiquetas
                if ($this->esLineaEncabezado($line)) {
                    continue;
                }

                // Intentar extraer fecha/hora
                $fechaTexto = isset($campos[$deteccion['colFecha']]) ? $campos[$deteccion['colFecha']] : '';
                $horaTexto = isset($campos[$deteccion['colHora']]) ? $campos[$deteccion['colHora']] : '';
                $ampm = '';

                if ($deteccion['horaIncluyeAmPm'] && !empty($horaTexto)) {
                    // Extraer AM/PM de la hora
                    if (preg_match('/(AM|PM|am|pm)/i', $horaTexto, $matches)) {
                        $ampm = strtoupper($matches[1]);
                        $horaTexto = trim(preg_replace('/(AM|PM|am|pm)/i', '', $horaTexto));
                    }
                } elseif ($deteccion['colAmPm'] !== null && isset($campos[$deteccion['colAmPm']])) {
                    $ampm = trim($campos[$deteccion['colAmPm']]);
                }

                // Parsear fecha/hora
                $resultado = $this->parseDateTimeComplete($fechaTexto, $horaTexto, $ampm);
                if (!$resultado) {
                    $registrosRechazados++;
                    continue;
                }

                $fecha = $resultado['fecha'];
                $hora = $resultado['hora'];
                $fechaHora = $resultado['fechahora'];

                // Actualizar rango de fechas
                if ($fechaInicio === null || $fechaHora < $fechaInicio) {
                    $fechaInicio = $fechaHora;
                }
                if ($fechaFin === null || $fechaHora > $fechaFin) {
                    $fechaFin = $fechaHora;
                }

                // Procesar valores numéricos para todas las columnas (CSV completo limpio)
                $registroCompleto = [];
                foreach ($campos as $idx => $valor) {
                    if ($idx == $deteccion['colFecha'] || $idx == $deteccion['colHora']) {
                        $registroCompleto[] = $valor; // Mantener fecha/hora tal cual
                    } else {
                        $numerico = $this->parseNumericValue($valor);
                        $registroCompleto[] = ($numerico !== null) ? $numerico : '';
                    }
                }
                $datosCompletos[] = $registroCompleto;

                // Extraer datos L2 usando mapeo de columnas
                $datosL2Item = [
                    'fecha_hora' => $fechaHora,
                    'fecha' => $fecha,
                    'hora' => $hora
                ];

                // Buscar y validar cada variable L2
                foreach ($this->mappingColumnas as $nombreOriginal => $nombreEstandar) {
                    // Buscar la columna en el header
                    $colIndex = $headerOriginal ? array_search($nombreOriginal, $headerOriginal) : false;

                    if ($colIndex !== false && isset($campos[$colIndex])) {
                        $valorTexto = $campos[$colIndex];
                        $valorNumerico = $this->parseNumericValue($valorTexto);

                        // Validar umbrales
                        $variable = $this->mapearVariableParaUmbral($nombreEstandar);
                        $valorNumerico = $this->validateThresholds($variable, $valorNumerico);

                        $datosL2Item[$nombreEstandar] = $valorNumerico;
                    } else {
                        $datosL2Item[$nombreEstandar] = null;
                    }
                }

                $datosL2[] = $datosL2Item;
                $registrosValidos++;
            }

            fclose($handle);

            error_log("✓ Procesado L2: $registrosValidos válidos, $registrosRechazados rechazados");

            return [
                'success' => true,
                'registros_validos' => $registrosValidos,
                'registros_rechazados' => $registrosRechazados,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'datos_l2' => $datosL2,
                'datos_completos' => $datosCompletos,
                'header_original' => $headerOriginal,
                'separador' => $separador
            ];

        } catch (Exception $e) {
            error_log("❌ Error en processCSVFile L2: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Detecta automáticamente las columnas de fecha/hora en el archivo
     */
    private function detectarColumnas($filePath)
    {
        try {
            $handle = fopen($filePath, 'r');
            if (!$handle) {
                return null;
            }

            // Detectar separador y leer primeras líneas
            $separador = null;
            $lineasConDatos = [];
            $lineNumber = 0;
            $maxLineas = 100;

            while (($line = fgets($handle)) !== false && $lineNumber < $maxLineas) {
                $lineNumber++;
                $line = trim($line);

                if (empty($line))
                    continue;

                // Detectar separador
                if ($separador === null) {
                    $separador = (substr_count($line, ',') > substr_count($line, ';')) ? ',' : ';';
                }

                // Recolectar líneas con datos reales
                if ($this->lineaTieneValoresReales($line, $separador)) {
                    $lineasConDatos[] = $line;
                    if (count($lineasConDatos) >= 50) {
                        break;
                    }
                }
            }

            fclose($handle);

            if (count($lineasConDatos) < 3) {
                error_log("❌ Insuficientes líneas con datos: " . count($lineasConDatos));
                return null;
            }

            return $this->analizarLineasParaColumnas($lineasConDatos, $separador);

        } catch (Exception $e) {
            error_log("❌ Error detectando columnas: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Analiza líneas para determinar qué columnas contienen fecha/hora
     */
    private function analizarLineasParaColumnas($lineas, $separador)
    {
        if (empty($lineas)) {
            return null;
        }

        $primeraLinea = array_map('trim', explode($separador, $lineas[0]));
        $totalColumnas = count($primeraLinea);

        $puntuacionFecha = array_fill(0, $totalColumnas, 0);
        $puntuacionHora = array_fill(0, $totalColumnas, 0);
        $puntuacionAmPm = array_fill(0, $totalColumnas, 0);

        // Analizar cada línea
        foreach ($lineas as $linea) {
            $campos = array_map('trim', explode($separador, $linea));

            foreach ($campos as $colIndex => $valor) {
                // Detectar fecha (formato MM/DD/YY o similar)
                if (preg_match('#^\d{1,2}/\d{1,2}/\d{2,4}$#', $valor)) {
                    $puntuacionFecha[$colIndex] += 10;
                } elseif (preg_match('#^\d{4}-\d{2}-\d{2}$#', $valor)) {
                    $puntuacionFecha[$colIndex] += 10;
                }

                // Detectar hora (formato HH:MM:SS o HH:MM)
                if (preg_match('#^\d{1,2}:\d{2}(:\d{2})?$#', $valor)) {
                    $puntuacionHora[$colIndex] += 10;
                } elseif (preg_match('#^\d{1,2}:\d{2}(:\d{2})?\s*(AM|PM|am|pm)$#', $valor)) {
                    $puntuacionHora[$colIndex] += 15;
                    $puntuacionAmPm[$colIndex] += 5;
                }

                // Detectar AM/PM en columna separada
                if (preg_match('/^(AM|PM|am|pm)$/i', $valor)) {
                    $puntuacionAmPm[$colIndex] += 10;
                }
            }
        }

        // Determinar columnas
        $colFecha = array_search(max($puntuacionFecha), $puntuacionFecha);
        $colHora = array_search(max($puntuacionHora), $puntuacionHora);

        // Verificar si la hora incluye AM/PM
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

        error_log("✓ Columnas detectadas L2: Fecha=$colFecha, Hora=$colHora, AmPm=" . ($colAmPm ?? 'incluido'));

        return [
            'separador' => $separador,
            'colFecha' => $colFecha,
            'colHora' => $colHora,
            'colAmPm' => $colAmPm,
            'horaIncluyeAmPm' => $horaIncluyeAmPm
        ];
    }

    /**
     * Parsea fecha/hora y la mantiene en UTC-5 (hora local Ecuador)
     */
    private function parseDateTimeComplete($fechaTexto, $horaTexto, $ampm = '')
    {
        // Parsear fecha
        $separador = '/';
        if (strpos($fechaTexto, '-') !== false) {
            $separador = '-';
        }

        $fechaComponentes = explode($separador, $fechaTexto);
        if (count($fechaComponentes) != 3) {
            return false;
        }

        // Formato MM/DD/YY (estadounidense)
        $mes = intval($fechaComponentes[0]);
        $dia = intval($fechaComponentes[1]);
        $anio = intval($fechaComponentes[2]);

        // Convertir año de 2 dígitos a 4 dígitos
        if ($anio < 100) {
            $anio = ($anio >= 70) ? (1900 + $anio) : (2000 + $anio);
        }

        // Parsear hora
        $horaComponentes = explode(':', $horaTexto);
        if (count($horaComponentes) < 2) {
            return false;
        }

        $hora = intval($horaComponentes[0]);
        $minuto = intval($horaComponentes[1]);
        $segundo = isset($horaComponentes[2]) ? intval($horaComponentes[2]) : 0;

        // Manejar formato 12 horas con AM/PM
        if (!empty($ampm)) {
            $ampm = strtoupper(trim($ampm));
            if ($ampm === 'PM' && $hora < 12) {
                $hora += 12;
            } elseif ($ampm === 'AM' && $hora == 12) {
                $hora = 0;
            }
        }

        // Validar
        if ($hora < 0 || $hora > 23 || $minuto < 0 || $minuto > 59 || $segundo < 0 || $segundo > 59) {
            return false;
        }

        if ($mes < 1 || $mes > 12 || $dia < 1 || $dia > 31) {
            return false;
        }

        // Retornar en formato UTC-5 (hora local Ecuador, SIN convertir)
        return [
            'fecha' => sprintf('%04d-%02d-%02d', $anio, $mes, $dia),
            'hora' => sprintf('%02d:%02d:%02d', $hora, $minuto, $segundo),
            'fechahora' => sprintf('%04d-%02d-%02d %02d:%02d:%02d', $anio, $mes, $dia, $hora, $minuto, $segundo)
        ];
    }

    /**
     * Parsea valor numérico controlando decimales y valores inválidos
     */
    private function parseNumericValue($value)
    {
        $value = trim($value);

        // Valores inválidos
        if (
            $value === '' ||
            strtoupper($value) === 'NULL' ||
            strtoupper($value) === 'INVALID' ||
            strtoupper($value) === 'FAILED' ||
            strtoupper($value) === 'N/A' ||
            strtoupper($value) === 'NA'
        ) {
            return null;
        }

        // Reemplazar coma por punto (separador decimal)
        $value = str_replace(',', '.', $value);

        // Eliminar caracteres no numéricos (excepto punto, signo menos, signo más)
        $value = preg_replace('/[^0-9\.\-+]/', '', $value);

        if ($value === '' || $value === '.' || $value === '+' || $value === '-') {
            return null;
        }

        $numericValue = floatval($value);

        if (is_nan($numericValue) || !is_finite($numericValue)) {
            return null;
        }

        return $numericValue;
    }

    /**
     * Valida que un valor esté dentro de los umbrales permitidos
     */
    private function validateThresholds($variable, $value)
    {
        if ($value === null) {
            return null;
        }

        if (!isset($this->umbrales[$variable])) {
            return $value; // Sin umbral definido, aceptar
        }

        $umbral = $this->umbrales[$variable];

        if ($value < $umbral['min'] || $value > $umbral['max']) {
            error_log("⚠️ Valor fuera de umbral para $variable: $value (rango: {$umbral['min']}-{$umbral['max']})");
            return null; // Fuera de rango, marcar como NULL
        }

        return $value;
    }

    /**
     * Mapea nombre de columna estándar a nombre de variable para umbrales
     */
    private function mapearVariableParaUmbral($nombreEstandar)
    {
        $map = [
            'temp_promedio_hora' => 'temperatura',
            'humedad_promedio_hora' => 'humedad',
            'presion_promedio_hora' => 'presion',
            'radiacion_total_hora' => 'radiacion',
            'viento_promedio_hora' => 'viento_velocidad',
            'direccion_viento_hora' => 'viento_direccion'
        ];

        return $map[$nombreEstandar] ?? null;
    }

    /**
     * Verifica si una línea contiene valores meteorológicos reales
     */
    private function lineaTieneValoresReales($line, $separador)
    {
        $campos = array_map('trim', explode($separador, $line));

        foreach ($campos as $valor) {
            if (is_numeric($valor)) {
                $num = floatval($valor);

                // Rangos típicos de variables meteorológicas L2
                if (
                    ($num >= -80 && $num <= 60) ||      // Temperatura
                    ($num >= 0 && $num <= 100) ||       // Humedad
                    ($num >= 500 && $num <= 1080) ||    // Presión
                    ($num >= 0 && $num <= 1373) ||      // Radiación
                    ($num >= 0 && $num <= 75)           // Viento
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Verifica si una línea es un encabezado
     */
    private function esLineaEncabezado($line)
    {
        $lineLower = strtolower($line);

        // Palabras clave de encabezados
        $keywords = [
            'date',
            'time',
            'temp',
            'humidity',
            'pressure',
            'radiation',
            'wind',
            'fecha',
            'hora',
            'temperatura',
            'humedad',
            'presion',
            'radiacion',
            'viento',
            'avg',
            'max',
            'min',
            'stat_',
            'sum_',
            'genwind'
        ];

        foreach ($keywords as $keyword) {
            if (strpos($lineLower, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verifica si una línea es el header con nombres de columnas
     */
    private function esLineaHeader($line, $separador)
    {
        $lineLower = strtolower($line);

        // El header debe tener nombres de variables conocidas
        $variablesL2 = ['stat_ta', 'stat_rh', 'stat_pa', 'sum_sr', 'genwind'];

        $coincidencias = 0;
        foreach ($variablesL2 as $var) {
            if (strpos($lineLower, $var) !== false) {
                $coincidencias++;
            }
        }

        // Si tiene al menos 2 variables conocidas, es el header
        return $coincidencias >= 2;
    }

    /**
     * Genera dos archivos CSV: uno para BD y otro completo limpio
     */
    public function generarDosCSVs($datosL2, $datosCompletos, $headerOriginal, $separador, $estacionInfo, $fechaInicio, $fechaFin)
    {
        $timestamp = date('Ymd_His');
        $nombreEstacion = preg_replace('/[^a-zA-Z0-9_]/', '_', $estacionInfo['nombre']);

        $dirBase = __DIR__ . "/../uploads/Limpios/L2/Estacion_{$nombreEstacion}";
        if (!is_dir($dirBase)) {
            mkdir($dirBase, 0755, true);
        }

        // 1. CSV Limpio para BD (solo columnas necesarias)
        $csvLimpioBD = $dirBase . "/CSV_limpio_BD_{$timestamp}.csv";
        $handleBD = fopen($csvLimpioBD, 'w');

        // Header para BD
        fputcsv($handleBD, [
            'fecha',
            'hora',
            'temp_promedio_hora',
            'humedad_promedio_hora',
            'presion_promedio_hora',
            'radiacion_total_hora',
            'viento_promedio_hora',
            'direccion_viento_hora'
        ]);

        // Datos para BD
        foreach ($datosL2 as $item) {
            fputcsv($handleBD, [
                $item['fecha'],
                $item['hora'],
                $item['temp_promedio_hora'] ?? '',
                $item['humedad_promedio_hora'] ?? '',
                $item['presion_promedio_hora'] ?? '',
                $item['radiacion_total_hora'] ?? '',
                $item['viento_promedio_hora'] ?? '',
                $item['direccion_viento_hora'] ?? ''
            ]);
        }
        fclose($handleBD);

        // 2. CSV Completo Limpio (todas las columnas originales, pero limpias)
        $csvCompletoLimpio = $dirBase . "/CSV_completo_limpio_{$timestamp}.csv";
        $handleCompleto = fopen($csvCompletoLimpio, 'w');

        // Header original
        if ($headerOriginal) {
            fputcsv($handleCompleto, $headerOriginal);
        }

        // Datos completos limpios
        foreach ($datosCompletos as $registro) {
            fputcsv($handleCompleto, $registro);
        }
        fclose($handleCompleto);

        error_log("✓ CSVs generados: " . basename($csvLimpioBD) . ", " . basename($csvCompletoLimpio));

        return [
            'csv_limpio_bd' => $csvLimpioBD,
            'csv_completo_limpio' => $csvCompletoLimpio
        ];
    }
}
