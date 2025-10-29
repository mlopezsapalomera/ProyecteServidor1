<?php
/**
 * Controlador de paginación para la lista de pokémons.
 * Lee parámetros GET (?page, ?perPage), calcula límites y
 * expone variables a la vista: $pokemons, $page, $perPage, $totalPages, $totalPokemons.
 */
require_once __DIR__ . '/../model/pokemon.php';

// Número de pokemons por página (por defecto 5, pero puede venir por GET)
$perPage = isset($_GET['perPage']) && is_numeric($_GET['perPage']) ? (int)$_GET['perPage'] : 5;
if ($perPage < 1) $perPage = 5;

// Página actual (por defecto 1)
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$totalPokemons = countPokemons();
$totalPages = max(1, ceil($totalPokemons / $perPage));
if ($page > $totalPages) $page = $totalPages;

$offset = ($page - 1) * $perPage;
$pokemons = getAllPokemons($perPage, $offset);
?>