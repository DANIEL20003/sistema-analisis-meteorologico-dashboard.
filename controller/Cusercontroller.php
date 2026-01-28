<?php
$RecoOpcion = $_GET['opcion'] ?? 1;

switch($RecoOpcion) {
    case 1:
        // Página principal de administrador
        include("../views/Administrador/VAdmin.php");
        break;
        
    case 3:
        // Dashboard
        include("../views/Administrador/VDashboardAdmin.php");
        break;
        
    case 4:
        // Datos
        include("../views/Administrador/VDatosAdmin.php");
        break;
        
    case 5:
        // Tiempo Real
        include("../views/Administrador/VTiempoRealAdmin.php");
        break;
        
    case 6:
        // Geoportal
        include("../views/Administrador/VGeoportalAdmin.php");
        break;
        
    case 7:
        // Reportes
        include("../views/Administrador/VReportesAdmin.php");
        break;
        
    case 8:
        // Cargar datos
        include("../views/Administrador/VCargarAdmin.php");
        break;
        
    case 2:
    default:
        // Cerrar sesión y redireccionar
        header('Location: ../index.php');
        exit;
}
?>