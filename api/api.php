<?php
require __DIR__ . '/conexion.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$headers = getallheaders();
$token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

if (!$token) {
    echo json_encode([]); 
    exit;
}

$datosToken = explode(':', base64_decode($token));
$usuario_id = $datosToken[0];

try {
    $coleccion = $baseDeDatos->mascotas;
    
    $resultado = $coleccion->find(['usuario_id' => $usuario_id]); 
    
    $datos = [];
    foreach ($resultado as $documento) {
        $datos[] = $documento->jsonSerialize();
    }
    echo json_encode($datos);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error: " . $e->getMessage()]);
}
?>