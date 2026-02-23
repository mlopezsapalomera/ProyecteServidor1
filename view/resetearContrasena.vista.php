<?php
require_once __DIR__ . '/../env.php';
require_once __DIR__ . '/../security/auth.php';
require_once __DIR__ . '/../model/user.php';
function e($s){return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');}

// Obtener token de la URL
$token = isset($_GET['token']) ? $_GET['token'] : null;
$error = isset($_GET['error']) ? $_GET['error'] : null;

// Verificar si el token es válido
if (!$token) {
    header('Location: ../view/login.vista.php?error=' . urlencode('Token de recuperación no válido'));
    exit();
}

// Verificar que el token existe y no ha expirado
$usuario = verificarTokenRecuperacion($token);
if (!$usuario) {
    header('Location: ../view/recuperarContrasena.vista.php?error=' . urlencode('El enlace de recuperación ha expirado o no es válido. Solicita uno nuevo'));
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <base href="/ProyecteServidor1/">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Restablecer Contraseña - PokéNet Social</title>
  <link rel="icon" type="image/jpeg" href="assets/img/fondo.jpg">
  <link rel="stylesheet" href="style/styles.css">
  <script>
    // Validación en el cliente
    function validarFormulario(event) {
      const password = document.getElementById('password').value;
      const passwordConfirm = document.getElementById('password_confirm').value;
      
      // Validar longitud mínima
      if (password.length < 6) {
        alert('La contraseña debe tener al menos 6 caracteres');
        event.preventDefault();
        return false;
      }
      
      // Validar que las contraseñas coincidan
      if (password !== passwordConfirm) {
        alert('Las contraseñas no coinciden');
        event.preventDefault();
        return false;
      }
      
      return true;
    }
  </script>
</head>
<body>
  <nav class="navbar">
    <div class="navbar-container">
      <a href="view/index.php" class="navbar-brand">🌟 PokéNet</a>
      <div class="navbar-actions">
        <a href="view/login.vista.php" class="nav-btn">Iniciar sesión</a>
        <a href="view/register.vista.php" class="nav-btn">Registrarse</a>
      </div>
    </div>
  </nav>

  <div class="container form">
    <div class="header form">
      <h1>Restablecer Contraseña</h1>
      <p>Hola, <strong><?= e($usuario['username']) ?></strong>. Introduce tu nueva contraseña.</p>
    </div>

    <?php if ($error): ?>
      <div class="alert error">❌ <?= e($error) ?></div>
    <?php endif; ?>

    <div class="form-container">
      <form action="controller/resetearContrasena.controller.php" method="post" onsubmit="return validarFormulario(event)">
        <input type="hidden" name="token" value="<?= e($token) ?>">
        
        <div class="form-group">
          <label for="password">Nueva contraseña *</label>
          <input id="password" name="password" type="password" required minlength="6" placeholder="Mínimo 6 caracteres">
          <small style="color: #666; display: block; margin-top: 5px;">
            Debe tener al menos 6 caracteres
          </small>
        </div>

        <div class="form-group">
          <label for="password_confirm">Confirmar nueva contraseña *</label>
          <input id="password_confirm" name="password_confirm" type="password" required minlength="6" placeholder="Repite la contraseña">
        </div>

        <div style="background-color: #fff3cd; border: 1px solid #ffc107; padding: 12px; border-radius: 5px; margin-bottom: 20px;">
          <strong>⚠️ Importante:</strong> Este enlace expira en 5 minutos desde que lo solicitaste.
        </div>

        <div class="actions form">
          <button class="btn primary" type="submit">Cambiar contraseña</button>
          <a class="btn secondary" href="view/login.vista.php">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
