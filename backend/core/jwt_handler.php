<?php
// backend/core/jwt_handler.php

// Simulación de la inclusión de la librería firebase/php-jwt
// En un proyecto real, esto se manejaría con Composer y su autoloader.
// require_once __DIR__ . '/../vendor/autoload.php'; // Si Composer está configurado
// Use Firebase\JWT\JWT;
// Use Firebase\JWT\Key;
// Use Firebase\JWT\ExpiredException;
// Use Firebase\JWT\SignatureInvalidException;
// Use Firebase\JWT\BeforeValidException;

// --- Bloque de simulación de la clase JWT y Key de firebase/php-jwt ---
// ESTO ES SOLO PARA SIMULACIÓN Y PERMITIR QUE EL CÓDIGO FUNCIONE SIN LA LIBRERÍA REAL.
// EN UN PROYECTO REAL, ELIMINAR ESTE BLOQUE Y USAR LA LIBRERÍA REAL VÍA COMPOSER.
if (!class_exists('Firebase\JWT\JWT')) {
    class JWT_SIMULATED { // Renombrado para evitar conflictos si la librería real se carga
        public static function encode($payload, $key, $alg) {
            // Simulación muy básica de la codificación JWT
            $header = ['alg' => $alg, 'typ' => 'JWT'];
            $encodedHeader = base64_encode(json_encode($header));
            $encodedPayload = base64_encode(json_encode($payload));
            // Simulación de firma (no segura, solo para demostración)
            $signature = hash_hmac('sha256', "$encodedHeader.$encodedPayload", $key, true);
            $encodedSignature = base64_encode($signature);
            return "$encodedHeader.$encodedPayload.$encodedSignature";
        }

        public static function decode($jwt, /* Firebase\JWT\Key */ $key_sim) {
            // Simulación muy básica de la decodificación JWT
            list($encodedHeader, $encodedPayload, $encodedSignature) = explode('.', $jwt);
            $header = json_decode(base64_decode($encodedHeader), true);
            $payload = json_decode(base64_decode($encodedPayload), true);

            // Verificar firma (simulación)
            $expectedSignature = base64_encode(hash_hmac('sha256', "$encodedHeader.$encodedPayload", $key_sim->getKeyMaterial(), true));
            if ($encodedSignature !== $expectedSignature) {
                throw new Exception('Signature verification failed'); // SignatureInvalidException
            }

            // Verificar expiración (simulación)
            if (isset($payload['exp']) && time() > $payload['exp']) {
                throw new Exception('Expired token'); // ExpiredException
            }
            
            // Verificar nbf (not before) (simulación)
            if (isset($payload['nbf']) && time() < $payload['nbf']) {
                throw new Exception('Cannot handle token prior to nbf'); // BeforeValidException
            }

            return (object) $payload; // Devolver como objeto, similar a la librería real
        }
    }
    // Simulación de la clase Key
    class Key_SIMULATED {
        private $keyMaterial;
        private $algorithm;
        public function __construct($keyMaterial, $algorithm) {
            $this->keyMaterial = $keyMaterial;
            $this->algorithm = $algorithm;
        }
        public function getKeyMaterial() { return $this->keyMaterial; }
        public function getAlgorithm() { return $this->algorithm; }
    }

    // Usar las clases simuladas
    if (!class_exists('Firebase\JWT\JWT')) {
        class_alias('JWT_SIMULATED', 'Firebase\JWT\JWT');
    }
    if (!class_exists('Firebase\JWT\Key')) {
         class_alias('Key_SIMULATED', 'Firebase\JWT\Key');
    }
    // Definir excepciones simuladas si no existen
    if (!class_exists('Firebase\JWT\ExpiredException')) { class ExpiredException_SIM extends Exception {} class_alias('ExpiredException_SIM', 'Firebase\JWT\ExpiredException'); }
    if (!class_exists('Firebase\JWT\SignatureInvalidException')) { class SignatureInvalidException_SIM extends Exception {} class_alias('SignatureInvalidException_SIM', 'Firebase\JWT\SignatureInvalidException'); }
    if (!class_exists('Firebase\JWT\BeforeValidException')) { class BeforeValidException_SIM extends Exception {} class_alias('BeforeValidException_SIM', 'Firebase\JWT\BeforeValidException'); }
}
// --- Fin del bloque de simulación ---


class JwtHandler {
    
    /**
     * Codifica un payload en un token JWT.
     *
     * @param array $payload El payload a codificar.
     * @param string $key La clave secreta.
     * @return string El token JWT generado.
     * @throws Exception Si la codificación falla.
     */
    public static function encode(array $payload, string $key): string {
        // Usar la clase JWT (real o simulada)
        return Firebase\JWT\JWT::encode($payload, $key, 'HS256');
    }

    /**
     * Decodifica y valida un token JWT.
     *
     * @param string $jwt El token JWT a decodificar.
     * @param string $key La clave secreta.
     * @return object El payload decodificado como un objeto.
     * @throws Firebase\JWT\ExpiredException Si el token ha expirado.
     * @throws Firebase\JWT\SignatureInvalidException Si la firma no es válida.
     * @throws Firebase\JWT\BeforeValidException Si el token aún no es válido (nbf).
     * @throws Exception Para otras errores de JWT.
     */
    public static function decode(string $jwt, string $key): object {
        // Usar la clase JWT y Key (real o simulada)
        $keyObject = new Firebase\JWT\Key($key, 'HS256');
        return Firebase\JWT\JWT::decode($jwt, $keyObject);
    }

    /**
     * Obtiene el token JWT de las cabeceras de autorización.
     *
     * @return string|null El token JWT o null si no se encuentra.
     */
    public static function getBearerToken(): ?string {
        $authHeader = null;
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        } elseif (isset($_SERVER['HTTP_X_AUTHORIZATION'])) { // Cabecera alternativa
            $authHeader = $_SERVER['HTTP_X_AUTHORIZATION'];
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) {
                $authHeader = trim($requestHeaders['Authorization']);
            }
        }

        if (!empty($authHeader)) {
            if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    /**
     * Valida el token JWT de la solicitud actual.
     * Usado como middleware o al inicio de scripts protegidos.
     *
     * @return object|null El payload del token si es válido, null en caso contrario.
     *                     También puede hacer exit() con un error HTTP.
     */
    public static function validateTokenFromRequest() {
        $token = self::getBearerToken();
        if (!$token) {
            http_response_code(401); // No Autorizado
            echo json_encode(["message" => "Token de autorización no proporcionado."]);
            exit();
        }

        try {
            require_once __DIR__ . '/../config/config.php'; // Asegura que JWT_SECRET_KEY esté definida
            $decoded_payload = self::decode($token, JWT_SECRET_KEY);
            return $decoded_payload; // Token válido, devuelve el payload
        } catch (Firebase\JWT\ExpiredException $e) {
            http_response_code(401); // No Autorizado
            echo json_encode(["message" => "Token expirado.", "error" => $e->getMessage()]);
            exit();
        } catch (Firebase\JWT\SignatureInvalidException $e) {
            http_response_code(401); // No Autorizado
            echo json_encode(["message" => "Firma de token inválida.", "error" => $e->getMessage()]);
            exit();
        } catch (Firebase\JWT\BeforeValidException $e) {
            http_response_code(401); // No Autorizado
            echo json_encode(["message" => "Token aún no válido (nbf).", "error" => $e->getMessage()]);
            exit();
        } catch (Exception $e) {
            http_response_code(401); // No Autorizado
            echo json_encode(["message" => "Token inválido.", "error" => $e->getMessage()]);
            exit();
        }
    }
}
?>
