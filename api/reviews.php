<?php
require __DIR__ . '/conexion.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    $coleccion = $baseDeDatos->resenas; 
    
    $resultado = $coleccion->find([]); 
    
    $datos = [];
    foreach ($resultado as $documento) {
        $datos[] = $documento->jsonSerialize();
    }
    
    echo json_encode($datos);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error real: " . $e->getMessage()]);
}
?>