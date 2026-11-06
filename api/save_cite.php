<?php
require __DIR__ . '/conexion.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$headers = getallheaders();
$token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

if (!$token) {
    http_response_code(401);
    echo json_encode(["error" => "You must log in to schedule an appointment"]);
    exit;
}

$datosToken = explode(':', base64_decode($token));
$usuario_id = $datosToken[0];
$usuario_email = $datosToken[1] ?? 'Unknown';

$datos = json_decode(file_get_contents("php://input"), true);

if ($datos) {
    try {
        $coleccion = $baseDeDatos->citas;
        
        $datos['usuario_id'] = $usuario_id;
        $datos['usuario_email'] = $usuario_email;
        $datos['fecha_registro'] = date('Y-m-d H:i:s');
        
        $coleccion->insertOne($datos);
        echo json_encode(["mensaje" => "Appointment scheduled successfully!"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => "Error saving appointment: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "No data received"]);
}
?>