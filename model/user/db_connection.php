<?php

function userDbConnection() {
    static $conn = null;

    if ($conn === null) {
        $conn = require __DIR__ . '/../db.php';
    }

    return $conn;
}
