<?php
// controller/register.controller.php

// Primero, incluimos los archivos necesarios para acceder a las funciones de usuario y autenticación.
require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../security/auth.php';

// Comprobamos que la petición se haya realizado mediante el método POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Si no es una petición POST, redirigimos al formulario de registro.
    header('Location: ../view/register.vista.php');
    exit;
}

// Recogemos los datos enviados por el formulario: usuario, correo y contraseñas.
$usuari = isset($_POST['username']) ? trim($_POST['username']) : '';
$correu = isset($_POST['email']) ? trim($_POST['email']) : '';
$contrasenya = isset($_POST['password']) ? $_POST['password'] : '';
$contrasenya2 = isset($_POST['password2']) ? $_POST['password2'] : '';

// Inicializamos un array para almacenar los posibles errores de validación.
$errors = [];
// Validamos que el nombre de usuario no esté vacío.
if ($usuari === '') $errors[] = 'El nombre de usuario es obligatorio.';
// Validamos que el correo no esté vacío y tenga formato válido.
if ($correu === '' || !filter_var($correu, FILTER_VALIDATE_EMAIL)) $errors[] = 'Correo electrónico inválido.';
// Validamos que ambas contraseñas estén presentes.
if ($contrasenya === '' || $contrasenya2 === '') $errors[] = 'Introduce la contraseña dos veces.';
// Validamos que ambas contraseñas coincidan.
if ($contrasenya !== $contrasenya2) $errors[] = 'Las contraseñas no coinciden.';

// Comprobamos la fortaleza mínima de la contraseña: al menos 8 caracteres, mayúscula, minúscula y número.
if (strlen($contrasenya) < 8 ||
    !preg_match('/[A-Z]/', $contrasenya) ||
    !preg_match('/[a-z]/', $contrasenya) ||
    !preg_match('/[0-9]/', $contrasenya)) {
    $errors[] = 'La contraseña debe tener al menos 8 caracteres, incluir mayúscula, minúscula y número.';
}

// Comprobamos si el usuario o el correo ya existen en la base de datos.
if (obtenerUsuarioPorNombre($usuari)) $errors[] = 'El usuario ya existe.';
if (obtenerUsuarioPorEmail($correu)) $errors[] = 'El correo ya está registrado.';

// Si hay errores, los mostramos en el formulario de registro y mantenemos los datos introducidos.
if (!empty($errors)) {
    $qs = http_build_query(['error' => implode(' ', $errors), 'usuari' => $usuari, 'correu' => $correu]);
    header('Location: ../view/register.vista.php?' . $qs);
    exit;
}

// Si todo es correcto, generamos el hash de la contraseña y creamos el usuario en la base de datos.
$hash = password_hash($contrasenya, PASSWORD_DEFAULT);
$newId = crearUsuario($usuari, $correu, $hash);
if ($newId) {
    // Si el usuario se crea correctamente, lo obtenemos y lo iniciamos sesión automáticamente.
    $usuariReg = obtenerUsuarioPorId($newId);
    iniciarSesion($usuariReg);
    header('Location: ../view/index.php?ok=' . urlencode('Registro completado. Bienvenido ' . $usuari . '!'));
    exit;
}

// Si ocurre algún error al crear el usuario, redirigimos al formulario con un mensaje de error.
header('Location: ../view/register.vista.php?error=' . urlencode('No se ha podido crear la cuenta.'));
exit;
