<?php
require __DIR__ . '/conexion.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$headers = getallheaders();
$token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
if (!$token) { http_response_code(401); exit; }

$datosToken = explode(':', base64_decode($token));
$usuario_id = $datosToken[0];

$usuario = $baseDeDatos->usuarios->findOne(['_id' => new MongoDB\BSON\ObjectId($usuario_id)]);
if (!isset($usuario['rol']) || $usuario['rol'] !== 'admin') {
    http_response_code(403); echo json_encode(["error" => "Unauthorized"]); exit;
}

$mascotas = $baseDeDatos->mascotas->find([]);
$datos = [];
foreach ($mascotas as $doc) { $datos[] = $doc->jsonSerialize(); }
echo json_encode($datos);
?>