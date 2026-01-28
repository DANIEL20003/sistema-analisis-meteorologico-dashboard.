<?php
$host = "127.0.0.1";
$port = "5432";
$dbname = "basedatos2025";
$user = "postgres";
$password = "amelia2006";

// Variable global para capturar errores
$DB_CONEXION_ERROR = null;

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    $DB_CONEXION_ERROR = "No se pudo conectar a PostgreSQL";
    if (function_exists('error_get_last')) {
        $lastError = error_get_last();
        if ($lastError) {
            $DB_CONEXION_ERROR .= ": " . $lastError['message'];
        }
    }
}
?>
