<?php
// controller/adminPanel.controller.php
// Controlador para panel de administración

require_once __DIR__ . '/../security/auth.php';
require_once __DIR__ . '/../model/user.php';

if (!estaIdentificado() || !esAdmin()) {
    header('Location: ../index.php?error=' . urlencode('Acceso denegado. Solo administradores.'));
    exit;
}

$usuarios = obtenerTodosLosUsuariosConPublicaciones(true);
$ok = isset($_GET['ok']) ? $_GET['ok'] : null;
$error = isset($_GET['error']) ? $_GET['error'] : null;

require_once __DIR__ . '/../view/adminPanel.vista.php';
