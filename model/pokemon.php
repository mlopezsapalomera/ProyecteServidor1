<?php
// model/pokemon.php
// CRUD básico para pokemons / elementos

$conn = require __DIR__ . '/db.php';

function getAllPokemons($limit = 100, $offset = 0) {
    global $conn;
    $query = "SELECT * FROM elementos ORDER BY id DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $limit, $offset);
    $stmt->execute();
    return $stmt->get_result();
}

function getPokemonById($id) {
    global $conn;
    $query = "SELECT * FROM elementos WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function insertPokemon($titulo, $descripcion = null) {
    global $conn;
    $query = "INSERT INTO elementos (titulo, descripcion) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $titulo, $descripcion);
    return $stmt->execute();
}

function updatePokemon($id, $titulo, $descripcion = null) {
    global $conn;
    $query = "UPDATE elementos SET titulo = ?, descripcion = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssi', $titulo, $descripcion, $id);
    return $stmt->execute();
}

function deletePokemon($id) {
    global $conn;
    $query = "DELETE FROM elementos WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id);
    return $stmt->execute();
}
