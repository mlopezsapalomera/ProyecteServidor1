<?php
// controller/paginacio.controller.php

// Primero, incluimos el modelo de Pokémon para poder acceder a las funciones de paginación y consulta.
require_once __DIR__ . '/../model/pokemon.php';

// Recogemos el parámetro 'perPage' del GET para determinar cuántos pokémons mostrar por página.
// Si no está definido o no es numérico, usamos 5 como valor por defecto.
$perPage = isset($_GET['perPage']) && is_numeric($_GET['perPage']) ? (int)$_GET['perPage'] : 5;
// Verificamos que el número de elementos por página sea al menos 1.
if ($perPage < 1) $perPage = 5;

// Recogemos el parámetro 'page' del GET para saber qué página estamos visualizando.
// Si no está definido o no es numérico, usamos 1 como valor por defecto (primera página).
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
// Verificamos que la página sea al menos 1.
if ($page < 1) $page = 1;

// Obtenemos el número total de pokémons en la base de datos para calcular cuántas páginas hay.
$totalPokemons = contarPokemons();
// Calculamos el número total de páginas dividiendo el total de pokémons entre los elementos por página.
$totalPages = max(1, ceil($totalPokemons / $perPage));
// Si la página solicitada es mayor que el total de páginas, ajustamos a la última página disponible.
if ($page > $totalPages) $page = $totalPages;

// Calculamos el offset (desplazamiento) para la consulta SQL según la página actual.
$offset = ($page - 1) * $perPage;
// Obtenemos los pokémons correspondientes a la página actual usando la función del modelo.
$pokemons = obtenerPokemons($perPage, $offset);
?>