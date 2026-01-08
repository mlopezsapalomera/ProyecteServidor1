<?php
// controller/cambiarContrasena.controller.php
// Controlador para cambiar la contraseña del usuario

require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../security/auth.php';

// Solo usuarios autenticados
if (!estaIdentificado()) {
    header('Location: ../view/login.vista.php');
    exit;
}

// Validar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/cambiarContrasena.vista.php');
    exit;
}

// Obtener ID del usuario actual
$userId = idUsuarioActual();
$usuarioActual = obtenerUsuarioPorId($userId);

if (!$usuarioActual) {
    header('Location: ../view/index.php?error=' . urlencode('Usuario no encontrado'));
    exit;
}

// Recoger datos del formulario
$currentPassword = isset($_POST['current_password']) ? $_POST['current_password'] : '';
$newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
$confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

// Validaciones
$errors = [];

// Validar que todos los campos estén completos
if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
    $errors[] = 'Todos los campos son obligatorios.';
}

// Validar que la contraseña actual sea correcta
if (empty($errors) && !password_verify($currentPassword, $usuarioActual['password_hash'])) {
    $errors[] = 'La contraseña actual es incorrecta.';
}

// Validar longitud de la nueva contraseña
if (strlen($newPassword) < 6) {
    $errors[] = 'La nueva contraseña debe tener al menos 6 caracteres.';
}

// Validar que las contraseñas nuevas coincidan
if ($newPassword !== $confirmPassword) {
    $errors[] = 'Las contraseñas nuevas no coinciden.';
}

// Validar que la nueva contraseña sea diferente a la actual
if (empty($errors) && $currentPassword === $newPassword) {
    $errors[] = 'La nueva contraseña debe ser diferente a la actual.';
}

// Mostrar errores si existen
if (!empty($errors)) {
    $qs = http_build_query(['error' => implode(' ', $errors)]);
    header('Location: ../view/cambiarContrasena.vista.php?' . $qs);
    exit;
}

// Hashear la nueva contraseña
$nuevoHash = password_hash($newPassword, PASSWORD_DEFAULT);

// Actualizar contraseña en la base de datos
$resultado = actualizarContrasena($userId, $nuevoHash);

if ($resultado) {
    header('Location: ../view/cambiarContrasena.vista.php?ok=' . urlencode('Contraseña actualizada correctamente.'));
} else {
    header('Location: ../view/cambiarContrasena.vista.php?error=' . urlencode('Error al actualizar la contraseña. Inténtalo de nuevo.'));
}
exit;
