<?php

require_once __DIR__ . '/conexion.php';

// Usar funciones pg_* para comprobar la conexión
if (isset($conn) && $conn && is_resource($conn)) {
    $res = @pg_query($conn, 'SELECT 1');
    if ($res) {
        echo "Conexión DB OK";
        exit(0);
    }
    error_log("verificar_conexion: la consulta SELECT 1 falló");
    echo "Conexión establecida pero la consulta SELECT 1 falló";
    exit(1);
} else {
    // No hay conexión válida
    $err = isset($DB_CONEXION_ERROR) ? $DB_CONEXION_ERROR : 'No hay conexión establecida con PostgreSQL';
    error_log("verificar_conexion: No se pudo conectar: $err");
    echo "No se pudo conectar a PostgreSQL: " . htmlspecialchars($err);
    exit(1);
}
