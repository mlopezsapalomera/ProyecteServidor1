<?php

require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../env.php';
require_once __DIR__ . '/../security/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/login.vista.php');
    exit;
}

csrfRequireOrRedirect('../view/login.vista.php');

if (!isset($_SESSION['intentos_login'])) {
    $_SESSION['intentos_login'] = 0;
}

$campUsuari = isset($_POST['user']) ? trim($_POST['user']) : '';
$contrasenya = isset($_POST['password']) ? $_POST['password'] : '';
$recordarme = isset($_POST['remember_me']) ? true : false;

if ($campUsuari === '' || $contrasenya === '') {
    header('Location: ../view/login.vista.php?error=' . urlencode('Usuario y contraseña obligatorios'));
    exit;
}

// verificar captcha si hay muchos intentos
if ($_SESSION['intentos_login'] >= 3) {
    if (!validarRecaptcha()) {
        $_SESSION['intentos_login']++;
        header('Location: ../view/login.vista.php?error=' . urlencode('Usuario o contraseña incorrectos.') . 
               '&captcha_error=' . urlencode('Completa el reCAPTCHA.') . 
               '&usuari=' . urlencode($campUsuari));
        exit;
    }
}

$usuari = verificarCredencialesUsuario($campUsuari, $contrasenya);
if ($usuari) {
    $_SESSION['intentos_login'] = 0;
    iniciarSesion($usuari);
    
    // recordar sesion
    if ($recordarme) {
        $diasRemember = rememberMeDias();
        $token = crearRememberToken($usuari['id'], $diasRemember);
        if ($token) {
            establecerCookieRememberToken($token, $diasRemember);
        }
    }
    
    header('Location: ../index.php?ok=' . urlencode('Has iniciado sesión'));
    exit;
}

$_SESSION['intentos_login']++;
header('Location: ../view/login.vista.php?error=' . urlencode('Usuario o contraseña incorrectos') . '&usuari=' . urlencode($campUsuari));
exit;

function validarRecaptcha() {
    if (!isset($_POST['g-recaptcha-response']) || empty($_POST['g-recaptcha-response'])) {
        return false;
    }
    
    $recaptchaResponse = $_POST['g-recaptcha-response'];
    
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret' => RECAPTCHA_SECRET_KEY,
        'response' => $recaptchaResponse,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];
    
    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    $context  = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    
    if ($response === false) {
        return false;
    }
    
    $responseData = json_decode($response);
    
    return $responseData->success === true;
}
