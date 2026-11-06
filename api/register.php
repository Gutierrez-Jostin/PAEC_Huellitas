<?php
require __DIR__ . '/conexion.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$datos = json_decode(file_get_contents("php://input"), true);

if ($datos) {
    try {
        $coleccion = $baseDeDatos->usuarios;
        
        $existe = $coleccion->findOne(['email' => $datos['email']]);
        if ($existe) {
            http_response_code(400);
            echo json_encode(["mensaje" => "El correo ya está registrado"]);
            exit;
        }

        $datos['password'] = password_hash($datos['password'], PASSWORD_DEFAULT);
        
        $resultado = $coleccion->insertOne($datos);
        echo json_encode(["mensaje" => "¡User registered successfully!"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["mensaje" => "Error: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["mensaje" => "No data received"]);
}
?>