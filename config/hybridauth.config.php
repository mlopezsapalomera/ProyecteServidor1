<?php
/**
 * Configuración de HybridAuth para OAuth
 * 
 * Este archivo contiene la configuración para conectar con proveedores OAuth
 * como Google, Facebook, GitHub, etc.
 */

require_once __DIR__ . '/../env.php';

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
    
    'debug_mode' => true,
    'debug_file' => __DIR__ . '/../logs/hybridauth.log'
];

return $hybridauth_config;
