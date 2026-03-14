<?php
require_once __DIR__ . '/../security/auth.php';
function e($s){return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');}
$error = isset($_GET['error']) ? $_GET['error'] : null;
$usuariPrefill = isset($_GET['usuari']) ? $_GET['usuari'] : '';
$correuPrefill = isset($_GET['correu']) ? $_GET['correu'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <base href="/ProyecteServidor1/">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrarse - PokéNet Social</title>
  <link rel="icon" type="image/jpeg" href="assets/img/fondo.jpg">
  <link rel="stylesheet" href="style/styles.css">
  </head>
<body>
  <nav class="navbar">
    <div class="navbar-container">
      <a href="view/index.php" class="navbar-brand">🌟 PokéNet</a>
      <div class="navbar-actions">
        <?php if(estaIdentificado()): ?>
          <span class="nav-user"><?= e(usuarioActual()['username']) ?></span>
          <a class="nav-btn" href="controller/logout.controller.php">Cerrar sesión</a>
        <?php else: ?>
          <a href="view/login.vista.php" class="nav-btn">Iniciar Sessió</a>
        <?php endif; ?>
      </div>
    </div>
  </nav>

  <div class="container form">
    <div class="header form">
      <h1>Crear cuenta</h1>
      <p>Regístrate para poder crear y editar tus pokémons.</p>
    </div>

    <?php if ($error): ?>
      <div class="alert error">❌ <?= e($error) ?></div>
    <?php endif; ?>

    <div class="form-container">
      <form action="controller/register.controller.php" method="post">
        <div class="form-group">
          <label for="username">Usuari *</label>
          <input id="username" name="username" required value="<?= e($usuariPrefill) ?>">
        </div>
        <div class="form-group">
          <label for="email">Correu *</label>
          <input id="email" name="email" required value="<?= e($correuPrefill) ?>">
        </div>
        <div class="form-group">
          <label for="password">Contraseña *</label>
          <input id="password" name="password" type="password" required>
        </div>
        <div class="form-group">
          <label for="password2">Repita la contraseña *</label>
          <input id="password2" name="password2" type="password" required>
        </div>
        <div class="actions form">
          <button class="btn primary" type="submit">Registrarse</button>
          <a class="btn secondary" href="view/index.php">Volver</a>
        </div>
      </form>
      
      <!-- Separador -->
      <div class="oauth-separator">
        <span>o regístrate con</span>
      </div>
      
      <!-- Botones OAuth -->
      <div class="oauth-buttons">
        <a href="controller/oauth.controller.php?provider=Google" class="btn-oauth btn-google">
          <svg class="oauth-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
          </svg>
          <span>Google</span>
        </a>
      </div>
    </div>
  </div>
</body>
</html>
