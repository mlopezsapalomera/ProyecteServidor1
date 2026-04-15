<?php
// model/user.php
// Punto de entrada del dominio usuario (funciones repartidas por responsabilidad)

require_once __DIR__ . '/user/db_connection.php';
require_once __DIR__ . '/user/account.model.php';
require_once __DIR__ . '/user/admin.model.php';
require_once __DIR__ . '/user/remember.model.php';
require_once __DIR__ . '/user/recovery.model.php';
require_once __DIR__ . '/user/oauth.model.php';
