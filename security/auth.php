<?php
// security/auth.php
// Temps d'inactivitat en segons (40 minuts)
define('AUTH_TEMPS_INACTIVITAT', 2400);

// Ajustar parámetros de cookie de sesión antes de iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    // Lifetime en segundos, httponly y samesite para mayor seguridad
    session_set_cookie_params([
        'lifetime' => AUTH_TEMPS_INACTIVITAT,
        'path' => '/',
        'domain' => '',
        'secure' => false, // si usas HTTPS, poner true
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

function mantenirSessio() {
    if (!isset($_SESSION['last_activity'])) return;
    if (time() - $_SESSION['last_activity'] > AUTH_TEMPS_INACTIVITAT) {
        // Expirada
        session_unset();
        session_destroy();
        return;
    }
    $_SESSION['last_activity'] = time();
}

// Cridar per mantenir sessió o expirar
mantenirSessio();

function iniciarSessio(array $usuari) {
    // $usuari ha de contenir com a mínim id i username
    $_SESSION['usuari'] = [
        'id' => $usuari['id'],
        'username' => $usuari['username']
    ];
    $_SESSION['last_activity'] = time();
}

function tancarSessio() {
    session_unset();
    session_destroy();
}

function estaIdentificat() {
    return isset($_SESSION['usuari']) && isset($_SESSION['usuari']['id']);
}

function usuariActual() {
    return estaIdentificat() ? $_SESSION['usuari'] : null;
}

function idUsuariActual() {
    return estaIdentificat() ? (int)$_SESSION['usuari']['id'] : null;
}
