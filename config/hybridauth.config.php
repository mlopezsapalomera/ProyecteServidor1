<?php
/**
 * Configuración de HybridAuth para OAuth
 * 
 * Este archivo contiene la configuración para conectar con proveedores OAuth
 * como Google, Facebook, GitHub, etc.
 */

require_once __DIR__ . '/../env.php';

$hybridauth_config = [
    'callback' => 'http://localhost/ProyecteServidor1/controller/oauth.controller.php',
    
    'providers' => [
        'Google' => [
            'enabled' => true,
            'keys' => [
                'id' => GOOGLE_CLIENT_ID,
                'secret' => GOOGLE_CLIENT_SECRET
            ],
            'scope' => 'openid profile email',
            'authorize_url_parameters' => [
                'approval_prompt' => 'auto'
            ]
        ],
        
        // Descomenta si quieres agregar Facebook más adelante
        /*
        'Facebook' => [
            'enabled' => false,
            'keys' => [
                'id' => FACEBOOK_APP_ID,
                'secret' => FACEBOOK_APP_SECRET
            ],
            'scope' => 'email, public_profile'
        ],
        */
        
        // Descomenta si quieres agregar GitHub más adelante
        /*
        'GitHub' => [
            'enabled' => false,
            'keys' => [
                'id' => GITHUB_CLIENT_ID,
                'secret' => GITHUB_CLIENT_SECRET
            ],
            'scope' => 'user:email'
        ],
        */
    ],
    
    'debug_mode' => true,
    'debug_file' => __DIR__ . '/../logs/hybridauth.log'
];

return $hybridauth_config;
