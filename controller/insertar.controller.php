<?php
// controller/insertar.controller.php
require_once __DIR__ . '/../model/pokemon.php';

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/insertar.vista.php');
    exit;
}

// Recoger y validar campos
$titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
$descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : null;

if ($titulo === '') {
    header('Location: ../view/insertar.vista.php?error=' . urlencode('El título es obligatorio'));
    exit;
}

$ok = insertPokemon($titulo, $descripcion);

if ($ok) {
    header('Location: ../view/index.php?ok=' . urlencode('Pokemon insertado correctamente'));
    exit;
}

header('Location: ../view/insertar.vista.php?error=' . urlencode('No se pudo insertar. Inténtalo de nuevo.'));
exit;
