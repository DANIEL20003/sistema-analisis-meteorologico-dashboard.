<?php
// Script de diagnóstico L2 simplificado
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Diagnóstico Gráficas L2</h1>";
echo "<hr>";

// Probar conexión directa
echo "<h2>1. Verificar Conexión a BD</h2>";
$host = "localhost";
$port = "5432";
$dbname = "Metereo";
$user = "postgres";
$password = "@SEbas211220";

$conn = @pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    echo "<strong style='color:red'>❌ ERROR: No se pudo conectar a PostgreSQL</strong><br>";
    echo "Verifica que PostgreSQL esté ejecutándose y las credenciales sean correctas.<br>";
    exit;
}

echo "<strong style='color:green'>✅ Conexión exitosa a PostgreSQL</strong><br>";
echo "<hr>";

// Verificar datos L2
echo "<h2>2. Verificar Datos L2 en Base de Datos</h2>";

$query = "SELECT COUNT(*) as total FROM registros WHERE tipo_nivel = 'L2'";
$result = pg_query($conn, $query);
$row = pg_fetch_assoc($result);
echo "<strong>Registros L2 en tabla 'registros':</strong> " . $row['total'] . "<br>";

$query = "SELECT COUNT(*) as total FROM datos_l2";
$result = pg_query($conn, $query);
$row = pg_fetch_assoc($result);
echo "<strong>Registros en tabla 'datos_l2':</strong> " . $row['total'] . "<br>";

echo "<hr>";

// Verificar vistas materializadas
echo "<h2>3. Verificar Vistas Materializadas</h2>";

$vistas = ['mv_l2_consolidado', 'mv_l2_heatmap_temperatura', 'mv_l2_heatmap_radiacion', 'mv_l2_rosa_vientos'];

foreach ($vistas as $vista) {
    $query = "SELECT COUNT(*) as total FROM $vista";
    $result = @pg_query($conn, $query);

    if ($result) {
        $row = pg_fetch_assoc($result);
        $total = $row['total'];

        if ($total == 0) {
            echo "<strong style='color:orange'>⚠️ Vista '$vista':</strong> EXISTE pero está VACÍA ($total registros)<br>";
        } else {
            echo "<strong style='color:green'>✅ Vista '$vista':</strong> $total registros<br>";
        }
    } else {
        echo "<strong style='color:red'>❌ Vista '$vista':</strong> NO EXISTE O ERROR<br>";
    }
}

echo "<hr>";

// Verificar datos recientes
echo "<h2>4. Muestra de Datos L2 Recientes</h2>";
$query = "SELECT r.fecha_hora, r.id_estacion, e.nombre,
                 dl2.temp_promedio_hora, dl2.humedad_promedio_hora, 
                 dl2.viento_promedio_hora
          FROM registros r
          INNER JOIN datos_l2 dl2 ON r.id_registro = dl2.id_registro
          INNER JOIN estaciones e ON r.id_estacion = e.id_estacion
          WHERE r.tipo_nivel = 'L2'
          ORDER BY r.fecha_hora DESC
          LIMIT 5";

$result = pg_query($conn, $query);

if ($result && pg_num_rows($result) > 0) {
    echo "<table border='1' cellpadding='5' style='border-collapse:collapse;'>";
    echo "<tr style='background:#333;color:white;'>";
    echo "<th>Fecha/Hora</th><th>Estación</th><th>Temp (°C)</th><th>Humedad (%)</th><th>Viento (m/s)</th>";
    echo "</tr>";

    while ($row = pg_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['fecha_hora'] . "</td>";
        echo "<td>" . $row['nombre'] . "</td>";
        echo "<td>" . number_format($row['temp_promedio_hora'], 2) . "</td>";
        echo "<td>" . number_format($row['humedad_promedio_hora'], 2) . "</td>";
        echo "<td>" . number_format($row['viento_promedio_hora'], 2) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<strong style='color:red'>❌ NO HAY DATOS L2 en la base de datos</strong><br>";
}

echo "<hr>";

// Probar consulta de vista consolidada
echo "<h2>5. Test Consulta Vista Consolidada</h2>";
$query = "SELECT * FROM mv_l2_consolidado LIMIT 5";
$result = @pg_query($conn, $query);

if ($result && pg_num_rows($result) > 0) {
    echo "<strong style='color:green'>✅ Vista mv_l2_consolidado funciona correctamente</strong><br>";
    echo "<table border='1' cellpadding='5' style='border-collapse:collapse;'>";
    echo "<tr style='background:#333;color:white;'>";
    echo "<th>Estación</th><th>Fecha/Hora</th><th>Temp</th><th>Humedad</th><th>Presión</th></tr>";

    while ($row = pg_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['id_estacion'] . "</td>";
        echo "<td>" . $row['fecha_hora_agrupada'] . "</td>";
        echo "<td>" . number_format($row['temp_promedio'], 2) . "</td>";
        echo "<td>" . number_format($row['humedad_promedio'], 2) . "</td>";
        echo "<td>" . number_format($row['presion_promedio'], 2) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<strong style='color:red'>❌ Vista mv_l2_consolidado está VACÍA o NO EXISTE</strong><br>";
    echo "<br><strong>SOLUCIÓN:</strong> Ejecutar en PostgreSQL:<br>";
    echo "<pre style='background:#f0f0f0;padding:10px;'>REFRESH MATERIALIZED VIEW mv_l2_consolidado;</pre>";
}

echo "<hr>";

// Diagnóstico de endpoints
echo "<h2>6. Diagnóstico de Endpoints</h2>";
$endpoint_file = __DIR__ . '/controller/CDashboard.php';

if (file_exists($endpoint_file)) {
    echo "<strong style='color:green'>✅ Archivo CDashboard.php existe</strong><br>";

    // Buscar endpoints L2
    $content = file_get_contents($endpoint_file);
    $endpoints_check = [
        'get_chart_l2_climograma' => strpos($content, 'get_chart_l2_climograma') !== false,
        'get_chart_l2_tendencia_anual' => strpos($content, 'get_chart_l2_tendencia_anual') !== false,
        'get_chart_l2_heatmap_temperatura' => strpos($content, 'get_chart_l2_heatmap_temperatura') !== false,
        'get_chart_l2_heatmap_radiacion' => strpos($content, 'get_chart_l2_heatmap_radiacion') !== false,
        'get_chart_l2_rosa_vientos' => strpos($content, 'get_chart_l2_rosa_vientos') !== false,
    ];

    foreach ($endpoints_check as $endpoint => $exists) {
        if ($exists) {
            echo "<strong style='color:green'>✅</strong> Endpoint '$endpoint' encontrado<br>";
        } else {
            echo "<strong style='color:red'>❌</strong> Endpoint '$endpoint' NO encontrado<br>";
        }
    }
} else {
    echo "<strong style='color:red'>❌ Archivo CDashboard.php NO encontrado</strong><br>";
}

echo "<hr>";

// Resumen
echo "<h2>📋 Resumen y Acciones</h2>";
echo "<ul style='line-height:2em;'>";

// Contar total en vista consolidada
$query = "SELECT COUNT(*) as total FROM mv_l2_consolidado";
$result = @pg_query($conn, $query);
$vista_count = 0;
if ($result) {
    $row = pg_fetch_assoc($result);
    $vista_count = $row['total'];
}

if ($vista_count == 0) {
    echo "<li><strong style='color:orange'>⚠️ ACCIÓN REQUERIDA:</strong> Las vistas materializadas están vacías.<br>";
    echo "Ejecutar en PostgreSQL:<br>";
    echo "<pre style='background:#fffacd;padding:10px;margin:10px 0;'>
REFRESH MATERIALIZED VIEW mv_l2_consolidado;
REFRESH MATERIALIZED VIEW mv_l2_heatmap_temperatura;
REFRESH MATERIALIZED VIEW mv_l2_heatmap_radiacion;
REFRESH MATERIALIZED VIEW mv_l2_rosa_vientos;
REFRESH MATERIALIZED VIEW mv_l2_humedad_rangos;
</pre></li>";
}

// Check endpoints nuevos
$new_endpoints_exist = strpos(file_get_contents($endpoint_file), 'get_chart_l2_heatmap_temperatura') !== false;
if (!$new_endpoints_exist) {
    echo "<li><strong style='color:orange'>⚠️ ACCIÓN REQUERIDA:</strong> Los nuevos endpoints L2 no están integrados.<br>";
    echo "Copiar el contenido de <code>controller/endpoints_l2_nuevos.php</code> al archivo <code>controller/CDashboard.php</code> después de la línea 823</li>";
}

echo "</ul>";

pg_close($conn);
?>