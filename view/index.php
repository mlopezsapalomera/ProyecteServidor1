<?php
require_once __DIR__ . '/../model/pokemon.php';

// Pequeño helper para escapar HTML
function e($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }

$result = getAllPokemons(200, 0);
$ok = isset($_GET['ok']) ? $_GET['ok'] : null;
$error = isset($_GET['error']) ? $_GET['error'] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <base href="/pt02_lopez_marcos/">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PokéNet Social - Red Social Pokémon</title>
    <link rel="stylesheet" href="style/styles.css">
</head>
<body>
    <!-- Navbar tipo Instagram -->
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">🌟 PokéNet</div>
            <div class="navbar-actions">
                <a href="#" class="nav-btn login">Iniciar Sesión</a>
                <a href="#" class="nav-btn register">Registrarse</a>
            </div>
        </div>
    </nav>

    <!-- Contenedor principal -->
    <div class="container">
        <?php if ($ok): ?>
            <div class="alert success">✅ <?= e($ok) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert error">❌ <?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($result === false): ?>
            <div class="empty">
                <h3>⚠️ Error de conexión</h3>
                <p>No se pudo obtener la lista. Revisa la conexión y que exista la tabla <strong>pokemons</strong>.</p>
            </div>
        <?php elseif (count($result) === 0): ?>
            <div class="empty">
                <h3>🔍 ¡La aventura comienza aquí!</h3>
                <p>Sé el primero en compartir tu Pokémon en PokéNet Social. ¡Empieza tu colección ahora!</p>
            </div>
        <?php else: ?>
            <!-- Posts tipo Instagram -->
            <?php foreach ($result as $row): ?>
                <div class="post-card">
                    <div class="post-avatar"><?= e(strtoupper(substr($row['titulo'], 0, 1))) ?></div>
                    <div class="post-main">
                        <div class="post-header">
                            <span class="post-username"><?= e($row['titulo']) ?></span>
                            <span class="post-id">#<?= e($row['id']) ?></span>
                        </div>
                        <div class="post-title">🐾 <?= e($row['titulo']) ?></div>
                        <?php if ($row['descripcion']): ?>
                            <div class="post-description">📝 <?= e($row['descripcion']) ?></div>
                        <?php endif; ?>
                        <div class="post-actions post-actions-right">
                            <a class="post-btn edit" href="view/modificar.vista.php?id=<?= e($row['id']) ?>" title="Editar">
                                &#x270F;&#xFE0F;
                            </a>
                            <a class="post-btn delete" href="controller/eliminar.controller.php?id=<?= e($row['id']) ?>"
                               onclick="return confirm('¿Seguro que quieres eliminar este Pokémon? Esta acción no se puede deshacer.');" title="Eliminar">
                                &#x1F5D1;&#xFE0F;
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Footer fijo con botón insertar -->
    <footer class="footer-insertar">
        <a href="view/insertar.vista.php" class="footer-insertar-btn">
            <span class="footer-insertar-icon">⚡</span>
            <span class="footer-insertar-text">¿Qué Pokémon has capturado hoy?</span>
        </a>
    </footer>

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