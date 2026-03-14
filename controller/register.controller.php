<?php

require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../security/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/register.vista.php');
    exit;
}

$usuari = isset($_POST['username']) ? trim($_POST['username']) : '';
$correu = isset($_POST['email']) ? trim($_POST['email']) : '';
$contrasenya = isset($_POST['password']) ? $_POST['password'] : '';
$contrasenya2 = isset($_POST['password2']) ? $_POST['password2'] : '';

$errores = [];

if ($usuari === '') {
    $errores[] = 'El nombre de usuario es obligatorio.';
}
if ($correu === '' || !filter_var($correu, FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'Correo electrónico inválido.';
}
if ($contrasenya === '' || $contrasenya2 === '') {
    $errores[] = 'Introduce la contraseña dos veces.';
}
if ($contrasenya !== $contrasenya2) {
    $errores[] = 'Las contraseñas no coinciden.';
}

// comprobar contraseña
$longitudOk = strlen($contrasenya) >= 8;
$tieneMayuscula = preg_match('/[A-Z]/', $contrasenya);
$tieneMinuscula = preg_match('/[a-z]/', $contrasenya);
$tieneNumero = preg_match('/[0-9]/', $contrasenya);

if (!$longitudOk || !$tieneMayuscula || !$tieneMinuscula || !$tieneNumero) {
    $errores[] = 'La contraseña debe tener al menos 8 caracteres, incluir mayúscula, minúscula y número.';
}

// ver si existe
$usuarioExistente = obtenerUsuarioPorNombre($usuari);
if ($usuarioExistente) {
    $errores[] = 'El usuario ya existe.';
}
$correoExistente = obtenerUsuarioPorEmail($correu);
if ($correoExistente) {
    $errores[] = 'El correo ya está registrado.';
}

if (!empty($errores)) {
    $mensajeError = implode(' ', $errores);
    $qs = http_build_query(['error' => $mensajeError, 'usuari' => $usuari, 'correu' => $correu]);
    header('Location: ../view/register.vista.php?' . $qs);
    exit;
}

// crear usuario
$hash = password_hash($contrasenya, PASSWORD_DEFAULT);
$nuevoId = crearUsuario($usuari, $correu, $hash);

if ($nuevoId) {
    $usuarioRegistrado = obtenerUsuarioPorId($nuevoId);
    iniciarSesion($usuarioRegistrado);
    $mensaje = 'Bienvenido ' . $usuari . '!';
    header('Location: ../view/index.php?ok=' . urlencode($mensaje));
    exit;
}

header('Location: ../view/register.vista.php?error=' . urlencode('No se ha podido crear la cuenta.'));
exit;
