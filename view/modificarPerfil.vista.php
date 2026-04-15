<?php

// Helper para escapar HTML
function e($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <base href="/ProyecteServidor1/">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - PokéNet</title>
    <link rel="icon" type="image/jpeg" href="assets/img/fondo.jpg">
    <link rel="stylesheet" href="style/styles.css?v=<?= time() ?>">
</head>
<body class="no-page-scroll">
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="index.php" class="navbar-brand" style="text-decoration: none; color: inherit;">🌟 PokéNet</a>
            <div class="navbar-actions">
                <a href="controller/perfilUsuarioPage.controller.php?id=<?= idUsuarioActual() ?>" class="nav-user" style="text-decoration: none; color: inherit;">
                    <?= e($usuario['username']) ?>
                </a>
                <a href="controller/logout.controller.php" class="nav-btn">Cerrar sesión</a>
            </div>
        </div>
    </nav>

    <div class="main-wrapper">
        <div class="sidebar-left">
            <a href="controller/perfilUsuarioPage.controller.php?id=<?= idUsuarioActual() ?>" class="btn-capturar" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <span class="icon">👤</span>
                <span>Ver mi Perfil</span>
            </a>
            <a href="index.php" class="btn-capturar" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <span class="icon">🏠</span>
                <span>Volver al Inicio</span>
            </a>
        </div>
        
        <div class="posts-container">
            <div class="posts-scroll">
                <!-- Formulario de edición de perfil -->
                <div class="edit-profile-container">
                    <div class="edit-profile-header">
                        <h1>✏️ Editar mi Perfil</h1>
                        <p>Personaliza tu cuenta de PokéNet</p>
                    </div>

                    <?php if ($ok): ?>
                        <div class="alert success">✅ <?= e($ok) ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert error">❌ <?= e($error) ?></div>
                    <?php endif; ?>

                    <form action="controller/modificarPerfil.controller.php" method="POST" enctype="multipart/form-data" class="edit-profile-form">
                        <?= csrfInput() ?>
                        <!-- Vista previa de la foto actual -->
                        <div class="profile-image-section">
                            <div class="current-profile-image">
                                <img id="preview-image" 
                                     src="<?php 
                                        $imgPath = $usuario['profile_image'];
                                        if ($imgPath === 'userDefaultImg.jpg') {
                                            echo 'assets/img/imgProfileuser/' . e($imgPath);
                                        } else {
                                            echo 'assets/img/userImg/' . e($imgPath);
                                        }
                                     ?>" 
                                     alt="<?= e($usuario['username']) ?>"
                                     onerror="this.src='assets/img/imgProfileuser/userDefaultImg.jpg'">
                            </div>
                            <div class="profile-image-info">
                                <h3>Foto de Perfil</h3>
                                <p>Formatos permitidos: JPG, JPEG, PNG, GIF</p>
                                <p>Tamaño máximo: 5MB</p>
                            </div>
                        </div>

                        <!-- Campo de archivo -->
                        <div class="form-group">
                            <label for="profile_image" class="form-label">
                                📸 Nueva Foto de Perfil
                            </label>
                            <input type="file" 
                                   id="profile_image" 
                                   name="profile_image" 
                                   accept="image/jpeg,image/jpg,image/png,image/gif"
                                   class="form-control file-input"
                                   onchange="previewImage(this)">
                            <small class="form-hint">Deja en blanco si no deseas cambiar tu foto</small>
                        </div>

                        <!-- Campo de nombre de usuario -->
                        <div class="form-group">
                            <label for="username" class="form-label">
                                👤 Nombre de Usuario
                            </label>
                            <input type="text" 
                                   id="username" 
                                   name="username" 
                                   value="<?= e($usuario['username']) ?>"
                                   required
                                   minlength="3"
                                   maxlength="100"
                                   class="form-control"
                                   placeholder="Tu nombre de usuario">
                            <small class="form-hint">Entre 3 y 100 caracteres</small>
                        </div>

                        <!-- Email (solo informativo) -->
                        <div class="form-group">
                            <label class="form-label">
                                📧 Email
                            </label>
                            <input type="email" 
                                   value="<?= e($usuario['email']) ?>"
                                   disabled
                                   class="form-control form-control-disabled">
                            <small class="form-hint">El email no se puede modificar</small>
                        </div>

                        <!-- Botones -->
                        <div class="form-actions">
                            <button type="submit" class="btn-submit">
                                <span class="icon">💾</span>
                                Guardar Cambios
                            </button>
                            <a href="controller/perfilUsuarioPage.controller.php?id=<?= idUsuarioActual() ?>" class="btn-cancel">
                                <span class="icon">❌</span>
                                Cancelar
                            </a>
                        </div>
                    </form>

                    <!-- Separador -->
                    <div class="edit-profile-separator">
                        <span>🔒 Seguridad de la Cuenta</span>
                    </div>

                    <!-- Botón para cambiar contraseña -->
                    <div class="security-section">
                        <div class="security-info">
                            <h3>🔐 Cambiar Contraseña</h3>
                            <p>Actualiza tu contraseña para mantener tu cuenta segura. Te recomendamos usar una contraseña fuerte y única.</p>
                        </div>
                        <a href="controller/cambiarContrasenaPage.controller.php" class="btn-security">
                            <span class="icon">🔒</span>
                            Cambiar Contraseña
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Vista previa de la imagen seleccionada
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                document.getElementById('preview-image').src = e.target.result;
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Limpiar mensajes de la URL
    if (window.location.search.match(/[?&](ok|error)=/)) {
        setTimeout(() => {
            const url = new URL(window.location);
            url.searchParams.delete('ok');
            url.searchParams.delete('error');
            window.history.replaceState({}, document.title, url.pathname);
        }, 3000);
    }
    </script>
</body>
</html>
