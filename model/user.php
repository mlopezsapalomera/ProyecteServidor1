<?php

$nom_variable_connexio = require __DIR__ . '/db.php';

function obtenerUsuarioPorNombre($username) {
    global $nom_variable_connexio;
    $sql = "SELECT * FROM users WHERE username = :username LIMIT 1";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute([':username' => $username]);
    $resultado = $stmt->fetch();
    return $resultado;
}

function obtenerUsuarioPorEmail($email) {
    global $nom_variable_connexio;
    $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute([':email' => $email]);
    $resultado = $stmt->fetch();
    return $resultado;
}

function obtenerUsuarioPorId($id) {
    global $nom_variable_connexio;
    $sql = "SELECT * FROM users WHERE id = :id LIMIT 1";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute([':id' => (int)$id]);
    $resultado = $stmt->fetch();
    return $resultado;
}

function crearUsuario($username, $email, $passwordHash, $profileImage = 'userDefaultImg.jpg') {
    global $nom_variable_connexio;
    $sql = "INSERT INTO users (username, email, password_hash, profile_image) VALUES (:username, :email, :password_hash, :profile_image)";
    $stmt = $nom_variable_connexio->prepare($sql);
    $resultado = $stmt->execute([
        ':username' => $username,
        ':email' => $email,
        ':password_hash' => $passwordHash,
        ':profile_image' => $profileImage
    ]);
    if ($resultado == false) {
        return false;
    } else {
        $ultimo_id = $nom_variable_connexio->lastInsertId();
        return (int)$ultimo_id;
    }
}

function verificarCredencialesUsuario($usernameOrEmail, $password) {
    $user = obtenerUsuarioPorNombre($usernameOrEmail);
    if (!$user) {
        $user = obtenerUsuarioPorEmail($usernameOrEmail);
    }
    if (!$user) {
        return false;
    }
    
    $verificacion = password_verify($password, $user['password_hash']);
    if ($verificacion == true) {
        return $user;
    } else {
        return false;
    }
}

function actualizarPerfil($userId, $username, $profileImage = null) {
    global $nom_variable_connexio;
    
    if ($profileImage != null && $profileImage != '') {
        $sql = "UPDATE users SET username = :username, profile_image = :profile_image WHERE id = :id";
        $stmt = $nom_variable_connexio->prepare($sql);
        $resultado = $stmt->execute([
            ':username' => $username,
            ':profile_image' => $profileImage,
            ':id' => (int)$userId
        ]);
        return $resultado;
    } else {
        $sql = "UPDATE users SET username = :username WHERE id = :id";
        $stmt = $nom_variable_connexio->prepare($sql);
        $resultado = $stmt->execute([
            ':username' => $username,
            ':id' => (int)$userId
        ]);
        return $resultado;
    }
}

function existeUsername($username, $excludeId = null) {
    global $nom_variable_connexio;
    if ($excludeId != null) {
        $sql = "SELECT COUNT(*) as total FROM users WHERE username = :username AND id != :id";
        $stmt = $nom_variable_connexio->prepare($sql);
        $stmt->execute([':username' => $username, ':id' => (int)$excludeId]);
        $row = $stmt->fetch();
        if ($row && $row['total'] > 0) {
            return true;
        } else {
            return false;
        }
    } else {
        $sql = "SELECT COUNT(*) as total FROM users WHERE username = :username";
        $stmt = $nom_variable_connexio->prepare($sql);
        $stmt->execute([':username' => $username]);
        $row = $stmt->fetch();
        if ($row && $row['total'] > 0) {
            return true;
        } else {
            return false;
        }
    }
}

function existeCorreo($email, $excludeId = null) {
    global $nom_variable_connexio;
    if ($excludeId != null) {
        $sql = "SELECT COUNT(*) as total FROM users WHERE email = :email AND id != :id";
        $stmt = $nom_variable_connexio->prepare($sql);
        $stmt->execute([':email' => $email, ':id' => (int)$excludeId]);
        $row = $stmt->fetch();
        if ($row && $row['total'] > 0) {
            return true;
        } else {
            return false;
        }
    } else {
        $sql = "SELECT COUNT(*) as total FROM users WHERE email = :email";
        $stmt = $nom_variable_connexio->prepare($sql);
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        if ($row && $row['total'] > 0) {
            return true;
        } else {
            return false;
        }
    }
}

function actualizarContrasena($userId, $nuevaPasswordHash) {
    global $nom_variable_connexio;
    $sql = "UPDATE users SET password_hash = :password_hash WHERE id = :id";
    $stmt = $nom_variable_connexio->prepare($sql);
    $resultado = $stmt->execute([
        ':password_hash' => $nuevaPasswordHash,
        ':id' => (int)$userId
    ]);
    return $resultado;
}

function obtenerTodosLosUsuarios($excluirAdmins = true) {
    global $nom_variable_connexio;
    if ($excluirAdmins == true) {
        $consulta = "SELECT id, username, email, profile_image, role, created_at FROM users WHERE role != 'admin' ORDER BY created_at DESC";
    } else {
        $consulta = "SELECT id, username, email, profile_image, role, created_at FROM users ORDER BY created_at DESC";
    }
    $stmt = $nom_variable_connexio->prepare($consulta);
    $stmt->execute();
    $usuarios = $stmt->fetchAll();
    return $usuarios;
}

function contarPublicacionesUsuario($userId) {
    global $nom_variable_connexio;
    $sql = "SELECT COUNT(*) as total FROM pokemons WHERE user_id = :user_id";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute([':user_id' => (int)$userId]);
    $row = $stmt->fetch();
    if ($row) {
        $total = (int)$row['total'];
        return $total;
    } else {
        return 0;
    }
}

// borrar usuario
function eliminarUsuario($userId) {
    global $nom_variable_connexio;
    
    try {
        $nom_variable_connexio->beginTransaction();
        
        // primero borro los pokemons
        $sql1 = "DELETE FROM pokemons WHERE user_id = :user_id";
        $stmt1 = $nom_variable_connexio->prepare($sql1);
        $stmt1->execute([':user_id' => (int)$userId]);
        
        // ahora elimino el user
        $sql2 = "DELETE FROM users WHERE id = :id";
        $stmt2 = $nom_variable_connexio->prepare($sql2);
        $stmt2->execute([':id' => (int)$userId]);
        
        $nom_variable_connexio->commit();
        return true;
    } catch (Exception $e) {
        $nom_variable_connexio->rollBack();
        return false;
    }
}

function actualizarRolUsuario($userId, $nuevoRol) {
    global $nom_variable_connexio;
    
    $rolesValidos = ['user', 'admin'];
    $esValido = false;
    foreach ($rolesValidos as $rol) {
        if ($rol == $nuevoRol) {
            $esValido = true;
            break;
        }
    }
    
    if (!$esValido) {
        return false;
    }
    
    $sql = "UPDATE users SET role = :role WHERE id = :id";
    $stmt = $nom_variable_connexio->prepare($sql);
    $resultado = $stmt->execute([
        ':role' => $nuevoRol,
        ':id' => (int)$userId
    ]);
    return $resultado;
}

function buscarUsuarios($query, $limit = 10) {
    global $nom_variable_connexio;
    $busqueda = "%" . $query . "%";
    $sql = "SELECT id, username, email, profile_image, role FROM users 
            WHERE username LIKE :query 
            ORDER BY username ASC 
            LIMIT :limit";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->bindValue(':query', $busqueda, PDO::PARAM_STR);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    $resultados = $stmt->fetchAll();
    return $resultados;
}


// recordar sesion
function crearRemembertoken($userId, $dias = 30) {
    global $nom_variable_connexio;

    if (!isset($nom_variable_connexio) || $nom_variable_connexio === null) {
        $nom_variable_connexio = require __DIR__ . '/db.php';
    }

    $token = bin2hex(random_bytes(64));
    $tokenHash = hash("sha256", $token);
    $segundos = $dias * 24 * 60 * 60;
    $timestamp = time() + $segundos;
    $expiresAt = date('Y-m-d H:i:s', $timestamp);

    $sql = "INSERT INTO remember_tokens (user_id, token_hash, expires_at) 
            VALUES (:user_id, :token_hash, :expires_at)";
    $stmt = $nom_variable_connexio->prepare($sql);
    $ok = $stmt->execute([
        ':user_id' => (int)$userId,
        ':token_hash' => $tokenHash,
        ':expires_at' => $expiresAt
    ]);
    
    if ($ok) {
        return $token;
    } else {
        return false;
    }
}


function verificarRememberToken($token) {
    global $nom_variable_connexio;
    
    if (empty($token)) {
        return false;
    }
    
    if (!isset($nom_variable_connexio) || $nom_variable_connexio === null) {
        $nom_variable_connexio = require __DIR__ . '/db.php';
    }
    
    $tokenHash = hash('sha256', $token);
    
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
        renovarRememberToken($tokenHash);
        return $result;
    } else {
        return false;
    }
}

function renovarRememberToken($tokenHash, $dias = 30) {
    global $nom_variable_connexio;
    
    if (!isset($nom_variable_connexio) || $nom_variable_connexio === null) {
        $nom_variable_connexio = require __DIR__ . '/db.php';
    }
    
    $segundos = $dias * 24 * 60 * 60;
    $timestamp = time() + $segundos;
    $expiresAt = date('Y-m-d H:i:s', $timestamp);
    
    $sql = "UPDATE remember_tokens 
            SET expires_at = :expires_at 
            WHERE token_hash = :token_hash";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute([
        ':expires_at' => $expiresAt,
        ':token_hash' => $tokenHash
    ]);
}


function eliminarRememberToken($token) {
    global $nom_variable_connexio;
    
    if (empty($token)) {
        return;
    }
    
    if (!isset($nom_variable_connexio) || $nom_variable_connexio === null) {
        $nom_variable_connexio = require __DIR__ . '/db.php';
    }
    
    $tokenHash = hash('sha256', $token);
    
    $sql = "DELETE FROM remember_tokens WHERE token_hash = :token_hash";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute([':token_hash' => $tokenHash]);
}


function eliminarTodosRememberTokens($userId) {
    global $nom_variable_connexio;
    
    if (!isset($nom_variable_connexio) || $nom_variable_connexio === null) {
        $nom_variable_connexio = require __DIR__ . '/db.php';
    }
    
    $sql = "DELETE FROM remember_tokens WHERE user_id = :user_id";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute([':user_id' => (int)$userId]);
}


function limpiarTokensExpirados() {
    global $nom_variable_connexio;
    
    if (!isset($nom_variable_connexio) || $nom_variable_connexio === null) {
        $nom_variable_connexio = require __DIR__ . '/db.php';
    }
    
    $sql = "DELETE FROM remember_tokens WHERE expires_at < NOW()";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute();
}

// recuperar password
function generarTokenRecuperacion($email) {
    global $nom_variable_connexio;
    
    $user = obtenerUsuarioPorEmail($email);
    if (!$user) {
        return false;
    }
    
    $token = bin2hex(random_bytes(32));
    
    // expira en 5 min
    $tiempoExpiracion = time() + (5 * 60);
    $expira = date('Y-m-d H:i:s', $tiempoExpiracion);
    
    $sql = "UPDATE users SET reset_token = :token, reset_token_expira = :expira WHERE email = :email";
    $stmt = $nom_variable_connexio->prepare($sql);
    $ok = $stmt->execute([
        ':token' => $token,
        ':expira' => $expira,
        ':email' => $email
    ]);
    
    if ($ok == true) {
        return $token;
    } else {
        return false;
    }
}

function verificarTokenRecuperacion($token) {
    global $nom_variable_connexio;
    
    if (empty($token)) {
        return false;
    }
    
    $sql = "SELECT * FROM users WHERE reset_token = :token AND reset_token_expira > NOW() LIMIT 1";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute([':token' => $token]);
    $usuario = $stmt->fetch();
    
    return $usuario;
}

function resetearContrasenaConToken($token, $nuevaPassword) {
    global $nom_variable_connexio;
    
    $user = verificarTokenRecuperacion($token);
    if (!$user) {
        return false;
    }
    
    $passwordHash = password_hash($nuevaPassword, PASSWORD_DEFAULT);
    
    $sql = "UPDATE users SET password_hash = :password_hash, reset_token = NULL, reset_token_expira = NULL WHERE id = :id";
    $stmt = $nom_variable_connexio->prepare($sql);
    $resultado = $stmt->execute([
        ':password_hash' => $passwordHash,
        ':id' => (int)$user['id']
    ]);
    return $resultado;
}

function limpiarTokenRecuperacion($userId) {
    global $nom_variable_connexio;
    
    $sql = "UPDATE users SET reset_token = NULL, reset_token_expira = NULL WHERE id = :id";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute([':id' => (int)$userId]);
}