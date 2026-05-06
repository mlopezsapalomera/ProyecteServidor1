<?php
// controller/solicitarAccesoEndpoint.controller.php
// Genera un token de acceso a la API desde el perfil de usuario

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../security/auth.php';
require_once __DIR__ . '/../model/api_tokens.php';

function responderJson(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (!estaIdentificado()) {
    responderJson([
        'success' => false,
        'message' => 'Debes iniciar sesión para solicitar acceso al endpoint',
    ], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJson([
        'success' => false,
        'message' => 'Método no permitido',
    ], 405);
}

if (!csrfValidoPost()) {
    responderJson([
        'success' => false,
        'message' => 'Solicitud inválida. Recarga la página e inténtalo de nuevo.',
    ], 403);
}

try {
    $usuario = usuarioActual();
    $username = $usuario['username'] ?? 'usuario';

    $descripcion = 'Token solicitado desde el perfil de ' . $username;
    $resultado = crearTokenApi('Acceso API - ' . $username, $descripcion, 30);

    responderJson([
        'success' => true,
        'message' => 'Acceso concedido. Guarda este token, solo se muestra una vez.',
        'endpoint' => 'http://localhost/ProyecteServidor1/controller/apiPokemons.controller.php',
        'token' => $resultado['token'],
        'expires_at' => $resultado['expires_at'],
        'header' => 'Authorization: Bearer ' . $resultado['token'],
        'instructions' => 'En Postman, usa el endpoint y añade el header Authorization con este Bearer token.',
    ]);
} catch (Throwable $e) {
    responderJson([
        'success' => false,
        'message' => 'No se pudo generar el token',
        'error' => $e->getMessage(),
    ], 500);
}
