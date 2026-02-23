<?php
// controller/solicitarRecuperacion.controller.php
// Procesa la solicitud de recuperación de contraseña

session_start();
require_once __DIR__ . '/../env.php';
require_once __DIR__ . '/../model/user.php';

// Usar PHPMailer con Composer (si está instalado)
// Si no tienes Composer, descarga PHPMailer manualmente y ajusta la ruta
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Intentar cargar PHPMailer con Composer
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
} else {
    // Si no existe Composer, intenta carga manual (debes descargar PHPMailer)
    // Descarga de: https://github.com/PHPMailer/PHPMailer
    if (file_exists(__DIR__ . '/../PHPMailer/src/PHPMailer.php')) {
        require __DIR__ . '/../PHPMailer/src/Exception.php';
        require __DIR__ . '/../PHPMailer/src/PHPMailer.php';
        require __DIR__ . '/../PHPMailer/src/SMTP.php';
    } else {
        die('PHPMailer no está instalado. Por favor, instala PHPMailer usando Composer: composer require phpmailer/phpmailer');
    }
}

// Validar que se recibió el correo
if (!isset($_POST['email']) || empty($_POST['email'])) {
    header('Location: ../view/recuperarContrasena.vista.php?error=' . urlencode('Por favor, introduce tu correo electrónico'));
    exit();
}

$email = trim($_POST['email']);

// Validar formato de correo
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../view/recuperarContrasena.vista.php?error=' . urlencode('El formato del correo no es válido'));
    exit();
}

// Verificar que el correo existe en la base de datos
if (!existeCorreo($email)) {
    header('Location: ../view/recuperarContrasena.vista.php?error=' . urlencode('Este correo no está registrado'));
    exit();
}

// Obtener datos del usuario
$usuario = obtenerUsuarioPorEmail($email);

// Generar token de recuperación
$token = generarTokenRecuperacion($email);
if (!$token) {
    header('Location: ../view/recuperarContrasena.vista.php?error=' . urlencode('Error al generar el token. Inténtalo de nuevo'));
    exit();
}

// Crear enlace de recuperación
$enlaceRecuperacion = 'http://' . $_SERVER['HTTP_HOST'] . '/ProyecteServidor1/view/resetearContrasena.vista.php?token=' . urlencode($token);

// Configurar y enviar correo con PHPMailer
try {
    $mail = new PHPMailer(true);
    
    // Configuración del servidor SMTP
    $mail->isSMTP();
    $mail->Host       = MAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USERNAME;
    $mail->Password   = MAIL_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = MAIL_PORT;
    $mail->CharSet    = 'UTF-8';
    
    // Remitente y destinatario
    $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
    $mail->addAddress($email, $usuario['username']);
    
    // Contenido del correo
    $mail->isHTML(true);
    $mail->Subject = 'Recuperación de Contraseña - PokéNet Social';
    $mail->Body    = '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #4a90e2; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
            .content { background-color: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
            .button { display: inline-block; padding: 12px 30px; background-color: #4a90e2; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 0.9em; }
            .warning { background-color: #fff3cd; border: 1px solid #ffc107; padding: 10px; border-radius: 5px; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🌟 PokéNet Social</h1>
            </div>
            <div class="content">
                <h2>Hola, ' . htmlspecialchars($usuario['username']) . '</h2>
                <p>Hemos recibido una solicitud para restablecer tu contraseña.</p>
                <p>Haz clic en el siguiente botón para crear una nueva contraseña:</p>
                <p style="text-align: center;">
                    <a href="' . $enlaceRecuperacion . '" class="button">Restablecer Contraseña</a>
                </p>
                <p>O copia y pega este enlace en tu navegador:</p>
                <p style="word-break: break-all; background: #fff; padding: 10px; border: 1px solid #ddd;">
                    ' . $enlaceRecuperacion . '
                </p>
                <div class="warning">
                    <strong>⚠️ Importante:</strong> Este enlace expirará en <strong>5 minutos</strong>.
                </div>
                <p>Si no solicitaste este cambio, puedes ignorar este correo y tu contraseña permanecerá sin cambios.</p>
            </div>
            <div class="footer">
                <p>Este es un correo automático, por favor no respondas.</p>
                <p>&copy; 2026 PokéNet Social - Todos los derechos reservados</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    // Versión en texto plano (para clientes de correo que no soportan HTML)
    $mail->AltBody = "Hola {$usuario['username']},\n\n" .
                     "Hemos recibido una solicitud para restablecer tu contraseña.\n\n" .
                     "Haz clic en el siguiente enlace para crear una nueva contraseña:\n" .
                     "{$enlaceRecuperacion}\n\n" .
                     "Este enlace expirará en 5 minutos.\n\n" .
                     "Si no solicitaste este cambio, puedes ignorar este correo.\n\n" .
                     "PokéNet Social";
    
    // Enviar correo
    $mail->send();
    
    // Redirigir con mensaje de éxito
    header('Location: ../view/recuperarContrasena.vista.php?success=' . urlencode('Se ha mandado un correo con el enlace de recuperación. Revisa tu bandeja de entrada'));
    exit();
    
} catch (Exception $e) {
    // Error al enviar correo
    error_log("Error al enviar correo de recuperación: {$mail->ErrorInfo}");
    header('Location: ../view/recuperarContrasena.vista.php?error=' . urlencode('Error al enviar el correo. Por favor, inténtalo de nuevo más tarde'));
    exit();
}
