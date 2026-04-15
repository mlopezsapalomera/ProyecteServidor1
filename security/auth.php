<?php
// security/auth.php

require_once __DIR__ . '/../env.php';

// Tiempo de inactividad (40 minutos)
define('AUTH_TIEMPO_INACTIVIDAD', 2400);

// Configuración de sesión
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => AUTH_TIEMPO_INACTIVIDAD,
        'path' => '/',
        'domain' => '',
        'secure' => authEsConexionSegura(),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

require_once __DIR__ . '/csrf.php';

// Verificar y mantener la sesión activa
function mantenerSesion() {
    if (!isset($_SESSION['ultima_actividad'])) return;
    if (time() - $_SESSION['ultima_actividad'] > AUTH_TIEMPO_INACTIVIDAD) {
        session_unset();
        session_destroy();
        return;
    }
    $_SESSION['ultima_actividad'] = time();
}

function authSesionActiva() {
    return isset($_SESSION['usuario']) && isset($_SESSION['usuario']['id']);
}

function authBootstrap() {
    static $bootstrapped = false;

    if ($bootstrapped) {
        return;
    }

    $bootstrapped = true;
    mantenerSesion();

    if (!authSesionActiva() && isset($_COOKIE['remember_token'])) {
        intentarLoginAutomatico();
    }
}

// Intentar login automático con cookie "Remember Me"
function intentarLoginAutomatico() {
    // Si ya hay sesión activa, no hacer nada
    if (authSesionActiva()) {
        return true;
    }
    
    // Verificar si existe cookie de "Recordarme"
    if (!isset($_COOKIE['remember_token'])) {
        return false;
    }
    
    // Cargar funciones de usuario
    require_once __DIR__ . '/../model/user.php';
    
    // Verificar el token
    $usuario = verificarRememberToken($_COOKIE['remember_token']);
    
    if ($usuario) {
        // Token válido: iniciar sesión automáticamente
        iniciarSesion($usuario);
        establecerCookieRememberToken($_COOKIE['remember_token'], rememberMeDias());
        return true;
    } else {
        // Token inválido o expirado: eliminar cookie
        limpiarCookieRememberToken();
        return false;
    }
}


// Iniciar sesión
function iniciarSesion(array $usuario) {
    $_SESSION['usuario'] = [
        'id' => $usuario['id'],
        'username' => $usuario['username'],
        'role' => $usuario['role'] ?? 'user'
    ];
    $_SESSION['ultima_actividad'] = time();
}

// Cerrar sesión
function cerrarSesion() {
    session_unset();
    session_destroy();
}

// Verificar si el usuario está identificado
function estaIdentificado() {
    authBootstrap();
    return authSesionActiva();
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

function rememberMeDias() {
    return defined('REMEMBER_ME_DAYS') ? (int)REMEMBER_ME_DAYS : 30;
}

function authEsConexionSegura() {
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }

    if (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443) {
        return true;
    }

    if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        return true;
    }

    return false;
}

function establecerCookieRememberToken($token, $dias = 30) {
    $expira = time() + ((int)$dias * 24 * 60 * 60);

    setcookie('remember_token', $token, [
        'expires' => $expira,
        'path' => '/',
        'domain' => '',
        'secure' => authEsConexionSegura(),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

function limpiarCookieRememberToken() {
    setcookie('remember_token', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'domain' => '',
        'secure' => authEsConexionSegura(),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}
