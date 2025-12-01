<?php
// model/user.php

// Primero, obtenemos la conexión a la base de datos incluyendo el archivo db.php.
$nom_variable_connexio = require __DIR__ . '/db.php';

// Función para obtener un usuario por su nombre de usuario.
// Recibe el nombre de usuario, prepara la consulta y devuelve el usuario si existe.
function obtenerUsuarioPorNombre($username) {
    global $nom_variable_connexio;
    $sql = "SELECT * FROM users WHERE username = :username LIMIT 1";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute([':username' => $username]);
    return $stmt->fetch();
}

// Función para obtener un usuario por su email.
// Recibe el email, prepara la consulta y devuelve el usuario si existe.
function obtenerUsuarioPorEmail($email) {
    global $nom_variable_connexio;
    $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute([':email' => $email]);
    return $stmt->fetch();
}

// Función para obtener un usuario por su id.
// Recibe el id, prepara la consulta y devuelve el usuario si existe.
function obtenerUsuarioPorId($id) {
    global $nom_variable_connexio;
    $sql = "SELECT * FROM users WHERE id = :id LIMIT 1";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute([':id' => (int)$id]);
    return $stmt->fetch();
}

// Función para crear un nuevo usuario en la base de datos.
// Recibe el nombre de usuario, email y el hash de la contraseña.
// Inserta el usuario y devuelve el id del nuevo usuario si todo va bien, o false si falla.
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

// Función para verificar las credenciales de un usuario.
// Permite iniciar sesión usando nombre de usuario o email y compara la contraseña con el hash almacenado.
function verificarCredencialesUsuario($usernameOrEmail, $password) {
    // Primero, intentamos buscar el usuario por nombre de usuario.
    $user = obtenerUsuarioPorNombre($usernameOrEmail);
    // Si no se encuentra, intentamos buscarlo por email.
    if (!$user) $user = obtenerUsuarioPorEmail($usernameOrEmail);
    // Si no existe el usuario, devolvemos false.
    if (!$user) return false;
    // Verificamos la contraseña usando password_verify.
    if (password_verify($password, $user['password_hash'])) {
        // Si la contraseña es correcta, devolvemos el usuario.
        return $user;
    }
    // Si la contraseña no es correcta, devolvemos false.
    return false;
}
