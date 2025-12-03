<?php
// controller/modificar.controller.php
// Controlador para modificar pokémons existentes

require_once __DIR__ . '/../model/pokemon.php';
require_once __DIR__ . '/../security/auth.php';

// Validar método de la petición
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/index.php');
    exit;
}

// Verificar autenticación
if (!estaIdentificado()) {
    header('Location: ../view/login.vista.php?error=' . urlencode('Debes iniciar sesión para editar.'));
    exit;
}

// Recoger y validar datos del formulario
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$titol = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
$descripcio = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : null;

if ($id <= 0 || $titol === '') {
    header('Location: ../view/modificar.vista.php?id=' . $id . '&error=' . urlencode('Datos inválidos.'));
    exit;
}

// Verificar que el pokémon existe
$pokemon = obtenerPokemonPorId($id);
if (!$pokemon) {
    header('Location: ../view/index.php?error=' . urlencode('Registro no encontrado.'));
    exit;
}

// Verificar permisos del usuario
if ((int)$pokemon['user_id'] !== idUsuarioActual()) {
    header('Location: ../view/index.php?error=' . urlencode('No tienes permiso para editar este Pokémon.'));
    exit;
}

// Actualizar el pokémon
$ok = actualizarPokemon($id, $titol, $descripcio);

if ($ok) {
    header('Location: ../view/index.php?ok=' . urlencode('Pokémon modificado correctamente'));
    exit;
}
header('Location: ../view/modificar.vista.php?id=' . $id . '&error=' . urlencode('No se ha podido modificar. Inténtalo de nuevo.'));
exit;
