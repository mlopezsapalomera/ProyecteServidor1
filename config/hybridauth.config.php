<?php
/**
 * Configuración de HybridAuth para OAuth
 * 
 * Este archivo contiene la configuración para conectar con proveedores OAuth
 * como Google, Facebook, GitHub, etc.
 */

$envFile = __DIR__ . '/../env.php';
if (file_exists($envFile)) {
    require_once $envFile;
}

$hybridauth_config = [
    'callback' => 'http://localhost/ProyecteServidor1/controller/oauthHybridGithub.controller.php',
    
    'providers' => [
        'GitHub' => [
            'enabled' => true,
            'keys' => [
                'id' => defined('GITHUB_CLIENT_ID') ? GITHUB_CLIENT_ID : '',
                'secret' => defined('GITHUB_CLIENT_SECRET') ? GITHUB_CLIENT_SECRET : ''
            ],
            'scope' => 'user:email'
        ],
    ],
    
    // Activar solo para depuracion puntual local.
    'debug_mode' => false,
    'debug_file' => __DIR__ . '/../logs/hybridauth.log'
];

return $hybridauth_config;
