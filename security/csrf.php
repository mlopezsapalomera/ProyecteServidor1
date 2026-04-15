<?php
// security/csrf.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('CSRF_TOKEN_FIELD')) {
    define('CSRF_TOKEN_FIELD', '_csrf_token');
}

function csrfToken() {
    if (empty($_SESSION[CSRF_TOKEN_FIELD])) {
        $_SESSION[CSRF_TOKEN_FIELD] = bin2hex(random_bytes(32));
    }

    return $_SESSION[CSRF_TOKEN_FIELD];
}

function csrfInput() {
    $token = csrfToken();
    return '<input type="hidden" name="' . CSRF_TOKEN_FIELD . '" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

function csrfValidoPost() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return false;
    }

    if (!isset($_POST[CSRF_TOKEN_FIELD]) || !isset($_SESSION[CSRF_TOKEN_FIELD])) {
        return false;
    }

    return hash_equals($_SESSION[CSRF_TOKEN_FIELD], (string)$_POST[CSRF_TOKEN_FIELD]);
}

function csrfRequireOrRedirect($redirectUrl, $mensaje = 'Solicitud inválida (CSRF).') {
    if (csrfValidoPost()) {
        return;
    }

    $separator = (strpos($redirectUrl, '?') === false) ? '?' : '&';
    header('Location: ' . $redirectUrl . $separator . 'error=' . urlencode($mensaje));
    exit;
}
