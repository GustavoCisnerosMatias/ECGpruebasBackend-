<?php
// auth_utils.php
require_once  'jwt/JWT.php';
require_once 'jwt/Key.php';
require_once  'jwt/ExpiredException.php';
require_once 'jwt/BeforeValidException.php';
require_once  'jwt/SignatureInvalidException.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function validateJWT($f3, $secretKey = 'viteced2025viteced', $algorithm = 'HS256')
{
    // Obtener el encabezado Authorization (soporta variaciones)
    $authHeader = $f3->get('HEADERS.Authorization') 
        ?? $f3->get('HEADERS.authorization') 
        ?? (function_exists('getallheaders') ? getallheaders()['Authorization'] ?? '' : '');

    // Validar si se recibió el token
    if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        echo json_encode(['mensaje' => 'Token no proporcionado']);
        return false;
    }
    
    $token = $matches[1];

    try {
        // Decodificar JWT usando la clave y algoritmo proporcionado
        $decoded = JWT::decode($token, new Key($secretKey, $algorithm));
        return $decoded;
    } catch (\Firebase\JWT\ExpiredException $e) {
        echo json_encode(['mensaje' => 'Token expirado, vuelve a iniciar sesion']);
    } catch (\Firebase\JWT\SignatureInvalidException $e) {
        echo json_encode(['mensaje' => 'Firma del token inválida']);
    } catch (\Firebase\JWT\BeforeValidException $e) {
        echo json_encode(['mensaje' => 'Token no es válido aún']);
    } catch (\Exception $e) {
        echo json_encode(['mensaje' => 'Token inválido: ' . $e->getMessage()]);
    }

    return false;
}
