<?php
// model/db.php

// Primero, incluimos el archivo de configuración de entorno para obtener las constantes de conexión (como DB_HOST).
require_once __DIR__ . '/../env.php';

// A continuación, intentamos establecer la conexión con la base de datos usando PDO.
try {
    // Creamos el Data Source Name (DSN) con los datos de conexión: host, nombre de la base de datos y charset.
    // En este caso, la base de datos es 'pt03_marcos_lopez', el usuario es 'root' y la contraseña está vacía.
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=pt03_marcos_lopez;charset=utf8mb4';
    // Creamos una nueva instancia de PDO para conectarnos a la base de datos.
    // Configuramos el modo de error para que lance excepciones y el modo de obtención de datos por defecto como array asociativo.
    $nom_variable_connexio = new PDO($dsn, 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    // Si ocurre un error al conectar, detenemos la ejecución y mostramos el mensaje de error.
    die('Error de conexión: ' . $e->getMessage());
}

// Finalmente, devolvemos la variable de conexión para que pueda ser utilizada en otros archivos.
return $nom_variable_connexio;
