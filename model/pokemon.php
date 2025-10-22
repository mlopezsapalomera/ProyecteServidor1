<?php
// model/pokemon.php
// CRUD básico para pokemons / elementos


$nom_variable_connexio = require __DIR__ . '/db.php';

function getAllPokemons($limit = 100, $offset = 0) {
    global $nom_variable_connexio;
    $sql = "SELECT * FROM pokemons ORDER BY id DESC LIMIT :limit OFFSET :offset";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getPokemonById($id) {
    global $nom_variable_connexio;
    $sql = "SELECT * FROM pokemons WHERE id = :id";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

function insertPokemon($titulo, $descripcion = null) {
    global $nom_variable_connexio;
    $sql = "INSERT INTO pokemons (titulo, descripcion) VALUES (:titulo, :descripcion)";
    $stmt = $nom_variable_connexio->prepare($sql);
    return $stmt->execute([
        ':titulo' => $titulo,
        ':descripcion' => $descripcion
    ]);
}

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

function deletePokemon($id) {
    global $nom_variable_connexio;
    $sql = "DELETE FROM pokemons WHERE id = :id";
    $stmt = $nom_variable_connexio->prepare($sql);
    return $stmt->execute([':id' => $id]);
}

// Devuelve el número total de pokemons
function countPokemons() {
    global $nom_variable_connexio;
    $sql = "SELECT COUNT(*) as total FROM pokemons";
    $stmt = $nom_variable_connexio->query($sql);
    $row = $stmt->fetch();
    return $row ? (int)$row['total'] : 0;
}
