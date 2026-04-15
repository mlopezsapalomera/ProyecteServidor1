<?php

function crearRememberToken($userId, $dias = 30) {
    $nom_variable_connexio = userDbConnection();

    $token = bin2hex(random_bytes(64));
    $tokenHash = hash('sha256', $token);
    $expiresAt = date('Y-m-d H:i:s', time() + ($dias * 24 * 60 * 60));

    $sql = "INSERT INTO remember_tokens (user_id, token_hash, expires_at)
            VALUES (:user_id, :token_hash, :expires_at)";
    $stmt = $nom_variable_connexio->prepare($sql);
    $ok = $stmt->execute([
        ':user_id' => (int)$userId,
        ':token_hash' => $tokenHash,
        ':expires_at' => $expiresAt,
    ]);

    return $ok ? $token : false;
}

function verificarRememberToken($token) {
    if (empty($token)) {
        return false;
    }

    $nom_variable_connexio = userDbConnection();
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
    }

    return false;
}

function renovarRememberToken($tokenHash, $dias = 30) {
    $nom_variable_connexio = userDbConnection();
    $expiresAt = date('Y-m-d H:i:s', time() + ($dias * 24 * 60 * 60));

    $sql = "UPDATE remember_tokens
            SET expires_at = :expires_at
            WHERE token_hash = :token_hash";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute([
        ':expires_at' => $expiresAt,
        ':token_hash' => $tokenHash,
    ]);
}

function eliminarRememberToken($token) {
    if (empty($token)) {
        return;
    }

    $nom_variable_connexio = userDbConnection();
    $tokenHash = hash('sha256', $token);

    $sql = "DELETE FROM remember_tokens WHERE token_hash = :token_hash";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute([':token_hash' => $tokenHash]);
}
