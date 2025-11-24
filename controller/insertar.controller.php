<?php
// controller/insertar.controller.php
require_once __DIR__ . '/../model/pokemon.php';
require_once __DIR__ . '/../security/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/insertar.vista.php');
    exit;
}

if (!estaIdentificado()) {
    header('Location: ../view/login.vista.php?error=' . urlencode('Debes iniciar sesión para insertar.'));
    exit;
}

// Recollir i validar camps
$titol = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
$descripcio = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : null;

if ($titol === '') {
    header('Location: ../view/insertar.vista.php?error=' . urlencode('El títol és obligatori'));
    exit;
}

$idUsuario = idUsuarioActual();
$ok = insertarPokemon($titol, $descripcio, $idUsuario);

if ($ok) {
    header('Location: ../view/index.php?ok=' . urlencode('Pokémon insertado correctamente'));
    exit;
}
header('Location: ../view/insertar.vista.php?error=' . urlencode('No se ha podido insertar. Inténtalo de nuevo.'));
exit;
