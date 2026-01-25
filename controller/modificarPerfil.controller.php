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
$idUsuario = idUsuarioActual();
$datosUsuarioActual = obtenerUsuarioPorId($idUsuario);

if (!$datosUsuarioActual) {
    header('Location: ../view/index.php?error=' . urlencode('Usuario no encontrado'));
    exit;
}

// Recoger datos del formulario
$nuevoNombreUsuario = isset($_POST['username']) ? trim($_POST['username']) : '';

// Validaciones
$errores = [];

// Validar nombre de usuario
if ($nuevoNombreUsuario === '') {
    $errores[] = 'El nombre de usuario es obligatorio.';
} elseif (strlen($nuevoNombreUsuario) < 3 || strlen($nuevoNombreUsuario) > 100) {
    $errores[] = 'El nombre de usuario debe tener entre 3 y 100 caracteres.';
} elseif ($nuevoNombreUsuario !== $datosUsuarioActual['username'] && existeUsername($nuevoNombreUsuario, $idUsuario)) {
    $errores[] = 'El nombre de usuario ya está en uso.';
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
        $errores[] = 'Solo se permiten imágenes JPG, PNG o GIF.';
    }
    
    // Validar tamaño (5MB máximo)
    if ($archivo['size'] > 5 * 1024 * 1024) {
        $errores[] = 'La imagen no puede superar los 5MB.';
    }
    
    if (empty($errores)) {
        // Generar nombre único para la imagen
        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombreArchivo = 'user_' . $idUsuario . '_' . time() . '.' . $extension;
        $rutaDestino = __DIR__ . '/../assets/img/userImg/' . $nombreArchivo;
        
        // Mover archivo
        if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            $nuevaImagen = $nombreArchivo;
            $imagenSubida = true;
            
            // Eliminar imagen anterior si no es la por defecto
            $imagenPrevia = $datosUsuarioActual['profile_image'];
            if ($imagenPrevia !== 'userDefaultImg.jpg' && $imagenPrevia !== $nombreArchivo) {
                $rutaPrevia = __DIR__ . '/../assets/img/userImg/' . $imagenPrevia;
                if (file_exists($rutaPrevia)) {
                    unlink($rutaPrevia);
                }
            }
        } else {
            $errores[] = 'Error al subir la imagen. Inténtalo de nuevo.';
        }
    }
}

// Mostrar errores si existen
if (!empty($errores)) {
    $qs = http_build_query(['error' => implode(' ', $errores)]);
    header('Location: ../view/modificarPerfil.vista.php?' . $qs);
    exit;
}

// Actualizar perfil en la base de datos
$resultado = actualizarPerfil($idUsuario, $nuevoNombreUsuario, $nuevaImagen);

if ($resultado) {
    // Actualizar datos en la sesión
    $_SESSION['usuario']['username'] = $nuevoNombreUsuario;
    
    // Mensaje de éxito
    $mensaje = 'Perfil actualizado correctamente.';
    if ($imagenSubida) {
        $mensaje .= ' Tu foto de perfil ha sido cambiada.';
    }
    
    header('Location: ../view/perfilUsuario.vista.php?id=' . $idUsuario . '&ok=' . urlencode($mensaje));
    exit;
}

// Error al actualizar
header('Location: ../view/modificarPerfil.vista.php?error=' . urlencode('No se pudo actualizar el perfil.'));
exit;
