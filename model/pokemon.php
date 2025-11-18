<?php
// model/pokemon.php
// CRUD básico para pokemons / elementos


/** @var PDO $nom_variable_connexio Conexión PDO a la base de datos */
$nom_variable_connexio = require __DIR__ . '/db.php';

/**
 * Obtiene una lista paginada de pokémons ordenados por id desc.
 * @param int $limit  Número máximo de registros a devolver
 * @param int $offset Desplazamiento inicial
 * @return array<int, array<string,mixed>> Lista de pokémons
 */
function getAllPokemons($limit = 100, $offset = 0) {
    global $nom_variable_connexio;
    // Traer también el nombre del autor (si existe)
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

/**
 * Obtiene un pokemon por su identificador.
 * @param int $id Identificador del pokemon
 * @return array<string,mixed>|false Registro o false si no existe
 */
function getPokemonById($id) {
    global $nom_variable_connexio;
    $sql = "SELECT * FROM pokemons WHERE id = :id";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

/**
 * Inserta un nuevo pokemon.
 * @param string $titulo Título del pokemon
 * @param string|null $descripcion Descripción (opcional)
 * @return bool Éxito de la operación
 */
function insertPokemon($titulo, $descripcion = null, $user_id = null) {
    global $nom_variable_connexio;
    $sql = "INSERT INTO pokemons (titulo, descripcion, user_id) VALUES (:titulo, :descripcion, :user_id)";
    $stmt = $nom_variable_connexio->prepare($sql);
    return $stmt->execute([
        ':titulo' => $titulo,
        ':descripcion' => $descripcion,
        ':user_id' => $user_id !== null ? (int)$user_id : null
    ]);
}

/**
 * Actualiza un pokemon existente.
 * @param int $id ID del pokemon
 * @param string $titulo Nuevo título
 * @param string|null $descripcion Nueva descripción
 * @return bool Éxito de la operación
 */
function updatePokemon($id, $titulo, $descripcion = null) {
    global $nom_variable_connexio;
    $sql = "UPDATE pokemons SET titulo = :titulo, descripcion = :descripcion WHERE id = :id";
    $stmt = $nom_variable_connexio->prepare($sql);
    return $stmt->execute([
        ':titulo' => $titulo,
        ':descripcion' => $descripcion,
        ':id' => $id
    ]);
}

/**
 * Elimina un pokemon por ID.
 * @param int $id ID del pokemon
 * @return bool Éxito de la operación
 */
function deletePokemon($id) {
    global $nom_variable_connexio;
    $sql = "DELETE FROM pokemons WHERE id = :id";
    $stmt = $nom_variable_connexio->prepare($sql);
    return $stmt->execute([':id' => $id]);
}

/**
 * Devuelve el número total de pokémons.
 * @return int Total de registros en la tabla pokemons
 */
function countPokemons() {
    global $nom_variable_connexio;
    $sql = "SELECT COUNT(*) as total FROM pokemons";
    $stmt = $nom_variable_connexio->query($sql);
    $row = $stmt->fetch();
    return $row ? (int)$row['total'] : 0;
}
