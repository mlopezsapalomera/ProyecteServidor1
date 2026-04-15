<?php
// controller/modificarPage.controller.php
// Renderiza la vista de edición de Pokémon

require_once __DIR__ . '/../model/pokemon.php';
require_once __DIR__ . '/../security/auth.php';

if (!estaIdentificado()) {
    header('Location: ../view/login.vista.php?error=' . urlencode('Debes iniciar sesión para editar.'));
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = isset($_GET['error']) ? $_GET['error'] : null;

if ($id <= 0) {
    header('Location: ../index.php?error=' . urlencode('ID inválido.'));
    exit;
}

$pokemon = obtenerPokemonPorId($id);
if (!$pokemon) {
    header('Location: ../index.php?error=' . urlencode('Registro no encontrado.'));
    exit;
}

if ((int)$pokemon['user_id'] !== idUsuarioActual()) {
    header('Location: ../index.php?error=' . urlencode('No tienes permiso para editar este Pokémon.'));
    exit;
}

require_once __DIR__ . '/../view/modificar.vista.php';
