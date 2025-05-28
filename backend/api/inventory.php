<?php
// backend/api/inventory.php

require_once __DIR__ . '/../core/jwt_handler.php';

// Cabeceras
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *"); // Ajustar en producción
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Manejar solicitud OPTIONS (pre-vuelo CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Validar el token JWT. Si no es válido, JwtHandler::validateTokenFromRequest() hará exit().
$user_data_from_token = JwtHandler::validateTokenFromRequest();

// Si el script llega aquí, el token es válido.
// $user_data_from_token contiene el payload del token (ej. datos del usuario).

// Verificar que el método sea GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Método no permitido
    echo json_encode(["message" => "Método no permitido."]);
    exit();
}

// Datos de ejemplo del inventario (simulados)
$inventory_data = [
    [
        "id" => "P001",
        "nombre" => "Tornillos de Acero Inoxidable 5mm",
        "sku" => "TRN-SS-005",
        "cantidad" => 12500,
        "unidad" => "unidades",
        "ubicacion" => ["pasillo" => "A", "estanteria" => "3", "nivel" => "2"],
        "valor_unitario" => 0.08,
        "fecha_ultima_actualizacion" => "2023-10-26T10:00:00Z"
    ],
    [
        "id" => "P002",
        "nombre" => "Cajas de Cartón Medianas",
        "sku" => "BOX-MED-001",
        "cantidad" => 350,
        "unidad" => "unidades",
        "ubicacion" => ["pasillo" => "C", "estanteria" => "1", "nivel" => "1"],
        "valor_unitario" => 1.20,
        "fecha_ultima_actualizacion" => "2023-10-25T14:30:00Z"
    ],
    [
        "id" => "P003",
        "nombre" => "Cinta Adhesiva Industrial",
        "sku" => "TAPE-IND-001",
        "cantidad" => 800,
        "unidad" => "rollos",
        "ubicacion" => ["pasillo" => "B", "estanteria" => "5", "nivel" => "4"],
        "valor_unitario" => 2.50,
        "fecha_ultima_actualizacion" => "2023-10-26T08:15:00Z"
    ]
];

http_response_code(200); // OK
echo json_encode([
    "message" => "Datos de inventario obtenidos exitosamente.",
    "user_requesting" => $user_data_from_token->data ?? null, // Mostrar datos del usuario del token (opcional)
    "inventory" => $inventory_data
]);

?>
