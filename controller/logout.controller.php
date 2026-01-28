<?php
// controller/logout.controller.php
// Controlador para cerrar sesión

require_once __DIR__ . '/../security/auth.php';
require_once __DIR__ . '/../model/user.php';

// Eliminar token de "Remember-me" si existe
if (isset($_COOKIE['remember_token'])) {
    eliminarRememeberToken($_COOKIE['remember_token']);
    setcookie('remember_token', '', time() - 3600, '/'); // Eliminar cookie
}
// Cerrar sesión del usuario
cerrarSesion();

// Redirigir a la página principal
header('Location: ../view/index.php?ok=' . urlencode('Sesión cerrada.'));
exit;
