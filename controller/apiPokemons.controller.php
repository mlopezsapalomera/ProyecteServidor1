<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../model/pokemon.php';
require_once __DIR__ . '/../model/api_tokens.php';

function responderJson(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function obtenerTokenDelHeader(): string|null
{
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    if ($authHeader === '' && function_exists('getallheaders')) {
        $headers = getallheaders();
        foreach ($headers as $name => $value) {
            if (strcasecmp($name, 'Authorization') === 0) {
                $authHeader = $value;
                break;
            }
        }
    }
    
    if (preg_match('/Bearer\s+(.+)/i', $authHeader, $matches)) {
        return trim($matches[1]);
    }
    
    return null;
}

function validarAccesoApi(): void
{
    $token = obtenerTokenDelHeader();
    
    if (!$token) {
        responderJson([
            'success' => false,
            'message' => 'Token no proporcionado. Usa: Authorization: Bearer <tu_token>',
        ], 401);
    }
    
    $tokenValido = validarTokenApi($token);
    
    if (!$tokenValido) {
        responderJson([
            'success' => false,
            'message' => 'Token inválido, expirado o revocado',
        ], 403);
    }
}

function formatearPokemonApi(array $pokemon): array
{
    return [
        'id' => isset($pokemon['id']) ? (int)$pokemon['id'] : null,
        'titulo' => $pokemon['titulo'] ?? null,
        'descripcion' => $pokemon['descripcion'] ?? null,
        'user_id' => isset($pokemon['user_id']) ? (int)$pokemon['user_id'] : null,
        'autor' => [
            'id' => isset($pokemon['autor_id']) ? (int)$pokemon['autor_id'] : null,
            'username' => $pokemon['autor_username'] ?? null,
            'profile_image' => $pokemon['autor_profile_image'] ?? null,
        ],
        'pokemon_api' => [
            'id' => isset($pokemon['pokemon_api_id']) ? (int)$pokemon['pokemon_api_id'] : null,
            'name' => $pokemon['pokemon_api_name'] ?? null,
            'sprite_url' => $pokemon['sprite_url'] ?? null,
        ],
        'stats' => [
            'tipo_principal' => $pokemon['tipo_principal'] ?? null,
            'tipo_secundario' => $pokemon['tipo_secundario'] ?? null,
            'vida' => isset($pokemon['vida']) ? (int)$pokemon['vida'] : null,
            'ataque' => isset($pokemon['ataque']) ? (int)$pokemon['ataque'] : null,
            'defensa' => isset($pokemon['defensa']) ? (int)$pokemon['defensa'] : null,
            'ataque_especial' => isset($pokemon['ataque_especial']) ? (int)$pokemon['ataque_especial'] : null,
            'defensa_especial' => isset($pokemon['defensa_especial']) ? (int)$pokemon['defensa_especial'] : null,
            'velocidad' => isset($pokemon['velocidad']) ? (int)$pokemon['velocidad'] : null,
        ],
        'created_at' => $pokemon['created_at'] ?? null,
        'updated_at' => $pokemon['updated_at'] ?? null,
    ];
}

try {
    // Validar acceso por token
    validarAccesoApi();
    
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    $limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT, [
        'options' => ['default' => 20, 'min_range' => 1, 'max_range' => 100],
    ]);
    $offset = filter_input(INPUT_GET, 'offset', FILTER_VALIDATE_INT, [
        'options' => ['default' => 0, 'min_range' => 0],
    ]);

    if ($id !== null && $id !== false) {
        $pokemon = obtenerPokemonPorId($id);

        if (!$pokemon) {
            responderJson([
                'success' => false,
                'message' => 'Pokémon no encontrado',
            ], 404);
        }

        responderJson([
            'success' => true,
            'data' => formatearPokemonApi($pokemon),
        ]);
    }

    $pokemons = obtenerPokemons($limit, $offset);

    responderJson([
        'success' => true,
        'meta' => [
            'limit' => (int)$limit,
            'offset' => (int)$offset,
            'count' => count($pokemons),
            'total' => contarPokemons(),
        ],
        'data' => array_map('formatearPokemonApi', $pokemons),
    ]);
} catch (Throwable $e) {
    responderJson([
        'success' => false,
        'message' => 'Error al obtener los datos',
        'debug' => $e->getMessage(),
    ], 500);
}