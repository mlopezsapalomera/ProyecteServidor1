<?php
// controller/resetearContrasena.controller.php
// Procesa el cambio de contraseña con el token de recuperación

session_start();
require_once __DIR__ . '/../env.php';
require_once __DIR__ . '/../model/user.php';

// Validar que se recibieron los datos necesarios
if (!isset($_POST['token']) || !isset($_POST['password']) || !isset($_POST['password_confirm'])) {
    header('Location: ../view/login.vista.php?error=' . urlencode('Datos incompletos'));
    exit();
}

$token = trim($_POST['token']);
$password = $_POST['password'];
$passwordConfirm = $_POST['password_confirm'];

// Validar que el token no esté vacío
if (empty($token)) {
    header('Location: ../view/login.vista.php?error=' . urlencode('Token no válido'));
    exit();
}

// Validar longitud mínima de contraseña
if (strlen($password) < 6) {
    header('Location: ../view/resetearContrasena.vista.php?token=' . urlencode($token) . '&error=' . urlencode('La contraseña debe tener al menos 6 caracteres'));
    exit();
}

// Validar que las contraseñas coincidan
if ($password !== $passwordConfirm) {
    header('Location: ../view/resetearContrasena.vista.php?token=' . urlencode($token) . '&error=' . urlencode('Las contraseñas no coinciden'));
    exit();
}

// Intentar resetear la contraseña
$resultado = resetearContrasenaConToken($token, $password);

if ($resultado) {
    // Éxito: redirigir al login con mensaje de éxito
    header('Location: ../view/login.vista.php?success=' . urlencode('Tu contraseña ha sido actualizada correctamente. Ya puedes iniciar sesión'));
    exit();
} else {
    // Error: el token ha expirado o no es válido
    header('Location: ../view/recuperarContrasena.vista.php?error=' . urlencode('El enlace de recuperación ha expirado o no es válido. Solicita uno nuevo'));
    exit();
}
