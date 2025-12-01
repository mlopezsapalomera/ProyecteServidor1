<?php
// controller/logout.controller.php

// Primero, incluimos el archivo de autenticación para poder cerrar la sesión del usuario.
require_once __DIR__ . '/../security/auth.php';

// Llamamos a la función tancarSessio para cerrar la sesión del usuario actual.
cerrarSesion();

// Después de cerrar la sesión, redirigimos al usuario a la página principal con un mensaje de confirmación.
header('Location: ../view/index.php?ok=' . urlencode('Sessió tancada.'));
exit;
