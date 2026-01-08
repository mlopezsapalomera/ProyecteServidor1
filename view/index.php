<?php
require_once __DIR__ . '/../controller/paginacio.controller.php';
require_once __DIR__ . '/../security/auth.php';
require_once __DIR__ . '/../model/user.php';

// Pequeño helper para escapar HTML
function e($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }

// Obtener datos completos del usuario actual si está autenticado
$usuarioCompleto = null;
if (estaIdentificado()) {
    $usuarioCompleto = obtenerUsuarioPorId(idUsuarioActual());
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
                            <a href="view/perfilUsuario.vista.php?id=<?= idUsuarioActual() ?>" class="dropdown-item">
                                <span class="dropdown-icon">👤</span>
                                Mi Perfil
                            </a>
                            <?php if (esAdmin()): ?>
                                <a href="view/adminPanel.vista.php" class="dropdown-item">
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
        <!-- Panel lateral izquierdo -->
        <div class="sidebar-left">
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
                <a href="view/insertar.vista.php" class="btn-capturar">
                    <span class="icon">⚡</span>
                    <span>Capturar Pokémon</span>
                </a>
            <?php else: ?>
                <a href="view/login.vista.php" class="btn-capturar" title="Inicia sesión para capturar">
                    <span class="icon">⚡</span>
                    <span>Capturar Pokémon</span>
                </a>
            <?php endif; ?>
            
            <!-- Selector de elementos por página -->
            <form method="get" action="index.php" class="per-page-selector">
                <label for="perPage">Pokémons por página:</label>
                <select name="perPage" id="perPage" onchange="this.form.submit()">
                    <option value="2" <?= $perPage==2?'selected':'' ?>>2</option>
                    <option value="5" <?= $perPage==5?'selected':'' ?>>5</option>
                    <option value="10" <?= $perPage==10?'selected':'' ?>>10</option>
                    <option value="20" <?= $perPage==20?'selected':'' ?>>20</option>
                </select>
                <!-- Si hay otros parámetros, mantenerlos excepto page -->
                <?php foreach($_GET as $k=>$v) {
                    if($k !== 'perPage' && $k !== 'page') { ?>
                        <input type="hidden" name="<?= e($k) ?>" value="<?= e($v) ?>">
                <?php }
                } ?>
                <input type="hidden" name="page" value="1">
            </form>
            
            <!-- Selector de ordenación -->
            <form method="get" action="index.php" class="per-page-selector">
                <label for="orderBy">Ordenar por:</label>
                <select name="orderBy" id="orderBy" onchange="this.form.submit()">
                    <option value="id" <?= $orderBy=='id'?'selected':'' ?>>ID</option>
                    <option value="titulo" <?= $orderBy=='titulo'?'selected':'' ?>>Título</option>
                    <option value="created_at" <?= $orderBy=='created_at'?'selected':'' ?>>Fecha</option>
                </select>
                
                <label for="orderDir">Dirección:</label>
                <select name="orderDir" id="orderDir" onchange="this.form.submit()">
                    <option value="ASC" <?= $orderDir=='ASC'?'selected':'' ?>>↑ Ascendente</option>
                    <option value="DESC" <?= $orderDir=='DESC'?'selected':'' ?>>↓ Descendente</option>
                </select>
                
                <!-- Mantener otros parámetros -->
                <?php foreach($_GET as $k=>$v) {
                    if($k !== 'orderBy' && $k !== 'orderDir' && $k !== 'page') { ?>
                        <input type="hidden" name="<?= e($k) ?>" value="<?= e($v) ?>">
                <?php }
                } ?>
                <input type="hidden" name="page" value="1">
            </form>
        </div>
        
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
                <div class="post-card">
                    <?php if (isset($row['autor_profile_image']) && isset($row['autor_id'])): ?>
                        <a href="view/perfilUsuario.vista.php?id=<?= e($row['autor_id']) ?>" style="text-decoration: none;">
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
                                    <a href="view/perfilUsuario.vista.php?id=<?= e($row['autor_id']) ?>" style="color: #9C27B0; text-decoration: none; font-weight: bold;">
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
                                <a class="post-btn edit" href="view/modificar.vista.php?id=<?= e($row['id']) ?>" title="Editar">
                                    &#x270F;&#xFE0F;
                                </a>
                                <a class="post-btn delete" href="controller/eliminar.controller.php?id=<?= e($row['id']) ?>"
                                   onclick="return confirm('¿Seguro que quieres eliminar este Pokémon? Esta acción no se puede deshacer.');" title="Eliminar">
                                    &#x1F5D1;&#xFE0F;
                                </a>
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
                    $baseParams = $_GET;
                    $baseParams['perPage'] = $perPage;
                    // Anterior
                    $prevDisabled = ($page <= 1);
                    $prevPage = max(1, $page - 1);
                    $baseParams['page'] = $prevPage;
                    $prevUrl = 'index.php?' . http_build_query($baseParams);
                ?>
                <a href="<?= e($prevUrl) ?>" class="<?= $prevDisabled ? 'disabled' : '' ?>">Anterior</a>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php
                        $params = $_GET;
                        $params['page'] = $i;
                        $params['perPage'] = $perPage;
                        $url = 'index.php?' . http_build_query($params);
                    ?>
                    <a href="<?= e($url) ?>" class="<?= $i == $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php
                    // Siguiente
                    $nextDisabled = ($page >= $totalPages);
                    $nextPage = min($totalPages, $page + 1);
                    $baseParams['page'] = $nextPage;
                    $nextUrl = 'index.php?' . http_build_query($baseParams);
                ?>
                <a href="<?= e($nextUrl) ?>" class="<?= $nextDisabled ? 'disabled' : '' ?>">Siguiente</a>
            </div>
        </div>
    </div>

    <script>
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
                            <a href="view/perfilUsuario.vista.php?id=${usuario.id}" class="search-result-item">
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
                            <a href="view/perfilUsuario.vista.php?id=${pub.autor_id}" class="search-result-item">
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