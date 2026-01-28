<?php
// controller/CPreview.php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

require_once '../model/MLoadData.php';

try {
    $modelo = new MLoadData();
    
    $origen = $_POST['origen'] ?? 'DB';
    $nombre = $_POST['nombre_archivo'] ?? '';
    $nivel = $_POST['nivel'] ?? 'L0';
    $id_carga = $_POST['id_carga'] ?? 0;

    // CASO 1: ARCHIVO FÍSICO (VDCrudos)
    if ($origen === 'FISICO') {
        if(empty($nombre)) throw new Exception("Nombre de archivo requerido.");
        
        // Construir ruta: ../uploads/L0/archivo.csv
        $ruta = dirname(__DIR__) . "/uploads/" . strtoupper($nivel) . "/" . basename($nombre);
        
        $preview = $modelo->previewCsvContent($ruta, 10); // Lee solo 10 filas y 5 columnas
        
        echo json_encode(['success' => true, 'data' => $preview]);
    }
    // CASO 2: BASE DE DATOS (VDLimpios)
    else {
        // ... (Tu código existente para DB) ...
        if(!$id_carga) throw new Exception("ID requerido.");
        $datos = $modelo->get_datos_meteorologicos($id_carga, 10);
        echo json_encode(['success' => true, 'data' => ['header'=>array_keys($datos[0]), 'data'=>$datos]]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>