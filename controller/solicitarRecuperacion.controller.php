<?php

require_once __DIR__ . '/../security/csrf.php';
require_once __DIR__ . '/../env.php';
require_once __DIR__ . '/../model/user.php';

// cargar phpmailer
if (file_exists(__DIR__ . '/../PHPMailer/src/PHPMailer.php')) {
    require __DIR__ . '/../PHPMailer/src/Exception.php';
    require __DIR__ . '/../PHPMailer/src/PHPMailer.php';
    require __DIR__ . '/../PHPMailer/src/SMTP.php';
} else {
    die('PHPMailer no instalado');
}

if (!isset($_POST['email']) || empty($_POST['email'])) {
    header('Location: ../view/recuperarContrasena.vista.php?error=' . urlencode('Introduce tu correo'));
    exit();
}

csrfRequireOrRedirect('../view/recuperarContrasena.vista.php');

$email = trim($_POST['email']);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../view/recuperarContrasena.vista.php?error=' . urlencode('El correo no es valido'));
    exit();
}

if (!existeCorreo($email)) {
    header('Location: ../view/recuperarContrasena.vista.php?error=' . urlencode('Este correo no está registrado'));
    exit();
}

$usuario = obtenerUsuarioPorEmail($email);

$token = generarTokenRecuperacion($email);
if (!$token) {
    header('Location: ../view/recuperarContrasena.vista.php?error=' . urlencode('Error al generar token'));
    exit();
}

$enlaceRecuperacion = 'http://' . $_SERVER['HTTP_HOST'] . '/ProyecteServidor1/controller/resetearContrasenaPage.controller.php?token=' . urlencode($token);

// enviar correo
try {
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    
    $mail->isSMTP();
    $mail->Host       = MAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USERNAME;
    $mail->Password   = MAIL_PASSWORD;
    $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = MAIL_PORT;
    $mail->CharSet    = 'UTF-8';
    
    $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
    $mail->addAddress($email, $usuario['username']);
    
    $mail->isHTML(true);
    $mail->Subject = 'Recuperar Contraseña';
    
    $nombreUsuario = htmlspecialchars($usuario['username']);
    $mail->Body = '<html><body style="font-family: Arial; padding: 20px;">';
    $mail->Body .= '<h2>Hola ' . $nombreUsuario . '</h2>';
    $mail->Body .= '<p>Has solicitado recuperar tu contraseña.</p>';
    $mail->Body .= '<p>Haz clic aqui para cambiar tu contraseña:</p>';
    $mail->Body .= '<p><a href="' . $enlaceRecuperacion . '" style="background: #4a90e2; color: white; padding: 10px 20px; text-decoration: none;">Cambiar Contraseña</a></p>';
    $mail->Body .= '<p>O copia este enlace:</p>';
    $mail->Body .= '<p style="background: #f0f0f0; padding: 10px;">' . $enlaceRecuperacion . '</p>';
    $mail->Body .= '<p><strong>Importante:</strong> Este enlace caduca en 5 minutos.</p>';
    $mail->Body .= '<p>Si no fuiste tu, ignora este correo.</p>';
    $mail->Body .= '</body></html>';
    
    $mail->AltBody = "Hola {$nombreUsuario},\n\nHas solicitado recuperar tu contraseña.\n\nEnlace: {$enlaceRecuperacion}\n\nCaduca en 5 minutos.\n\nSi no fuiste tu, ignora este correo.";
    
    $mail->send();
    
    header('Location: ../view/recuperarContrasena.vista.php?success=' . urlencode('Se ha mandado un correo con el enlace de recuperación'));
    exit();
    
} catch (\PHPMailer\PHPMailer\Exception $e) {
    $errorMsg = "Error PHPMailer: " . $e->errorMessage();
    error_log($errorMsg);
    header('Location: ../view/recuperarContrasena.vista.php?error=' . urlencode($errorMsg));
    exit();
} catch (Exception $e) {
    $errorMsg = "Error: " . $e->getMessage();
    error_log($errorMsg);
    header('Location: ../view/recuperarContrasena.vista.php?error=' . urlencode($errorMsg));
    exit();
}
