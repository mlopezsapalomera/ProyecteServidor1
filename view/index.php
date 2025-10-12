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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PokéNet Social - Red Social Pokémon</title>
    <link rel="stylesheet" href="../style/styles.css">
    </head>
<body>
    <div class="container">
        <div class="header">
            <h1>PokéNet Social</h1>
            <p class="subtitle">Descubre y comparte tus Pokémon favoritos</p>
        </div>
        
        <div class="content">
            <div class="actions">
                <a class="button primary" href="/ProyecteServidor1/view/insertar.vista.php">
                    ⚡ Compartir tu Pokémon
                </a>
            </div>

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
            <?php else: ?>
                <?php if (count($result) === 0): ?>
                    <div class="empty">
                        <h3>🔍 ¡La aventura comienza aquí!</h3>
                        <p>Sé el primero en compartir tu Pokémon en PokéNet Social. ¡Empieza tu colección ahora!</p>
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>🆔 ID</th>
                                    <th>🎯 Nombre del Pokémon</th>
                                    <th>📝 Historia/Descripción</th>
                                    <th>⚡ Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($result as $row): ?>
                                <tr>
                                    <td class="id-column">#<?= e($row['id']) ?></td>
                                    <td class="title-column"><?= e($row['titulo']) ?></td>
                                    <td class="description-column"><?= e($row['descripcion']) ?></td>
                                    <td class="actions-column">
                                        <a class="button secondary" href="/ProyecteServidor1/view/modificar.vista.php?id=<?= e($row['id']) ?>">
                                            🔧 Editar
                                        </a>
                                        <a class="button secondary" href="/ProyecteServidor1/controller/eliminar.controller.php?id=<?= e($row['id']) ?>" onclick="return confirm('¿Seguro que quieres liberar este Pokémon? Esta acción no se puede deshacer.');">
                                            💫 Liberar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<script>
// Limpia ?ok y ?error de la URL tras mostrar el mensaje
if (window.location.search.match(/[?&](ok|error)=/)) {
    window.history.replaceState({}, document.title, window.location.pathname);
}
</script>