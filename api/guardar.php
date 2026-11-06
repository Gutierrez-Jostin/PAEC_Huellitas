<?php
require __DIR__ . '/conexion.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$headers = getallheaders();
$token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

if (!$token) {
    http_response_code(401);
    echo json_encode(["mensaje" => "Debes iniciar sesión"]);
    exit;
}


$datosToken = explode(':', base64_decode($token));
$usuario_id = $datosToken[0];

$datosRecibidos = json_decode(file_get_contents("php://input"), true);

if ($datosRecibidos) {
    try {
        $coleccion = $baseDeDatos->mascotas;
        
        
        $datosRecibidos['usuario_id'] = $usuario_id;
        
        $resultado = $coleccion->insertOne($datosRecibidos);
        echo json_encode(["mensaje" => "¡Mascota guardada con éxito!"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["mensaje" => "Error al guardar: " . $e->getMessage()]);
    }
}
?>