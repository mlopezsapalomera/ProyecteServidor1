<?php
// model/db.php
require_once __DIR__ . '/../env.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Export $conn para uso global
return $conn;
