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
$recordarme = isset($_POST['remember_me']) ? true : false;

// Validar campos obligatorios
if ($campUsuari === '' || $contrasenya === '') {
    header('Location: ../view/login.vista.php?error=' . urlencode('Usuario y contraseña son obligatorios.'));
    exit;
}

// Verificar credenciales
$usuari = verificarCredencialesUsuario($campUsuari, $contrasenya);
if ($usuari) {
    iniciarSesion($usuari);
    
    // Si el usuario marcó "Recordarme", crear token y cookie
    if ($recordarme) {
        $token = crearRememberToken($usuari['id'], 30); // Token válido por 30 días
        if ($token) {
            // Guardar token en cookie (30 días = 2592000 segundos)
            setcookie(
                'remember_token',
                $token,
                time() + (30 * 24 * 60 * 60),
                '/',
                '',
                false, // No requerir HTTPS (cambiar a true en producción)
                true   // HttpOnly: no accesible desde JavaScript
            );
        }
    }
    
    header('Location: ../view/index.php?ok=' . urlencode('Has iniciado sesión.'));
    exit;
}

// Credenciales incorrectas
header('Location: ../view/login.vista.php?error=' . urlencode('Usuario o contraseña incorrectos.') . '&usuari=' . urlencode($campUsuari));
exit;
