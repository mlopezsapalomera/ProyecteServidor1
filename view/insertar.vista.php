<?php
// view/insertar.vista.php
function e($s){return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');}
$error = isset($_GET['error']) ? $_GET['error'] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Capturar Pokémon - PokéNet Social</title>
  <link rel="stylesheet" href="../style/styles.css">
  </head>
<body>
  <div class="container form">
    <div class="header form">
      <h1 class="form">⚡ Capturar Pokémon</h1>
      <p class="subtitle">Añade un nuevo Pokémon a tu equipo</p>
    </div>
    
    <div class="content form">
      <?php if ($error): ?>
        <div class="alert error">❌ <?= e($error) ?></div>
      <?php endif; ?>

      <form action="/ProyecteServidor1/controller/insertar.controller.php" method="post">
        <div class="form-group">
          <label for="titulo">🎯 Nombre del Pokémon *</label>
          <input type="text" id="titulo" name="titulo" placeholder="Ej: Pikachu, Charizard, Mewtwo..." required>
        </div>

        <div class="form-group">
          <label for="descripcion">� Historia o Descripción</label>
          <textarea id="descripcion" name="descripcion" placeholder="Cuéntanos la historia de tu Pokémon, cómo lo capturaste, sus habilidades especiales..."></textarea>
        </div>

        <div class="actions form">
            <button class="btn primary" type="submit">🏆 Capturar Pokémon</button>
            <a class="btn secondary" href="/ProyecteServidor1/view/index.php">🔙 Volver a PokéNet</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>