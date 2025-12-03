<?php
// controller/register.controller.php
// Controlador para el proceso de registro de usuarios

require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../security/auth.php';

// Validar método de la petición
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/register.vista.php');
    exit;
}

// Recoger datos del formulario
$usuari = isset($_POST['username']) ? trim($_POST['username']) : '';
$correu = isset($_POST['email']) ? trim($_POST['email']) : '';
$contrasenya = isset($_POST['password']) ? $_POST['password'] : '';
$contrasenya2 = isset($_POST['password2']) ? $_POST['password2'] : '';

// Validar campos del formulario
$errors = [];
if ($usuari === '') $errors[] = 'El nombre de usuario es obligatorio.';
if ($correu === '' || !filter_var($correu, FILTER_VALIDATE_EMAIL)) $errors[] = 'Correo electrónico inválido.';
if ($contrasenya === '' || $contrasenya2 === '') $errors[] = 'Introduce la contraseña dos veces.';
if ($contrasenya !== $contrasenya2) $errors[] = 'Las contraseñas no coinciden.';

// Validar fortaleza de la contraseña
if (strlen($contrasenya) < 8 ||
    !preg_match('/[A-Z]/', $contrasenya) ||
    !preg_match('/[a-z]/', $contrasenya) ||
    !preg_match('/[0-9]/', $contrasenya)) {
    $errors[] = 'La contraseña debe tener al menos 8 caracteres, incluir mayúscula, minúscula y número.';
}

// Verificar que el usuario o email no existan
if (obtenerUsuarioPorNombre($usuari)) $errors[] = 'El usuario ya existe.';
if (obtenerUsuarioPorEmail($correu)) $errors[] = 'El correo ya está registrado.';

// Mostrar errores si existen
if (!empty($errors)) {
    $qs = http_build_query(['error' => implode(' ', $errors), 'usuari' => $usuari, 'correu' => $correu]);
    header('Location: ../view/register.vista.php?' . $qs);
    exit;
}

// Crear el usuario
$hash = password_hash($contrasenya, PASSWORD_DEFAULT);
$newId = crearUsuario($usuari, $correu, $hash);
if ($newId) {
    $usuariReg = obtenerUsuarioPorId($newId);
    iniciarSesion($usuariReg);
    header('Location: ../view/index.php?ok=' . urlencode('Registro completado. Bienvenido ' . $usuari . '!'));
    exit;
}

// Error al crear usuario
header('Location: ../view/register.vista.php?error=' . urlencode('No se ha podido crear la cuenta.'));
exit;
