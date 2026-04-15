<?php
// controller/insertarPage.controller.php
// Renderiza la vista de inserción de Pokémon

require_once __DIR__ . '/../security/auth.php';

if (!estaIdentificado()) {
    header('Location: ../view/login.vista.php?error=' . urlencode('Debes iniciar sesión para acceder al formulario.'));
    exit;
}

$usuario = usuarioActual();
$error = isset($_GET['error']) ? $_GET['error'] : null;

require_once __DIR__ . '/../view/insertar.vista.php';
