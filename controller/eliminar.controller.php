<?php
// controller/eliminar.controller.php

// Incluimos el modelo de Pokémon para poder interactuar con la base de datos de Pokémon.
require_once __DIR__ . '/../model/pokemon.php';
// Y el archivo de autenticación para poder comprobar si el usuario está identificado.
require_once __DIR__ . '/../security/auth.php';

// A continuación, verificamos que se haya recibido un parámetro 'id' por GET y que sea numérico.
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Si el parámetro 'id' no existe o no es válido, redirigimos al usuario a la página principal con un mensaje de error.
    header('Location: ../view/index.php?error=' . urlencode('ID invàlid.'));
    exit;
}

// Después de validar el parámetro 'id', comprobamos si el usuario está identificado (logueado).
if (!estaIdentificado()) {
    // Si el usuario no ha iniciado sesión, lo redirigimos a la página de login con un mensaje de error.
    header('Location: ../view/login.vista.php?error=' . urlencode('Debes iniciar sesión para eliminar.'));
    exit;
}

// Si el usuario está identificado y el id es válido, procedemos a obtener el Pokémon correspondiente a ese id.
$id = (int)$_GET['id'];
$pokemon = obtenerPokemonPorId($id);
// Comprobamos si el Pokémon existe en la base de datos.
if (!$pokemon) {
    // Si no existe, redirigimos a la página principal con un mensaje de error.
    header('Location: ../view/index.php?error=' . urlencode('Registre no trobat.'));
    exit;
}

// Ahora verificamos que el usuario actual sea el propietario del Pokémon que intenta eliminar.
if ((int)$pokemon['user_id'] !== idUsuarioActual()) {
    // Si el usuario no es el propietario, no tiene permiso para eliminarlo y se le redirige con un error.
    header('Location: ../view/index.php?error=' . urlencode('No tienes permiso para eliminar este Pokémon.'));
    exit;
}

// Si todas las comprobaciones anteriores son correctas, intentamos eliminar el Pokémon llamando a la función eliminarPokemon.
$ok = eliminarPokemon($id);

// Después de intentar eliminar, comprobamos si la operación fue exitosa.
if ($ok) {
    // Si se eliminó correctamente, redirigimos a la página principal con un mensaje de éxito.
    header('Location: ../view/index.php?ok=' . urlencode('Pokémon eliminado correctamente'));
    exit;
}

// Si la eliminación falla por cualquier motivo, redirigimos a la página principal con un mensaje de error.
header('Location: ../view/index.php?error=' . urlencode('No se ha podido eliminar.'));
exit;
