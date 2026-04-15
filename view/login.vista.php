<?php
require_once __DIR__ . '/../env.php';
require_once __DIR__ . '/../security/auth.php';
function e($s){return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');}
$error = isset($_GET['error']) ? $_GET['error'] : null;
$success = isset($_GET['success']) ? $_GET['success'] : null;
$captcha_error = isset($_GET['captcha_error']) ? $_GET['captcha_error'] : null;
$usuariPrefill = isset($_GET['usuari']) ? $_GET['usuari'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <base href="/ProyecteServidor1/">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar sesión - PokéNet Social</title>
  <link rel="icon" type="image/jpeg" href="assets/img/fondo.jpg">
  <link rel="stylesheet" href="style/styles.css">
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
  <nav class="navbar">
    <div class="navbar-container">
      <a href="index.php" class="navbar-brand">🌟 PokéNet</a>
      <div class="navbar-actions">
        <?php if(estaIdentificado()): ?>
          <span class="nav-user"><?= e(usuarioActual()['username']) ?></span>
          <a class="nav-btn" href="controller/logout.controller.php">Cerrar sesión</a>
        <?php else: ?>
          <a href="view/register.vista.php" class="nav-btn">Registrarse</a>
        <?php endif; ?>
      </div>
    </div>
  </nav>

  <div class="container form">
    <div class="header form">
      <h1>Iniciar sesión</h1>
      <p>Introduce tu usuario o email y contraseña.</p>
    </div>

    <?php if ($success): ?>
      <div class="alert success">✅ <?= e($success) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert error">❌ <?= e($error) ?></div>
    <?php endif; ?>

    <div class="form-container">
      <form action="controller/login.controller.php" method="post">
        <?= csrfInput() ?>
        <div class="form-group">
          <label for="user">Usuari o Email *</label>
          <input id="user" name="user" required value="<?= e($usuariPrefill) ?>">
        </div>
        <div class="form-group">
          <label for="password">Contraseña *</label>
          <input id="password" name="password" type="password" required>
          <div style="text-align: right; margin-top: 8px;">
            <a href="view/recuperarContrasena.vista.php" style="font-size: 0.9em; color: #4a90e2; text-decoration: none;">¿No recuerdas tu contraseña?</a>
          </div>
        </div>

        <div class="form-group">
          <label class="checkbox-label">
            <input type="checkbox" name="remember_me" id="remember_me">
            <span>Recordarme durante 30 días</span>
          </label>
        </div>

        <?php if (isset($_SESSION['intentos_login']) && $_SESSION['intentos_login'] >= 3): ?>
          <div class="form-group">
            <div class="g-recaptcha" data-sitekey="<?= RECAPTCHA_SITE_KEY ?>"></div>
            <?php if ($captcha_error): ?>
              <div class="alert error" style="margin-top: 8px;">❌ <?= e($captcha_error) ?></div>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <div class="actions form">
          <button class="btn primary" type="submit">Iniciar sesión</button>
          <a class="btn secondary" href="index.php">Volver</a>
        </div>
      </form>
      
      <!-- Separador -->
      <div class="oauth-separator">
        <span>o continúa con</span>
      </div>
      
      <!-- Botones OAuth -->
      <div class="oauth-buttons">
        <a href="controller/oauthDirectGoogle.controller.php" class="btn-oauth btn-google">
          <svg class="oauth-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
          </svg>
          <span>Google</span>
        </a>

        <a href="controller/oauthHybridGithub.controller.php?provider=GitHub" class="btn-oauth btn-github">
          <svg class="oauth-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path fill="currentColor" d="M12 .5C5.65.5.5 5.65.5 12c0 5.1 3.3 9.43 7.88 10.96.58.1.8-.25.8-.56v-2.16c-3.2.7-3.87-1.36-3.87-1.36-.52-1.32-1.28-1.67-1.28-1.67-1.04-.7.08-.69.08-.69 1.15.08 1.75 1.18 1.75 1.18 1.02 1.75 2.67 1.25 3.32.95.1-.74.4-1.25.72-1.54-2.56-.29-5.25-1.28-5.25-5.71 0-1.26.45-2.3 1.18-3.1-.12-.29-.51-1.45.11-3.03 0 0 .97-.31 3.17 1.18a11.04 11.04 0 0 1 5.78 0c2.2-1.49 3.17-1.18 3.17-1.18.62 1.58.23 2.74.11 3.03.73.8 1.18 1.84 1.18 3.1 0 4.44-2.69 5.42-5.26 5.71.41.35.78 1.03.78 2.08v3.08c0 .31.21.67.8.56A11.5 11.5 0 0 0 23.5 12C23.5 5.65 18.35.5 12 .5z"/>
          </svg>
          <span>GitHub</span>
        </a>
      </div>
    </div>
  </div>
</body>
</html>
