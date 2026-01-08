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
    </div>
  </div>
</body>
</html>
