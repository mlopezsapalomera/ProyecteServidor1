<?php
// controller/login.controller.php
// Controlador para el proceso de inicio de sesión

require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../env.php';
require_once __DIR__ . '/../security/auth.php';

// Validar método de la petición
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/login.vista.php');
    exit;
}

if (!isset($_SESSION['intentos_login'])) {
    $_SESSION['intentos_login'] = 0;
}

// Recoger datos del formulario
$campUsuari = isset($_POST['user']) ? trim($_POST['user']) : '';
$contrasenya = isset($_POST['password']) ? $_POST['password'] : '';
$recordarme = isset($_POST['remember_me']) ? true : false;

// Validar campos obligatorios
if ($campUsuari === '' || $contrasenya === '') {
    header('Location: ../view/login.vista.php?error=' . urlencode('Usuario y contraseña son obligatorios.'));
    exit;
}

// Validar reCAPTCHA si hay 3 o más intentos fallidos
if ($_SESSION['intentos_login'] >= 3) {
    if (!validarRecaptcha()) {
        $_SESSION['intentos_login']++;
        header('Location: ../view/login.vista.php?error=' . urlencode('Usuario o contraseña incorrectos.') . 
               '&captcha_error=' . urlencode('Debes completar el reCAPTCHA correctamente.') . 
               '&usuari=' . urlencode($campUsuari));
        exit;
    }
}

// Verificar credenciales
$usuari = verificarCredencialesUsuario($campUsuari, $contrasenya);
if ($usuari) {
    // Resetear contador de intentos en login exitoso
    $_SESSION['intentos_login'] = 0;
    iniciarSesion($usuari);
    
    // Si el usuario marcó "Recordarme", crear token y cookie
    if ($recordarme) {
        $token = crearRememberToken($usuari['id'], 30); // Token válido por 30 días
        if ($token) {
            // Guardar token en cookie (30 días = 2592000 segundos)
            setcookie(
                'remember_token',
                $token,
                time() + (30 * 24 * 60 * 60),
                '/',
                '',
                false, // No requerir HTTPS (cambiar a true en producción)
                true   // HttpOnly: no accesible desde JavaScript
            );
        }
    }
    
    header('Location: ../view/index.php?ok=' . urlencode('Has iniciado sesión.'));
    exit;
}

// Credenciales incorrectas - incrementar contador
$_SESSION['intentos_login']++;
header('Location: ../view/login.vista.php?error=' . urlencode('Usuario o contraseña incorrectos.') . '&usuari=' . urlencode($campUsuari));
exit;

/**
 * Valida si el reCAPTCHA ha sido completado correctamente
 * @return bool true si es válido, false si no
 */
function validarRecaptcha() {
    // Si no hay respuesta de captcha, es inválido
    if (!isset($_POST['g-recaptcha-response']) || empty($_POST['g-recaptcha-response'])) {
        return false;
    }
    
    $recaptchaResponse = $_POST['g-recaptcha-response'];
    
    // Hacer petición a la API de Google para verificar
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret' => RECAPTCHA_SECRET_KEY,
        'response' => $recaptchaResponse,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];
    
    // Usar file_get_contents con stream context
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
        // Error en la petición
        return false;
    }
    
    $responseData = json_decode($response);
    
    // Retornar si fue exitoso
    return $responseData->success === true;
}
