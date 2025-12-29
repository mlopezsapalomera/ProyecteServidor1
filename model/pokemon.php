<?php
// model/pokemon.php
// Modelo para gestión de pokémons

// Obtener conexión a la base de datos
$nom_variable_connexio = require __DIR__ . '/db.php';

// Obtener lista paginada de pokémons
function obtenerPokemons($limit = 100, $offset = 0) {
    global $nom_variable_connexio;
    // Consulta con JOIN para obtener el nombre del autor
    $sql = "SELECT p.*, u.username AS autor_username
            FROM pokemons p
            LEFT JOIN users u ON p.user_id = u.id
            ORDER BY p.id DESC
            LIMIT :limit OFFSET :offset";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Obtener pokemon por ID
function obtenerPokemonPorId($id) {
    global $nom_variable_connexio;
    $sql = "SELECT * FROM pokemons WHERE id = :id";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

// Insertar nuevo pokemon
function insertarPokemon($titulo, $descripcion = null, $user_id = null) {
    global $nom_variable_connexio;
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
    global $nom_variable_connexio;
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
    global $nom_variable_connexio;
    $sql = "DELETE FROM pokemons WHERE id = :id";
    $stmt = $nom_variable_connexio->prepare($sql);
    return $stmt->execute([':id' => $id]);
}

// Contar total de pokémons
function contarPokemons() {
    global $nom_variable_connexio;
    $sql = "SELECT COUNT(*) as total FROM pokemons";
    $stmt = $nom_variable_connexio->query($sql);
    $row = $stmt->fetch();
    return $row ? (int)$row['total'] : 0;
}

/*
function ordenarPokemons($order = 'Desc') {
    global $nom_variable_connexio;

    // Sanitizamos la entrada
    $order = ($order === 'Asc') ? 'ASC' : 'DESC';

    $sql = "SELECT * FROM pokemons ORDER BY id $order"; // o el campo que quieras
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
*/
