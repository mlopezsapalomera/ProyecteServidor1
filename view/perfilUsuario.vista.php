<?php

// Helper para escapar HTML
function e($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }

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
                    <a href="controller/perfilUsuarioPage.controller.php?id=<?= idUsuarioActual() ?>" class="nav-user" style="text-decoration: none; color: inherit;">
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
        <!-- Panel lateral izquierdo - Navegación -->
        <div class="sidebar-left">
            <a href="index.php" class="btn-capturar" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <span class="icon">🏠</span>
                <span>Volver al Inicio</span>
            </a>
            
            <?php if ($esMiPerfil): ?>
                <a href="controller/insertarPage.controller.php" class="btn-capturar">
                    <span class="icon">⚡</span>
                    <span>Capturar Pokémon</span>
                </a>
                <a href="controller/modificarPerfilPage.controller.php" class="btn-capturar" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <span class="icon">✏️</span>
                    <span>Editar Perfil</span>
                </a>
            <?php endif; ?>
        </div>
        
        <!-- Contenedor central de perfil -->
        <div class="posts-container profile-center">
            <div class="posts-scroll">
                <!-- Header del perfil mejorado -->
                <div class="profile-header-enhanced">
                    <div class="profile-banner"></div>
                    <div class="profile-main-info">
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
                        <div class="profile-details">
                            <h1 class="profile-username-large"><?= e($perfilUsuario['username']) ?></h1>
                            <?php if ($esMiPerfil): ?>
                                <p class="profile-email-text">📧 <?= e($perfilUsuario['email']) ?></p>
                            <?php endif; ?>
                            <p class="profile-joined">📅 Miembro desde <?= date('M Y', strtotime($perfilUsuario['created_at'])) ?></p>
                        </div>
                    </div>
                </div>

                <h2 class="section-title-profile">📱 Publicaciones de <?= e($perfilUsuario['username']) ?></h2>

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
                                        <a class="post-btn edit" href="controller/modificarPage.controller.php?id=<?= e($row['id']) ?>" title="Editar">
                                            &#x270F;&#xFE0F;
                                        </a>
                                        <form action="controller/eliminar.controller.php" method="post" style="display:inline;" onsubmit="return confirm('¿Seguro que quieres eliminar este Pokémon?');">
                                            <?= csrfInput() ?>
                                            <input type="hidden" name="id" value="<?= e($row['id']) ?>">
                                            <button class="post-btn delete" type="submit" title="Eliminar">&#x1F5D1;&#xFE0F;</button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Paginación -->
            <?php if ($totalPaginas > 1): ?>
                <div class="pagination pagination-fixed">
                    <?php
                        $parametrosBase = $_GET;
                        $parametrosBase['porPagina'] = $porPagina;
                        $parametrosBase['id'] = $perfilUserId;
                        
                        $paginaAnteriorDeshabilitada = ($pagina <= 1);
                        $paginaAnterior = max(1, $pagina - 1);
                        $parametrosBase['pagina'] = $paginaAnterior;
                        $urlAnterior = 'controller/perfilUsuarioPage.controller.php?' . http_build_query($parametrosBase);
                    ?>
                    <a href="<?= e($urlAnterior) ?>" class="<?= $paginaAnteriorDeshabilitada ? 'disabled' : '' ?>">Anterior</a>

                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                        <?php
                            $parametros = $parametrosBase;
                            $parametros['pagina'] = $i;
                            $url = 'controller/perfilUsuarioPage.controller.php?' . http_build_query($parametros);
                        ?>
                        <a href="<?= e($url) ?>" class="<?= $i == $pagina ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php
                        $paginaSiguienteDeshabilitada = ($pagina >= $totalPaginas);
                        $paginaSiguiente = min($totalPaginas, $pagina + 1);
                        $baseParams['page'] = $nextPage;
                        $nextUrl = 'controller/perfilUsuarioPage.controller.php?' . http_build_query($baseParams);
                    ?>
                    <a href="<?= e($nextUrl) ?>" class="<?= $nextDisabled ? 'disabled' : '' ?>">Siguiente</a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Panel lateral derecho - Estadísticas -->
        <div class="sidebar-right profile-stats-sidebar">
            <div class="stats-card">
                <h3 class="stats-title">📊 Estadísticas</h3>
                <div class="stat-item">
                    <span class="stat-icon">📝</span>
                    <div class="stat-info">
                        <span class="stat-value"><?= $totalPokemons ?></span>
                        <span class="stat-label">Publicaciones</span>
                    </div>
                </div>
                <div class="stat-item">
                    <span class="stat-icon">📅</span>
                    <div class="stat-info">
                        <span class="stat-value"><?= date('d/m/Y', strtotime($perfilUsuario['created_at'])) ?></span>
                        <span class="stat-label">Fecha de registro</span>
                    </div>
                </div>
                <?php if ($esMiPerfil): ?>
                    <div class="stat-item">
                        <span class="stat-icon">👤</span>
                        <div class="stat-info">
                            <span class="stat-value"><?= ucfirst($perfilUsuario['role']) ?></span>
                            <span class="stat-label">Rol</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($totalPokemons > 0): ?>
                <div class="stats-card">
                    <h3 class="stats-title">🎯 Actividad</h3>
                    <div class="activity-info">
                        <p class="activity-text">
                            <?php if ($totalPokemons === 1): ?>
                                Ha capturado <strong>1 Pokémon</strong>
                            <?php else: ?>
                                Ha capturado <strong><?= $totalPokemons ?> Pokémons</strong>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
