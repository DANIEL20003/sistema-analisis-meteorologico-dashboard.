<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ CORRECCIÓN: Agregar la barra diagonal
$ruta_conexion = dirname(__DIR__) . '/config/conexion.php';

if (!file_exists($ruta_conexion)) {
    echo json_encode([
        'success' => false,
        'message' => 'Archivo de conexión no encontrado',
        'debug' => [
            'ruta_buscada' => $ruta_conexion,
            'dir_actual' => __DIR__,
            'dirname_dir' => dirname(__DIR__),
            'file_exists' => file_exists($ruta_conexion)
        ]
    ]);
    exit;
}

require_once($ruta_conexion);

// Si no existe conexión válida, devolver un error
if (!isset($conn) || !$conn) {
    $lastError = "Error de conexión desconocido";
    
    // Preferir error proporcionado por el config si existe
    if (isset($DB_CONEXION_ERROR) && !empty($DB_CONEXION_ERROR)) {
        $lastError = $DB_CONEXION_ERROR;
    }

    echo json_encode([
        'success' => false,
        'message' => 'No se pudo conectar a la base de datos',
        'error' => $lastError,
        'debug' => [
            'conn_isset' => isset($conn),
            'conn_value' => $conn ? 'existe' : 'null/false',
            'db_error' => $DB_CONEXION_ERROR ?? 'no definido'
        ]
    ]);
    exit;
}

header('Content-Type: application/json');

// Añadimos soporte para test de conexión vía GET
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'testConexion') {
    $ok = isset($conn) && $conn; // ← Quita el is_resource()
    echo json_encode([
        'success' => $ok,
        'message' => $ok ? 'Conexión a la base de datos OK' : 'No hay conexión a la base de datos'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Validar que existan los datos
    if (!isset($_POST['correo']) || !isset($_POST['contra'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Datos incompletos'
        ]);
        exit;
    }

    $correo = pg_escape_string($conn, trim($_POST['correo']));
    $contra = trim($_POST['contra']);

    if (empty($correo) || empty($contra)) {
        echo json_encode([
            'success' => false,
            'message' => 'Todos los campos son obligatorios'
        ]);
        exit;
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'Formato de correo inválido'
        ]);
        exit;
    }

    // Consulta con prepared statement
    $query = "SELECT id_administrador, nombre, correo, contra FROM administradores WHERE correo = $1";
    $result = pg_query_params($conn, $query, array($correo));

    if (!$result) {
        echo json_encode([
            'success' => false,
            'message' => 'Error en la consulta a la base de datos',
            'error' => pg_last_error($conn)
        ]);
        exit;
    }

    $numRows = pg_num_rows($result);

    if ($numRows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Credenciales incorrectas'
        ]);
        exit;
    }

    $admin = pg_fetch_assoc($result);

    // Verificar contraseña
    // ⚠️ RECOMENDACIÓN: Deberías usar password_hash() y password_verify()
    if ($contra !== $admin['contra']) {
        echo json_encode([
            'success' => false,
            'message' => 'Credenciales incorrectas'
        ]);
        exit;
    }

    // Configurar sesión
    $_SESSION['admin_logged'] = true;
    $_SESSION['admin_id'] = $admin['id_administrador'];
    $_SESSION['admin_nombre'] = $admin['nombre'];
    $_SESSION['admin_correo'] = $admin['correo'];
    $_SESSION['login_time'] = time();

    echo json_encode([
        'success' => true,
        'message' => 'Inicio de sesión exitoso',
        'admin' => [
            'id' => $admin['id_administrador'],
            'nombre' => $admin['nombre'],
            'correo' => $admin['correo']
        ]
    ]);

} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
}

// Cerrar conexión solo si existe recurso
if (isset($conn) && $conn) { // ← Quita el is_resource()
    @pg_close($conn);
}
?>