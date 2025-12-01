<?php
// controller/insertar.controller.php

// Primero, incluimos los archivos necesarios para poder acceder a las funciones del modelo y de autenticación.
// Incluimos el modelo de Pokémon para poder insertar nuevos registros en la base de datos.
require_once __DIR__ . '/../model/pokemon.php';
// Incluimos el archivo de autenticación para comprobar si el usuario está identificado.
require_once __DIR__ . '/../security/auth.php';

// A continuación, comprobamos que la petición se haya realizado mediante el método POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Si no es una petición POST, redirigimos al formulario de inserción.
    header('Location: ../view/insertar.vista.php');
    exit;
}

// Después de comprobar el método, verificamos si el usuario está identificado (logueado).
if (!estaIdentificado()) {
    // Si el usuario no ha iniciado sesión, lo redirigimos a la página de login con un mensaje de error.
    header('Location: ../view/login.vista.php?error=' . urlencode('Debes iniciar sesión para insertar.'));
    exit;
}

// Si el usuario está identificado, recogemos y validamos los campos del formulario.
$titol = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
$descripcio = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : null;

// Comprobamos que el título no esté vacío, ya que es obligatorio.
if ($titol === '') {
    // Si el título está vacío, redirigimos al formulario con un mensaje de error.
    header('Location: ../view/insertar.vista.php?error=' . urlencode('El títol és obligatori'));
    exit;
}

// Si los datos son válidos, obtenemos el id del usuario actual para asociar el Pokémon a ese usuario.
$idUsuario = idUsuarioActual();
// Llamamos a la función insertarPokemon para guardar el nuevo Pokémon en la base de datos.
$ok = insertarPokemon($titol, $descripcio, $idUsuario);

// Después de intentar insertar, comprobamos si la operación fue exitosa.
if ($ok) {
    // Si se insertó correctamente, redirigimos a la página principal con un mensaje de éxito.
    header('Location: ../view/index.php?ok=' . urlencode('Pokémon insertado correctamente'));
    exit;
}
// Si la inserción falla, redirigimos al formulario con un mensaje de error.
header('Location: ../view/insertar.vista.php?error=' . urlencode('No se ha podido insertar. Inténtalo de nuevo.'));
exit;
