<?php
// controller/paginacio.controller.php
// Controlador para la lógica de paginación

require_once __DIR__ . '/../model/pokemon.php';

// Obtener parámetros de paginación
$porPagina = isset($_GET['porPagina']) && is_numeric($_GET['porPagina']) ? (int)$_GET['porPagina'] : 5;
if ($porPagina < 1) $porPagina = 5;

$pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina < 1) $pagina = 1;

// Obtener parámetros de ordenación
$ordenarPor = isset($_GET['ordenarPor']) ? $_GET['ordenarPor'] : 'id';
$direccionOrden = isset($_GET['direccionOrden']) ? $_GET['direccionOrden'] : 'DESC';

// Calcular paginación
$totalPokemons = contarPokemons();
$totalPaginas = max(1, ceil($totalPokemons / $porPagina));
if ($pagina > $totalPaginas) $pagina = $totalPaginas;

// Obtener pokémons de la página actual con ordenación
$desplazamiento = ($pagina - 1) * $porPagina;
$pokemons = obtenerPokemons($porPagina, $desplazamiento, $ordenarPor, $direccionOrden);
?>