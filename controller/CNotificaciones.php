<?php
// controller/CNotificaciones.php
// CONTROLADOR PARA OBTENER NOTIFICACIONES VÍA AJAX

header('Content-Type: application/json');

require_once '../model/MNotificaciones.php';

try {
    $modelo = new MNotificaciones();
    
    // Obtener últimas 15 notificaciones
    $notificaciones = $modelo->obtenerUltimasNotificaciones(15);
    $total = $modelo->contarNotificaciones();
    
    echo json_encode([
        'exito' => true,
        'notificaciones' => $notificaciones,
        'total' => $total,
        'nuevas' => count($notificaciones)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'exito' => false,
        'mensaje' => $e->getMessage(),
        'notificaciones' => [],
        'total' => 0,
        'nuevas' => 0
    ]);
}
?>