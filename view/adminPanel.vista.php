<?php
require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../security/auth.php';

// Solo administradores pueden acceder
if (!estaIdentificado() || !esAdmin()) {
    header('Location: ../view/index.php?error=' . urlencode('Acceso denegado. Solo administradores.'));
    exit;
}

// Helper para escapar HTML
function e($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }

// Obtener todos los usuarios (excepto admins)
$usuarios = obtenerTodosLosUsuarios(true);

$ok = isset($_GET['ok']) ? $_GET['ok'] : null;
$error = isset($_GET['error']) ? $_GET['error'] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <base href="/ProyecteServidor1/">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - PokéNet</title>
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
                    <?= e(usuarioActual()['username']) ?> <span style="background: #ff006e; padding: 2px 8px; border-radius: 8px; font-size: 0.75rem; margin-left: 4px;">ADMIN</span>
                </a>
                <a href="controller/logout.controller.php" class="nav-btn">Cerrar sesión</a>
            </div>
        </div>
    </nav>

    <div class="main-wrapper">
        <div class="sidebar-left">
            <a href="index.php" class="btn-capturar" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <span class="icon">🏠</span>
                <span>Inicio</span>
            </a>
            <a href="view/perfilUsuario.vista.php?id=<?= idUsuarioActual() ?>" class="btn-capturar" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <span class="icon">👤</span>
                <span>Mi Perfil</span>
            </a>
            <div class="sidebar-stats">
                <h4>📊 Estadísticas</h4>
                <p><strong><?= count($usuarios) ?></strong> Usuarios registrados</p>
            </div>
        </div>
        
        <div class="posts-container">
            <div class="posts-scroll">
                <!-- Panel de Administración -->
                <div class="admin-panel-container">
                    <div class="admin-panel-header">
                        <h1>👨‍💼 Panel de Administración</h1>
                        <p>Gestiona los usuarios de PokéNet Social</p>
                    </div>

                    <?php if ($ok): ?>
                        <div class="alert success">✅ <?= e($ok) ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert error">❌ <?= e($error) ?></div>
                    <?php endif; ?>

                    <!-- Tabla de usuarios -->
                    <?php if (empty($usuarios)): ?>
                        <div class="empty-admin">
                            <h3>👥 No hay usuarios registrados</h3>
                            <p>Aún no hay usuarios en el sistema (excepto administradores).</p>
                        </div>
                    <?php else: ?>
                        <div class="users-table-container">
                            <table class="users-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Foto</th>
                                        <th>Usuario</th>
                                        <th>Email</th>
                                        <th>Publicaciones</th>
                                        <th>Fecha Registro</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <?php $numPublicaciones = contarPublicacionesUsuario($usuario['id']); ?>
                                        <tr>
                                            <td><strong>#<?= e($usuario['id']) ?></strong></td>
                                            <td>
                                                <img src="<?php 
                                                        $imgPath = $usuario['profile_image'];
                                                        if ($imgPath === 'userDefaultImg.jpg') {
                                                            echo 'assets/img/imgProfileuser/' . e($imgPath);
                                                        } else {
                                                            echo 'assets/img/userImg/' . e($imgPath);
                                                        }
                                                     ?>" 
                                                     alt="<?= e($usuario['username']) ?>" 
                                                     class="user-table-avatar"
                                                     onerror="this.src='assets/img/imgProfileuser/userDefaultImg.jpg'">
                                            </td>
                                            <td>
                                                <a href="view/perfilUsuario.vista.php?id=<?= e($usuario['id']) ?>" class="user-link">
                                                    <?= e($usuario['username']) ?>
                                                </a>
                                            </td>
                                            <td><?= e($usuario['email']) ?></td>
                                            <td>
                                                <span class="badge-publications"><?= $numPublicaciones ?></span>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($usuario['created_at'])) ?></td>
                                            <td>
                                                <div class="admin-actions">
                                                    <a href="view/perfilUsuario.vista.php?id=<?= e($usuario['id']) ?>" 
                                                       class="btn-admin-action view"
                                                       title="Ver perfil">
                                                        👁️
                                                    </a>
                                                    <a href="controller/eliminarUsuario.controller.php?id=<?= e($usuario['id']) ?>" 
                                                       class="btn-admin-action delete"
                                                       onclick="return confirm('⚠️ ¿Estás seguro de eliminar a <?= e($usuario['username']) ?>?\n\nEsto eliminará:\n- Su cuenta de usuario\n- Todas sus <?= $numPublicaciones ?> publicaciones\n\nEsta acción NO se puede deshacer.');"
                                                       title="Eliminar usuario">
                                                        🗑️
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
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
