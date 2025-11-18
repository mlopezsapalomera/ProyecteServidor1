<?php
require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../security/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/register.vista.php');
    exit;
}

$usuari = isset($_POST['username']) ? trim($_POST['username']) : '';
$correu = isset($_POST['email']) ? trim($_POST['email']) : '';
$contrasenya = isset($_POST['password']) ? $_POST['password'] : '';
$contrasenya2 = isset($_POST['password2']) ? $_POST['password2'] : '';

$errors = [];
if ($usuari === '') $errors[] = 'El nom d\'usuari és obligatori.';
if ($correu === '' || !filter_var($correu, FILTER_VALIDATE_EMAIL)) $errors[] = 'Correu electrònic invàlid.';
if ($contrasenya === '' || $contrasenya2 === '') $errors[] = 'Introdueix la contrasenya dues vegades.';
if ($contrasenya !== $contrasenya2) $errors[] = 'Les contrasenyes no coincideixen.';

// Força mínima: mínim 8 caràcters, majúscula, minúscula, número
if (strlen($contrasenya) < 8 ||
    !preg_match('/[A-Z]/', $contrasenya) ||
    !preg_match('/[a-z]/', $contrasenya) ||
    !preg_match('/[0-9]/', $contrasenya)) {
    $errors[] = 'La contrasenya ha de tenir almenys 8 caràcters, incloure majúscula, minúscula i número.';
}

// Comprovar existència
if (obtenirUsuariPerNom($usuari)) $errors[] = 'L\'usuari ja existeix.';
if (obtenirUsuariPerEmail($correu)) $errors[] = 'El correu ja està registrat.';

if (!empty($errors)) {
    $qs = http_build_query(['error' => implode(' ', $errors), 'usuari' => $usuari, 'correu' => $correu]);
    header('Location: ../view/register.vista.php?' . $qs);
    exit;
}

$hash = password_hash($contrasenya, PASSWORD_DEFAULT);
$newId = crearUsuari($usuari, $correu, $hash);
if ($newId) {
    $usuariReg = obtenirUsuariPerId($newId);
    iniciarSessio($usuariReg);
    header('Location: ../view/index.php?ok=' . urlencode('Registre complet. Benvingut ' . $usuari . '!'));
    exit;
}

header('Location: ../view/register.vista.php?error=' . urlencode('No s\'ha pogut crear el compte.'));
exit;
