<?php

function oauthGoogleConfigurado() {
    if (!defined('GOOGLE_CLIENT_ID') || !defined('GOOGLE_CLIENT_SECRET')) {
        return false;
    }

    $clientId = trim((string)GOOGLE_CLIENT_ID);
    $clientSecret = trim((string)GOOGLE_CLIENT_SECRET);

    if ($clientId === '' || $clientSecret === '') {
        return false;
    }

    if (stripos($clientId, 'TU_GOOGLE_CLIENT_ID') !== false) {
        return false;
    }

    if (stripos($clientSecret, 'TU_GOOGLE_CLIENT_SECRET') !== false) {
        return false;
    }

    return true;
}

function oauthGithubConfigurado() {
    if (!defined('GITHUB_CLIENT_ID') || !defined('GITHUB_CLIENT_SECRET')) {
        return false;
    }

    $clientId = trim((string)GITHUB_CLIENT_ID);
    $clientSecret = trim((string)GITHUB_CLIENT_SECRET);

    if ($clientId === '' || $clientSecret === '') {
        return false;
    }

    if (stripos($clientId, 'TU_GITHUB_CLIENT_ID') !== false) {
        return false;
    }

    if (stripos($clientSecret, 'TU_GITHUB_CLIENT_SECRET') !== false) {
        return false;
    }

    return true;
}

function crearUsuarioOAuth($username, $email, $provider, $oauthUid, $oauthToken, $profileImage = 'userDefaultImg.jpg') {
    $nom_variable_connexio = userDbConnection();

    $sql = "INSERT INTO users (username, email, password_hash, oauth_provider, oauth_uid, oauth_token, profile_image)
            VALUES (:username, :email, NULL, :provider, :oauth_uid, :oauth_token, :profile_image)";

    $stmt = $nom_variable_connexio->prepare($sql);
    $ok = $stmt->execute([
        ':username' => $username,
        ':email' => $email,
        ':provider' => $provider,
        ':oauth_uid' => $oauthUid,
        ':oauth_token' => $oauthToken,
        ':profile_image' => $profileImage,
    ]);

    return $ok ? (int)$nom_variable_connexio->lastInsertId() : false;
}

function obtenerUsuarioPorOAuthUID($provider, $oauthUid) {
    $nom_variable_connexio = userDbConnection();

    $sql = "SELECT * FROM users WHERE oauth_provider = :provider AND oauth_uid = :oauth_uid LIMIT 1";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute([
        ':provider' => $provider,
        ':oauth_uid' => $oauthUid,
    ]);

    return $stmt->fetch();
}

function vincularOAuthAUsuario($userId, $provider, $oauthUid, $oauthToken) {
    $nom_variable_connexio = userDbConnection();

    $sql = "UPDATE users SET oauth_provider = :provider, oauth_uid = :oauth_uid, oauth_token = :oauth_token WHERE id = :id";
    $stmt = $nom_variable_connexio->prepare($sql);
    return $stmt->execute([
        ':provider' => $provider,
        ':oauth_uid' => $oauthUid,
        ':oauth_token' => $oauthToken,
        ':id' => (int)$userId,
    ]);
}

function actualizarOAuthToken($userId, $oauthToken) {
    $nom_variable_connexio = userDbConnection();

    $sql = "UPDATE users SET oauth_token = :oauth_token WHERE id = :id";
    $stmt = $nom_variable_connexio->prepare($sql);
    return $stmt->execute([
        ':oauth_token' => $oauthToken,
        ':id' => (int)$userId,
    ]);
}
