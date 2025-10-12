
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
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Entrenar Pokémon - PokéNet Social</title>
	<link rel="stylesheet" href="../style/styles.css">
	</head>
<body>
	<div class="container form">
		<div class="header form">
			<h1 class="form">🔧 Entrenar Pokémon</h1>
			<p class="subtitle">Actualiza la información de tu Pokémon</p>
		</div>
		
		<div class="content form">
			<?php if ($error): ?>
				<div class="alert error">❌ <?= e($error) ?></div>
			<?php endif; ?>

			<form action="/ProyecteServidor1/controller/modificar.controller.php" method="post">
				<input type="hidden" name="id" value="<?= e($pokemon['id']) ?>">
				
				<div class="form-group">
					<label for="titulo">🎯 Nombre del Pokémon *</label>
					<input type="text" id="titulo" name="titulo" value="<?= e($pokemon['titulo']) ?>" placeholder="Ej: Pikachu, Charizard, Mewtwo..." required>
				</div>

				<div class="form-group">
					<label for="descripcion">� Historia o Descripción</label>
					<textarea id="descripcion" name="descripcion" placeholder="Cuéntanos más sobre tu Pokémon, nuevas habilidades aprendidas..."><?= e($pokemon['descripcion']) ?></textarea>
				</div>

				<div class="actions form">
					<button class="btn primary" type="submit">💪 Guardar Entrenamiento</button>
					<a class="btn secondary" href="/ProyecteServidor1/view/index.php">🔙 Volver a PokéNet</a>
				</div>
			</form>
		</div>
	</div>
</body>
</html>
