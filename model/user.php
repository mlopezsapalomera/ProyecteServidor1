<?php
// model/user.php

$nom_variable_connexio = require __DIR__ . '/db.php';

function obtenirUsuariPerNom($username) {
    global $nom_variable_connexio;
    $sql = "SELECT * FROM users WHERE username = :username LIMIT 1";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute([':username' => $username]);
    return $stmt->fetch();
}

function obtenirUsuariPerEmail($email) {
    global $nom_variable_connexio;
    $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute([':email' => $email]);
    return $stmt->fetch();
}

function obtenirUsuariPerId($id) {
    global $nom_variable_connexio;
    $sql = "SELECT * FROM users WHERE id = :id LIMIT 1";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute([':id' => (int)$id]);
    return $stmt->fetch();
}

function crearUsuari($username, $email, $passwordHash) {
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

function verificarCredencialsUsuari($usernameOrEmail, $password) {
    // Buscar per username o email
    $user = obtenirUsuariPerNom($usernameOrEmail);
    if (!$user) $user = obtenirUsuariPerEmail($usernameOrEmail);
    if (!$user) return false;
    if (password_verify($password, $user['password_hash'])) {
        return $user;
    }
    return false;
}
