<?php
require_once __DIR__ . '/../controller/paginacio.controller.php';
require_once __DIR__ . '/../security/auth.php';

// Pequeño helper para escapar HTML
function e($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }

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
    <link rel="stylesheet" href="style/styles.css">
</head>
<body class="no-page-scroll">
    <!-- Navbar tipo Instagram -->
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">🌟 PokéNet</div>
            <div class="navbar-actions">
                <?php if (estaIdentificat()): ?>
                    <span class="nav-user"><?= e(usuariActual()['username']) ?></span>
                    <a href="controller/logout.controller.php" class="nav-btn">Tancar sessió</a>
                <?php else: ?>
                    <a href="view/login.vista.php" class="nav-btn login">Iniciar Sessió</a>
                    <a href="view/register.vista.php" class="nav-btn register">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="main-wrapper">
        <!-- Panel lateral izquierdo -->
        <div class="sidebar-left">
            <!-- Botón insertar (solo para usuarios autenticados) -->
            <?php if (estaIdentificat()): ?>
                <a href="view/insertar.vista.php" class="btn-capturar">
                    <span class="icon">⚡</span>
                    <span>Capturar Pokémon</span>
                </a>
            <?php else: ?>
                <a href="view/login.vista.php" class="btn-capturar" title="Inicia sessió per capturar">
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
                    <div class="post-avatar"><?= e(strtoupper(substr($row['titulo'], 0, 1))) ?></div>
                    <div class="post-main">
                        <div class="post-header">
                            <span class="post-username"><?= e($row['titulo']) ?></span>
                            <span class="post-id">#<?= e($row['id']) ?></span>
                        </div>
                        <div class="post-meta">
                            <small class="post-author">Publicat per: <?= e($row['autor_username'] ?? 'Anònim') ?></small>
                        </div>
                        <div class="post-title">🐾 <?= e($row['titulo']) ?></div>
                        <?php if ($row['descripcion']): ?>
                            <div class="post-description">📝 <?= e($row['descripcion']) ?></div>
                        <?php endif; ?>
                        <div class="post-actions post-actions-right">
                            <?php if (estaIdentificat() && isset($row['user_id']) && (int)$row['user_id'] === idUsuariActual()): ?>
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
    </script>
</body>
</html>