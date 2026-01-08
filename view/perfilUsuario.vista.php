<?php
require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../model/pokemon.php';
require_once __DIR__ . '/../security/auth.php';

// Helper para escapar HTML
function e($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }

// Obtener ID del usuario del perfil
$perfilUserId = isset($_GET['id']) ? (int)$_GET['id'] : idUsuarioActual();

// Si no hay usuario especificado ni sesión activa, redirigir
if (!$perfilUserId) {
    header('Location: ../view/login.vista.php');
    exit;
}

// Obtener información del usuario
$perfilUsuario = obtenerUsuarioPorId($perfilUserId);
if (!$perfilUsuario) {
    header('Location: ../view/index.php?error=' . urlencode('Usuario no encontrado'));
    exit;
}

// Obtener parámetros de paginación para posts del usuario
$perPage = isset($_GET['perPage']) && is_numeric($_GET['perPage']) ? (int)$_GET['perPage'] : 10;
if ($perPage < 1) $perPage = 10;

$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// Calcular paginación
$totalPokemons = contarPokemonsPorUsuario($perfilUserId);
$totalPages = max(1, ceil($totalPokemons / $perPage));
if ($page > $totalPages) $page = $totalPages;

// Obtener pokémons del usuario
$offset = ($page - 1) * $perPage;
$pokemons = obtenerPokemonsPorUsuario($perfilUserId, $perPage, $offset);

// Verificar si es el perfil del usuario actual
$esMiPerfil = estaIdentificado() && idUsuarioActual() === $perfilUserId;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <base href="/ProyecteServidor1/">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?= e($perfilUsuario['username']) ?> - PokéNet</title>
    <link rel="icon" type="image/jpeg" href="assets/img/fondo.jpg">
    <link rel="stylesheet" href="style/styles.css?v=<?= time() ?>">
</head>
<body class="no-page-scroll">
    <!-- Navbar tipo Instagram -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="index.php" class="navbar-brand" style="text-decoration: none; color: inherit;">🌟 PokéNet</a>
            <div class="navbar-actions">
                <?php if (estaIdentificado()): ?>
                    <a href="view/perfilUsuario.vista.php?id=<?= idUsuarioActual() ?>" class="nav-user" style="text-decoration: none; color: inherit;">
                        <?= e(usuarioActual()['username']) ?>
                    </a>
                    <a href="controller/logout.controller.php" class="nav-btn">Cerrar sesión</a>
                <?php else: ?>
                    <a href="view/login.vista.php" class="nav-btn login">Iniciar sesión</a>
                    <a href="view/register.vista.php" class="nav-btn register">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="main-wrapper">
        <!-- Panel lateral izquierdo -->
        <div class="sidebar-left">
            <a href="index.php" class="btn-capturar" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <span class="icon">🏠</span>
                <span>Volver al Inicio</span>
            </a>
            
            <?php if ($esMiPerfil): ?>
                <a href="view/insertar.vista.php" class="btn-capturar">
                    <span class="icon">⚡</span>
                    <span>Capturar Pokémon</span>
                </a>
                <a href="view/modificarPerfil.vista.php" class="btn-capturar" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <span class="icon">✏️</span>
                    <span>Editar Perfil</span>
                </a>
            <?php endif; ?>
        </div>
        
        <div class="posts-container">
            <div class="posts-scroll">
                <!-- Información del perfil -->
                <div class="profile-header">
                    <img src="<?php 
                            $imgPath = $perfilUsuario['profile_image'];
                            if ($imgPath === 'userDefaultImg.jpg') {
                                echo 'assets/img/imgProfileuser/' . e($imgPath);
                            } else {
                                echo 'assets/img/userImg/' . e($imgPath);
                            }
                         ?>" 
                         alt="<?= e($perfilUsuario['username']) ?>" 
                         class="profile-avatar-large"
                         onerror="this.src='assets/img/imgProfileuser/userDefaultImg.jpg'">
                    <div class="profile-info">
                        <h1 class="profile-username"><?= e($perfilUsuario['username']) ?></h1>
                        <p class="profile-stats">
                            <span><strong><?= $totalPokemons ?></strong> publicaciones</span>
                            <span>Miembro desde <?= date('M Y', strtotime($perfilUsuario['created_at'])) ?></span>
                        </p>
                        <?php if ($esMiPerfil): ?>
                            <p class="profile-email"><?= e($perfilUsuario['email']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <h2 class="section-title">📱 Publicaciones de <?= e($perfilUsuario['username']) ?></h2>

                <?php if (count($pokemons) === 0): ?>
                    <div class="empty">
                        <h3>🔍 <?= $esMiPerfil ? '¡Aún no has publicado nada!' : 'Este usuario aún no ha publicado nada' ?></h3>
                        <p><?= $esMiPerfil ? 'Empieza a capturar Pokémon y comparte con la comunidad.' : '' ?></p>
                    </div>
                <?php else: ?>
                    <!-- Posts del usuario -->
                    <?php foreach ($pokemons as $row): ?>
                        <div class="post-card">
                            <img src="<?php 
                                    $imgPath = $row['autor_profile_image'];
                                    if ($imgPath === 'userDefaultImg.jpg') {
                                        echo 'assets/img/imgProfileuser/' . e($imgPath);
                                    } else {
                                        echo 'assets/img/userImg/' . e($imgPath);
                                    }
                                 ?>" 
                                 alt="<?= e($row['autor_username']) ?>" 
                                 class="post-avatar"
                                 onerror="this.src='assets/img/imgProfileuser/userDefaultImg.jpg'">
                            <div class="post-main">
                                <div class="post-header">
                                    <span class="post-username"><?= e($row['titulo']) ?></span>
                                    <span class="post-id">#<?= e($row['id']) ?></span>
                                </div>
                                <div class="post-meta">
                                    <small class="post-author">Publicado por: <?= e($row['autor_username'] ?? 'Anónimo') ?></small>
                                </div>
                                <div class="post-title">🐾 <?= e($row['titulo']) ?></div>
                                <?php if ($row['descripcion']): ?>
                                    <div class="post-description">📝 <?= e($row['descripcion']) ?></div>
                                <?php endif; ?>
                                <?php if ($esMiPerfil): ?>
                                    <div class="post-actions post-actions-right">
                                        <a class="post-btn edit" href="view/modificar.vista.php?id=<?= e($row['id']) ?>" title="Editar">
                                            &#x270F;&#xFE0F;
                                        </a>
                                        <a class="post-btn delete" href="controller/eliminar.controller.php?id=<?= e($row['id']) ?>"
                                           onclick="return confirm('¿Seguro que quieres eliminar este Pokémon?');" title="Eliminar">
                                            &#x1F5D1;&#xFE0F;
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Paginación -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination pagination-fixed">
                    <?php
                        $baseParams = $_GET;
                        $baseParams['perPage'] = $perPage;
                        $baseParams['id'] = $perfilUserId;
                        
                        $prevDisabled = ($page <= 1);
                        $prevPage = max(1, $page - 1);
                        $baseParams['page'] = $prevPage;
                        $prevUrl = 'view/perfilUsuario.vista.php?' . http_build_query($baseParams);
                    ?>
                    <a href="<?= e($prevUrl) ?>" class="<?= $prevDisabled ? 'disabled' : '' ?>">Anterior</a>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php
                            $params = $baseParams;
                            $params['page'] = $i;
                            $url = 'view/perfilUsuario.vista.php?' . http_build_query($params);
                        ?>
                        <a href="<?= e($url) ?>" class="<?= $i == $page ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php
                        $nextDisabled = ($page >= $totalPages);
                        $nextPage = min($totalPages, $page + 1);
                        $baseParams['page'] = $nextPage;
                        $nextUrl = 'view/perfilUsuario.vista.php?' . http_build_query($baseParams);
                    ?>
                    <a href="<?= e($nextUrl) ?>" class="<?= $nextDisabled ? 'disabled' : '' ?>">Siguiente</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
