<?php
require __DIR__ . '/conexion.php';
header('Content-Type: application/json');

try {
    $coleccion = $baseDeDatos->articulos; // Nombre de tu nueva colección

    $articulos = [
        [
            "nombre" => "METACAM 0.5%",
            "categoria" => "Antibiotic",
            "forma" => "Solution for injection",
            "descripcion" => "Non-steroidal anti-inflammatory, analgesic, and antipyretic for the control of pain, fever, and inflammation in dogs and cats.",
            "imagen" => "metacam.jpg"
        ],
        [
            "nombre" => "MELODEX GOTAS",
            "categoria" => "Analgesic",
            "forma" => "Oral solution",
            "descripcion" => "Analgesic, antipyretic, non-steroidal anti-inflammatory.",
            "imagen" => "melodex-gotas.jfif"
        ],
        [
            "nombre" => "MELODEX PETS 0.5%",
            "categoria" => "Analgesic",
            "forma" => "Solution for injection",
            "descripcion" => "Non-steroidal anti-inflammatory, analgesic, and antipyretic agent with an affinity for COX-2.",
            "imagen" => "melodex-pet.jfif"
        ],
        [
            "nombre" => "METRONID",
            "categoria" => "Antiparasitic",
            "forma" => "Tablets Solution",
            "descripcion" => "Antimicrobial agent with activity against anaerobic bacteria and protozoa. Metronidazole.",
            "imagen" => "metronid.jfif"
        ]
    ];

    $resultado = $coleccion->insertMany($articulos);
    echo json_encode(["mensaje" => "Se insertaron " . $resultado->getInsertedCount() . " artículos correctamente."]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error: " . $e->getMessage()]);
}
?>