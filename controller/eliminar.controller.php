<?php
// controller/eliminar.controller.php
require_once __DIR__ . '/../model/pokemon.php';
require_once __DIR__ . '/../security/auth.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../view/index.php?error=' . urlencode('ID invàlid.'));
    exit;
}

if (!estaIdentificado()) {
    header('Location: ../view/login.vista.php?error=' . urlencode('Debes iniciar sesión para eliminar.'));
    exit;
}

$id = (int)$_GET['id'];
$pokemon = getPokemonById($id);
if (!$pokemon) {
    header('Location: ../view/index.php?error=' . urlencode('Registre no trobat.'));
    exit;
}

if ((int)$pokemon['user_id'] !== idUsuarioActual()) {
    header('Location: ../view/index.php?error=' . urlencode('No tienes permiso para eliminar este Pokémon.'));
    exit;
}

$ok = deletePokemon($id);

if ($ok) {
    header('Location: ../view/index.php?ok=' . urlencode('Pokémon eliminado correctamente'));
    exit;
}

header('Location: ../view/index.php?error=' . urlencode('No se ha podido eliminar.'));
exit;
