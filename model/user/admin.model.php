<?php

function obtenerTodosLosUsuarios($excluirAdmins = true) {
    $nom_variable_connexio = userDbConnection();

    if ($excluirAdmins) {
        $consulta = "SELECT id, username, email, profile_image, role, created_at FROM users WHERE role != 'admin' ORDER BY created_at DESC";
    } else {
        $consulta = "SELECT id, username, email, profile_image, role, created_at FROM users ORDER BY created_at DESC";
    }

    $stmt = $nom_variable_connexio->prepare($consulta);
    $stmt->execute();
    return $stmt->fetchAll();
}

function obtenerTodosLosUsuariosConPublicaciones($excluirAdmins = true) {
    $nom_variable_connexio = userDbConnection();

    $filtroAdmin = $excluirAdmins ? "WHERE u.role != 'admin'" : "";
    $consulta = "SELECT
                    u.id,
                    u.username,
                    u.email,
                    u.profile_image,
                    u.role,
                    u.created_at,
                    COUNT(p.id) AS num_publicaciones
                FROM users u
                LEFT JOIN pokemons p ON p.user_id = u.id
                $filtroAdmin
                GROUP BY u.id, u.username, u.email, u.profile_image, u.role, u.created_at
                ORDER BY u.created_at DESC";

    $stmt = $nom_variable_connexio->prepare($consulta);
    $stmt->execute();
    return $stmt->fetchAll();
}

function contarPublicacionesUsuario($userId) {
    $nom_variable_connexio = userDbConnection();
    $sql = "SELECT COUNT(*) as total FROM pokemons WHERE user_id = :user_id";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute([':user_id' => (int)$userId]);
    $row = $stmt->fetch();
    return $row ? (int)$row['total'] : 0;
}

function eliminarUsuario($userId) {
    $nom_variable_connexio = userDbConnection();

    try {
        $nom_variable_connexio->beginTransaction();

        $sql1 = "DELETE FROM pokemons WHERE user_id = :user_id";
        $stmt1 = $nom_variable_connexio->prepare($sql1);
        $stmt1->execute([':user_id' => (int)$userId]);

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
    $nom_variable_connexio = userDbConnection();

    if (!in_array($nuevoRol, ['user', 'admin'], true)) {
        return false;
    }

    $sql = "UPDATE users SET role = :role WHERE id = :id";
    $stmt = $nom_variable_connexio->prepare($sql);
    return $stmt->execute([
        ':role' => $nuevoRol,
        ':id' => (int)$userId,
    ]);
}
