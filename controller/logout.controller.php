<?php
// controller/logout.controller.php
// Controlador para cerrar sesión

require_once __DIR__ . '/../security/auth.php';

// Cerrar sesión del usuario
cerrarSesion();

// Redirigir a la página principal
header('Location: ../view/index.php?ok=' . urlencode('Sesión cerrada.'));
exit;
