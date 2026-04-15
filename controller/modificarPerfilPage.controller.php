<?php
// controller/modificarPerfilPage.controller.php
// Renderiza la vista de edición de perfil

require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../security/auth.php';

if (!estaIdentificado()) {
    header('Location: ../view/login.vista.php');
    exit;
}

$usuario = obtenerUsuarioPorId(idUsuarioActual());
if (!$usuario) {
    header('Location: ../index.php?error=' . urlencode('Usuario no encontrado'));
    exit;
}

$ok = isset($_GET['ok']) ? $_GET['ok'] : null;
$error = isset($_GET['error']) ? $_GET['error'] : null;

require_once __DIR__ . '/../view/modificarPerfil.vista.php';
