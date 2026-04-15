<?php
// Router de pruebas para php -S, emula reglas básicas de .htaccess.

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$docRoot = __DIR__ . '/..';
$filePath = realpath($docRoot . $uri);

// Servir archivo estático existente.
if ($filePath !== false && str_starts_with($filePath, realpath($docRoot)) && is_file($filePath)) {
    return false;
}

// Rutas canónicas al home controller.
if ($uri === '/' || $uri === '/index.php' || $uri === '/view/index.php') {
    require $docRoot . '/controller/home.controller.php';
    exit;
}

// Si no existe archivo/directorio real, fallback al home controller.
if ($filePath === false || !str_starts_with($filePath, realpath($docRoot)) || (!is_file($filePath) && !is_dir($filePath))) {
    require $docRoot . '/controller/home.controller.php';
    exit;
}

// Directorio existente sin index: 404 simple.
http_response_code(404);
echo '404 Not Found';
