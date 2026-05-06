<?php
// model/api_tokens.php
// Modelo para gestión de tokens de API

function apiTokensDbConnection() {
    static $conn = null;
    if ($conn === null) {
        $conn = require __DIR__ . '/db.php';
    }
    return $conn;
}

/**
 * Genera un token aleatorio seguro
 * @return string Token sin hash (lo que se entrega al usuario)
 */
function generarTokenApi($longitud = 32): string
{
    return bin2hex(random_bytes($longitud));
}

/**
 * Hash del token para almacenamiento seguro
 * @param string $token Token sin hash
 * @return string Token hasheado
 */
function hashearTokenApi(string $token): string
{
    return hash('sha256', $token);
}

/**
 * Crear nuevo token API
 * @param string $name Nombre descriptivo del token
 * @param string $description Descripción opcional
 * @param int $diasExpiracion Días hasta que expire (default 30)
 * @return array ['token' => token_sin_hash, 'id' => id_en_db]
 */
function crearTokenApi(string $name, string $description = null, int $diasExpiracion = 30): array
{
    $db = apiTokensDbConnection();
    $token = generarTokenApi();
    $tokenHash = hashearTokenApi($token);
    $expiresAt = date('Y-m-d H:i:s', strtotime("+$diasExpiracion days"));
    
    $sql = "INSERT INTO api_tokens (token_hash, name, description, expires_at, is_active)
            VALUES (:token_hash, :name, :description, :expires_at, 1)";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':token_hash' => $tokenHash,
        ':name' => $name,
        ':description' => $description,
        ':expires_at' => $expiresAt,
    ]);
    
    return [
        'token' => $token,
        'id' => (int)$db->lastInsertId(),
        'expires_at' => $expiresAt,
    ];
}

/**
 * Validar token API
 * @param string $token Token sin hash (lo que envía el cliente)
 * @return array|false Datos del token o false si no es válido
 */
function validarTokenApi(string $token): array|false
{
    $db = apiTokensDbConnection();
    $tokenHash = hashearTokenApi($token);
    
    $sql = "SELECT id, name, description, expires_at, is_active, last_used_at
            FROM api_tokens
            WHERE token_hash = :token_hash
            LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->execute([':token_hash' => $tokenHash]);
    $tokenData = $stmt->fetch();
    
    if (!$tokenData) {
        return false;
    }
    
    // Verificar que no ha expirado
    if (strtotime($tokenData['expires_at']) < time()) {
        return false;
    }
    
    // Verificar que está activo
    if (!$tokenData['is_active']) {
        return false;
    }
    
    // Actualizar last_used_at
    $sqlUpdate = "UPDATE api_tokens SET last_used_at = NOW() WHERE id = :id";
    $stmtUpdate = $db->prepare($sqlUpdate);
    $stmtUpdate->execute([':id' => $tokenData['id']]);
    
    return $tokenData;
}

/**
 * Obtener todos los tokens activos
 * @return array Lista de tokens
 */
function obtenerTokensApi(): array
{
    $db = apiTokensDbConnection();
    $sql = "SELECT id, name, description, expires_at, is_active, last_used_at, created_at
            FROM api_tokens
            ORDER BY created_at DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Revocar un token
 * @param int $tokenId ID del token
 * @return bool true si se revocó correctamente
 */
function revocarTokenApi(int $tokenId): bool
{
    $db = apiTokensDbConnection();
    $sql = "UPDATE api_tokens SET is_active = 0 WHERE id = :id";
    $stmt = $db->prepare($sql);
    return $stmt->execute([':id' => $tokenId]);
}

/**
 * Eliminar un token
 * @param int $tokenId ID del token
 * @return bool true si se eliminó correctamente
 */
function eliminarTokenApi(int $tokenId): bool
{
    $db = apiTokensDbConnection();
    $sql = "DELETE FROM api_tokens WHERE id = :id";
    $stmt = $db->prepare($sql);
    return $stmt->execute([':id' => $tokenId]);
}

/**
 * Obtener token específico por ID
 * @param int $tokenId ID del token
 * @return array|false Datos del token o false
 */
function obtenerTokenApi(int $tokenId): array|false
{
    $db = apiTokensDbConnection();
    $sql = "SELECT id, name, description, expires_at, is_active, last_used_at, created_at
            FROM api_tokens
            WHERE id = :id
            LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->execute([':id' => $tokenId]);
    return $stmt->fetch();
}
