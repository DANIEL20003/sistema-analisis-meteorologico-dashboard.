<?php
// scripts/procesar_tiempo_real.php
// SCRIPT EJECUTADO POR CRON CADA 30 MINUTOS
// Procesa archivos de tiempo real con mapeo dinámico de variables

error_reporting(E_ALL);
ini_set('display_errors', 0);

// Rutas absolutas
$root_dir = dirname(__DIR__);
$log_file = $root_dir . '/logs/tiempo_real.log';
$uploads_dir = $root_dir . '/uploads';

require_once $root_dir . '/config/conexion.php';
require_once $root_dir . '/model/MTiempoReal.php';

// ============================================================
// FUNCIONES DE LOGGING
// ============================================================

function escribirLog($mensaje)
{
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    $linea = "[{$timestamp}] {$mensaje}\n";
    file_put_contents($log_file, $linea, FILE_APPEND);
    echo $linea; // También mostrar en consola
}

function iniciarLog()
{
    escribirLog("========================================");
    escribirLog("🚀 INICIO PROCESAMIENTO TIEMPO REAL");
    escribirLog("========================================");
}

function finalizarLog($total_procesados, $total_errores)
{
    escribirLog("========================================");
    escribirLog("✅ Archivos procesados exitosamente: $total_procesados");
    escribirLog("❌ Archivos con errores: $total_errores");
    escribirLog("🏁 FIN PROCESAMIENTO");
    escribirLog("========================================\n");
}

// ============================================================
// FUNCIÓN PRINCIPAL: ESCANEAR TODAS LAS ESTACIONES
// ============================================================

function escanearEstaciones()
{
    global $uploads_dir;

    $archivos_procesados = 0;
    $archivos_con_error = 0;

    // Listar todas las carpetas en uploads/
    if (!is_dir($uploads_dir)) {
        escribirLog("❌ ERROR: Carpeta uploads/ no existe");
        return [0, 0];
    }

    $carpetas = scandir($uploads_dir);

    foreach ($carpetas as $nombre_estacion) {
        // Saltar . y ..
        if ($nombre_estacion === '.' || $nombre_estacion === '..')
            continue;

        $ruta_estacion = $uploads_dir . '/' . $nombre_estacion;

        // Solo procesar si es un directorio
        if (!is_dir($ruta_estacion))
            continue;

        // Verificar que existe la carpeta TiempoR
        $ruta_tiempoR = $ruta_estacion . '/TiempoR';
        $ruta_tiempoRPr = $ruta_estacion . '/TiempoRPr';

        if (!is_dir($ruta_tiempoR)) {
            escribirLog("⚠️ [{$nombre_estacion}] No existe carpeta TiempoR, omitiendo...");
            continue;
        }

        // Crear TiempoRPr si no existe
        if (!is_dir($ruta_tiempoRPr)) {
            mkdir($ruta_tiempoRPr, 0755, true);
            escribirLog("✅ [{$nombre_estacion}] Carpeta TiempoRPr creada");
        }

        // Procesar archivos de esta estación
        escribirLog("📂 [{$nombre_estacion}] Escaneando estación...");
        list($procesados, $errores) = procesarEstacion($nombre_estacion, $ruta_tiempoR, $ruta_tiempoRPr);

        $archivos_procesados += $procesados;
        $archivos_con_error += $errores;
    }

    return [$archivos_procesados, $archivos_con_error];
}

// ============================================================
// PROCESAR TODOS LOS ARCHIVOS DE UNA ESTACIÓN
// ============================================================

function procesarEstacion($nombre_estacion, $ruta_tiempoR, $ruta_tiempoRPr)
{
    global $conn;

    // Buscar archivos .txt, .rep o .tmp en TiempoR
    $archivos = glob($ruta_tiempoR . '/*.{txt,rep,tmp}', GLOB_BRACE);

    if (empty($archivos)) {
        escribirLog("⚠️ [{$nombre_estacion}] No hay archivos en TiempoR");
        return [0, 0];
    }

    escribirLog("📋 [{$nombre_estacion}] Archivos encontrados: " . count($archivos));

    // Obtener ID de la estación desde la BD
    $id_estacion = obtenerIdEstacion($conn, $nombre_estacion);

    if (!$id_estacion) {
        escribirLog("❌ [{$nombre_estacion}] ERROR: Estación no encontrada en BD");

        // Mover todos los archivos con error
        foreach ($archivos as $archivo) {
            moverArchivoConError($archivo, $ruta_tiempoRPr, $nombre_estacion, 'ESTACION_NO_ENCONTRADA_BD');
        }

        return [0, count($archivos)];
    }

    $archivos_procesados = 0;
    $archivos_con_error = 0;

    // Procesar cada archivo uno por uno
    foreach ($archivos as $archivo) {
        $nombre_archivo = basename($archivo);
        escribirLog("📄 [{$nombre_estacion}] Procesando: {$nombre_archivo}");

        try {
            // Procesar archivo con el modelo (pasando nombre_estacion para logs)
            $modelo = new MTiempoReal();
            $resultado = $modelo->procesarArchivoTiempoReal($archivo, $id_estacion, $nombre_estacion);

            // Mover archivo según el resultado
            $movido = moverArchivoSegunResultado(
                $archivo,
                $ruta_tiempoRPr,
                $resultado,
                $nombre_estacion
            );

            if ($resultado['exito']) {
                escribirLog("✅ [{$nombre_estacion}] {$nombre_archivo}: {$resultado['mensaje']}");
                $archivos_procesados++;
            } else {
                // Mensaje de error diferenciado según el tipo
                if ($resultado['tipo_resultado'] === 'VARIABLES_FALTANTES') {
                    $lista_vars = implode(', ', $resultado['variables_faltantes']);
                    escribirLog("🚨 [{$nombre_estacion}] {$nombre_archivo}: VARIABLES NO REGISTRADAS");
                    escribirLog("   └─ Variables faltantes: {$lista_vars}");
                    escribirLog("   └─ ⚠️ ARCHIVO QUEDÓ EN TiempoR - Debe agregar las variables a 'metadatos_variables'");
                } else {
                    escribirLog("❌ [{$nombre_estacion}] {$nombre_archivo}: {$resultado['mensaje']}");
                }
                $archivos_con_error++;
            }

        } catch (Exception $e) {
            escribirLog("🔴 [{$nombre_estacion}] {$nombre_archivo}: Error crítico - " . $e->getMessage());
            moverArchivoConError($archivo, $ruta_tiempoRPr, $nombre_estacion, 'ERROR_CRITICO');
            $archivos_con_error++;
        }
    }

    escribirLog("📊 [{$nombre_estacion}] Resumen: {$archivos_procesados} exitosos, {$archivos_con_error} con errores");

    return [$archivos_procesados, $archivos_con_error];
}

// ============================================================
// OBTENER ID DE ESTACIÓN POR NOMBRE DE CARPETA
// ============================================================

function obtenerIdEstacion($conn, $nombre_carpeta)
{
    // Limpiar nombre de carpeta (por si tiene guiones bajos)
    $nombre_limpio = str_replace('_', ' ', $nombre_carpeta);

    $query = "SELECT id_estacion FROM estaciones 
              WHERE LOWER(REPLACE(nombre, ' ', '_')) = LOWER($1) 
              OR LOWER(nombre) = LOWER($2)
              LIMIT 1";

    $result = pg_query_params($conn, $query, [$nombre_carpeta, $nombre_limpio]);

    if ($result && pg_num_rows($result) > 0) {
        $row = pg_fetch_assoc($result);
        return intval($row['id_estacion']);
    }

    return null;
}

// ============================================================
// MOVER ARCHIVO SEGÚN EL RESULTADO DEL PROCESAMIENTO
// ============================================================

function moverArchivoSegunResultado($archivo_origen, $destino_dir, $resultado, $nombre_estacion)
{
    // ⚠️ IMPORTANTE: Si el resultado indica que NO se debe mover el archivo, DETENER
    if (isset($resultado['no_mover_archivo']) && $resultado['no_mover_archivo'] === true) {
        escribirLog("⚠️ [{$nombre_estacion}] Archivo NO movido - Quedó en TiempoR para corrección: " . basename($archivo_origen));
        return false;
    }

    $tipo_resultado = $resultado['tipo_resultado'];
    $nombre_original = basename($archivo_origen);
    $extension = pathinfo($archivo_origen, PATHINFO_EXTENSION);

    // Determinar nombre del archivo destino
    switch ($tipo_resultado) {
        case 'EXITOSO':
            // Mantener nombre original
            $nombre_destino = $nombre_original;
            break;

        case 'SIN_DATOS':
            $nombre_destino = $nombre_estacion . '_SIN_DATOS_' . date('Ymd_His') . '.' . $extension;
            break;

        case 'DUPLICADO':
            $nombre_destino = $nombre_estacion . '_DUPLICADO_' . date('Ymd_His') . '.' . $extension;
            break;

        case 'SIN_MAPEO':
        case 'SIN_COLUMNAS_VALIDAS':
            $nombre_destino = $nombre_estacion . '_SIN_MAPEO_' . date('Ymd_His') . '.' . $extension;
            break;

        case 'ERROR_PROCESAMIENTO':
        default:
            $nombre_destino = $nombre_estacion . '_ERROR_PROCESAMIENTO_' . date('Ymd_His') . '.' . $extension;
            break;
    }

    $destino = $destino_dir . '/' . $nombre_destino;

    // DEBUG: Mostrar rutas
    escribirLog("DEBUG: Intentando mover archivo...");
    escribirLog("DEBUG: Origen: {$archivo_origen}");
    escribirLog("DEBUG: Destino: {$destino}");
    escribirLog("DEBUG: Archivo existe: " . (file_exists($archivo_origen) ? 'SÍ' : 'NO'));
    escribirLog("DEBUG: Destino dir existe: " . (is_dir($destino_dir) ? 'SÍ' : 'NO'));
    escribirLog("DEBUG: Destino dir escribible: " . (is_writable($destino_dir) ? 'SÍ' : 'NO'));

    // Mover archivo
    if (rename($archivo_origen, $destino)) {
        escribirLog("📦 [{$nombre_estacion}] Archivo movido: {$nombre_destino}");
        return true;
    } else {
        $error = error_get_last();
        escribirLog("⚠️ [{$nombre_estacion}] No se pudo mover el archivo");
        escribirLog("⚠️ Error PHP: " . ($error ? $error['message'] : 'Desconocido'));
        return false;
    }
}

// ============================================================
// MOVER ARCHIVO CON NOMBRE DE ERROR (para errores críticos)
// ============================================================

function moverArchivoConError($archivo_origen, $destino_dir, $nombre_estacion, $tipo_error)
{
    $extension = pathinfo($archivo_origen, PATHINFO_EXTENSION);
    $nombre_error = $nombre_estacion . '_' . $tipo_error . '_' . date('Ymd_His') . '.' . $extension;
    $destino = $destino_dir . '/' . $nombre_error;

    if (rename($archivo_origen, $destino)) {
        escribirLog("📦 [{$nombre_estacion}] Archivo movido como ERROR: {$nombre_error}");
        return true;
    }

    escribirLog("⚠️ [{$nombre_estacion}] No se pudo mover archivo con error");
    return false;
}

// ============================================================
// EJECUCIÓN PRINCIPAL
// ============================================================

iniciarLog();

try {
    list($procesados, $errores) = escanearEstaciones();
    finalizarLog($procesados, $errores);

    // Código de salida
    exit($errores > 0 ? 1 : 0);

} catch (Exception $e) {
    escribirLog("🔴 ERROR CRÍTICO: " . $e->getMessage());
    escribirLog("Stack trace: " . $e->getTraceAsString());
    exit(1);
}
?>