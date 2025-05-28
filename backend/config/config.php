<?php
// backend/config/config.php

define('JWT_SECRET_KEY', 'TU_CLAVE_SECRETA_MUY_SEGURA_AQUI'); // ¡Cambiar esto en producción!
define('JWT_ISSUER', 'tu-dominio.com'); // Emisor del token
define('JWT_AUDIENCE', 'tu-dominio.com'); // Audiencia del token
define('JWT_EXPIRATION_TIME_SECONDS', 3600); // 1 hora de expiración

// Configuración de base de datos (ejemplo, si se usa)
// define('DB_HOST', 'localhost');
// define('DB_NAME', 'nombre_base_datos');
// define('DB_USER', 'usuario_db');
// define('DB_PASS', 'contraseña_db');

?>
