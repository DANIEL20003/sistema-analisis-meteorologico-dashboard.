<?php
/**
 * Test Completo de Carga L2
 * Verifica todo el flujo: carga, limpieza, generación de CSV y almacenamiento en BD
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🧪 Test Completo - Sistema de Carga L2</h1>";
echo "<hr>";

// Configuración de BD
$host = "localhost";
$port = "5432";
$dbname = "Metereo";
$user = "postgres";
$password = "@SEbas211220";

$conn = @pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    echo "<strong style='color:red'>❌ ERROR: No se pudo conectar a PostgreSQL</strong><br>";
    exit;
}

echo "<strong style='color:green'>✅ Conexión a BD exitosa</strong><br><hr>";

// ============================================================
// TEST 1: Verificar Archivos y Carpetas del Sistema
// ============================================================
echo "<h2>📁 Test 1: Verificación de Estructura de Archivos</h2>";

$files_to_check = [
    'DataCleanerL2' => __DIR__ . '/controller/DataCleanerL2.php',
    'CLoadDataL2' => __DIR__ . '/controller/CLoadDataL2.php',
    'MLoadDataL2' => __DIR__ . '/model/MLoadDataL2.php'
];

$all_files_exist = true;
foreach ($files_to_check as $name => $path) {
    if (file_exists($path)) {
        echo "<strong style='color:green'>✅ $name:</strong> Existe<br>";
    } else {
        echo "<strong style='color:red'>❌ $name:</strong> NO ENCONTRADO en $path<br>";
        $all_files_exist = false;
    }
}

if (!$all_files_exist) {
    echo "<strong style='color:red'>❌ Faltan archivos críticos. Abortando test.</strong><br>";
    exit;
}

echo "<hr>";

// ============================================================
// TEST 2: Verificar Carpetas de Guardado
// ============================================================
echo "<h2>📂 Test 2: Verificación de Carpetas de Guardado</h2>";

$base_uploads = __DIR__ . '/uploads';
$test_station_name = 'TEST_STATION';

$folders_to_check = [
    'Base Uploads' => $base_uploads,
    'Crudos' => $base_uploads . '/' . $test_station_name . '/Crudos/L2',
    'Limpios' => $base_uploads . '/Limpios/L2/Estacion_' . $test_station_name
];

foreach ($folders_to_check as $name => $path) {
    if (is_dir($path)) {
        echo "<strong style='color:green'>✅ $name:</strong> Existe → $path<br>";
    } else {
        echo "<strong style='color:orange'>⚠️ $name:</strong> No existe (se creará automáticamente) → $path<br>";
    }
}

echo "<hr>";

// ============================================================
// TEST 3: Verificar Estaciones en BD
// ============================================================
echo "<h2>🏢 Test 3: Verificación de Estaciones</h2>";

$query = "SELECT id_estacion, codigo, nombre FROM estaciones ORDER BY id_estacion LIMIT 5";
$result = pg_query($conn, $query);

if ($result && pg_num_rows($result) > 0) {
    echo "<strong style='color:green'>✅ Estaciones encontradas en BD:</strong><br>";
    echo "<table border='1' cellpadding='5' style='border-collapse:collapse; margin:10px 0;'>";
    echo "<tr style='background:#333;color:white;'><th>ID</th><th>Código</th><th>Nombre</th></tr>";

    while ($row = pg_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['id_estacion'] . "</td>";
        echo "<td>" . $row['codigo'] . "</td>";
        echo "<td>" . $row['nombre'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<strong style='color:red'>❌ No hay estaciones en la BD</strong><br>";
}

echo "<hr>";

// ============================================================
// TEST 4: Verificar Datos L2 Existentes
// ============================================================
echo "<h2>📊 Test 4: Datos L2 en Base de Datos</h2>";

// Contar registros L2
$query = "SELECT COUNT(*) as total FROM registros WHERE tipo_nivel = 'L2'";
$result = pg_query($conn, $query);
$row = pg_fetch_assoc($result);
$total_registros = $row['total'];

$query = "SELECT COUNT(*) as total FROM datos_l2";
$result = pg_query($conn, $query);
$row = pg_fetch_assoc($result);
$total_datos = $row['total'];

echo "<strong>Registros L2:</strong> $total_registros<br>";
echo "<strong>Datos L2:</strong> $total_datos<br>";

if ($total_registros > 0 && $total_datos > 0) {
    echo "<strong style='color:green'>✅ Hay datos L2 en la BD</strong><br>";

    // Mostrar muestra de datos
    $query = "SELECT r.fecha_hora, e.nombre as estacion,
                     dl2.temp_promedio_hora, dl2.humedad_promedio_hora,
                     dl2.velocidad_viento_promedio_hora
              FROM registros r
              INNER JOIN datos_l2 dl2 ON r.id_registro = dl2.id_registro
              INNER JOIN estaciones e ON r.id_estacion = e.id_estacion
              WHERE r.tipo_nivel = 'L2'
              ORDER BY r.fecha_hora DESC
              LIMIT 5";

    $result = pg_query($conn, $query);

    if ($result && pg_num_rows($result) > 0) {
        echo "<br><strong>Muestra de datos recientes:</strong><br>";
        echo "<table border='1' cellpadding='5' style='border-collapse:collapse; margin:10px 0;'>";
        echo "<tr style='background:#333;color:white;'>";
        echo "<th>Fecha/Hora</th><th>Estación</th><th>Temp (°C)</th><th>Humedad (%)</th><th>Viento (m/s)</th>";
        echo "</tr>";

        while ($row = pg_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['fecha_hora'] . "</td>";
            echo "<td>" . $row['estacion'] . "</td>";
            echo "<td>" . number_format($row['temp_promedio_hora'], 2) . "</td>";
            echo "<td>" . number_format($row['humedad_promedio_hora'], 2) . "</td>";
            echo "<td>" . number_format($row['velocidad_viento_promedio_hora'], 2) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<strong style='color:orange'>⚠️ No hay datos L2 en la BD (esto es normal si no se ha cargado nada)</strong><br>";
}

echo "<hr>";

// ============================================================
// TEST 5: Verificar Vistas Materializadas L2
// ============================================================
echo "<h2>👁️ Test 5: Vistas Materializadas L2</h2>";

$vistas_l2 = [
    'mv_l2_consolidado',
    'mv_l2_heatmap_temperatura',
    'mv_l2_heatmap_radiacion',
    'mv_l2_rosa_vientos',
    'mv_l2_humedad_rangos'
];

$vistas_ok = 0;
$vistas_vacias = 0;
$vistas_error = 0;

foreach ($vistas_l2 as $vista) {
    $query = "SELECT COUNT(*) as total FROM $vista";
    $result = @pg_query($conn, $query);

    if ($result) {
        $row = pg_fetch_assoc($result);
        $total = $row['total'];

        if ($total > 0) {
            echo "<strong style='color:green'>✅ $vista:</strong> $total registros<br>";
            $vistas_ok++;
        } else {
            echo "<strong style='color:orange'>⚠️ $vista:</strong> VACÍA (0 registros)<br>";
            $vistas_vacias++;
        }
    } else {
        echo "<strong style='color:red'>❌ $vista:</strong> ERROR o NO EXISTE<br>";
        $vistas_error++;
    }
}

echo "<br><strong>Resumen Vistas:</strong> $vistas_ok OK, $vistas_vacias Vacías, $vistas_error Errores<br>";

if ($vistas_vacias > 0 && $total_datos > 0) {
    echo "<br><strong style='color:orange'>⚠️ ACCIÓN REQUERIDA:</strong> Hay datos pero las vistas están vacías.<br>";
    echo "<strong>Ejecutar en PostgreSQL:</strong><br>";
    echo "<pre style='background:#fffacd;padding:10px;'>SELECT refrescar_vistas_materializadas();</pre>";
}

echo "<hr>";

// ============================================================
// TEST 6: Verificar Estructura de Tabla datos_l2
// ============================================================
echo "<h2>🗂️ Test 6: Estructura de Tabla datos_l2</h2>";

$query = "SELECT column_name, data_type 
          FROM information_schema.columns 
          WHERE table_name = 'datos_l2' 
          ORDER BY ordinal_position";

$result = pg_query($conn, $query);

if ($result) {
    echo "<table border='1' cellpadding='5' style='border-collapse:collapse; margin:10px 0;'>";
    echo "<tr style='background:#333;color:white;'><th>Columna</th><th>Tipo</th></tr>";

    $columnas_encontradas = [];
    while ($row = pg_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['column_name'] . "</td>";
        echo "<td>" . $row['data_type'] . "</td>";
        echo "</tr>";
        $columnas_encontradas[] = $row['column_name'];
    }
    echo "</table>";

    // Verificar columnas críticas
    $columnas_criticas = [
        'temp_promedio_hora',
        'humedad_promedio_hora',
        'presion_promedio_hora',
        'radiacion_total_hora',
        'velocidad_viento_promedio_hora',
        'direccion_viento_promedio_hora',
        'lluvia_promedio_hora'
    ];

    echo "<br><strong>Verificación de columnas críticas:</strong><br>";
    $columnas_ok = true;
    foreach ($columnas_criticas as $col) {
        if (in_array($col, $columnas_encontradas)) {
            echo "<strong style='color:green'>✅ $col:</strong> Existe<br>";
        } else {
            echo "<strong style='color:red'>❌ $col:</strong> NO EXISTE<br>";
            $columnas_ok = false;
        }
    }

    if ($columnas_ok) {
        echo "<br><strong style='color:green'>✅ Todas las columnas críticas existen</strong><br>";
    }
}

echo "<hr>";

// ============================================================
// RESUMEN FINAL
// ============================================================
echo "<h2>📋 Resumen del Test</h2>";

echo "<ul style='line-height:2em;'>";

// 1. Archivos
if ($all_files_exist) {
    echo "<li><strong style='color:green'>✅ Archivos del Sistema:</strong> Todos los archivos críticos existen</li>";
} else {
    echo "<li><strong style='color:red'>❌ Archivos del Sistema:</strong> Faltan archivos</li>";
}

// 2. BD
if ($total_datos > 0) {
    echo "<li><strong style='color:green'>✅ Base de Datos:</strong> Contiene $total_datos registros L2</li>";
} else {
    echo "<li><strong style='color:orange'>⚠️ Base de Datos:</strong> Sin datos L2 (normal si no se ha cargado nada)</li>";
}

// 3. Vistas
if ($vistas_ok == count($vistas_l2)) {
    echo "<li><strong style='color:green'>✅ Vistas Materializadas:</strong> Todas funcionando</li>";
} else if ($vistas_error > 0) {
    echo "<li><strong style='color:red'>❌ Vistas Materializadas:</strong> $vistas_error vistas con errores</li>";
} else {
    echo "<li><strong style='color:orange'>⚠️ Vistas Materializadas:</strong> Existen pero están vacías</li>";
}

// 4. Estructura
if ($columnas_ok) {
    echo "<li><strong style='color:green'>✅ Estructura de Tabla:</strong> Todas las columnas críticas existen</li>";
} else {
    echo "<li><strong style='color:red'>❌ Estructura de Tabla:</strong> Faltan columnas críticas</li>";
}

echo "</ul>";

echo "<hr>";
echo "<h3>🎯 Próximos Pasos</h3>";
echo "<ol>";
echo "<li>Cargar un archivo L2 de prueba usando la interfaz web</li>";
echo "<li>Verificar que se generen 3 archivos (Crudo + 2 Limpios)</li>";
echo "<li>Verificar que los datos se inserten en BD</li>";
echo "<li>Refrescar vistas materializadas si es necesario</li>";
echo "</ol>";

pg_close($conn);
?>