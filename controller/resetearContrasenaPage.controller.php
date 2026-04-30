<?php
// controller/resetearContrasenaPage.controller.php
// Renderiza la vista de reseteo de contraseña

require_once __DIR__ . '/../security/auth.php';
require_once __DIR__ . '/../model/user/db_connection.php';
require_once __DIR__ . '/../model/user/account.model.php';
require_once __DIR__ . '/../model/user/recovery.model.php';

$token = isset($_GET['token']) ? $_GET['token'] : null;
$error = isset($_GET['error']) ? $_GET['error'] : null;

if (!$token) {
    header('Location: ../view/login.vista.php?error=' . urlencode('Token de recuperación no válido'));
    exit;
}

$usuario = verificarTokenRecuperacion($token);
if (!$usuario) {
    header('Location: ../view/recuperarContrasena.vista.php?error=' . urlencode('El enlace de recuperación ha expirado o no es válido. Solicita uno nuevo'));
    exit;
}

require_once __DIR__ . '/../view/resetearContrasena.vista.php';
