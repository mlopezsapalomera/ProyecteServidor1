<?php
// controller/login.controller.php

// Primero, incluimos los archivos necesarios para acceder a las funciones de usuario y autenticación.
require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../security/auth.php';

// Comprobamos que la petición se haya realizado mediante el método POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Si no es una petición POST, redirigimos al formulario de login.
    header('Location: ../view/login.vista.php');
    exit;
}

// Recogemos los datos enviados por el formulario: usuario y contraseña.
$campUsuari = isset($_POST['user']) ? trim($_POST['user']) : '';
$contrasenya = isset($_POST['password']) ? $_POST['password'] : '';

// Comprobamos que ambos campos no estén vacíos.
if ($campUsuari === '' || $contrasenya === '') {
    // Si falta alguno, redirigimos al formulario con un mensaje de error.
    header('Location: ../view/login.vista.php?error=' . urlencode('Usuario y contraseña son obligatorios.'));
    exit;
}

// Si los datos están completos, intentamos verificar las credenciales del usuario.
$usuari = verificarCredencialesUsuario($campUsuari, $contrasenya);
if ($usuari) {
    // Si las credenciales son correctas, iniciamos sesión y redirigimos a la página principal con un mensaje de éxito.
    iniciarSesion($usuari);
    header('Location: ../view/index.php?ok=' . urlencode('Has iniciado sesión.'));
    exit;
}

// Si las credenciales no son válidas, redirigimos al formulario con un mensaje de error y mantenemos el usuario introducido.
header('Location: ../view/login.vista.php?error=' . urlencode('Usuario o contraseña incorrectos.') . '&usuari=' . urlencode($campUsuari));
exit;
