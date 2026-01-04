<?php
// controller/modificarPerfil.controller.php
// Controlador para modificar el perfil del usuario

require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../security/auth.php';

// Solo usuarios autenticados
if (!estaIdentificado()) {
    header('Location: ../view/login.vista.php');
    exit;
}

// Validar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/modificarPerfil.vista.php');
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
$nuevoUsername = isset($_POST['username']) ? trim($_POST['username']) : '';

// Validaciones
$errors = [];

// Validar nombre de usuario
if ($nuevoUsername === '') {
    $errors[] = 'El nombre de usuario es obligatorio.';
} elseif (strlen($nuevoUsername) < 3 || strlen($nuevoUsername) > 100) {
    $errors[] = 'El nombre de usuario debe tener entre 3 y 100 caracteres.';
} elseif ($nuevoUsername !== $usuarioActual['username'] && existeUsername($nuevoUsername, $userId)) {
    $errors[] = 'El nombre de usuario ya está en uso.';
}

// Procesar imagen de perfil si se subió
$nuevaImagen = null;
$imagenSubida = false;

if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $archivo = $_FILES['profile_image'];
    
    // Validar tipo de archivo
    $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $tipoArchivo = finfo_file($finfo, $archivo['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($tipoArchivo, $tiposPermitidos)) {
        $errors[] = 'Solo se permiten imágenes JPG, PNG o GIF.';
    }
    
    // Validar tamaño (5MB máximo)
    if ($archivo['size'] > 5 * 1024 * 1024) {
        $errors[] = 'La imagen no puede superar los 5MB.';
    }
    
    if (empty($errors)) {
        // Generar nombre único para la imagen
        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombreArchivo = 'user_' . $userId . '_' . time() . '.' . $extension;
        $rutaDestino = __DIR__ . '/../assets/img/userImg/' . $nombreArchivo;
        
        // Mover archivo
        if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            $nuevaImagen = $nombreArchivo;
            $imagenSubida = true;
            
            // Eliminar imagen anterior si no es la por defecto
            $imagenAnterior = $usuarioActual['profile_image'];
            if ($imagenAnterior !== 'userDefaultImg.jpg' && $imagenAnterior !== $nombreArchivo) {
                $rutaAnterior = __DIR__ . '/../assets/img/userImg/' . $imagenAnterior;
                if (file_exists($rutaAnterior)) {
                    unlink($rutaAnterior);
                }
            }
        } else {
            $errors[] = 'Error al subir la imagen. Inténtalo de nuevo.';
        }
    }
}

// Mostrar errores si existen
if (!empty($errors)) {
    $qs = http_build_query(['error' => implode(' ', $errors)]);
    header('Location: ../view/modificarPerfil.vista.php?' . $qs);
    exit;
}

// Actualizar perfil en la base de datos
$resultado = actualizarPerfil($userId, $nuevoUsername, $nuevaImagen);

if ($resultado) {
    // Actualizar datos en la sesión
    $_SESSION['usuario']['username'] = $nuevoUsername;
    
    // Mensaje de éxito
    $mensaje = 'Perfil actualizado correctamente.';
    if ($imagenSubida) {
        $mensaje .= ' Tu foto de perfil ha sido cambiada.';
    }
    
    header('Location: ../view/perfilUsuario.vista.php?id=' . $userId . '&ok=' . urlencode($mensaje));
    exit;
}

// Error al actualizar
header('Location: ../view/modificarPerfil.vista.php?error=' . urlencode('No se pudo actualizar el perfil.'));
exit;
