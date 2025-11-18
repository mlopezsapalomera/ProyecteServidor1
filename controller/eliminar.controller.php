<?php
// controller/eliminar.controller.php
require_once __DIR__ . '/../model/pokemon.php';
require_once __DIR__ . '/../security/auth.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../view/index.php?error=' . urlencode('ID invàlid.'));
    exit;
}

if (!estaIdentificat()) {
    header('Location: ../view/login.vista.php?error=' . urlencode('Cal iniciar sessió per eliminar.'));
    exit;
}

$id = (int)$_GET['id'];
$pokemon = getPokemonById($id);
if (!$pokemon) {
    header('Location: ../view/index.php?error=' . urlencode('Registre no trobat.'));
    exit;
}

if ((int)$pokemon['user_id'] !== idUsuariActual()) {
    header('Location: ../view/index.php?error=' . urlencode('No tens permís per eliminar aquest Pokémon.'));
    exit;
}

$ok = deletePokemon($id);

if ($ok) {
    header('Location: ../view/index.php?ok=' . urlencode('Pokémon eliminat correctament'));
    exit;
}

header('Location: ../view/index.php?error=' . urlencode('No s\'ha pogut eliminar.'));
exit;
