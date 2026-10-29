<?php
require __DIR__ . '/conexion.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$datos = json_decode(file_get_contents("php://input"), true);

if ($datos) {
    try {
        $coleccion = $baseDeDatos->resenas; 
        $resultado = $coleccion->insertOne($datos);
        echo json_encode(["mensaje" => "Reseña guardada", "id" => (string)$resultado->getInsertedId()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => "Error al guardar: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "No se recibieron datos válidos"]);
}
?>