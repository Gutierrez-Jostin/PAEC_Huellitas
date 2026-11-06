<?php
require __DIR__ . '/conexion.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$datos = json_decode(file_get_contents("php://input"), true);

if ($datos) {
    try {
        $coleccion = $baseDeDatos->usuarios;
        $usuario = $coleccion->findOne(['email' => $datos['email']]);

        if ($usuario && password_verify($datos['password'], $usuario['password'])) {
           
            $token = base64_encode($usuario['_id'] . ':' . $usuario['email']);
            
            echo json_encode([
                "mensaje" => "¡Welcome!", 
                "token" => $token,
                "nombre" => $usuario['nombre']
            ]);
        } else {
            http_response_code(401);
            echo json_encode(["mensaje" => "Incorrect email or password"]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["mensaje" => "Error: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["mensaje" => "No data received"]);
}
?>