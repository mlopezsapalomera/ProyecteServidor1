<?php
// controller/buscar.controller.php
// Controlador para búsqueda AJAX de usuarios y publicaciones

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../model/user/db_connection.php';
require_once __DIR__ . '/../model/user/account.model.php';
require_once __DIR__ . '/../model/pokemon.php';

// Obtener término de búsqueda
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

// Si no hay query, devolver vacío
if (empty($query) || strlen($query) < 2) {
    echo json_encode([
        'success' => false,
        'message' => 'Mínimo 2 caracteres para buscar',
        'usuarios' => [],
        'publicaciones' => []
    ]);
    exit;
}

try {
    // Buscar usuarios
    $usuarios = buscarUsuarios($query, 5);
    
    // Buscar publicaciones (pokémons)
    $publicaciones = buscarPokemons($query, 5);
    
    // Formatear resultados de usuarios
    $usuariosFormateados = array_map(function($usuario) {
        return [
            'id' => (int)$usuario['id'],
            'username' => $usuario['username'],
            'profile_image' => $usuario['profile_image'],
            'tipo' => 'usuario'
        ];
    }, $usuarios);
    
    // Formatear resultados de publicaciones
    $publicacionesFormateadas = array_map(function($pokemon) {
        return [
            'id' => (int)$pokemon['id'],
            'titulo' => $pokemon['titulo'],
            'descripcion' => $pokemon['descripcion'],
            'tipo_principal' => $pokemon['tipo_principal'] ?? null,
            'tipo_secundario' => $pokemon['tipo_secundario'] ?? null,
            'vida' => isset($pokemon['vida']) ? (int)$pokemon['vida'] : null,
            'ataque' => isset($pokemon['ataque']) ? (int)$pokemon['ataque'] : null,
            'defensa' => isset($pokemon['defensa']) ? (int)$pokemon['defensa'] : null,
            'ataque_especial' => isset($pokemon['ataque_especial']) ? (int)$pokemon['ataque_especial'] : null,
            'defensa_especial' => isset($pokemon['defensa_especial']) ? (int)$pokemon['defensa_especial'] : null,
            'velocidad' => isset($pokemon['velocidad']) ? (int)$pokemon['velocidad'] : null,
            'sprite_url' => $pokemon['sprite_url'] ?? null,
            'autor_username' => $pokemon['autor_username'],
            'autor_id' => (int)$pokemon['autor_id'],
            'autor_profile_image' => $pokemon['autor_profile_image'],
            'tipo' => 'publicacion'
        ];
    }, $publicaciones);
    
    // Respuesta JSON
    echo json_encode([
        'success' => true,
        'query' => $query,
        'total' => count($usuariosFormateados) + count($publicacionesFormateadas),
        'usuarios' => $usuariosFormateados,
        'publicaciones' => $publicacionesFormateadas
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al realizar la búsqueda',
        'error' => $e->getMessage(),
        'usuarios' => [],
        'publicaciones' => []
    ]);
}
