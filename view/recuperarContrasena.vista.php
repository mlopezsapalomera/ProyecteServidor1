<?php
require_once __DIR__ . '/../env.php';
require_once __DIR__ . '/../security/auth.php';
function e($s){return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');}

$error = isset($_GET['error']) ? $_GET['error'] : null;
$success = isset($_GET['success']) ? $_GET['success'] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <base href="/ProyecteServidor1/">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recuperar Contraseña - PokéNet Social</title>
  <link rel="icon" type="image/jpeg" href="assets/img/fondo.jpg">
  <link rel="stylesheet" href="style/styles.css">
</head>
<body>
  <nav class="navbar">
    <div class="navbar-container">
      <a href="index.php" class="navbar-brand">🌟 PokéNet</a>
      <div class="navbar-actions">
        <a href="view/login.vista.php" class="nav-btn">Iniciar sesión</a>
        <a href="view/register.vista.php" class="nav-btn">Registrarse</a>
      </div>
    </div>
  </nav>

  <div class="container form">
    <div class="header form">
      <h1>Recuperar Contraseña</h1>
      <p>Introduce tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.</p>
    </div>

    <?php if ($error): ?>
      <div class="alert error">❌ <?= e($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert success">✅ <?= e($success) ?></div>
    <?php endif; ?>

    <div class="form-container">
      <form action="controller/solicitarRecuperacion.controller.php" method="post">
        <?= csrfInput() ?>
        <div class="form-group">
          <label for="email">Correo electrónico *</label>
          <input id="email" name="email" type="email" required placeholder="tu_correo@ejemplo.com">
          <small style="color: #666; display: block; margin-top: 5px;">
            Te enviaremos un enlace que será válido por 5 minutos.
          </small>
        </div>

        <div class="actions form">
          <button class="btn primary" type="submit">Enviar enlace de recuperación</button>
          <a class="btn secondary" href="view/login.vista.php">Volver al login</a>
        </div>
      </form>
    </div>

    <div style="text-align: center; margin-top: 20px; color: #666; font-size: 0.9em;">
      <p>¿Recordaste tu contraseña? <a href="view/login.vista.php" style="color: #4a90e2;">Iniciar sesión</a></p>
    </div>
  </div>
</body>
</html>
