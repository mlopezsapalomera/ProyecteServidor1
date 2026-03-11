<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación OAuth - PokéNet</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        h1 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 2rem;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 1rem;
        }
        .check-item {
            padding: 15px;
            margin: 10px 0;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .check-item.success {
            background: #d4edda;
            border-left: 4px solid #28a745;
        }
        .check-item.error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
        }
        .check-item.warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
        }
        .icon {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .check-content {
            flex: 1;
        }
        .check-title {
            font-weight: 600;
            margin-bottom: 5px;
        }
        .check-detail {
            font-size: 0.9rem;
            color: #666;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 20px;
            transition: all 0.3s;
        }
        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .section {
            margin: 30px 0;
        }
        .section-title {
            font-size: 1.3rem;
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Verificación de Configuración OAuth</h1>
        <p class="subtitle">Comprueba que todo esté configurado correctamente para HybridAuth</p>

        <?php
        // Función auxiliar para mostrar check items
        function checkItem($type, $title, $detail = '') {
            $icons = [
                'success' => '✅',
                'error' => '❌',
                'warning' => '⚠️'
            ];
            echo "<div class='check-item {$type}'>";
            echo "<div class='icon'>{$icons[$type]}</div>";
            echo "<div class='check-content'>";
            echo "<div class='check-title'>{$title}</div>";
            if ($detail) echo "<div class='check-detail'>{$detail}</div>";
            echo "</div>";
            echo "</div>";
        }

        // Verificaciones
        $errores = 0;
        $advertencias = 0;

        echo "<div class='section'>";
        echo "<div class='section-title'>1. Dependencias</div>";

        // Verificar HybridAuth
        if (file_exists(__DIR__ . '/vendor/autoload.php')) {
            require_once __DIR__ . '/vendor/autoload.php';
            if (class_exists('Hybridauth\Hybridauth')) {
                checkItem('success', 'HybridAuth instalado', 'Librería OAuth disponible');
            } else {
                checkItem('error', 'HybridAuth no encontrado', 'La clase no está disponible');
                $errores++;
            }
        } else {
            checkItem('error', 'Composer no ejecutado', 'Ejecuta: php composer.phar install');
            $errores++;
        }

        echo "</div>";

        echo "<div class='section'>";
        echo "<div class='section-title'>2. Archivos de Configuración</div>";

        // Verificar env.php
        if (file_exists(__DIR__ . '/env.php')) {
            require_once __DIR__ . '/env.php';
            checkItem('success', 'env.php encontrado', 'Archivo de configuración presente');
            
            // Verificar credenciales Google
            if (defined('GOOGLE_CLIENT_ID') && GOOGLE_CLIENT_ID !== 'TU_GOOGLE_CLIENT_ID_AQUI') {
                checkItem('success', 'GOOGLE_CLIENT_ID configurado', 'ID: ' . substr(GOOGLE_CLIENT_ID, 0, 20) . '...');
            } else {
                checkItem('warning', 'GOOGLE_CLIENT_ID no configurado', 'Debes añadir tus credenciales de Google');
                $advertencias++;
            }
            
            if (defined('GOOGLE_CLIENT_SECRET') && GOOGLE_CLIENT_SECRET !== 'TU_GOOGLE_CLIENT_SECRET_AQUI') {
                checkItem('success', 'GOOGLE_CLIENT_SECRET configurado', 'Secret configurado correctamente');
            } else {
                checkItem('warning', 'GOOGLE_CLIENT_SECRET no configurado', 'Debes añadir tu secret de Google');
                $advertencias++;
            }
        } else {
            checkItem('error', 'env.php no encontrado', 'Copia env.php.template a env.php');
            $errores++;
        }

        // Verificar config HybridAuth
        if (file_exists(__DIR__ . '/config/hybridauth.config.php')) {
            checkItem('success', 'hybridauth.config.php encontrado', 'Configuración OAuth presente');
        } else {
            checkItem('error', 'hybridauth.config.php no encontrado', 'Archivo de configuración faltante');
            $errores++;
        }

        echo "</div>";

        echo "<div class='section'>";
        echo "<div class='section-title'>3. Controladores OAuth</div>";

        // Verificar controlador OAuth
        if (file_exists(__DIR__ . '/controller/oauth.controller.php')) {
            checkItem('success', 'oauth.controller.php encontrado', 'Controlador OAuth presente');
        } else {
            checkItem('error', 'oauth.controller.php no encontrado', 'Controlador faltante');
            $errores++;
        }

        echo "</div>";

        echo "<div class='section'>";
        echo "<div class='section-title'>4. Base de Datos</div>";

        // Verificar conexión BD
        try {
            require_once __DIR__ . '/model/db.php';
            checkItem('success', 'Conexión a base de datos', 'Conectado correctamente');
            
            // Verificar columnas OAuth en users
            $stmt = $nom_variable_connexio->query("SHOW COLUMNS FROM users LIKE 'oauth_%'");
            $columnas = $stmt->fetchAll();
            
            if (count($columnas) >= 3) {
                checkItem('success', 'Columnas OAuth presentes', 'Tabla users actualizada con campos OAuth');
            } else {
                checkItem('error', 'Columnas OAuth faltantes', 'Ejecuta model/migration_oauth.sql en tu base de datos');
                $errores++;
            }
            
        } catch (Exception $e) {
            checkItem('error', 'Error de base de datos', $e->getMessage());
            $errores++;
        }

        echo "</div>";

        echo "<div class='section'>";
        echo "<div class='section-title'>5. Extensiones PHP</div>";

        // Verificar extensiones PHP
        $extensiones = ['curl', 'openssl', 'json'];
        foreach ($extensiones as $ext) {
            if (extension_loaded($ext)) {
                checkItem('success', "Extensión {$ext}", 'Habilitada');
            } else {
                checkItem('error', "Extensión {$ext} no disponible", "Habilita {$ext} en php.ini");
                $errores++;
            }
        }

        echo "</div>";

        // Resumen
        echo "<div class='section'>";
        echo "<div class='section-title'>📊 Resumen</div>";
        
        if ($errores === 0 && $advertencias === 0) {
            checkItem('success', '¡Todo configurado correctamente!', 'Tu aplicación está lista para usar OAuth con Google');
        } elseif ($errores === 0) {
            checkItem('warning', 'Configuración casi completa', "{$advertencias} advertencia(s). Configura las credenciales de Google para continuar.");
        } else {
            checkItem('error', 'Configuración incompleta', "{$errores} error(es) encontrado(s). Revisa los pasos anteriores.");
        }
        
        echo "</div>";
        ?>

        <div style="text-align: center; margin-top: 30px;">
            <?php if ($errores === 0 && $advertencias === 0): ?>
                <a href="view/login.vista.php" class="btn">Ir a Login</a>
            <?php else: ?>
                <a href="OAUTH_SETUP.md" class="btn">Ver Guía de Configuración</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
