<?php

function generarTokenRecuperacion($email) {
    $nom_variable_connexio = userDbConnection();

    $user = obtenerUsuarioPorEmail($email);
    if (!$user) {
        return false;
    }

    $token = bin2hex(random_bytes(32));
    $expira = date('Y-m-d H:i:s', time() + (5 * 60));

    $sql = "UPDATE users SET reset_token = :token, reset_token_expira = :expira WHERE email = :email";
    $stmt = $nom_variable_connexio->prepare($sql);
    $ok = $stmt->execute([
        ':token' => $token,
        ':expira' => $expira,
        ':email' => $email,
    ]);

    return $ok ? $token : false;
}

function verificarTokenRecuperacion($token) {
    if (empty($token)) {
        return false;
    }

    $nom_variable_connexio = userDbConnection();
    $sql = "SELECT * FROM users WHERE reset_token = :token AND reset_token_expira > NOW() LIMIT 1";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute([':token' => $token]);
    return $stmt->fetch();
}

function limpiarTokenRecuperacion($userId) {
    $nom_variable_connexio = userDbConnection();
    $sql = "UPDATE users SET reset_token = NULL, reset_token_expira = NULL WHERE id = :id";
    $stmt = $nom_variable_connexio->prepare($sql);
    return $stmt->execute([':id' => (int)$userId]);
}

function resetearContrasenaConToken($token, $nuevaPassword) {
    $user = verificarTokenRecuperacion($token);
    if (!$user) {
        return false;
    }

    $passwordHash = password_hash($nuevaPassword, PASSWORD_DEFAULT);
    $okPassword = actualizarContrasena((int)$user['id'], $passwordHash);

    if (!$okPassword) {
        return false;
    }

    return limpiarTokenRecuperacion((int)$user['id']);
}
