<?php

require_once __DIR__ . '/../model/pokemon.php';
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

$titol = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
$descripcio = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : null;

if ($titol === '') {
    header('Location: ../controller/insertarPage.controller.php?error=' . urlencode('El titulo es obligatorio'));
    exit;
}

$idUsuario = idUsuarioActual();
$ok = insertarPokemon($titol, $descripcio, $idUsuario);

if ($ok) {
    header('Location: ../index.php?ok=' . urlencode('Pokemon insertado'));
    exit;
}
header('Location: ../controller/insertarPage.controller.php?error=' . urlencode('No se ha podido insertar'));
exit;
