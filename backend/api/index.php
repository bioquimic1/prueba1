<?php
// backend/api/index.php

// Establecer cabeceras para respuestas JSON y CORS (si es necesario)
header("Content-Type: application/json; charset=UTF-8");
// header("Access-Control-Allow-Origin: *"); // Descomentar si el frontend está en otro dominio/puerto
// header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
// header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Simple mensaje de bienvenida para la API
// En una implementación real, aquí habría un sistema de enrutamiento
// para dirigir las peticiones a los controladores adecuados.

// Ejemplo de respuesta
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Solicitud pre-vuelo CORS
    http_response_code(200);
    exit();
}

echo json_encode(["message" => "Bienvenido a la API del Sistema de Almacén"]);

?>
