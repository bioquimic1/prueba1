<?php
// backend/api/login.php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/jwt_handler.php'; // Lo crearemos en el siguiente paso

// Cabeceras
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *"); // Permitir acceso desde cualquier origen (ajustar en producción)
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Manejar solicitud OPTIONS (pre-vuelo CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Verificar que el método sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Método no permitido
    echo json_encode(["message" => "Método no permitido."]);
    exit();
}

// Obtener datos del POST (esperamos JSON)
$data = json_decode(file_get_contents("php://input"));

// Validar datos de entrada
if (!isset($data->email) || !isset($data->password)) {
    http_response_code(400); // Solicitud incorrecta
    echo json_encode(["message" => "Faltan datos de email o contraseña."]);
    exit();
}

// Credenciales hardcodeadas para prueba
$hardcoded_email = "admin@example.com";
$hardcoded_password_hash = password_hash("password123", PASSWORD_DEFAULT); // Simular contraseña hasheada

// Verificar credenciales (en un caso real, consultarías la base de datos)
if ($data->email === $hardcoded_email && password_verify($data->password, $hardcoded_password_hash)) {
    // Credenciales válidas, generar token JWT
    try {
        $jwt_payload = [
            'iss' => JWT_ISSUER,
            'aud' => JWT_AUDIENCE,
            'iat' => time(), // Hora de emisión del token
            'nbf' => time(), // Token no válido antes de esta hora
            'exp' => time() + JWT_EXPIRATION_TIME_SECONDS, // Hora de expiración
            'data' => [ // Datos personalizados del usuario
                'user_id' => 1, // ID de usuario de ejemplo
                'email' => $data->email,
                'role' => 'admin' // Rol de ejemplo
            ]
        ];

        // Asumimos que JwtHandler::encode() existe y funciona (se definirá en jwt_handler.php)
        $token = JwtHandler::encode($jwt_payload, JWT_SECRET_KEY);

        http_response_code(200); // OK
        echo json_encode([
            "message" => "Login exitoso.",
            "token" => $token,
            "expiresIn" => JWT_EXPIRATION_TIME_SECONDS
        ]);

    } catch (Exception $e) {
        http_response_code(500); // Error interno del servidor
        echo json_encode([
            "message" => "Error al generar el token.",
            "error" => $e->getMessage()
        ]);
    }

} else {
    // Credenciales inválidas
    http_response_code(401); // No autorizado
    echo json_encode(["message" => "Email o contraseña incorrectos."]);
}

?>
