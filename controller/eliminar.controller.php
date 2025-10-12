<?php
// controller/eliminar.controller.php
require_once __DIR__ . '/../model/pokemon.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /ProyecteServidor1/view/index.php?error=' . urlencode('ID inválido.'));
    exit;
}

$id = (int)$_GET['id'];
$ok = deletePokemon($id);

if ($ok) {
    header('Location: /ProyecteServidor1/view/index.php?ok=' . urlencode('Pokemon eliminado correctamente'));
    exit;
}

header('Location: /ProyecteServidor1/view/index.php?error=' . urlencode('No se pudo eliminar.'));
exit;
