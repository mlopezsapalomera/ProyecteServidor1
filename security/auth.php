<?php
// security/auth.php

// Tiempo de inactividad (40 minutos)
define('AUTH_TIEMPO_INACTIVIDAD', 2400);

// Configuración de sesión
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => AUTH_TIEMPO_INACTIVIDAD,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Verificar y mantener la sesión activa
function mantenerSesion() {
    if (!isset($_SESSION['last_activity'])) return;
    if (time() - $_SESSION['last_activity'] > AUTH_TIEMPO_INACTIVIDAD) {
        session_unset();
        session_destroy();
        return;
    }
    $_SESSION['last_activity'] = time();
}

mantenerSesion();

// Iniciar sesión
function iniciarSesion(array $usuario) {
    $_SESSION['usuario'] = [
        'id' => $usuario['id'],
        'username' => $usuario['username'],
        'role' => $usuario['role'] ?? 'user'
    ];
    $_SESSION['last_activity'] = time();
}

// Cerrar sesión
function cerrarSesion() {
    session_unset();
    session_destroy();
}

// Verificar si el usuario está identificado
function estaIdentificado() {
    return isset($_SESSION['usuario']) && isset($_SESSION['usuario']['id']);
}

// Obtener datos del usuario actual
function usuarioActual() {
    return estaIdentificado() ? $_SESSION['usuario'] : null;
}

// Obtener ID del usuario actual
function idUsuarioActual() {
    return estaIdentificado() ? (int)$_SESSION['usuario']['id'] : null;
}

// Obtener rol del usuario actual
function rolUsuarioActual() {
    return estaIdentificado() ? ($_SESSION['usuario']['role'] ?? 'user') : null;
}

// Verificar si el usuario actual es admin
function esAdmin() {
    return estaIdentificado() && rolUsuarioActual() === 'admin';
}
