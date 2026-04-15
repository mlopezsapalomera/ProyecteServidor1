<?php
// model/pokemon.php
// Modelo para gestión de pokémons

// Obtener conexión a la base de datos
function pokemonDbConnection() {
    static $conn = null;

    if ($conn === null) {
        $conn = require __DIR__ . '/db.php';
    }

    return $conn;
}

// Obtener lista paginada de pokémons
function obtenerPokemons($limit = 100, $offset = 0, $orderBy = 'id', $orderDir = 'DESC') {
    $nom_variable_connexio = pokemonDbConnection();
    
    // Validar campo de ordenación (whitelist)
    $camposPermitidos = ['id', 'titulo', 'created_at'];
    if (!in_array($orderBy, $camposPermitidos)) {
        $orderBy = 'id';
    }
    
    // Validar dirección de ordenación
    $orderDir = strtoupper($orderDir);
    if ($orderDir !== 'ASC' && $orderDir !== 'DESC') {
        $orderDir = 'DESC';
    }
    
    // Consulta con JOIN para obtener el nombre del autor y foto de perfil
    $sql = "SELECT p.*, u.username AS autor_username, u.profile_image AS autor_profile_image, u.id AS autor_id
            FROM pokemons p
            LEFT JOIN users u ON p.user_id = u.id
            ORDER BY p.$orderBy $orderDir
            LIMIT :limit OFFSET :offset";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Obtener pokemon por ID
function obtenerPokemonPorId($id) {
    $nom_variable_connexio = pokemonDbConnection();
    $sql = "SELECT * FROM pokemons WHERE id = :id";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

// Insertar nuevo pokemon
function insertarPokemon($titulo, $descripcion = null, $user_id = null) {
    $nom_variable_connexio = pokemonDbConnection();
    $sql = "INSERT INTO pokemons (titulo, descripcion, user_id) VALUES (:titulo, :descripcion, :user_id)";
    $stmt = $nom_variable_connexio->prepare($sql);
    return $stmt->execute([
        ':titulo' => $titulo,
        ':descripcion' => $descripcion,
        ':user_id' => $user_id !== null ? (int)$user_id : null
    ]);
}

// Actualizar pokemon existente
function actualizarPokemon($id, $titulo, $descripcion = null) {
    $nom_variable_connexio = pokemonDbConnection();
    $sql = "UPDATE pokemons SET titulo = :titulo, descripcion = :descripcion WHERE id = :id";
    $stmt = $nom_variable_connexio->prepare($sql);
    return $stmt->execute([
        ':titulo' => $titulo,
        ':descripcion' => $descripcion,
        ':id' => $id
    ]);
}

// Eliminar pokemon por ID
function eliminarPokemon($id) {
    $nom_variable_connexio = pokemonDbConnection();
    $sql = "DELETE FROM pokemons WHERE id = :id";
    $stmt = $nom_variable_connexio->prepare($sql);
    return $stmt->execute([':id' => $id]);
}

// Contar total de pokémons
function contarPokemons() {
    $nom_variable_connexio = pokemonDbConnection();
    $sql = "SELECT COUNT(*) as total FROM pokemons";
    $stmt = $nom_variable_connexio->query($sql);
    $row = $stmt->fetch();
    return $row ? (int)$row['total'] : 0;
}

// Obtener pokémons de un usuario específico
function obtenerPokemonsPorUsuario($userId, $limit = 100, $offset = 0) {
    $nom_variable_connexio = pokemonDbConnection();
    $sql = "SELECT p.*, u.username AS autor_username, u.profile_image AS autor_profile_image
            FROM pokemons p
            LEFT JOIN users u ON p.user_id = u.id
            WHERE p.user_id = :user_id
            ORDER BY p.id DESC
            LIMIT :limit OFFSET :offset";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}
// Buscar pokémons por título (para búsqueda AJAX)
function buscarPokemons($query, $limit = 10) {
    $nom_variable_connexio = pokemonDbConnection();
    $sql = "SELECT p.*, u.username AS autor_username, u.profile_image AS autor_profile_image, u.id AS autor_id
            FROM pokemons p
            LEFT JOIN users u ON p.user_id = u.id
            WHERE p.titulo LIKE :query 
            ORDER BY p.created_at DESC 
            LIMIT :limit";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->bindValue(':query', '%' . $query . '%', PDO::PARAM_STR);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}
// Contar pokémons de un usuario específico
function contarPokemonsPorUsuario($userId) {
    $nom_variable_connexio = pokemonDbConnection();
    $sql = "SELECT COUNT(*) as total FROM pokemons WHERE user_id = :user_id";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch();
    return $row ? (int)$row['total'] : 0;
}

function ordenarPokemons($order = 'Desc') {
    $nom_variable_connexio = pokemonDbConnection();

    $order = ($order === 'Asc') ? 'ASC' : 'DESC';

    $sql = "SELECT * FROM pokemons ORDER BY id $order";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
