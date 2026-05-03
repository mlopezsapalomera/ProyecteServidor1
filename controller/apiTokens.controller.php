<?php
// controller/apiTokens.controller.php
// Endpoint para gestión de tokens API (crear, listar, revocar)

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../model/api_tokens.php';

function responderJson(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? null;
    
    // GET /controller/apiTokens.controller.php
    // Listar todos los tokens (sin mostrar el hash)
    if ($method === 'GET' && !$action) {
        $tokens = obtenerTokensApi();
        
        $tokenFormateados = array_map(function($token) {
            return [
                'id' => (int)$token['id'],
                'name' => $token['name'],
                'description' => $token['description'],
                'expires_at' => $token['expires_at'],
                'is_active' => (bool)$token['is_active'],
                'last_used_at' => $token['last_used_at'],
                'created_at' => $token['created_at'],
            ];
        }, $tokens);
        
        responderJson([
            'success' => true,
            'message' => 'Tokens obtenidos correctamente',
            'count' => count($tokenFormateados),
            'data' => $tokenFormateados,
        ]);
    }
    
    // POST /controller/apiTokens.controller.php?action=create
    // Crear nuevo token
    if ($method === 'POST' && $action === 'create') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $name = $input['name'] ?? null;
        $description = $input['description'] ?? null;
        $diasExpiracion = (int)($input['dias_expiracion'] ?? 30);
        
        if (!$name || strlen($name) < 3) {
            responderJson([
                'success' => false,
                'message' => 'El nombre del token debe tener al menos 3 caracteres',
            ], 400);
        }
        
        $result = crearTokenApi($name, $description, $diasExpiracion);
        
        responderJson([
            'success' => true,
            'message' => 'Token creado correctamente. Guarda este token en un lugar seguro, no podrás verlo de nuevo.',
            'token' => $result['token'],
            'id' => $result['id'],
            'expires_at' => $result['expires_at'],
            'instructions' => 'Usa este token en el header: Authorization: Bearer ' . $result['token'],
        ]);
    }
    
    // DELETE /controller/apiTokens.controller.php?action=revoke&id=1
    // Revocar un token
    if ($method === 'DELETE' && $action === 'revoke') {
        $tokenId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        
        if (!$tokenId) {
            responderJson([
                'success' => false,
                'message' => 'ID de token inválido',
            ], 400);
        }
        
        $revocado = revocarTokenApi($tokenId);
        
        if (!$revocado) {
            responderJson([
                'success' => false,
                'message' => 'No se pudo revocar el token',
            ], 500);
        }
        
        responderJson([
            'success' => true,
            'message' => 'Token revocado correctamente',
        ]);
    }
    
    // DELETE /controller/apiTokens.controller.php?action=delete&id=1
    // Eliminar un token
    if ($method === 'DELETE' && $action === 'delete') {
        $tokenId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        
        if (!$tokenId) {
            responderJson([
                'success' => false,
                'message' => 'ID de token inválido',
            ], 400);
        }
        
        $eliminado = eliminarTokenApi($tokenId);
        
        if (!$eliminado) {
            responderJson([
                'success' => false,
                'message' => 'No se pudo eliminar el token',
            ], 500);
        }
        
        responderJson([
            'success' => true,
            'message' => 'Token eliminado correctamente',
        ]);
    }
    
    responderJson([
        'success' => false,
        'message' => 'Acción no válida o método no permitido',
    ], 400);
    
} catch (Throwable $e) {
    responderJson([
        'success' => false,
        'message' => 'Error al procesar la solicitud',
        'error' => $e->getMessage(),
    ], 500);
}
