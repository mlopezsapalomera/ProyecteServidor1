<?php
// controller/login.controller.php
// Controlador para el proceso de inicio de sesión

require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../env.php';
require_once __DIR__ . '/../security/auth.php';

// Array global para errores de captcha
$errors = [];

// Validar método de la petición
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/login.vista.php');
    exit;
}

if (!isset($_SESSION['loginTries'])) {
    $_SESSION['loginTries'] = 0;
}

// Recoger datos del formulario
$campUsuari = isset($_POST['user']) ? trim($_POST['user']) : '';
$contrasenya = isset($_POST['password']) ? $_POST['password'] : '';

// Validar campos obligatorios
if ($campUsuari === '' || $contrasenya === '') {
    header('Location: ../view/login.vista.php?error=' . urlencode('Usuario y contraseña son obligatorios.'));
    exit;
}

// Validar reCAPTCHA si hay 3 o más intentos fallidos
if ($_SESSION['loginTries'] >= 3) {
    if (!isCaptchaValid()) {
        $_SESSION['loginTries']++;
        header('Location: ../view/login.vista.php?error=' . urlencode('Usuario o contraseña incorrectos.') . '&usuari=' . urlencode($campUsuari));
        exit;
    }
}

// Verificar credenciales
$usuari = verificarCredencialesUsuario($campUsuari, $contrasenya);
if ($usuari) {
    // Resetear contador de intentos en login exitoso
    $_SESSION['loginTries'] = 0;
    iniciarSesion($usuari);
    header('Location: ../view/index.php?ok=' . urlencode('Has iniciado sesión.'));
    exit;
}

// Credenciales incorrectas - incrementar contador
$_SESSION['loginTries']++;
header('Location: ../view/login.vista.php?error=' . urlencode('Usuario o contraseña incorrectos.') . '&usuari=' . urlencode($campUsuari));
exit;

/**
 * Comprova si el captcha ha sigut completat i completa l'array global d'errors si n'hi ha
 * @return boolean si el captcha és vàlid o no
 */
function isCaptchaValid() {
    global $errors;

    // Verificar la resposta de l'API reCAPTCHA 
    if (isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {
        $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . RECAPTCHA_KEY . '&response=' . $_POST['g-recaptcha-response']);

        // Decodificar JSON data de la resposta de l'API
        $responseData = json_decode($verifyResponse);

        if (!$responseData->success) {
            $errors['captcha'] = "Wrong captcha";
            return false;
        }
    } else {
        $errors['captcha'] = "You must check the captcha.";
        return false;
    }
    
    return true;
}
