<?php
require __DIR__ . '/vendor/autoload.php';

$usuario = "gutierrezmaganajostinernestogen2024_db_user"; 
$password = urlencode("nEFmwxffg9YMCTFR"); 
$cluster = "cluster0.wggbcsb.mongodb.net"; 

$uri = "mongodb+srv://{$usuario}:{$password}@{$cluster}/?retryWrites=true&w=majority";

try {
    $cliente = new MongoDB\Client($uri);
    
    $baseDeDatos = $cliente->Huellitas_paec; 
    
} 

catch (Exception $e) {
    die("Error al conectar a MongoDB: " . $e->getMessage());
}
?>