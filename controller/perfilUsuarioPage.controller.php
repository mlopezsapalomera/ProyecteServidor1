<?php
// controller/perfilUsuarioPage.controller.php
// Renderiza la vista de perfil de usuario

require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../model/pokemon.php';
require_once __DIR__ . '/../security/auth.php';

$perfilUserId = isset($_GET['id']) ? (int)$_GET['id'] : idUsuarioActual();
if (!$perfilUserId) {
    header('Location: ../view/login.vista.php');
    exit;
}

$perfilUsuario = obtenerUsuarioPorId($perfilUserId);
if (!$perfilUsuario) {
    header('Location: ../index.php?error=' . urlencode('Usuario no encontrado'));
    exit;
}

$porPagina = isset($_GET['porPagina']) && is_numeric($_GET['porPagina']) ? (int)$_GET['porPagina'] : 10;
if ($porPagina < 1) {
    $porPagina = 10;
}

$pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina < 1) {
    $pagina = 1;
}

$totalPokemons = contarPokemonsPorUsuario($perfilUserId);
$totalPaginas = max(1, ceil($totalPokemons / $porPagina));
if ($pagina > $totalPaginas) {
    $pagina = $totalPaginas;
}

$desplazamiento = ($pagina - 1) * $porPagina;
$pokemons = obtenerPokemonsPorUsuario($perfilUserId, $porPagina, $desplazamiento);
$esMiPerfil = estaIdentificado() && idUsuarioActual() === $perfilUserId;

require_once __DIR__ . '/../view/perfilUsuario.vista.php';
