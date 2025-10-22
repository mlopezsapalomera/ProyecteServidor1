<?php

require_once __DIR__ . '/../model/pokemon.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /../view/inde')
}



?>