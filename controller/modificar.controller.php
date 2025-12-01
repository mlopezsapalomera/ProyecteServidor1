<?php
// controller/modificar.controller.php

// Primero, incluimos los archivos necesarios para poder acceder a las funciones del modelo y de autenticación.
// Incluimos el modelo de Pokémon para poder modificar registros en la base de datos.
require_once __DIR__ . '/../model/pokemon.php';
// Incluimos el archivo de autenticación para comprobar si el usuario está identificado.
require_once __DIR__ . '/../security/auth.php';

// A continuación, comprobamos que la petición se haya realizado mediante el método POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Si no es una petición POST, redirigimos a la página principal.
    header('Location: ../view/index.php');
    exit;
}

// Después de comprobar el método, verificamos si el usuario está identificado (logueado).
if (!estaIdentificado()) {
    // Si el usuario no ha iniciado sesión, lo redirigimos a la página de login con un mensaje de error.
    header('Location: ../view/login.vista.php?error=' . urlencode('Debes iniciar sesión para editar.'));
    exit;
}

// Si el usuario está identificado, recogemos y validamos los campos del formulario.
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$titol = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
$descripcio = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : null;

// Comprobamos que el id sea válido y que el título no esté vacío.
if ($id <= 0 || $titol === '') {
    // Si los datos no son válidos, redirigimos al formulario de modificación con un mensaje de error.
    header('Location: ../view/modificar.vista.php?id=' . $id . '&error=' . urlencode('Dades invàlides.'));
    exit;
}

// Si los datos son válidos, obtenemos el Pokémon correspondiente a ese id.
$pokemon = obtenerPokemonPorId($id);
// Comprobamos si el Pokémon existe en la base de datos.
if (!$pokemon) {
    // Si no existe, redirigimos a la página principal con un mensaje de error.
    header('Location: ../view/index.php?error=' . urlencode('Registre no trobat.'));
    exit;
}

// Sólo el propietario del Pokémon puede editarlo. Comprobamos que el usuario actual sea el propietario.
if ((int)$pokemon['user_id'] !== idUsuarioActual()) {
    // Si el usuario no es el propietario, no tiene permiso para editarlo y se le redirige con un error.
    header('Location: ../view/index.php?error=' . urlencode('No tienes permiso para editar este Pokémon.'));
    exit;
}

// Si todas las comprobaciones anteriores son correctas, intentamos actualizar el Pokémon llamando a la función actualizarPokemon.
$ok = actualizarPokemon($id, $titol, $descripcio);

// Después de intentar actualizar, comprobamos si la operación fue exitosa.
if ($ok) {
    // Si se modificó correctamente, redirigimos a la página principal con un mensaje de éxito.
    header('Location: ../view/index.php?ok=' . urlencode('Pokémon modificat correctament'));
    exit;
}
// Si la modificación falla, redirigimos al formulario con un mensaje de error.
header('Location: ../view/modificar.vista.php?id=' . $id . '&error=' . urlencode('No se ha podido modificar. Inténtalo de nuevo.'));
exit;
