<?php
require __DIR__ . '/../vendor/autoload.php';


$uri = getenv('MONGODB_URI');

if (!$uri) {

    die("Error: La variable de entorno MONGODB_URI no está configurada.");
}

try {
    $cliente = new MongoDB\Client($uri);
    $baseDeDatos = $cliente->Huellitas_paec; 
} catch (Exception $e) {
    die("Error al conectar a MongoDB: " . $e->getMessage());
}
?>