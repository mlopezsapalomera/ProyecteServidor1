<?php
// controller/insertar.controller.php
// Controlador para insertar nuevos pokémons

require_once __DIR__ . '/../model/pokemon.php';
require_once __DIR__ . '/../security/auth.php';

// Validar método de la petición
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/insertar.vista.php');
    exit;
}

// Verificar autenticación
if (!estaIdentificado()) {
    header('Location: ../view/login.vista.php?error=' . urlencode('Debes iniciar sesión para insertar.'));
    exit;
}

// Recoger y validar datos del formulario
$titol = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
$descripcio = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : null;

if ($titol === '') {
    header('Location: ../view/insertar.vista.php?error=' . urlencode('El título es obligatorio'));
    exit;
}

// Insertar el pokémon
$idUsuario = idUsuarioActual();
$ok = insertarPokemon($titol, $descripcio, $idUsuario);

if ($ok) {
    header('Location: ../view/index.php?ok=' . urlencode('Pokémon insertado correctamente'));
    exit;
}
header('Location: ../view/insertar.vista.php?error=' . urlencode('No se ha podido insertar. Inténtalo de nuevo.'));
exit;
