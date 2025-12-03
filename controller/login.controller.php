<?php
// controller/login.controller.php
// Controlador para el proceso de inicio de sesión

require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../security/auth.php';

// Validar método de la petición
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/login.vista.php');
    exit;
}

// Recoger datos del formulario
$campUsuari = isset($_POST['user']) ? trim($_POST['user']) : '';
$contrasenya = isset($_POST['password']) ? $_POST['password'] : '';

// Validar campos obligatorios
if ($campUsuari === '' || $contrasenya === '') {
    header('Location: ../view/login.vista.php?error=' . urlencode('Usuario y contraseña son obligatorios.'));
    exit;
}

// Verificar credenciales
$usuari = verificarCredencialesUsuario($campUsuari, $contrasenya);
if ($usuari) {
    iniciarSesion($usuari);
    header('Location: ../view/index.php?ok=' . urlencode('Has iniciado sesión.'));
    exit;
}

// Credenciales incorrectas
header('Location: ../view/login.vista.php?error=' . urlencode('Usuario o contraseña incorrectos.') . '&usuari=' . urlencode($campUsuari));
exit;
