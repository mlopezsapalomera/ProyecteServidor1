<?php
// controller/logout.controller.php
// Controlador para cerrar sesión

require_once __DIR__ . '/../security/auth.php';
require_once __DIR__ . '/../model/user.php';

// Eliminar token de "Remember-me" si existe
if (isset($_COOKIE['remember_token'])) {
    eliminarRememberToken($_COOKIE['remember_token']);
    limpiarCookieRememberToken();
}
// Cerrar sesión del usuario
cerrarSesion();

// Redirigir a la página principal
header('Location: ../index.php?ok=' . urlencode('Sesión cerrada.'));
exit;
