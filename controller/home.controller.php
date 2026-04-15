<?php
// controller/home.controller.php
// Controlador principal de la portada

require_once __DIR__ . '/paginacio.controller.php';
require_once __DIR__ . '/../security/auth.php';
require_once __DIR__ . '/../model/user.php';

$usuarioCompleto = null;
if (estaIdentificado()) {
	$usuarioCompleto = obtenerUsuarioPorId(idUsuarioActual());
}

$ok = isset($_GET['ok']) ? $_GET['ok'] : null;
$error = isset($_GET['error']) ? $_GET['error'] : null;

require_once __DIR__ . '/../view/index.php';
