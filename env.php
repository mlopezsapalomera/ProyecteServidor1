<?php
if (!defined('DB_HOST')) {
	define('DB_HOST', 'localhost');
}
if (!defined('DB_NAME')) {
	define('DB_NAME', 'proyecte_servidor1');
}
if (!defined('DB_USER')) {
	define('DB_USER', 'root');
}
if (!defined('DB_PASS')) {
	define('DB_PASS', '');
}

// Configuración reCAPTCHA v2
if (!defined('RECAPTCHA_SITE_KEY')) {
    define('RECAPTCHA_SITE_KEY', '6LfM2VUsAAAAAO3Tnq6LryZmAfOpmEHxPLzIxjBn');
}
if (!defined('RECAPTCHA_SECRET_KEY')) {
    define('RECAPTCHA_SECRET_KEY', '6LfM2VUsAAAAACfI3SXD2DFPee_aROxGQ0G5X6Rp');
}

// Configuración PHPMailer para recuperación de contraseña
if (!defined('MAIL_HOST')) {
    define('MAIL_HOST', 'smtp.gmail.com'); // Servidor SMTP (ej: smtp.gmail.com, smtp.office365.com)
}
if (!defined('MAIL_PORT')) {
    define('MAIL_PORT', 587); // Puerto SMTP (587 para TLS, 465 para SSL)
}
if (!defined('MAIL_USERNAME')) {
    define('MAIL_USERNAME', 'mxrcol18@gmail.com'); // Tu correo electrónico
}
if (!defined('MAIL_PASSWORD')) {
    define('MAIL_PASSWORD', 'tgbr opqv cchx tysl'); // Contraseña o App Password
}
if (!defined('MAIL_FROM_EMAIL')) {
    define('MAIL_FROM_EMAIL', 'mxrcol18@gmail.com'); // Correo del remitente
}
if (!defined('MAIL_FROM_NAME')) {
    define('MAIL_FROM_NAME', 'PokéNet Social'); // Nombre del remitente
}

// Configuración Google OAuth 2.0
// INSTRUCCIONES: Obtener credenciales en https://console.cloud.google.com/
// 1. Crear un proyecto
// 2. Habilitar Google+ API
// 3. Crear credenciales OAuth 2.0 Client ID
// 4. Añadir URI de redirección: http://localhost/ProyecteServidor1/controller/oauth.controller.php
if (!defined('GOOGLE_CLIENT_ID')) {
    define('GOOGLE_CLIENT_ID', 'TU_GOOGLE_CLIENT_ID_AQUI');
}
if (!defined('GOOGLE_CLIENT_SECRET')) {
    define('GOOGLE_CLIENT_SECRET', 'TU_GOOGLE_CLIENT_SECRET_AQUI');
}

?>
