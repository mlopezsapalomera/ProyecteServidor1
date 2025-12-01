<?php
// security/auth.php

// Primero, definimos una constante que establece el tiempo máximo de inactividad permitido (40 minutos = 2400 segundos).
// Si un usuario no realiza ninguna acción durante este tiempo, su sesión expirará automáticamente.
define('AUTH_TIEMPO_INACTIVIDAD', 2400);

// A continuación, configuramos los parámetros de la cookie de sesión antes de iniciar la sesión.
// Comprobamos si no hay ninguna sesión activa para evitar errores al intentar iniciarla dos veces.
if (session_status() === PHP_SESSION_NONE) {
    // Configuramos los parámetros de la cookie de sesión para mayor seguridad y control.
    session_set_cookie_params([
        'lifetime' => AUTH_TIEMPO_INACTIVIDAD,  // Duración de la cookie (40 minutos)
        'path' => '/',                          // La cookie está disponible en todo el sitio
        'domain' => '',                         // Dominio por defecto
        'secure' => false,                      // Si usas HTTPS, poner true para mayor seguridad
        'httponly' => true,                     // Evita que JavaScript acceda a la cookie (protección XSS)
        'samesite' => 'Lax'                     // Protección contra CSRF (Cross-Site Request Forgery)
    ]);
    // Iniciamos la sesión PHP para poder almacenar datos del usuario.
    session_start();
}

// Función para mantener o expirar la sesión según el tiempo de inactividad.
function mantenerSesion() {
    // Si no existe la marca de tiempo de la última actividad, no hacemos nada.
    if (!isset($_SESSION['last_activity'])) return;
    // Calculamos cuánto tiempo ha pasado desde la última actividad del usuario.
    if (time() - $_SESSION['last_activity'] > AUTH_TIEMPO_INACTIVIDAD) {
        // Si ha pasado más tiempo del permitido, la sesión ha expirado.
        // Limpiamos todas las variables de sesión y destruimos la sesión.
        session_unset();
        session_destroy();
        return;
    }
    // Si la sesión sigue activa, actualizamos la marca de tiempo de la última actividad.
    $_SESSION['last_activity'] = time();
}

// Llamamos a la función mantenerSesion cada vez que se incluye este archivo para verificar y actualizar la sesión.
mantenerSesion();

// Función para iniciar sesión de un usuario.
// Recibe un array con los datos del usuario (debe contener al menos 'id' y 'username').
function iniciarSesion(array $usuario) {
    // Almacenamos los datos del usuario en la sesión para poder identificarlo en todas las páginas.
    $_SESSION['usuario'] = [
        'id' => $usuario['id'],
        'username' => $usuario['username']
    ];
    // Guardamos la marca de tiempo actual para controlar la inactividad.
    $_SESSION['last_activity'] = time();
}

// Función para cerrar la sesión del usuario.
function cerrarSesion() {
    // Limpiamos todas las variables de sesión.
    session_unset();
    // Destruimos la sesión completamente.
    session_destroy();
}

// Función para comprobar si hay un usuario identificado (logueado).
// Devuelve true si existe un usuario en la sesión con un id válido.
function estaIdentificado() {
    return isset($_SESSION['usuario']) && isset($_SESSION['usuario']['id']);
}

// Función para obtener los datos del usuario actual.
// Devuelve el array con los datos del usuario si está identificado, o null si no lo está.
function usuarioActual() {
    return estaIdentificado() ? $_SESSION['usuario'] : null;
}

// Función para obtener el id del usuario actual.
// Devuelve el id del usuario como entero si está identificado, o null si no lo está.
function idUsuarioActual() {
    return estaIdentificado() ? (int)$_SESSION['usuario']['id'] : null;
}
