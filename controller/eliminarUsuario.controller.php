<?php
// controller/eliminarUsuario.controller.php
// Controlador para eliminar usuarios (solo para administradores)

require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../security/auth.php';

// Solo administradores pueden eliminar usuarios
if (!estaIdentificado() || !esAdmin()) {
    header('Location: ../view/index.php?error=' . urlencode('Acceso denegado. Solo administradores.'));
    exit;
}

// Validar que se recibió un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ../view/adminPanel.vista.php?error=' . urlencode('ID de usuario no proporcionado.'));
    exit;
}

$userId = (int)$_GET['id'];

// Validar que el ID sea válido
if ($userId <= 0) {
    header('Location: ../view/adminPanel.vista.php?error=' . urlencode('ID de usuario inválido.'));
    exit;
}

// Obtener información del usuario a eliminar
$usuarioAEliminar = obtenerUsuarioPorId($userId);

if (!$usuarioAEliminar) {
    header('Location: ../view/adminPanel.vista.php?error=' . urlencode('Usuario no encontrado.'));
    exit;
}

// Verificar que no sea un administrador
if ($usuarioAEliminar['role'] === 'admin') {
    header('Location: ../view/adminPanel.vista.php?error=' . urlencode('No se pueden eliminar usuarios administradores.'));
    exit;
}

// Verificar que no se esté intentando eliminar a sí mismo
if ($userId === idUsuarioActual()) {
    header('Location: ../view/adminPanel.vista.php?error=' . urlencode('No puedes eliminarte a ti mismo.'));
    exit;
}

// Contar publicaciones antes de eliminar
$numPublicaciones = contarPublicacionesUsuario($userId);

// Eliminar usuario y sus publicaciones
$resultado = eliminarUsuario($userId);

if ($resultado) {
    $mensaje = "Usuario '{$usuarioAEliminar['username']}' eliminado correctamente.";
    if ($numPublicaciones > 0) {
        $mensaje .= " Se eliminaron también {$numPublicaciones} publicaciones.";
    }
    header('Location: ../view/adminPanel.vista.php?ok=' . urlencode($mensaje));
} else {
    header('Location: ../view/adminPanel.vista.php?error=' . urlencode('Error al eliminar el usuario. Inténtalo de nuevo.'));
}
exit;
