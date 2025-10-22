<?php
// controller/modificar.controller.php
require_once __DIR__ . '/../model/pokemon.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/index.php');
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
$descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : null;

if ($id <= 0 || $titulo === '') {
    header('Location: ../view/modificar.vista.php?id=' . $id . '&error=' . urlencode('Datos inválidos.'));
    exit;
}

$ok = updatePokemon($id, $titulo, $descripcion);

if ($ok) {
    header('Location: ../view/index.php?ok=' . urlencode('Pokemon modificado correctamente'));
    exit;
}
header('Location: ../view/modificar.vista.php?id=' . $id . '&error=' . urlencode('No se pudo modificar. Inténtalo de nuevo.'));
exit;
