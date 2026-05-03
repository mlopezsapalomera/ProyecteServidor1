<?php

require_once __DIR__ . '/../model/pokemon.php';
require_once __DIR__ . '/../model/pokemon_api.php';
require_once __DIR__ . '/../security/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../controller/insertarPage.controller.php');
    exit;
}

csrfRequireOrRedirect('../controller/insertarPage.controller.php');

if (!estaIdentificado()) {
    header('Location: ../view/login.vista.php?error=' . urlencode('Debes iniciar sesión'));
    exit;
}

$tituloVisible = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
$descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
$descripcion = $descripcion !== '' ? $descripcion : null;
$nombreApi = isset($_POST['pokemon_api_name']) ? trim($_POST['pokemon_api_name']) : '';

if ($tituloVisible === '') {
    header('Location: ../controller/insertarPage.controller.php?error=' . urlencode('El nombre es obligatorio') . '&titulo=' . urlencode($tituloVisible));
    exit;
}

if ($nombreApi === '') {
    $nombreApi = pokemonApiSlugify($tituloVisible);
}

$pokemonApi = obtenerPokemonApiPorNombre($nombreApi);
if (!$pokemonApi) {
    header('Location: ../controller/insertarPage.controller.php?error=' . urlencode('Selecciona un Pokémon válido de la lista') . '&titulo=' . urlencode($tituloVisible));
    exit;
}

$idUsuario = idUsuarioActual();
$ok = insertarPokemon(
    $pokemonApi['display_name'],
    $descripcion,
    $idUsuario,
    $pokemonApi['api_id'],
    $pokemonApi['api_name'],
    $pokemonApi['primary_type'],
    $pokemonApi['secondary_type'],
    $pokemonApi['vida'],
    $pokemonApi['ataque'],
    $pokemonApi['defensa'],
    $pokemonApi['ataque_especial'],
    $pokemonApi['defensa_especial'],
    $pokemonApi['velocidad'],
    $pokemonApi['sprite_url']
);

if ($ok) {
    header('Location: ../index.php?ok=' . urlencode('Pokemon insertado correctamente'));
    exit;
}
header('Location: ../controller/insertarPage.controller.php?error=' . urlencode('No se ha podido insertar') . '&titulo=' . urlencode($tituloVisible));
exit;
