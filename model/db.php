<?php
// model/db.php
require_once __DIR__ . '/../env.php';

try {
    // Connexió segons requisit de l'enunciat (pt03_marcos_lopez, root, password buida)
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=pt03_marcos_lopez;charset=utf8mb4';
    $nom_variable_connexio = new PDO($dsn, 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Error de conexión: ' . $e->getMessage());
}

return $nom_variable_connexio;
