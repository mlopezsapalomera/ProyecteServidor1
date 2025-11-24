<?php
// controller/modificar.controller.php
require_once __DIR__ . '/../model/pokemon.php';
require_once __DIR__ . '/../security/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/index.php');
    exit;
}

if (!estaIdentificado()) {
    header('Location: ../view/login.vista.php?error=' . urlencode('Debes iniciar sesión para editar.'));
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$titol = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
$descripcio = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : null;

if ($id <= 0 || $titol === '') {
    header('Location: ../view/modificar.vista.php?id=' . $id . '&error=' . urlencode('Dades invàlides.'));
    exit;
}

$pokemon = obtenerPokemonPorId($id);
if (!$pokemon) {
    header('Location: ../view/index.php?error=' . urlencode('Registre no trobat.'));
    exit;
}

// Sólo el propietario puede editar
if ((int)$pokemon['user_id'] !== idUsuarioActual()) {
    header('Location: ../view/index.php?error=' . urlencode('No tienes permiso para editar este Pokémon.'));
    exit;
}

$ok = actualizarPokemon($id, $titol, $descripcio);

if ($ok) {
    header('Location: ../view/index.php?ok=' . urlencode('Pokémon modificat correctament'));
    exit;
}
header('Location: ../view/modificar.vista.php?id=' . $id . '&error=' . urlencode('No se ha podido modificar. Inténtalo de nuevo.'));
exit;
