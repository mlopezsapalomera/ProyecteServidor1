<?php
// model/user.php
// Modelo para gestión de usuarios

// Obtener conexión a la base de datos
$nom_variable_connexio = require __DIR__ . '/db.php';

// Obtener usuario por nombre
function obtenerUsuarioPorNombre($username) {
    global $nom_variable_connexio;
    $sql = "SELECT * FROM users WHERE username = :username LIMIT 1";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute([':username' => $username]);
    return $stmt->fetch();
}

// Obtener usuario por email
function obtenerUsuarioPorEmail($email) {
    global $nom_variable_connexio;
    $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute([':email' => $email]);
    return $stmt->fetch();
}

// Obtener usuario por ID
function obtenerUsuarioPorId($id) {
    global $nom_variable_connexio;
    $sql = "SELECT * FROM users WHERE id = :id LIMIT 1";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute([':id' => (int)$id]);
    return $stmt->fetch();
}

// Crear nuevo usuario
function crearUsuario($username, $email, $passwordHash, $profileImage = 'userDefaultImg.jpg') {
    global $nom_variable_connexio;
    $sql = "INSERT INTO users (username, email, password_hash, profile_image) VALUES (:username, :email, :password_hash, :profile_image)";
    $stmt = $nom_variable_connexio->prepare($sql);
    $ok = $stmt->execute([
        ':username' => $username,
        ':email' => $email,
        ':password_hash' => $passwordHash,
        ':profile_image' => $profileImage
    ]);
    if (!$ok) return false;
    return (int)$nom_variable_connexio->lastInsertId();
}

// Verificar credenciales de usuario
function verificarCredencialesUsuario($usernameOrEmail, $password) {
    // Buscar usuario por nombre o email
    $user = obtenerUsuarioPorNombre($usernameOrEmail);
    if (!$user) $user = obtenerUsuarioPorEmail($usernameOrEmail);
    if (!$user) return false;
    
    // Verificar contraseña
    if (password_verify($password, $user['password_hash'])) {
        return $user;
    }
    return false;
}

// Actualizar perfil de usuario
function actualizarPerfil($userId, $username, $profileImage = null) {
    global $nom_variable_connexio;
    
    if ($profileImage) {
        $sql = "UPDATE users SET username = :username, profile_image = :profile_image WHERE id = :id";
        $stmt = $nom_variable_connexio->prepare($sql);
        return $stmt->execute([
            ':username' => $username,
            ':profile_image' => $profileImage,
            ':id' => (int)$userId
        ]);
    } else {
        $sql = "UPDATE users SET username = :username WHERE id = :id";
        $stmt = $nom_variable_connexio->prepare($sql);
        return $stmt->execute([
            ':username' => $username,
            ':id' => (int)$userId
        ]);
    }
}

// Verificar si username existe (excluyendo un ID específico)
function existeUsername($username, $excludeId = null) {
    global $nom_variable_connexio;
    if ($excludeId) {
        $sql = "SELECT COUNT(*) as total FROM users WHERE username = :username AND id != :id";
        $stmt = $nom_variable_connexio->prepare($sql);
        $stmt->execute([':username' => $username, ':id' => (int)$excludeId]);
    } else {
        $sql = "SELECT COUNT(*) as total FROM users WHERE username = :username";
        $stmt = $nom_variable_connexio->prepare($sql);
        $stmt->execute([':username' => $username]);
    }
    $row = $stmt->fetch();
    return $row && $row['total'] > 0;
}

// Actualizar contraseña de usuario
function actualizarContrasena($userId, $nuevaPasswordHash) {
    global $nom_variable_connexio;
    $sql = "UPDATE users SET password_hash = :password_hash WHERE id = :id";
    $stmt = $nom_variable_connexio->prepare($sql);
    return $stmt->execute([
        ':password_hash' => $nuevaPasswordHash,
        ':id' => (int)$userId
    ]);
}

// Obtener todos los usuarios (excepto admins para el panel de administración)
function obtenerTodosLosUsuarios($excluirAdmins = true) {
    global $nom_variable_connexio;
    if ($excluirAdmins) {
        $sql = "SELECT id, username, email, profile_image, role, created_at FROM users WHERE role != 'admin' ORDER BY created_at DESC";
    } else {
        $sql = "SELECT id, username, email, profile_image, role, created_at FROM users ORDER BY created_at DESC";
    }
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Contar publicaciones de un usuario
function contarPublicacionesUsuario($userId) {
    global $nom_variable_connexio;
    $sql = "SELECT COUNT(*) as total FROM pokemons WHERE user_id = :user_id";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute([':user_id' => (int)$userId]);
    $row = $stmt->fetch();
    return $row ? (int)$row['total'] : 0;
}

// Eliminar usuario y todas sus publicaciones (CASCADE)
function eliminarUsuario($userId) {
    global $nom_variable_connexio;
    
    try {
        // Iniciar transacción
        $nom_variable_connexio->beginTransaction();
        
        // Primero eliminar todas las publicaciones del usuario
        $sql1 = "DELETE FROM pokemons WHERE user_id = :user_id";
        $stmt1 = $nom_variable_connexio->prepare($sql1);
        $stmt1->execute([':user_id' => (int)$userId]);
        
        // Luego eliminar el usuario
        $sql2 = "DELETE FROM users WHERE id = :id";
        $stmt2 = $nom_variable_connexio->prepare($sql2);
        $stmt2->execute([':id' => (int)$userId]);
        
        // Confirmar transacción
        $nom_variable_connexio->commit();
        return true;
    } catch (Exception $e) {
        // Revertir en caso de error
        $nom_variable_connexio->rollBack();
        return false;
    }
}

// Actualizar rol de usuario
function actualizarRolUsuario($userId, $nuevoRol) {
    global $nom_variable_connexio;
    
    // Validar que el rol sea válido
    $rolesValidos = ['user', 'admin'];
    if (!in_array($nuevoRol, $rolesValidos)) {
        return false;
    }
    
    $sql = "UPDATE users SET role = :role WHERE id = :id";
    $stmt = $nom_variable_connexio->prepare($sql);
    return $stmt->execute([
        ':role' => $nuevoRol,
        ':id' => (int)$userId
    ]);
}

// Buscar usuarios por nombre (para búsqueda AJAX)
function buscarUsuarios($query, $limit = 10) {
    global $nom_variable_connexio;
    $sql = "SELECT id, username, email, profile_image, role FROM users 
            WHERE username LIKE :query 
            ORDER BY username ASC 
            LIMIT :limit";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->bindValue(':query', '%' . $query . '%', PDO::PARAM_STR);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

// ===== FUNCIONES PARA REMEMBER ME =====

/**
 * Crear un token de "Recordarme" para el usuario
 * @param int $userId ID del usuario
 * @param int $dias Días de validez del token (default: 30)
 * @return string|false Token generado o false si falla
 */
function crearRememberToken($userId, $dias = 30) {
    global $nom_variable_connexio;
    
    // Generar token aleatorio seguro (64 bytes = 128 caracteres hex)
    $token = bin2hex(random_bytes(64));
    
    // Hash del token para guardar en BD (seguridad: no guardar token plano)
    $tokenHash = hash('sha256', $token);
    
    // Calcular fecha de expiración
    $expiresAt = date('Y-m-d H:i:s', time() + ($dias * 24 * 60 * 60));
    
    // Insertar en BD
    $sql = "INSERT INTO remember_tokens (user_id, token_hash, expires_at) 
            VALUES (:user_id, :token_hash, :expires_at)";
    $stmt = $nom_variable_connexio->prepare($sql);
    $ok = $stmt->execute([
        ':user_id' => (int)$userId,
        ':token_hash' => $tokenHash,
        ':expires_at' => $expiresAt
    ]);
    
    return $ok ? $token : false;
}

/**
 * Verificar un token de "Recordarme"
 * @param string $token Token a verificar
 * @return array|false Datos del usuario si el token es válido, false si no
 */
function verificarRememberToken($token) {
    global $nom_variable_connexio;
    
    if (empty($token)) return false;
    
    // Hash del token para comparar
    $tokenHash = hash('sha256', $token);
    
    // Buscar token válido (no expirado)
    $sql = "SELECT rt.user_id, u.* 
            FROM remember_tokens rt
            INNER JOIN users u ON rt.user_id = u.id
            WHERE rt.token_hash = :token_hash 
            AND rt.expires_at > NOW()
            LIMIT 1";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute([':token_hash' => $tokenHash]);
    $result = $stmt->fetch();
    
    if ($result) {
        // Token válido: actualizar fecha de expiración (renovar por 30 días más)
        renovarRememberToken($tokenHash);
        return $result;
    }
    
    return false;
}

/**
 * Renovar la fecha de expiración de un token
 * @param string $tokenHash Hash del token
 * @param int $dias Días adicionales (default: 30)
 */
function renovarRememberToken($tokenHash, $dias = 30) {
    global $nom_variable_connexio;
    
    $expiresAt = date('Y-m-d H:i:s', time() + ($dias * 24 * 60 * 60));
    
    $sql = "UPDATE remember_tokens 
            SET expires_at = :expires_at 
            WHERE token_hash = :token_hash";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute([
        ':expires_at' => $expiresAt,
        ':token_hash' => $tokenHash
    ]);
}

/**
 * Eliminar un token específico de "Recordarme"
 * @param string $token Token a eliminar
 */
function eliminarRememberToken($token) {
    global $nom_variable_connexio;
    
    if (empty($token)) return;
    
    $tokenHash = hash('sha256', $token);
    
    $sql = "DELETE FROM remember_tokens WHERE token_hash = :token_hash";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute([':token_hash' => $tokenHash]);
}

/**
 * Eliminar todos los tokens de un usuario
 * @param int $userId ID del usuario
 */
function eliminarTodosRememberTokens($userId) {
    global $nom_variable_connexio;
    
    $sql = "DELETE FROM remember_tokens WHERE user_id = :user_id";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute([':user_id' => (int)$userId]);
}

/**
 * Limpiar tokens expirados (mantenimiento)
 */
function limpiarTokensExpirados() {
    global $nom_variable_connexio;
    
    $sql = "DELETE FROM remember_tokens WHERE expires_at < NOW()";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute();
}
