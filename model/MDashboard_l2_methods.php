/**
* Obtiene datos para gráfica de Tendencia Anual L2
* Retorna promedios diarios de temperatura, humedad y presión
*/
public function obtenerTendenciaAnualL2($id_estacion, $anio, $mes)
{
$query = "SELECT
TO_CHAR(fecha_hora_agrupada, 'DD/MM') as etiqueta,
EXTRACT(DAY FROM fecha_hora_agrupada)::INTEGER as dia,
ROUND(temp_promedio::numeric, 2) as temperatura,
ROUND(humedad_promedio::numeric, 1) as humedad,
ROUND(presion_promedio::numeric, 2) as presion,
ROUND(radiacion_total::numeric, 2) as radiacion
FROM mv_l2_consolidado
WHERE id_estacion = $1
AND anio = $2
AND mes = $3
ORDER BY fecha_hora_agrupada ASC";

$result = pg_query_params($this->conn, $query, [$id_estacion, $anio, $mes]);

if (!$result) {
error_log("❌ Error SQL en obtenerTendenciaAnualL2: " . pg_last_error($this->conn));
throw new Exception("Error al obtener tendencia anual L2");
}

$labels = [];
$temperatura = [];
$humedad = [];
$presion = [];
$radiacion = [];

while ($row = pg_fetch_assoc($result)) {
$labels[] = $row['etiqueta'];
$temperatura[] = floatval($row['temperatura']);
$humedad[] = floatval($row['humedad']);
$presion[] = floatval($row['presion']);
$radiacion[] = floatval($row['radiacion']);
}

error_log("✅ Tendencia Anual L2 obtenida: " . count($labels) . " registros");

return [
'fechas' => $labels,
'temperatura_promedio' => $temperatura,
'humedad_promedio' => $humedad,
'presion_promedio' => $presion,
'radiacion_promedio' => $radiacion,
'total_registros' => count($labels)
];
}

/**
* Obtiene datos para climograma L2
* Retorna temperatura media y precipitación por mes del año
*/
public function obtenerClimogramaL2($id_estacion, $anio)
{
$query = "SELECT
mes,
ROUND(AVG(temp_promedio)::numeric, 2) as temp_media,
ROUND(AVG(lluvia_promedio)::numeric, 2) as precip_media,
ROUND(AVG(humedad_promedio)::numeric, 1) as humedad_media
FROM mv_l2_consolidado
WHERE id_estacion = $1
AND anio = $2
GROUP BY mes
ORDER BY mes ASC";

$result = pg_query_params($this->conn, $query, [$id_estacion, $anio]);

if (!$result) {
error_log("❌ Error SQL en obtenerClimogramaL2: " . pg_last_error($this->conn));
throw new Exception("Error al obtener climograma L2");
}

$nombresMeses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
$temperatura = array_fill(0, 12, null);
$precipitacion = array_fill(0, 12, null);
$humedad = array_fill(0, 12, null);

while ($row = pg_fetch_assoc($result)) {
$mes_idx = intval($row['mes']) - 1;
if ($mes_idx >= 0 && $mes_idx < 12) { $temperatura[$mes_idx]=floatval($row['temp_media']);
    $precipitacion[$mes_idx]=floatval($row['precip_media']); $humedad[$mes_idx]=floatval($row['humedad_media']); } }
    error_log("✅ Climograma L2 obtenido para año $anio"); return [ 'meses'=> $nombresMeses,
    'temperatura_promedio' => $temperatura,
    'precipitacion_promedio' => $precipitacion,
    'humedad_promedio' => $humedad
    ];
    }