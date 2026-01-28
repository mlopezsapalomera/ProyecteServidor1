<?php
require_once __DIR__ . '/../security/auth.php';
function e($s){return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');}
$error = isset($_GET['error']) ? $_GET['error'] : null;
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

    <?php if ($error): ?>
      <div class="alert error">❌ <?= e($error) ?></div>
    <?php endif; ?>

    <div class="form-container">
      <form action="controller/login.controller.php" method="post">
        <div class="form-group">
          <label for="user">Usuari o Email *</label>
          <input id="user" name="user" required value="<?= e($usuariPrefill) ?>">
        </div>
        <div class="form-group">
          <label for="password">Contraseña *</label>
          <input id="password" name="password" type="password" required>
        </div>

        <div class="form-group">
          <label class="checkbox-label">
            <input type="checkbox" name="remember_me" id="remember_me">
            <span>Recordarme durante 30 días</span>
          </label>
        </div>

        <div class="actions form">
          <button class="btn primary" type="submit">Iniciar sesión</button>
          <a class="btn secondary" href="view/index.php">Volver</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
