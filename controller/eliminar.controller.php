<?php
// controller/eliminar.controller.php
// Controlador para eliminar pokémons

require_once __DIR__ . '/../model/pokemon.php';
require_once __DIR__ . '/../security/auth.php';

// Validar parámetro ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../view/index.php?error=' . urlencode('ID inválido.'));
    exit;
}

// Verificar autenticación
if (!estaIdentificado()) {
    header('Location: ../view/login.vista.php?error=' . urlencode('Debes iniciar sesión para eliminar.'));
    exit;
}

// Verificar que el pokémon existe
$id = (int)$_GET['id'];
$pokemon = obtenerPokemonPorId($id);
if (!$pokemon) {
    header('Location: ../view/index.php?error=' . urlencode('Registro no encontrado.'));
    exit;
}

// Verificar permisos del usuario
if ((int)$pokemon['user_id'] !== idUsuarioActual()) {
    header('Location: ../view/index.php?error=' . urlencode('No tienes permiso para eliminar este Pokémon.'));
    exit;
}

// Eliminar el pokémon
$ok = eliminarPokemon($id);

if ($ok) {
    header('Location: ../view/index.php?ok=' . urlencode('Pokémon eliminado correctamente'));
    exit;
}

header('Location: ../view/index.php?error=' . urlencode('No se ha podido eliminar.'));
exit;
