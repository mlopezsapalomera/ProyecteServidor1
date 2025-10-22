<?php
// view/insertar.vista.php
function e($s){return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');}
$error = isset($_GET['error']) ? $_GET['error'] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <base href="/pt02_lopez_marcos/">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Capturar Pokémon - PokéNet Social</title>
  <link rel="stylesheet" href="style/styles.css">
</head>
<body>
  <!-- Navbar tipo Instagram -->
  <nav class="navbar">
    <div class="navbar-container">
      <a href="view/index.php" class="navbar-brand" style="text-decoration: none;">🌟 PokéNet</a>
      <div class="navbar-actions">
        <a href="#" class="nav-btn login">Iniciar Sesión</a>
        <a href="#" class="nav-btn register">Registrarse</a>
      </div>
    </div>
  </nav>

  <!-- Contenedor principal -->
  <div class="container form">
    <!-- Header con glassmorphism -->
    <div class="header form">
      <h1 class="form">⚡ Capturar Pokémon</h1>
      <p class="subtitle">Añade un nuevo Pokémon a tu colección</p>
    </div>

    <?php if ($error): ?>
      <div class="alert error">❌ <?= e($error) ?></div>
    <?php endif; ?>

    <!-- Formulario con glassmorphism -->
    <div class="form-container">
      <form action="controller/insertar.controller.php" method="post">
        <div class="form-group">
          <label for="titulo">🎯 Nombre del Pokémon *</label>
          <input type="text" id="titulo" name="titulo" placeholder="Ej: Pikachu, Charizard, Mewtwo..." required autofocus>
        </div>

        <div class="form-group">
          <label for="descripcion">📝 Historia o Descripción</label>
          <textarea id="descripcion" name="descripcion" placeholder="Cuéntanos la historia de tu Pokémon, cómo lo capturaste, sus habilidades especiales..."></textarea>
        </div>

        <div class="actions form">
          <button class="btn primary" type="submit">🏆 Capturar Pokémon</button>
          <a class="btn secondary" href="view/index.php">🔙 Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>