
<?php
require_once __DIR__ . '/../model/pokemon.php';
function e($s){return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');}
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = isset($_GET['error']) ? $_GET['error'] : null;
$pokemon = $id > 0 ? getPokemonById($id) : null;
if (!$pokemon) {
		echo '<div style="padding:24px;color:#991b1b">No se encontró el pokemon.</div>';
		exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<base href="/pt02_lopez_marcos/">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Entrenar Pokémon - PokéNet Social</title>
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
			<h1 class="form">🔧 Entrenar Pokémon</h1>
			<p class="subtitle">Actualiza la información de tu Pokémon</p>
		</div>

		<?php if ($error): ?>
			<div class="alert error">❌ <?= e($error) ?></div>
		<?php endif; ?>

		<!-- Formulario con glassmorphism -->
		<div class="form-container">
			<form action="controller/modificar.controller.php" method="post">
				<input type="hidden" name="id" value="<?= e($pokemon['id']) ?>">
				
				<div class="form-group">
					<label for="titulo">🎯 Nombre del Pokémon *</label>
					<input type="text" id="titulo" name="titulo" value="<?= e($pokemon['titulo']) ?>" placeholder="Ej: Pikachu, Charizard, Mewtwo..." required autofocus>
				</div>

				<div class="form-group">
					<label for="descripcion">📝 Historia o Descripción</label>
					<textarea id="descripcion" name="descripcion" placeholder="Cuéntanos más sobre tu Pokémon, nuevas habilidades aprendidas..."><?= e($pokemon['descripcion']) ?></textarea>
				</div>

				<div class="actions form">
					<button class="btn primary" type="submit">💪 Guardar Cambios</button>
					<a class="btn secondary" href="view/index.php">🔙 Cancelar</a>
				</div>
			</form>
		</div>
	</div>
</body>
</html>