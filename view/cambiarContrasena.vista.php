<?php
require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../security/auth.php';

// Solo usuarios autenticados pueden cambiar su contraseña
if (!estaIdentificado()) {
    header('Location: ../view/login.vista.php');
    exit;
}

// Helper para escapar HTML
function e($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }

// Obtener datos del usuario actual
$usuario = obtenerUsuarioPorId(idUsuarioActual());
if (!$usuario) {
    header('Location: ../view/index.php?error=' . urlencode('Usuario no encontrado'));
    exit;
}

$ok = isset($_GET['ok']) ? $_GET['ok'] : null;
$error = isset($_GET['error']) ? $_GET['error'] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <base href="/ProyecteServidor1/">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña - PokéNet</title>
    <link rel="icon" type="image/jpeg" href="assets/img/fondo.jpg">
    <link rel="stylesheet" href="style/styles.css?v=<?= time() ?>">
</head>
<body class="no-page-scroll">
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="index.php" class="navbar-brand" style="text-decoration: none; color: inherit;">🌟 PokéNet</a>
            <div class="navbar-actions">
                <a href="view/perfilUsuario.vista.php?id=<?= idUsuarioActual() ?>" class="nav-user" style="text-decoration: none; color: inherit;">
                    <?= e($usuario['username']) ?>
                </a>
                <a href="controller/logout.controller.php" class="nav-btn">Cerrar sesión</a>
            </div>
        </div>
    </nav>

    <div class="main-wrapper">
        <div class="sidebar-left">
            <a href="view/modificarPerfil.vista.php" class="btn-capturar" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <span class="icon">👤</span>
                <span>Editar Perfil</span>
            </a>
            <a href="view/perfilUsuario.vista.php?id=<?= idUsuarioActual() ?>" class="btn-capturar" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <span class="icon">🏠</span>
                <span>Ver mi Perfil</span>
            </a>
            <a href="index.php" class="btn-capturar" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <span class="icon">🔙</span>
                <span>Volver al Inicio</span>
            </a>
        </div>
        
        <div class="posts-container">
            <div class="posts-scroll">
                <!-- Formulario de cambio de contraseña -->
                <div class="edit-profile-container">
                    <div class="edit-profile-header">
                        <h1>🔒 Cambiar Contraseña</h1>
                        <p>Actualiza tu contraseña para mantener tu cuenta segura</p>
                    </div>

                    <?php if ($ok): ?>
                        <div class="alert success">✅ <?= e($ok) ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert error">❌ <?= e($error) ?></div>
                    <?php endif; ?>

                    <!-- Información de seguridad -->
                    <div class="security-tips">
                        <h3>💡 Consejos para una contraseña segura:</h3>
                        <ul>
                            <li>✓ Usa al menos 6 caracteres (recomendado 8 o más)</li>
                            <li>✓ Combina letras mayúsculas y minúsculas</li>
                            <li>✓ Incluye números y caracteres especiales</li>
                            <li>✓ No uses información personal fácil de adivinar</li>
                            <li>✓ Usa una contraseña única para cada sitio</li>
                        </ul>
                    </div>

                    <!-- Formulario de cambio de contraseña -->
                    <form action="controller/cambiarContrasena.controller.php" method="POST" class="edit-profile-form">
                        <div class="form-group">
                            <label for="current_password" class="form-label">
                                🔑 Contraseña Actual
                            </label>
                            <input type="password" 
                                   id="current_password" 
                                   name="current_password" 
                                   required
                                   class="form-control"
                                   placeholder="Tu contraseña actual"
                                   autocomplete="current-password">
                            <small class="form-hint">Confirma tu identidad con tu contraseña actual</small>
                        </div>

                        <div class="form-group">
                            <label for="new_password" class="form-label">
                                🔐 Nueva Contraseña
                            </label>
                            <input type="password" 
                                   id="new_password" 
                                   name="new_password" 
                                   required
                                   minlength="6"
                                   class="form-control"
                                   placeholder="Nueva contraseña (mínimo 6 caracteres)"
                                   autocomplete="new-password">
                            <small class="form-hint">Mínimo 6 caracteres</small>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password" class="form-label">
                                🔐 Confirmar Nueva Contraseña
                            </label>
                            <input type="password" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   required
                                   minlength="6"
                                   class="form-control"
                                   placeholder="Repite la nueva contraseña"
                                   autocomplete="new-password">
                            <small class="form-hint">Debe coincidir con la nueva contraseña</small>
                        </div>

                        <!-- Botones -->
                        <div class="form-actions">
                            <button type="submit" class="btn-submit" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                <span class="icon">🔒</span>
                                Cambiar Contraseña
                            </button>
                            <a href="view/modificarPerfil.vista.php" class="btn-cancel">
                                <span class="icon">❌</span>
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Limpiar mensajes de la URL
    if (window.location.search.match(/[?&](ok|error)=/)) {
        setTimeout(() => {
            const url = new URL(window.location);
            url.searchParams.delete('ok');
            url.searchParams.delete('error');
            window.history.replaceState({}, document.title, url.pathname);
        }, 3000);
    }
    </script>
</body>
</html>
