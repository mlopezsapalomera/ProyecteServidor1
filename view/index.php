<?php

// Pequeño helper para escapar HTML
function e($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <base href="/ProyecteServidor1/">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PokéNet Social - Red Social Pokémon</title>
    <link rel="icon" type="image/jpeg" href="assets/img/fondo.jpg">
    <link rel="stylesheet" href="style/styles.css?v=<?= time() ?>">
</head>
<body class="no-page-scroll">
    <!-- Navbar tipo Instagram -->
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">🌟 PokéNet</div>
            <div class="navbar-actions">
                <?php if (estaIdentificado() && $usuarioCompleto): ?>
                    <!-- Dropdown de usuario con foto de perfil -->
                    <div class="user-dropdown">
                        <button class="user-dropdown-toggle" onclick="toggleUserDropdown()">
                            <img src="<?php 
                                    $imgPath = $usuarioCompleto['profile_image'];
                                    if ($imgPath === 'userDefaultImg.jpg') {
                                        echo 'assets/img/imgProfileuser/' . e($imgPath);
                                    } else {
                                        echo 'assets/img/userImg/' . e($imgPath);
                                    }
                                 ?>" 
                                 alt="<?= e($usuarioCompleto['username']) ?>" 
                                 class="user-dropdown-avatar"
                                 onerror="this.src='assets/img/imgProfileuser/userDefaultImg.jpg'">
                            <span class="user-dropdown-name"><?= e($usuarioCompleto['username']) ?></span>
                            <span class="user-dropdown-arrow">▼</span>
                        </button>
                        <div class="user-dropdown-menu" id="userDropdownMenu">
                            <a href="controller/perfilUsuarioPage.controller.php?id=<?= idUsuarioActual() ?>" class="dropdown-item">
                                <span class="dropdown-icon">👤</span>
                                Mi Perfil
                            </a>
                            <?php if (esAdmin()): ?>
                                <a href="controller/adminPanel.controller.php" class="dropdown-item">
                                    <span class="dropdown-icon">👨‍💼</span>
                                    Panel de Usuarios
                                </a>
                            <?php endif; ?>
                            <div class="dropdown-divider"></div>
                            <a href="controller/logout.controller.php" class="dropdown-item logout">
                                <span class="dropdown-icon">🚪</span>
                                Cerrar Sesión
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="view/login.vista.php" class="nav-btn login">Iniciar sesión</a>
                    <a href="view/register.vista.php" class="nav-btn register">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="main-wrapper">
        <!-- Panel lateral izquierdo - Controles de visualización -->
        <div class="sidebar-left">
            <!-- Selector de elementos por página -->
            <form method="get" action="index.php" class="per-page-selector">
                <label for="porPagina">Pokémons por página:</label>
                <select name="porPagina" id="porPagina" onchange="this.form.submit()">
                    <option value="2" <?= $porPagina==2?'selected':'' ?>>2</option>
                    <option value="5" <?= $porPagina==5?'selected':'' ?>>5</option>
                    <option value="10" <?= $porPagina==10?'selected':'' ?>>10</option>
                    <option value="20" <?= $porPagina==20?'selected':'' ?>>20</option>
                </select>
                <!-- Si hay otros parámetros, mantenerlos excepto pagina -->
                <?php foreach($_GET as $k=>$v) {
                    if($k !== 'porPagina' && $k !== 'pagina') { ?>
                        <input type="hidden" name="<?= e($k) ?>" value="<?= e($v) ?>">
                <?php }
                } ?>
                <input type="hidden" name="pagina" value="1">
            </form>
            
            <!-- Selector de ordenación -->
            <form method="get" action="index.php" class="per-page-selector">
                <label for="ordenarPor">Ordenar por:</label>
                <select name="ordenarPor" id="ordenarPor" onchange="this.form.submit()">
                    <option value="id" <?= $ordenarPor=='id'?'selected':'' ?>>ID</option>
                    <option value="titulo" <?= $ordenarPor=='titulo'?'selected':'' ?>>Título</option>
                    <option value="created_at" <?= $ordenarPor=='created_at'?'selected':'' ?>>Fecha</option>
                </select>
                
                <label for="direccionOrden">Dirección:</label>
                <select name="direccionOrden" id="direccionOrden" onchange="this.form.submit()">
                    <option value="ASC" <?= $direccionOrden=='ASC'?'selected':'' ?>>↑ Ascendente</option>
                    <option value="DESC" <?= $direccionOrden=='DESC'?'selected':'' ?>>↓ Descendente</option>
                </select>
                
                <!-- Mantener otros parámetros -->
                <?php foreach($_GET as $k=>$v) {
                    if($k !== 'ordenarPor' && $k !== 'direccionOrden' && $k !== 'pagina') { ?>
                        <input type="hidden" name="<?= e($k) ?>" value="<?= e($v) ?>">
                <?php }
                } ?>
                <input type="hidden" name="pagina" value="1">
            </form>
        </div>
        
        <!-- Contenedor central de posts -->
        <div class="posts-container">
            <div class="posts-scroll">
        <?php if ($ok): ?>
            <div class="alert success">✅ <?= e($ok) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert error">❌ <?= e($error) ?></div>
        <?php endif; ?>

    <?php if ($pokemons === false): ?>
            <div class="empty">
                <h3>⚠️ Error de conexión</h3>
                <p>No se pudo obtener la lista. Revisa la conexión y que exista la tabla <strong>pokemons</strong>.</p>
            </div>
    <?php elseif (count($pokemons) === 0): ?>
            <div class="empty">
                <h3>🔍 ¡La aventura comienza aquí!</h3>
                <p>Sé el primero en compartir tu Pokémon en PokéNet Social. ¡Empieza tu colección ahora!</p>
            </div>
        <?php else: ?>
            <!-- Posts tipo Instagram -->
            <?php foreach ($pokemons as $row): ?>
                <div class="post-card" onclick="abrirModalPublicacion(<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>)">
                    <?php if (isset($row['autor_profile_image']) && isset($row['autor_id'])): ?>
                        <a href="controller/perfilUsuarioPage.controller.php?id=<?= e($row['autor_id']) ?>" style="text-decoration: none;" onclick="event.stopPropagation();">
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
                        </a>
                    <?php else: ?>
                        <div class="post-avatar"><?= e(strtoupper(substr($row['titulo'], 0, 1))) ?></div>
                    <?php endif; ?>
                    <div class="post-main">
                        <div class="post-header">
                            <span class="post-username"><?= e($row['titulo']) ?></span>
                            <span class="post-id">#<?= e($row['id']) ?></span>
                        </div>
                        <div class="post-meta">
                            <small class="post-author">
                                Publicado por: 
                                <?php if (isset($row['autor_id'])): ?>
                                    <a href="controller/perfilUsuarioPage.controller.php?id=<?= e($row['autor_id']) ?>" style="color: #9C27B0; text-decoration: none; font-weight: bold;" onclick="event.stopPropagation();">
                                        <?= e($row['autor_username'] ?? 'Anónimo') ?>
                                    </a>
                                <?php else: ?>
                                    <?= e($row['autor_username'] ?? 'Anónimo') ?>
                                <?php endif; ?>
                            </small>
                        </div>
                        <div class="post-title">🐾 <?= e($row['titulo']) ?></div>
                        <?php if ($row['descripcion']): ?>
                            <div class="post-description">📝 <?= e($row['descripcion']) ?></div>
                        <?php endif; ?>
                        <div class="post-actions post-actions-right">
                            <?php if (estaIdentificado() && isset($row['user_id']) && (int)$row['user_id'] === idUsuarioActual()): ?>
                                <a class="post-btn edit" href="controller/modificarPage.controller.php?id=<?= e($row['id']) ?>" title="Editar" onclick="event.stopPropagation();">
                                    &#x270F;&#xFE0F;
                                </a>
                                <form action="controller/eliminar.controller.php" method="post" style="display:inline;" onsubmit="event.stopPropagation(); return confirm('¿Seguro que quieres eliminar este Pokémon? Esta acción no se puede deshacer.');">
                                    <?= csrfInput() ?>
                                    <input type="hidden" name="id" value="<?= e($row['id']) ?>">
                                    <button class="post-btn delete" type="submit" title="Eliminar">&#x1F5D1;&#xFE0F;</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
            </div>
            
            <!-- Paginación fija -->
            <div class="pagination pagination-fixed">
                <?php
                    // Enlaces Anterior / Siguiente con preservación de parámetros
                    $parametrosBase = $_GET;
                    $parametrosBase['porPagina'] = $porPagina;
                    // Anterior
                    $paginaAnteriorDeshabilitada = ($pagina <= 1);
                    $paginaAnterior = max(1, $pagina - 1);
                    $parametrosBase['pagina'] = $paginaAnterior;
                    $urlAnterior = 'index.php?' . http_build_query($parametrosBase);
                ?>
                <a href="<?= e($urlAnterior) ?>" class="<?= $paginaAnteriorDeshabilitada ? 'disabled' : '' ?>">Anterior</a>

                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                    <?php
                        $parametros = $_GET;
                        $parametros['pagina'] = $i;
                        $parametros['porPagina'] = $porPagina;
                        $url = 'index.php?' . http_build_query($parametros);
                    ?>
                    <a href="<?= e($url) ?>" class="<?= $i == $pagina ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php
                    // Siguiente
                    $paginaSiguienteDeshabilitada = ($pagina >= $totalPaginas);
                    $paginaSiguiente = min($totalPaginas, $pagina + 1);
                    $parametrosBase['pagina'] = $paginaSiguiente;
                    $urlSiguiente = 'index.php?' . http_build_query($parametrosBase);
                ?>
                <a href="<?= e($urlSiguiente) ?>" class="<?= $paginaSiguienteDeshabilitada ? 'disabled' : '' ?>">Siguiente</a>
            </div>
        </div>
        
        <!-- Panel lateral derecho - Búsqueda y Acciones -->
        <div class="sidebar-right">
            <!-- Barra de búsqueda -->
            <div class="search-container">
                <div class="search-box">
                    <span class="search-icon">🔍</span>
                    <input type="text" 
                           id="searchInput" 
                           placeholder="Buscar usuarios o pokémons..." 
                           autocomplete="off">
                    <button type="button" id="clearSearch" class="clear-search" style="display: none;">✕</button>
                </div>
                
                <!-- Contenedor de resultados -->
                <div id="searchResults" class="search-results" style="display: none;">
                    <div class="search-loading" id="searchLoading" style="display: none;">
                        <span class="loader"></span>
                        <span>Buscando...</span>
                    </div>
                    <div id="searchContent"></div>
                </div>
            </div>
            
            <!-- Botón insertar (solo para usuarios autenticados) -->
            <?php if (estaIdentificado()): ?>
                <a href="controller/insertarPage.controller.php" class="btn-capturar">
                    <span class="icon">⚡</span>
                    <span>Capturar Pokémon</span>
                </a>
            <?php else: ?>
                <a href="view/login.vista.php" class="btn-capturar" title="Inicia sesión para capturar">
                    <span class="icon">⚡</span>
                    <span>Capturar Pokémon</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para ver publicación completa -->
    <div id="modalPublicacion" class="modal-overlay" style="display: none;" onclick="cerrarModalPublicacion()">
        <div class="modal-content" onclick="event.stopPropagation()">
            <button class="modal-close" onclick="cerrarModalPublicacion()">✕</button>
            <div class="modal-header">
                <a id="modalAvatarLink" href="#" onclick="event.stopPropagation();">
                    <img id="modalAvatar" src="" alt="" class="modal-avatar">
                </a>
                <div class="modal-user-info">
                    <h3 id="modalUsername" class="modal-username"></h3>
                    <p class="modal-autor">
                        Publicado por: 
                        <a id="modalAutorLink" href="#" class="modal-autor-link" onclick="event.stopPropagation();">
                            <span id="modalAutor"></span>
                        </a>
                    </p>
                </div>
            </div>
            <div class="modal-body">
                <h2 id="modalTitulo" class="modal-titulo"></h2>
                <p id="modalDescripcion" class="modal-descripcion"></p>
                <p id="modalFecha" class="modal-fecha"></p>
            </div>
            <div id="modalActions" class="modal-actions" style="display: none;">
                <a id="modalEditBtn" href="#" class="modal-btn edit">
                    <span>✏️</span> Editar
                </a>
                <button id="modalDeleteBtn" type="button" class="modal-btn delete">
                    <span>🗑️</span> Eliminar
                </button>
            </div>
        </div>
    </div>

    <form id="deletePokemonForm" action="controller/eliminar.controller.php" method="post" style="display:none;">
        <?= csrfInput() ?>
        <input type="hidden" name="id" id="deletePokemonId" value="">
    </form>

    <script>
    // ===== MODAL DE PUBLICACIÓN =====
    function abrirModalPublicacion(post) {
        const modal = document.getElementById('modalPublicacion');
        const avatarLink = document.getElementById('modalAvatarLink');
        const avatar = document.getElementById('modalAvatar');
        const username = document.getElementById('modalUsername');
        const autorLink = document.getElementById('modalAutorLink');
        const autor = document.getElementById('modalAutor');
        const titulo = document.getElementById('modalTitulo');
        const descripcion = document.getElementById('modalDescripcion');
        const fecha = document.getElementById('modalFecha');
        const actions = document.getElementById('modalActions');
        const editBtn = document.getElementById('modalEditBtn');
        const deleteBtn = document.getElementById('modalDeleteBtn');

        // Configurar imagen de avatar
        let imgSrc = 'assets/img/imgProfileuser/userDefaultImg.jpg';
        if (post.autor_profile_image) {
            if (post.autor_profile_image === 'userDefaultImg.jpg') {
                imgSrc = 'assets/img/imgProfileuser/' + post.autor_profile_image;
            } else {
                imgSrc = 'assets/img/userImg/' + post.autor_profile_image;
            }
        }
        avatar.src = imgSrc;
        avatar.alt = post.autor_username || 'Usuario';

        // Configurar enlaces al perfil del autor
        if (post.autor_id) {
            const perfilUrl = 'controller/perfilUsuarioPage.controller.php?id=' + post.autor_id;
            avatarLink.href = perfilUrl;
            autorLink.href = perfilUrl;
        } else {
            avatarLink.href = '#';
            autorLink.href = '#';
        }

        // Configurar contenido
        username.textContent = post.titulo;
        autor.textContent = post.autor_username || 'Anónimo';
        titulo.innerHTML = '🐾 ' + post.titulo;
        descripcion.innerHTML = post.descripcion ? '📝 ' + post.descripcion : '<em style="color: #999;">Sin descripción</em>';
        
        // Formatear fecha si existe
        if (post.created_at) {
            const fechaObj = new Date(post.created_at);
            fecha.textContent = '📅 ' + fechaObj.toLocaleDateString('es-ES', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } else {
            fecha.textContent = '';
        }

        // Mostrar botones de acción si es el dueño
        <?php if (estaIdentificado()): ?>
            const currentUserId = <?= idUsuarioActual() ?>;
            if (post.user_id && parseInt(post.user_id) === currentUserId) {
                actions.style.display = 'flex';
                editBtn.href = 'controller/modificarPage.controller.php?id=' + post.id;
                deleteBtn.onclick = function() {
                    eliminarPokemonDesdeModal(post.id);
                };
            } else {
                actions.style.display = 'none';
            }
        <?php else: ?>
            actions.style.display = 'none';
        <?php endif; ?>

        // Mostrar modal
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function cerrarModalPublicacion() {
        const modal = document.getElementById('modalPublicacion');
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }

    function eliminarPokemonDesdeModal(id) {
        if (!confirm('¿Seguro que quieres eliminar este Pokémon? Esta acción no se puede deshacer.')) {
            return;
        }

        document.getElementById('deletePokemonId').value = id;
        document.getElementById('deletePokemonForm').submit();
    }

    // Cerrar modal con tecla ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarModalPublicacion();
        }
    });

    // Limpia ?ok y ?error de la URL tras mostrar el mensaje
    if (window.location.search.match(/[?&](ok|error)=/)) {
        setTimeout(() => {
            window.history.replaceState({}, document.title, window.location.pathname);
        }, 100);
    }

    // Toggle del dropdown de usuario
    function toggleUserDropdown() {
        const menu = document.getElementById('userDropdownMenu');
        menu.classList.toggle('show');
    }

    // Cerrar dropdown al hacer click fuera
    window.onclick = function(event) {
        if (!event.target.matches('.user-dropdown-toggle') && 
            !event.target.matches('.user-dropdown-avatar') && 
            !event.target.matches('.user-dropdown-name') &&
            !event.target.matches('.user-dropdown-arrow')) {
            const dropdowns = document.getElementsByClassName('user-dropdown-menu');
            for (let i = 0; i < dropdowns.length; i++) {
                const openDropdown = dropdowns[i];
                if (openDropdown.classList.contains('show')) {
                    openDropdown.classList.remove('show');
                }
            }
        }
    }

    // ===== BÚSQUEDA AJAX =====
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    const searchContent = document.getElementById('searchContent');
    const searchLoading = document.getElementById('searchLoading');
    const clearSearch = document.getElementById('clearSearch');
    let searchTimeout = null;

    // Función para realizar búsqueda
    async function realizarBusqueda(query) {
        if (query.length < 2) {
            searchResults.style.display = 'none';
            return;
        }

        // Mostrar loading
        searchLoading.style.display = 'flex';
        searchResults.style.display = 'block';
        searchContent.innerHTML = '';

        try {
            const response = await fetch(`controller/buscar.controller.php?q=${encodeURIComponent(query)}`);
            const data = await response.json();

            searchLoading.style.display = 'none';

            if (data.success && data.total > 0) {
                let html = '';

                // Mostrar usuarios
                if (data.usuarios && data.usuarios.length > 0) {
                    html += '<div class="search-section"><h4>👥 Usuarios</h4>';
                    data.usuarios.forEach(usuario => {
                        const imgSrc = usuario.profile_image === 'userDefaultImg.jpg' 
                            ? `assets/img/imgProfileuser/${usuario.profile_image}`
                            : `assets/img/userImg/${usuario.profile_image}`;
                        
                        html += `
                            <a href="controller/perfilUsuarioPage.controller.php?id=${usuario.id}" class="search-result-item">
                                <img src="${imgSrc}" alt="${usuario.username}" class="search-avatar" onerror="this.src='assets/img/imgProfileuser/userDefaultImg.jpg'">
                                <div class="search-info">
                                    <div class="search-name">${usuario.username}</div>
                                    <div class="search-type">Usuario</div>
                                </div>
                            </a>
                        `;
                    });
                    html += '</div>';
                }

                // Mostrar publicaciones
                if (data.publicaciones && data.publicaciones.length > 0) {
                    html += '<div class="search-section"><h4>🐾 Publicaciones</h4>';
                    data.publicaciones.forEach(pub => {
                        const imgSrc = pub.autor_profile_image === 'userDefaultImg.jpg' 
                            ? `assets/img/imgProfileuser/${pub.autor_profile_image}`
                            : `assets/img/userImg/${pub.autor_profile_image}`;
                        
                        html += `
                            <a href="controller/perfilUsuarioPage.controller.php?id=${pub.autor_id}" class="search-result-item">
                                <img src="${imgSrc}" alt="${pub.autor_username}" class="search-avatar" onerror="this.src='assets/img/imgProfileuser/userDefaultImg.jpg'">
                                <div class="search-info">
                                    <div class="search-name">${pub.titulo}</div>
                                    <div class="search-type">Por ${pub.autor_username}</div>
                                </div>
                            </a>
                        `;
                    });
                    html += '</div>';
                }

                searchContent.innerHTML = html;
            } else {
                searchContent.innerHTML = '<div class="search-empty">😕 No se encontraron resultados</div>';
            }
        } catch (error) {
            searchLoading.style.display = 'none';
            searchContent.innerHTML = '<div class="search-error">❌ Error al buscar. Inténtalo de nuevo.</div>';
        }
    }

    // Event listener para el input de búsqueda
    searchInput.addEventListener('input', function(e) {
        const query = e.target.value.trim();
        
        // Mostrar/ocultar botón de limpiar
        clearSearch.style.display = query.length > 0 ? 'block' : 'none';

        // Limpiar timeout anterior
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        // Si está vacío, ocultar resultados
        if (query.length === 0) {
            searchResults.style.display = 'none';
            return;
        }

        // Esperar 300ms antes de buscar (debounce)
        searchTimeout = setTimeout(() => {
            realizarBusqueda(query);
        }, 300);
    });

    // Botón para limpiar búsqueda
    clearSearch.addEventListener('click', function() {
        searchInput.value = '';
        clearSearch.style.display = 'none';
        searchResults.style.display = 'none';
    });

    // Cerrar resultados al hacer click fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-container')) {
            searchResults.style.display = 'none';
        }
    });

    // Reabrir resultados al hacer focus en el input si hay texto
    searchInput.addEventListener('focus', function() {
        if (searchInput.value.trim().length >= 2) {
            searchResults.style.display = 'block';
        }
    });
    </script>
</body>
</html>