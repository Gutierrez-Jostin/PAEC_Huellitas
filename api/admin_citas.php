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
    echo json_encode(["error" => "No token received"]);
    exit; 
}

$datosToken = explode(':', base64_decode($token));
$usuario_id = $datosToken[0] ?? null;

if (!$usuario_id) {
    http_response_code(401); 
    echo json_encode(["error" => "Invalid token"]);
    exit; 
}

$usuario = $baseDeDatos->usuarios->findOne(['_id' => new MongoDB\BSON\ObjectId($usuario_id)]);

if (!isset($usuario['rol']) || $usuario['rol'] !== 'admin') {
    http_response_code(403); 
    echo json_encode(["error" => "Unauthorized"]); 
    exit;
}

$citas = $baseDeDatos->citas->find([]);
$datos = [];
foreach ($citas as $doc) { $datos[] = $doc->jsonSerialize(); }
echo json_encode($datos);
?>