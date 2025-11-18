<?php
require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../security/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/login.vista.php');
    exit;
}

$campUsuari = isset($_POST['user']) ? trim($_POST['user']) : '';
$contrasenya = isset($_POST['password']) ? $_POST['password'] : '';

if ($campUsuari === '' || $contrasenya === '') {
    header('Location: ../view/login.vista.php?error=' . urlencode('Usuari i contrasenya són obligatoris.'));
    exit;
}

$usuari = verificarCredencialsUsuari($campUsuari, $contrasenya);
if ($usuari) {
    iniciarSessio($usuari);
    header('Location: ../view/index.php?ok=' . urlencode('Has iniciat sessió.'));
    exit;
}

header('Location: ../view/login.vista.php?error=' . urlencode('Usuari o contrasenya incorrectes.') . '&usuari=' . urlencode($campUsuari));
exit;
