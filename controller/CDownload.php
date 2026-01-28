<?php
// controller/CDownload.php
error_reporting(0);
ini_set('display_errors', 0);

require_once '../model/MLoadData.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); die("Método no permitido");
}

try {
    $modelo = new MLoadData();
    
    // Parámetros
    $tipo_seleccion = $_POST['tipo_seleccion'] ?? 'LIMPIOS';
    $tipo_nivel = $_POST['tipo_nivel'] ?? 'L0';
    $id_carga = $_POST['id_carga'] ?? null;
    $nombre_archivo = $_POST['nombre_archivo'] ?? null;

    // --- VALIDACIÓN DINÁMICA ---
    if ($tipo_seleccion === 'LIMPIOS' && empty($id_carga)) {
        throw new Exception("Error: Falta el ID de carga para datos limpios.");
    }
    if ($tipo_seleccion === 'CRUDOS' && empty($nombre_archivo)) {
        throw new Exception("Error: Falta el nombre del archivo para datos crudos.");
    }

    // 1. Registrar la descarga (Auditoría)
    // Para crudos, usamos ID estación 1 por defecto o lo extraemos del nombre si es posible
    $registro_data = [
        'nombre_solicitante' => $_POST['nombre_solicitante'],
        'institucion' => $_POST['institucion'],
        'cedula_pasaporte' => $_POST['cedula_pasaporte'],
        'motivo' => $_POST['motivo'],
        'id_estacion' => 1, 
        'tipo_nivel' => $tipo_nivel,
        'tipo_archivo' => 'CSV'
    ];
    $modelo->registrarDescarga($registro_data);

    // Limpiar buffer
    if (ob_get_level()) ob_end_clean();

    // ============================================================
    // CASO A: DATOS CRUDOS (FISICOS)
    // ============================================================
    if ($tipo_seleccion === 'CRUDOS') {
        // Ruta: ../uploads/L0/archivo.csv
        $rutaFisica = dirname(__DIR__) . "/uploads/" . strtoupper($tipo_nivel) . "/" . basename($nombre_archivo);

        if (!file_exists($rutaFisica)) {
            throw new Exception("El archivo no existe en el servidor: uploads/" . strtoupper($tipo_nivel) . "/" . basename($nombre_archivo));
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($rutaFisica) . '"');
        header('Content-Length: ' . filesize($rutaFisica));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        
        readfile($rutaFisica);
        exit();
    }

    // ============================================================
    // CASO B: DATOS LIMPIOS (BASE DE DATOS)
    // ============================================================
    else {
        // Obtener metadatos reales para el nombre del archivo
        $datos = $modelo->get_datos_meteorologicos($id_carga);
        
        if (empty($datos)) throw new Exception("No hay datos en BD.");

        $filename = "Reporte_Limpio_" . $id_carga . ".csv";
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ["ESCUELA SUPERIOR POLITECNICA DE CHIMBORAZO - GEAA"]);
        fputcsv($output, array_keys($datos[0]));
        foreach ($datos as $fila) fputcsv($output, $fila);
        fclose($output);
        exit();
    }

} catch (Exception $e) {
    if (!headers_sent()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    } else {
        echo "Error Crítico: " . $e->getMessage();
    }
}
?>