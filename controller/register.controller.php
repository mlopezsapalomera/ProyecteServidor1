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

$errors = [];
if ($usuari === '') $errors[] = 'El nombre de usuario es obligatorio.';
if ($correu === '' || !filter_var($correu, FILTER_VALIDATE_EMAIL)) $errors[] = 'Correo electrónico inválido.';
if ($contrasenya === '' || $contrasenya2 === '') $errors[] = 'Introduce la contraseña dos veces.';
if ($contrasenya !== $contrasenya2) $errors[] = 'Las contraseñas no coinciden.';

// Fortaleza mínima: mínimo 8 caracteres, mayúscula, minúscula, número
if (strlen($contrasenya) < 8 ||
    !preg_match('/[A-Z]/', $contrasenya) ||
    !preg_match('/[a-z]/', $contrasenya) ||
    !preg_match('/[0-9]/', $contrasenya)) {
    $errors[] = 'La contraseña debe tener al menos 8 caracteres, incluir mayúscula, minúscula y número.';
}

// Comprobar existencia
if (obtenerUsuarioPorNombre($usuari)) $errors[] = 'El usuario ya existe.';
if (obtenerUsuarioPorEmail($correu)) $errors[] = 'El correo ya está registrado.';

if (!empty($errors)) {
    $qs = http_build_query(['error' => implode(' ', $errors), 'usuari' => $usuari, 'correu' => $correu]);
    header('Location: ../view/register.vista.php?' . $qs);
    exit;
}

$hash = password_hash($contrasenya, PASSWORD_DEFAULT);
$newId = crearUsuario($usuari, $correu, $hash);
if ($newId) {
    $usuariReg = obtenerUsuarioPorId($newId);
    iniciarSesion($usuariReg);
    header('Location: ../view/index.php?ok=' . urlencode('Registro completado. Bienvenido ' . $usuari . '!'));
    exit;
}

header('Location: ../view/register.vista.php?error=' . urlencode('No se ha podido crear la cuenta.'));
exit;
