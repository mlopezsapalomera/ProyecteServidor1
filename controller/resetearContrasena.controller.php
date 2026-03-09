<?php

session_start();
require_once __DIR__ . '/../env.php';
require_once __DIR__ . '/../model/user.php';

if (!isset($_POST['token']) || !isset($_POST['password']) || !isset($_POST['password_confirm'])) {
    header('Location: ../view/login.vista.php?error=' . urlencode('Faltan datos'));
    exit();
}

$token = trim($_POST['token']);
$password = $_POST['password'];
$passwordConfirm = $_POST['password_confirm'];

if (empty($token)) {
    header('Location: ../view/login.vista.php?error=' . urlencode('Token no valido'));
    exit();
}

if (strlen($password) < 6) {
    header('Location: ../view/resetearContrasena.vista.php?token=' . urlencode($token) . '&error=' . urlencode('La contraseña debe tener minimo 6 caracteres'));
    exit();
}

if ($password !== $passwordConfirm) {
    header('Location: ../view/resetearContrasena.vista.php?token=' . urlencode($token) . '&error=' . urlencode('Las contraseñas no coinciden'));
    exit();
}

$resultado = resetearContrasenaConToken($token, $password);

if ($resultado) {
    header('Location: ../view/login.vista.php?success=' . urlencode('Contraseña actualizada correctamente'));
    exit();
} else {
    header('Location: ../view/recuperarContrasena.vista.php?error=' . urlencode('El enlace ha expirado o no es valido'));
    exit();
}
