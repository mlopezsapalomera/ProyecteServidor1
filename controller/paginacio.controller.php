<?php
// controller/paginacio.controller.php
// Controlador para la lógica de paginación

require_once __DIR__ . '/../model/pokemon.php';

// Obtener parámetros de paginación
$perPage = isset($_GET['perPage']) && is_numeric($_GET['perPage']) ? (int)$_GET['perPage'] : 5;
if ($perPage < 1) $perPage = 5;

$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// Obtener parámetros de ordenación
$orderBy = isset($_GET['orderBy']) ? $_GET['orderBy'] : 'id';
$orderDir = isset($_GET['orderDir']) ? $_GET['orderDir'] : 'DESC';

// Calcular paginación
$totalPokemons = contarPokemons();
$totalPages = max(1, ceil($totalPokemons / $perPage));
if ($page > $totalPages) $page = $totalPages;

// Obtener pokémons de la página actual con ordenación
$offset = ($page - 1) * $perPage;
$pokemons = obtenerPokemons($perPage, $offset, $orderBy, $orderDir);
?>