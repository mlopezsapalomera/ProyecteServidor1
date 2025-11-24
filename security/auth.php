<?php
// Tiempo de inactividad en segundos (40 minutos)
define('AUTH_TIEMPO_INACTIVIDAD', 2400);

// Ajustar parámetros de cookie de sesión antes de iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => AUTH_TIEMPO_INACTIVIDAD,
        'path' => '/',
        'domain' => '',
        'secure' => false, // si usas HTTPS, poner true
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

function mantenerSesion() {
    if (!isset($_SESSION['last_activity'])) return;
    if (time() - $_SESSION['last_activity'] > AUTH_TIEMPO_INACTIVIDAD) {
        // Expirada
        session_unset();
        session_destroy();
        return;
    }
    $_SESSION['last_activity'] = time();
}

// Llamar para mantener sesión o expirar
mantenerSesion();

function iniciarSesion(array $usuario) {
    // $usuario debe contener al menos id y username
    $_SESSION['usuario'] = [
        'id' => $usuario['id'],
        'username' => $usuario['username']
    ];
    $_SESSION['last_activity'] = time();
}

function cerrarSesion() {
    session_unset();
    session_destroy();
}

function estaIdentificado() {
    return isset($_SESSION['usuario']) && isset($_SESSION['usuario']['id']);
}

function usuarioActual() {
    return estaIdentificado() ? $_SESSION['usuario'] : null;
}

function idUsuarioActual() {
    return estaIdentificado() ? (int)$_SESSION['usuario']['id'] : null;
}
