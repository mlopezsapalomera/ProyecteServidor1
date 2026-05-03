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
    <style>
        .endpoint-request-btn {
            background: linear-gradient(135deg, #ff006e 0%, #ffbe0b 100%);
            color: #fff;
            border: none;
            cursor: pointer;
        }

        .endpoint-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(18, 14, 28, 0.64);
            backdrop-filter: blur(12px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            padding: 20px;
        }

        .endpoint-modal-overlay.is-open {
            display: flex;
        }

        .endpoint-modal {
            width: min(100%, 720px);
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid rgba(255, 0, 110, 0.12);
            box-shadow: 0 24px 80px rgba(0, 0, 0, 0.28);
            padding: 28px;
            position: relative;
        }

        .endpoint-modal-close {
            position: absolute;
            top: 16px;
            right: 16px;
            border: none;
            background: transparent;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }

        .endpoint-modal-title {
            margin: 0 0 8px;
            font-size: 1.5rem;
            color: #1a1a1a;
        }

        .endpoint-modal-text {
            margin: 0 0 16px;
            color: #555;
            line-height: 1.5;
        }

        .endpoint-box {
            border-radius: 16px;
            padding: 14px 16px;
            background: rgba(255, 246, 250, 0.95);
            border: 1px solid rgba(255, 0, 110, 0.12);
            margin-bottom: 14px;
            word-break: break-all;
        }

        .endpoint-code {
            font-family: Consolas, 'Courier New', monospace;
            font-size: 0.95rem;
            color: #8a0040;
        }

        .endpoint-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 18px;
        }

        .endpoint-generate-btn,
        .endpoint-copy-btn,
        .endpoint-close-btn {
            border: none;
            border-radius: 14px;
            padding: 12px 18px;
            font-weight: 700;
            cursor: pointer;
        }

        .endpoint-generate-btn {
            background: linear-gradient(135deg, #ff006e 0%, #ffbe0b 100%);
            color: #fff;
        }

        .endpoint-copy-btn,
        .endpoint-close-btn {
            background: #f3f5f9;
            color: #333;
        }

        .endpoint-status {
            margin-top: 12px;
            font-size: 0.95rem;
            color: #666;
        }

        .endpoint-result {
            display: none;
            margin-top: 16px;
        }

        .endpoint-result.is-visible {
            display: block;
        }

        @media (max-width: 640px) {
            .endpoint-modal {
                padding: 20px;
            }

            .endpoint-actions {
                flex-direction: column;
            }

            .endpoint-generate-btn,
            .endpoint-copy-btn,
            .endpoint-close-btn {
                width: 100%;
            }
        }
    </style>
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
                <button type="button" class="btn-capturar endpoint-request-btn" id="openEndpointModalBtn">
                    <span class="icon">🔑</span>
                    <span>Solicitar acceso endpoint</span>
                </button>
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

    <?php if ($esMiPerfil): ?>
        <div class="endpoint-modal-overlay" id="endpointModalOverlay" aria-hidden="true">
            <div class="endpoint-modal" role="dialog" aria-modal="true" aria-labelledby="endpointModalTitle">
                <button type="button" class="endpoint-modal-close" id="closeEndpointModalBtn" aria-label="Cerrar">&times;</button>
                <h2 class="endpoint-modal-title" id="endpointModalTitle">Solicitar acceso al endpoint</h2>
                <p class="endpoint-modal-text">
                    Desde aquí puedes generar un token temporal para que otra persona consuma tu API desde Postman.
                    El token caduca automáticamente en 30 días y solo se muestra una vez.
                </p>

                <div class="endpoint-box">
                    <div><strong>Endpoint:</strong></div>
                    <div class="endpoint-code">http://localhost/ProyecteServidor1/controller/apiPokemons.controller.php</div>
                </div>

                <div class="endpoint-box">
                    <div><strong>Cabecera requerida:</strong></div>
                    <div class="endpoint-code">Authorization: Bearer &lt;tu_token&gt;</div>
                </div>

                <div class="endpoint-actions">
                    <button type="button" class="endpoint-generate-btn" id="generateEndpointTokenBtn">Generar token</button>
                    <button type="button" class="endpoint-copy-btn" id="copyEndpointUrlBtn">Copiar endpoint</button>
                    <button type="button" class="endpoint-close-btn" id="dismissEndpointModalBtn">Cerrar</button>
                </div>

                <p class="endpoint-status" id="endpointStatus">Pulsa generar para crear un token nuevo.</p>

                <div class="endpoint-result" id="endpointResult">
                    <div class="endpoint-box">
                        <div><strong>Token:</strong></div>
                        <div class="endpoint-code" id="endpointTokenValue"></div>
                    </div>
                    <div class="endpoint-box">
                        <div><strong>Expira el:</strong></div>
                        <div class="endpoint-code" id="endpointExpiresValue"></div>
                    </div>
                    <div class="endpoint-box">
                        <div><strong>Header para Postman:</strong></div>
                        <div class="endpoint-code" id="endpointHeaderValue"></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($esMiPerfil): ?>
    <script>
        const endpointModalOverlay = document.getElementById('endpointModalOverlay');
        const openEndpointModalBtn = document.getElementById('openEndpointModalBtn');
        const closeEndpointModalBtn = document.getElementById('closeEndpointModalBtn');
        const dismissEndpointModalBtn = document.getElementById('dismissEndpointModalBtn');
        const generateEndpointTokenBtn = document.getElementById('generateEndpointTokenBtn');
        const copyEndpointUrlBtn = document.getElementById('copyEndpointUrlBtn');
        const endpointStatus = document.getElementById('endpointStatus');
        const endpointResult = document.getElementById('endpointResult');
        const endpointTokenValue = document.getElementById('endpointTokenValue');
        const endpointExpiresValue = document.getElementById('endpointExpiresValue');
        const endpointHeaderValue = document.getElementById('endpointHeaderValue');
        const csrfTokenValue = <?= json_encode(csrfToken(), JSON_UNESCAPED_UNICODE) ?>;
        const endpointUrl = 'http://localhost/ProyecteServidor1/controller/apiPokemons.controller.php';

        function openEndpointModal() {
            endpointModalOverlay.classList.add('is-open');
            endpointModalOverlay.setAttribute('aria-hidden', 'false');
        }

        function closeEndpointModal() {
            endpointModalOverlay.classList.remove('is-open');
            endpointModalOverlay.setAttribute('aria-hidden', 'true');
        }

        async function generarTokenEndpoint() {
            endpointStatus.textContent = 'Generando token...';
            generateEndpointTokenBtn.disabled = true;

            try {
                const formData = new FormData();
                formData.append('_csrf_token', csrfTokenValue);

                const response = await fetch('controller/solicitarAccesoEndpoint.controller.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'No se pudo generar el token');
                }

                endpointTokenValue.textContent = data.token;
                endpointExpiresValue.textContent = data.expires_at;
                endpointHeaderValue.textContent = data.header;
                endpointStatus.textContent = data.message;
                endpointResult.classList.add('is-visible');
            } catch (error) {
                endpointStatus.textContent = error.message;
            } finally {
                generateEndpointTokenBtn.disabled = false;
            }
        }

        async function copiarEndpoint() {
            try {
                await navigator.clipboard.writeText(endpointUrl);
                endpointStatus.textContent = 'Endpoint copiado al portapapeles.';
            } catch (error) {
                endpointStatus.textContent = 'No se pudo copiar el endpoint.';
            }
        }

        openEndpointModalBtn.addEventListener('click', openEndpointModal);
        closeEndpointModalBtn.addEventListener('click', closeEndpointModal);
        dismissEndpointModalBtn.addEventListener('click', closeEndpointModal);
        generateEndpointTokenBtn.addEventListener('click', generarTokenEndpoint);
        copyEndpointUrlBtn.addEventListener('click', copiarEndpoint);

        endpointModalOverlay.addEventListener('click', function(event) {
            if (event.target === endpointModalOverlay) {
                closeEndpointModal();
            }
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeEndpointModal();
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>
