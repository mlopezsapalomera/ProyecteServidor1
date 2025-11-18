<?php
// controller/insertar.controller.php
require_once __DIR__ . '/../model/pokemon.php';
require_once __DIR__ . '/../security/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/insertar.vista.php');
    exit;
}

if (!estaIdentificat()) {
    header('Location: ../view/login.vista.php?error=' . urlencode('Cal iniciar sessió per inserir.'));
    exit;
}

// Recollir i validar camps
$titol = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
$descripcio = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : null;

if ($titol === '') {
    header('Location: ../view/insertar.vista.php?error=' . urlencode('El títol és obligatori'));
    exit;
}

$idUsuari = idUsuariActual();
$ok = insertPokemon($titol, $descripcio, $idUsuari);

if ($ok) {
    header('Location: ../view/index.php?ok=' . urlencode('Pokémon inserit correctament'));
    exit;
}

header('Location: ../view/insertar.vista.php?error=' . urlencode('No s\'ha pogut inserir. Torna-ho a provar.'));
exit;
