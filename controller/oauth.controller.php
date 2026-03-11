<?php
/**
 * Controlador OAuth
 * 
 * Maneja la autenticación con proveedores OAuth (Google, Facebook, etc.)
 * usando HybridAuth
 */

session_start();

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../security/auth.php';

use Hybridauth\Hybridauth;

try {
    // Cargar configuración de HybridAuth
    $config = require __DIR__ . '/../config/hybridauth.config.php';
    
    // Obtener el proveedor desde la URL (ej: ?provider=Google)
    $provider = isset($_GET['provider']) ? $_GET['provider'] : null;
    
    if (!$provider) {
        throw new Exception('Proveedor OAuth no especificado');
    }
    
    // Inicializar HybridAuth
    $hybridauth = new Hybridauth($config);
    
    // Intentar autenticar con el proveedor
    $adapter = $hybridauth->authenticate($provider);
    
    // Verificar si está conectado
    if (!$adapter->isConnected()) {
        throw new Exception('No se pudo conectar con ' . $provider);
    }
    
    // Obtener perfil del usuario desde el proveedor OAuth
    $userProfile = $adapter->getUserProfile();
    
    // Obtener token de acceso (opcional, para futuras llamadas a API)
    $accessToken = $adapter->getAccessToken();
    
    // Buscar si el usuario ya existe con este proveedor OAuth
    $usuarioExistente = obtenerUsuarioPorOAuthUID($provider, $userProfile->identifier);
    
    if ($usuarioExistente) {
        // Usuario ya existe, actualizar token y hacer login
        actualizarOAuthToken($usuarioExistente['id'], json_encode($accessToken));
        iniciarSesion($usuarioExistente);
        
        // Desconectar del proveedor OAuth
        $adapter->disconnect();
        
        // Redirigir a la página principal
        header('Location: ../view/index.php?success=' . urlencode('¡Bienvenido de nuevo, ' . $usuarioExistente['username'] . '!'));
        exit;
    }
    
    // Usuario nuevo: verificar si el email ya está registrado
    $usuarioPorEmail = obtenerUsuarioPorEmail($userProfile->email);
    
    if ($usuarioPorEmail) {
        // Email ya existe, vincular cuenta OAuth
        vincularOAuthAUsuario(
            $usuarioPorEmail['id'],
            $provider,
            $userProfile->identifier,
            json_encode($accessToken)
        );
        
        iniciarSesion($usuarioPorEmail);
        
        $adapter->disconnect();
        
        header('Location: ../view/index.php?success=' . urlencode('Cuenta de ' . $provider . ' vinculada correctamente'));
        exit;
    }
    
    // Usuario completamente nuevo: crear cuenta
    $username = generarUsernameUnico($userProfile->displayName, $userProfile->firstName, $userProfile->lastName);
    $profileImage = 'userDefaultImg.jpg';
    
    // Descargar imagen de perfil si está disponible
    if (!empty($userProfile->photoURL)) {
        $profileImage = descargarImagenPerfil($userProfile->photoURL, $username);
    }
    
    // Crear usuario con OAuth
    $nuevoUserId = crearUsuarioOAuth(
        $username,
        $userProfile->email,
        $provider,
        $userProfile->identifier,
        json_encode($accessToken),
        $profileImage
    );
    
    if ($nuevoUserId) {
        // Obtener el usuario recién creado
        $nuevoUsuario = obtenerUsuarioPorId($nuevoUserId);
        iniciarSesion($nuevoUsuario);
        
        $adapter->disconnect();
        
        header('Location: ../view/index.php?success=' . urlencode('¡Bienvenido a PokéNet, ' . $username . '!'));
        exit;
    } else {
        throw new Exception('Error al crear la cuenta');
    }
    
} catch (Exception $e) {
    // Registrar error en log
    error_log('Error OAuth: ' . $e->getMessage());
    
    // Redirigir con mensaje de error
    header('Location: ../view/login.vista.php?error=' . urlencode('Error de autenticación: ' . $e->getMessage()));
    exit;
}

/**
 * Genera un username único basado en el nombre del usuario
 */
function generarUsernameUnico($displayName, $firstName = '', $lastName = '') {
    // Prioridad: displayName > firstName + lastName
    $base = $displayName ?: ($firstName . ' ' . $lastName);
    
    // Limpiar el nombre: solo letras, números y guiones bajos
    $base = preg_replace('/[^a-zA-Z0-9_]/', '_', $base);
    $base = strtolower($base);
    $base = substr($base, 0, 50); // Máximo 50 caracteres
    
    $username = $base;
    $contador = 1;
    
    // Verificar si existe y agregar número si es necesario
    while (existeUsername($username)) {
        $username = $base . '_' . $contador;
        $contador++;
    }
    
    return $username;
}

/**
 * Descarga la imagen de perfil del usuario desde la URL del proveedor OAuth
 */
function descargarImagenPerfil($photoURL, $username) {
    try {
        // Directorio de imágenes de perfil
        $uploadDir = __DIR__ . '/../assets/img/imgProfileuser/';
        
        // Crear directorio si no existe
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Nombre único para la imagen
        $extension = 'jpg';
        $nombreArchivo = $username . '_oauth_' . time() . '.' . $extension;
        $rutaCompleta = $uploadDir . $nombreArchivo;
        
        // Descargar imagen
        $contenidoImagen = @file_get_contents($photoURL);
        
        if ($contenidoImagen !== false) {
            file_put_contents($rutaCompleta, $contenidoImagen);
            return $nombreArchivo;
        }
    } catch (Exception $e) {
        error_log('Error al descargar imagen de perfil: ' . $e->getMessage());
    }
    
    // Si falla, usar imagen por defecto
    return 'userDefaultImg.jpg';
}
