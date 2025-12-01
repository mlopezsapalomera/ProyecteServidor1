<?php
// model/pokemon.php
// CRUD básico para pokemons / elementos

// Primero, obtenemos la conexión a la base de datos incluyendo el archivo db.php.
// Esto nos permite usar la variable de conexión en todas las funciones de este archivo.
/** @var PDO $nom_variable_connexio Conexión PDO a la base de datos */
$nom_variable_connexio = require __DIR__ . '/db.php';

/**
 * Función para obtener una lista paginada de pokémons ordenados por id descendente.
 * Recibe el límite de registros a devolver y el desplazamiento (offset) para la paginación.
 * Además, incluye el nombre del autor mediante un LEFT JOIN con la tabla de usuarios.
 * @param int $limit  Número máximo de registros a devolver
 * @param int $offset Desplazamiento inicial
 * @return array<int, array<string,mixed>> Lista de pokémons
 */
function obtenerPokemons($limit = 100, $offset = 0) {
    global $nom_variable_connexio;
    // Preparamos una consulta SQL que obtiene todos los pokémons junto con el nombre de usuario del autor.
    $sql = "SELECT p.*, u.username AS autor_username
            FROM pokemons p
            LEFT JOIN users u ON p.user_id = u.id
            ORDER BY p.id DESC
            LIMIT :limit OFFSET :offset";
    $stmt = $nom_variable_connexio->prepare($sql);
    // Vinculamos los parámetros limit y offset como enteros para evitar inyección SQL.
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    // Devolvemos todos los registros obtenidos como un array.
    return $stmt->fetchAll();
}

/**
 * Función para obtener un pokemon específico por su identificador.
 * Recibe el id del pokemon y devuelve su registro completo si existe.
 * @param int $id Identificador del pokemon
 * @return array<string,mixed>|false Registro o false si no existe
 */
function obtenerPokemonPorId($id) {
    global $nom_variable_connexio;
    // Preparamos una consulta SQL para buscar el pokemon por su id.
    $sql = "SELECT * FROM pokemons WHERE id = :id";
    $stmt = $nom_variable_connexio->prepare($sql);
    // Vinculamos el parámetro id como entero para evitar inyección SQL.
    $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
    $stmt->execute();
    // Devolvemos el registro encontrado o false si no existe.
    return $stmt->fetch();
}

/**
 * Función para insertar un nuevo pokemon en la base de datos.
 * Recibe el título (obligatorio), la descripción (opcional) y el id del usuario propietario (opcional).
 * @param string $titulo Título del pokemon
 * @param string|null $descripcion Descripción (opcional)
 * @return bool Éxito de la operación
 */
function insertarPokemon($titulo, $descripcion = null, $user_id = null) {
    global $nom_variable_connexio;
    // Preparamos una consulta SQL para insertar un nuevo registro en la tabla pokemons.
    $sql = "INSERT INTO pokemons (titulo, descripcion, user_id) VALUES (:titulo, :descripcion, :user_id)";
    $stmt = $nom_variable_connexio->prepare($sql);
    // Ejecutamos la consulta con los parámetros proporcionados y devolvemos el resultado (true/false).
    return $stmt->execute([
        ':titulo' => $titulo,
        ':descripcion' => $descripcion,
        ':user_id' => $user_id !== null ? (int)$user_id : null
    ]);
}

/**
 * Función para actualizar un pokemon existente en la base de datos.
 * Recibe el id del pokemon a modificar, el nuevo título y la nueva descripción.
 * @param int $id ID del pokemon
 * @param string $titulo Nuevo título
 * @param string|null $descripcion Nueva descripción
 * @return bool Éxito de la operación
 */
function actualizarPokemon($id, $titulo, $descripcion = null) {
    global $nom_variable_connexio;
    // Preparamos una consulta SQL para actualizar el título y descripción del pokemon especificado por id.
    $sql = "UPDATE pokemons SET titulo = :titulo, descripcion = :descripcion WHERE id = :id";
    $stmt = $nom_variable_connexio->prepare($sql);
    // Ejecutamos la consulta con los nuevos valores y devolvemos el resultado (true/false).
    return $stmt->execute([
        ':titulo' => $titulo,
        ':descripcion' => $descripcion,
        ':id' => $id
    ]);
}

/**
 * Función para eliminar un pokemon de la base de datos por su ID.
 * Recibe el id del pokemon a eliminar y devuelve el resultado de la operación.
 * @param int $id ID del pokemon
 * @return bool Éxito de la operación
 */
function eliminarPokemon($id) {
    global $nom_variable_connexio;
    // Preparamos una consulta SQL para eliminar el pokemon especificado por id.
    $sql = "DELETE FROM pokemons WHERE id = :id";
    $stmt = $nom_variable_connexio->prepare($sql);
    // Ejecutamos la consulta y devolvemos el resultado (true/false).
    return $stmt->execute([':id' => $id]);
}

/**
 * Función para contar el número total de pokémons en la base de datos.
 * Útil para calcular la paginación y saber cuántas páginas existen.
 * @return int Total de registros en la tabla pokemons
 */
function contarPokemons() {
    global $nom_variable_connexio;
    // Ejecutamos una consulta SQL para contar todos los registros en la tabla pokemons.
    $sql = "SELECT COUNT(*) as total FROM pokemons";
    $stmt = $nom_variable_connexio->query($sql);
    $row = $stmt->fetch();
    // Devolvemos el total de registros o 0 si no hay ninguno.
    return $row ? (int)$row['total'] : 0;
}
