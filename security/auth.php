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
    if (!isset($_SESSION['ultima_actividad'])) return;
    if (time() - $_SESSION['ultima_actividad'] > AUTH_TIEMPO_INACTIVIDAD) {
        session_unset();
        session_destroy();
        return;
    }
    $_SESSION['ultima_actividad'] = time();
}

// Intentar login automático con cookie "Remember Me"
function intentarLoginAutomatico() {
    // Si ya hay sesión activa, no hacer nada
    if (estaIdentificado()) {
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
        return true;
    } else {
        // Token inválido o expirado: eliminar cookie
        setcookie('remember_token', '', time() - 3600, '/');
        return false;
    }
}


mantenerSesion();
intentarLoginAutomatico();


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
