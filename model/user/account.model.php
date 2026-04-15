<?php

function obtenerUsuarioPorNombre($username) {
    $nom_variable_connexio = userDbConnection();
    $sql = "SELECT * FROM users WHERE username = :username LIMIT 1";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute([':username' => $username]);
    return $stmt->fetch();
}

function obtenerUsuarioPorEmail($email) {
    $nom_variable_connexio = userDbConnection();
    $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute([':email' => $email]);
    return $stmt->fetch();
}

function obtenerUsuarioPorId($id) {
    $nom_variable_connexio = userDbConnection();
    $sql = "SELECT * FROM users WHERE id = :id LIMIT 1";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute([':id' => (int)$id]);
    return $stmt->fetch();
}

function crearUsuario($username, $email, $passwordHash, $profileImage = 'userDefaultImg.jpg') {
    $nom_variable_connexio = userDbConnection();
    $sql = "INSERT INTO users (username, email, password_hash, profile_image) VALUES (:username, :email, :password_hash, :profile_image)";
    $stmt = $nom_variable_connexio->prepare($sql);
    $ok = $stmt->execute([
        ':username' => $username,
        ':email' => $email,
        ':password_hash' => $passwordHash,
        ':profile_image' => $profileImage,
    ]);

    if (!$ok) {
        return false;
    }

    return (int)$nom_variable_connexio->lastInsertId();
}

function verificarCredencialesUsuario($usernameOrEmail, $password) {
    $user = obtenerUsuarioPorNombre($usernameOrEmail);
    if (!$user) {
        $user = obtenerUsuarioPorEmail($usernameOrEmail);
    }
    if (!$user) {
        return false;
    }

    return password_verify($password, $user['password_hash']) ? $user : false;
}

function actualizarPerfil($userId, $username, $profileImage = null) {
    $nom_variable_connexio = userDbConnection();

    if ($profileImage !== null && $profileImage !== '') {
        $sql = "UPDATE users SET username = :username, profile_image = :profile_image WHERE id = :id";
        $stmt = $nom_variable_connexio->prepare($sql);
        return $stmt->execute([
            ':username' => $username,
            ':profile_image' => $profileImage,
            ':id' => (int)$userId,
        ]);
    }

    $sql = "UPDATE users SET username = :username WHERE id = :id";
    $stmt = $nom_variable_connexio->prepare($sql);
    return $stmt->execute([
        ':username' => $username,
        ':id' => (int)$userId,
    ]);
}

function existeUsername($username, $excludeId = null) {
    $nom_variable_connexio = userDbConnection();

    if ($excludeId !== null) {
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

function existeCorreo($email, $excludeId = null) {
    $nom_variable_connexio = userDbConnection();

    if ($excludeId !== null) {
        $sql = "SELECT COUNT(*) as total FROM users WHERE email = :email AND id != :id";
        $stmt = $nom_variable_connexio->prepare($sql);
        $stmt->execute([':email' => $email, ':id' => (int)$excludeId]);
    } else {
        $sql = "SELECT COUNT(*) as total FROM users WHERE email = :email";
        $stmt = $nom_variable_connexio->prepare($sql);
        $stmt->execute([':email' => $email]);
    }

    $row = $stmt->fetch();
    return $row && $row['total'] > 0;
}

function actualizarContrasena($userId, $nuevaPasswordHash) {
    $nom_variable_connexio = userDbConnection();
    $sql = "UPDATE users SET password_hash = :password_hash WHERE id = :id";
    $stmt = $nom_variable_connexio->prepare($sql);
    return $stmt->execute([
        ':password_hash' => $nuevaPasswordHash,
        ':id' => (int)$userId,
    ]);
}

function buscarUsuarios($query, $limit = 10) {
    $nom_variable_connexio = userDbConnection();
    $sql = "SELECT id, username, email, profile_image, role
            FROM users
            WHERE username LIKE :query
            ORDER BY username ASC
            LIMIT :limit";

    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->bindValue(':query', '%' . $query . '%', PDO::PARAM_STR);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}
