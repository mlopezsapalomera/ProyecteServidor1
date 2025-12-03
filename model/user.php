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
function crearUsuario($username, $email, $passwordHash) {
    global $nom_variable_connexio;
    $sql = "INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :password_hash)";
    $stmt = $nom_variable_connexio->prepare($sql);
    $ok = $stmt->execute([
        ':username' => $username,
        ':email' => $email,
        ':password_hash' => $passwordHash
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
