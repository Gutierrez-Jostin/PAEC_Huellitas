<?php
require __DIR__ . '/conexion.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$headers = getallheaders();
$token = null;

if (isset($headers['Authorization'])) {
    $token = $headers['Authorization'];
} elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $token = $_SERVER['HTTP_AUTHORIZATION'];
} elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
    $token = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
}

if ($token) {
    $token = str_replace('Bearer ', '', $token);
}

if (!$token) {
    http_response_code(401);
    echo json_encode(["error" => "You must log in to leave a review"]);
    exit;
}

$datosToken = explode(':', base64_decode($token));
$usuario_id = $datosToken[0] ?? null;
$usuario_email = $datosToken[1] ?? 'Unknown';

if (!$usuario_id) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid session. Please log in again."]);
    exit;
}

$datos = json_decode(file_get_contents("php://input"), true);

if ($datos) {
    try {
        $coleccion = $baseDeDatos->resenas;
        $datos['usuario_id'] = $usuario_id;
        $datos['usuario'] = $usuario_email; 
        $coleccion->insertOne($datos);
        echo json_encode(["mensaje" => "Review saved successfully!"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => "Error saving review: " . $e->getMessage()]);
    }
}
?>