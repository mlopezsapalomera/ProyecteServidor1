# 🌟 PokéNet Social - Documentación Técnica

Red social temática de Pokémon desarrollada con PHP, MySQL y JavaScript.

---

## 📑 Contenido

1. [Ordenación de Artículos](#1-ordenación-de-artículos)
2. [Sistema Remember-Me](#2-sistema-remember-me)
3. [Integración reCAPTCHA](#3-integración-recaptcha)
4. [Edición de Perfil](#4-edición-de-perfil)
5. [Búsqueda AJAX](#5-búsqueda-ajax)
6. [Panel de Administración](#6-panel-de-administración)

---

## 1. Ordenación de Artículos

**Funcionamiento:** Ordenar publicaciones por ID, título o fecha (ASC/DESC).

**Implementación:**
```php
// Controlador
$ordenarPor = $_GET['ordenarPor'] ?? 'id';
$direccionOrden = $_GET['direccionOrden'] ?? 'DESC';

// Modelo - Whitelist de seguridad
$camposPermitidos = ['id', 'titulo', 'created_at'];
if (!in_array($orderBy, $camposPermitidos)) $orderBy = 'id';

$sql = "SELECT p.*, u.username FROM pokemons p 
        LEFT JOIN users u ON p.user_id = u.id
        ORDER BY p.$orderBy $orderDir LIMIT :limit OFFSET :offset";
```

**Seguridad:** Whitelist de campos, validación ASC/DESC, Prepared Statements.

---

## 2. Sistema Remember-Me

**Funcionamiento:** Mantiene la sesión activa 30 días mediante cookies seguras.

**Proceso:**

1. **Crear Token:**
```php
$token = bin2hex(random_bytes(64));  // 128 caracteres
$tokenHash = hash("sha256", $token); // Hash para BD
```

2. **Guardar Cookie:**
```php
setcookie('remember_token', $token, time() + (30*24*60*60), '/', '', false, true);
```

3. **Verificar Auto:**
```php
if (!estaIdentificado() && isset($_COOKIE['remember_token'])) {
    $usuario = verificarRememberToken($_COOKIE['remember_token']);
    if ($usuario) iniciarSesion($usuario);
}
```

**Tabla BD:**
```sql
CREATE TABLE remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

**Seguridad:** `random_bytes()`, Hash SHA-256, HttpOnly Cookie, expiración automática.

---

## 3. Integración reCAPTCHA

**Funcionamiento:** Protección contra bots tras 3 intentos fallidos.

**Configuración:**
```php
define('RECAPTCHA_SITE_KEY', 'tu_site_key');
define('RECAPTCHA_SECRET_KEY', 'tu_secret_key');
```

**Control de Intentos:**
```php
if (!isset($_SESSION['intentos_login'])) {
    $_SESSION['intentos_login'] = 0;
}

// Mostrar captcha después de 3 intentos
if ($_SESSION['intentos_login'] >= 3) {
    if (!validarRecaptcha()) {
        $_SESSION['intentos_login']++;
        // Mostrar error
        exit;
    }
}

// Si login OK: resetear
if ($credencialesCorrectas) {
    $_SESSION['intentos_login'] = 0;
} else {
    $_SESSION['intentos_login']++;
}
```

**Validación Backend:**
```php
function validarRecaptcha() {
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret' => RECAPTCHA_SECRET_KEY,
        'response' => $_POST['g-recaptcha-response'],
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];
    // POST a Google y verificar respuesta
    $response = json_decode(file_get_contents($url, false, $context));
    return $response->success === true;
}
```

**Vista:**
```html
<script src="https://www.google.com/recaptcha/api.js"></script>
<?php if ($_SESSION['intentos_login'] >= 3): ?>
    <div class="g-recaptcha" data-sitekey="<?= RECAPTCHA_SITE_KEY ?>"></div>
<?php endif; ?>
```

**Seguridad:** Activación progresiva, verificación backend, reset automático, tracking de IP.

---

## 4. Edición de Perfil

**Funcionamiento:** Modificar username y foto de perfil con validaciones.

**Validaciones Username:**
- Longitud: 3-100 caracteres
- Único en BD (excepto el actual)
- Obligatorio

**Validación Imagen:**
```php
// Validar MIME type real (no solo extensión)
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$tipoArchivo = finfo_file($finfo, $_FILES['profile_image']['tmp_name']);

$tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($tipoArchivo, $tiposPermitidos)) {
    $errores[] = 'Solo JPG, PNG o GIF';
}

// Validar tamaño (5MB máx)
if ($_FILES['profile_image']['size'] > 5 * 1024 * 1024) {
    $errores[] = 'Máximo 5MB';
}

// Generar nombre único con timestamp
$nombreArchivo = 'user_' . $idUsuario . '_' . time() . '.jpg';
move_uploaded_file($tmpName, 'assets/img/userImg/' . $nombreArchivo);

// Eliminar imagen anterior (si no es la por defecto)
if ($imagenAnterior !== 'userDefaultImg.jpg') {
    unlink('assets/img/userImg/' . $imagenAnterior);
}
```

**Actualizar Sesión:**
```php
$_SESSION['usuario']['username'] = $nuevoUsername; // Reflejar cambios inmediatos
```

**Modelo:**
```php
function actualizarPerfil($userId, $username, $profileImage = null) {
    if ($profileImage) {
        $sql = "UPDATE users SET username = :username, profile_image = :image WHERE id = :id";
    } else {
        $sql = "UPDATE users SET username = :username WHERE id = :id";
    }
    // Ejecutar con prepared statement
}
```

**Validaciones:** Username único, longitud 3-100, MIME type real, tamaño máx 5MB, nombre único timestamp.

---

## 5. Búsqueda AJAX

**Funcionamiento:** Búsqueda en tiempo real sin recargar, con debouncing (300ms) y límite de 5 resultados por categoría.

**JavaScript:**
```javascript
searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    if (query.length < 2) return; // Mínimo 2 caracteres
    
    searchTimeout = setTimeout(() => {
        fetch(`controller/buscar.controller.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => mostrarResultados(data));
    }, 300);
});
```

**Backend (buscar.controller.php):**
```php
header('Content-Type: application/json; charset=utf-8');

$query = trim($_GET['q'] ?? '');
if (strlen($query) < 2) {
    echo json_encode(['success' => false, 'usuarios' => [], 'publicaciones' => []]);
    exit;
}

try {
    $usuarios = buscarUsuarios($query, 5);
    $publicaciones = buscarPokemons($query, 5);
    
    echo json_encode([
        'success' => true,
        'total' => count($usuarios) + count($publicaciones),
        'usuarios' => $usuarios,
        'publicaciones' => $publicaciones
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
```

**Modelo (user.php):**
```php
function buscarUsuarios($query, $limit = 10) {
    $sql = "SELECT id, username, profile_image FROM users 
            WHERE username LIKE :query ORDER BY username LIMIT :limit";
    $stmt->bindValue(':query', '%' . $query . '%', PDO::PARAM_STR);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}
```

**Optimizaciones:** Debouncing 300ms, validación mín 2 caracteres, Prepared Statements, try-catch, JSON válido

---

## 6. Sistema de Administración

**Funcionamiento:** Panel exclusivo para admins que gestiona usuarios. Rol guardado en `$_SESSION['usuario']['role']`.

**Tabla BD:**
```sql
CREATE TABLE users (
    ...
    role ENUM('user', 'admin') DEFAULT 'user',
    ...
);
```

**Crear Admin (3 opciones):**
```sql
-- Opción 1: SQL
UPDATE users SET role = 'admin' WHERE id = 1;
UPDATE users SET role = 'admin' WHERE username = 'nombre_usuario';

-- Opción 2: phpMyAdmin
-- Editar tabla users → cambiar campo role a 'admin'

-- Opción 3: Función PHP
function actualizarRolUsuario($userId, $nuevoRol) {
    $rolesValidos = ['user', 'admin'];
    if (!in_array($nuevoRol, $rolesValidos)) return false;
    $sql = "UPDATE users SET role = :role WHERE id = :id";
    $stmt->execute([':role' => $nuevoRol, ':id' => $userId]);
}
```

**Importante:** Después de cambiar el rol, el usuario debe **cerrar sesión y volver a entrar** para que se actualice `$_SESSION`.

**Sistema de Autorización (auth.php):**
```php
function iniciarSesion($usuario) {
    $_SESSION['usuario'] = [
        'id' => $usuario['id'],
        'username' => $usuario['username'],
        'role' => $usuario['role'] ?? 'user'  // Guardar rol en sesión
    ];
}

function estaIdentificado() {
    return isset($_SESSION['usuario']['id']);
}

function esAdmin() {
    return estaIdentificado() && ($_SESSION['usuario']['role'] ?? 'user') === 'admin';
}
```

**Proteger Rutas Admin:**
```php
// En adminPanel.vista.php y eliminarUsuario.controller.php
if (!estaIdentificado() || !esAdmin()) {
    header('Location: ../view/index.php?error=Acceso denegado');
    exit;
}

// Validaciones adicionales
if ($datosUsuarioEliminar['role'] === 'admin') {
    // No permitir eliminar otros admins
    exit;
}
if ($idEliminar === idUsuarioActual()) {
    // No permitir auto-eliminación
    exit;
}
```

**Funciones Principales:**

**1. Listar Usuarios (sin admins):**
```php
function obtenerTodosLosUsuarios($excluirAdmins = true) {
    if ($excluirAdmins) {
        $sql = "SELECT * FROM users WHERE role != 'admin' ORDER BY created_at DESC";
    }
    return $stmt->fetchAll();
}
```

**2. Eliminar Usuario con Transacción:**
```php
function eliminarUsuario($userId) {
    try {
        $pdo->beginTransaction();
        $stmt1->execute([':id' => $userId]); // DELETE pokemons
        $stmt2->execute([':id' => $userId]); // DELETE users
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}
```

**3. Contar Publicaciones:**
```php
function contarPublicacionesUsuario($userId) {
    $sql = "SELECT COUNT(*) as total FROM pokemons WHERE user_id = :id";
    return $stmt->fetch()['total'];
}
```

**Mostrar Enlace en Menú (solo si es admin):**
```php
<?php if (esAdmin()): ?>
    <a href="view/adminPanel.vista.php">👨‍💼 Panel de Usuarios</a>
<?php endif; ?>
```

**Seguridad:** Autenticación + Autorización, transacciones SQL, confirmación JS antes de eliminar, prevención de auto-eliminación y eliminación de  admins.

---

## 📚 Tecnologías Utilizadas

**Backend:** PHP 8+, MySQL/MariaDB, PDO, Session Management  
**Frontend:** HTML5, CSS3, JavaScript (Vanilla), AJAX (Fetch API)  
**Seguridad:** Prepared Statements, Bcrypt, reCAPTCHA v2, MIME type validation  
**Librerías:** PHPMailer (envío emails)

---

## 🚀 Instalación

```bash
# 1. Importar BD
mysql -u root -p < model/Pt03_Marcos_Lopez.sql

# 2. Configurar env.php (credenciales BD, email, reCAPTCHA)

# 3. Crear admin
mysql -u root -p
USE proyecte_servidor1;
UPDATE users SET role = 'admin' WHERE username = 'tu_usuario';

# 4. Permisos de carpetas
chmod 755 assets/img/userImg/
```

Acceder: `http://localhost/ProyecteServidor1/`

---

## 🔒 Seguridad Implementada

**General:** Prepared Statements, htmlspecialchars(), password_hash(), validación sesión  
**Cookies:** HttpOnly (anti-XSS), tokens hasheados (SHA-256), expiración automática  
**Archivos:** Validación MIME real (finfo), límite 5MB, nombres únicos timestamp  
**Acceso:** Autenticación + autorización roles, prevención escalada privilegios

---

**© 2026 PokéNet Social - Proyecto Educativo**
