<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../model/pokemon_api.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$name = isset($_GET['name']) ? trim($_GET['name']) : '';

try {
    if ($query !== '') {
        if (strlen($query) < 2) {
            echo json_encode([
                'success' => false,
                'message' => 'Mínimo 2 caracteres para buscar',
                'results' => [],
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $results = buscarPokemonApi($query, 12);
        echo json_encode([
            'success' => true,
            'query' => $query,
            'total' => count($results),
            'results' => $results,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($name !== '') {
        $pokemon = obtenerPokemonApiPorNombre($name);
        if (!$pokemon) {
            echo json_encode([
                'success' => false,
                'message' => 'Pokémon no encontrado',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode([
            'success' => true,
            'pokemon' => $pokemon,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode([
        'success' => false,
        'message' => 'Parámetros insuficientes',
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al consultar la API de Pokémon',
    ], JSON_UNESCAPED_UNICODE);
}
