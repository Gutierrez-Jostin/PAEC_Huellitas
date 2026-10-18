<?php
require __DIR__ . '/conexion.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$datosRecibidos = json_decode(file_get_contents("php://input"), true);

if ($datosRecibidos) {
    try {
        $coleccion = $baseDeDatos->mascotas; 
        
        $resultado = $coleccion->insertOne($datosRecibidos);
        
        echo json_encode([
            "mensaje" => "¡Mascota guardada con éxito!",
            "id" => (string) $resultado->getInsertedId()
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["mensaje" => "Error al guardar: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["mensaje" => "No se recibieron datos válidos"]);
}
?>