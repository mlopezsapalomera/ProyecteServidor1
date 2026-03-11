# Guía de Configuración OAuth con Google

Esta guía te explicará cómo configurar la autenticación OAuth con Google para tu aplicación PokéNet Social.

## 📋 Prerequisitos

- ✅ HybridAuth instalado (ya hecho)
- ✅ Base de datos actualizada (ejecutar migration_oauth.sql)
- ✅ PHP 7.4 o superior
- ✅ Extensión PHP: openssl, curl

## 🔧 Paso 1: Actualizar la Base de Datos

Ejecuta el archivo de migración SQL para agregar soporte OAuth:

```sql
-- En phpMyAdmin o tu cliente MySQL:
USE pt03_marcos_lopez;
SOURCE model/migration_oauth.sql;
```

O manualmente:
1. Abre phpMyAdmin (http://localhost/phpmyadmin)
2. Selecciona la base de datos `pt03_marcos_lopez`
3. Ve a la pestaña "SQL"
4. Copia y pega el contenido de `model/migration_oauth.sql`
5. Haz clic en "Continuar"

## 🔑 Paso 2: Obtener Credenciales de Google

### 2.1 Crear un Proyecto en Google Cloud Console

1. Ve a: https://console.cloud.google.com/
2. Haz clic en "Seleccionar un proyecto" → "Nuevo proyecto"
3. Nombre del proyecto: `PokéNet Social` (o el que prefieras)
4. Haz clic en "Crear"

### 2.2 Habilitar Google+ API

1. En el menú lateral, ve a: **APIs y servicios** → **Biblioteca**
2. Busca: "Google+ API" o "Google People API"
3. Haz clic en "Habilitar"

### 2.3 Crear Credenciales OAuth 2.0

1. Ve a: **APIs y servicios** → **Credenciales**
2. Haz clic en: **+ CREAR CREDENCIALES** → **ID de cliente de OAuth**
3. Si es tu primera vez, configura la pantalla de consentimiento:
   - Tipo de usuario: **Externo**
   - Nombre de la aplicación: `PokéNet Social`
   - Correo electrónico de asistencia: tu email
   - Logo (opcional): puedes añadirlo más tarde
   - Ámbitos: Añade `email` y `profile`
   - Correo de contacto del desarrollador: tu email
   - Haz clic en "Guardar y continuar"

4. Vuelve a **Credenciales** → **+ CREAR CREDENCIALES** → **ID de cliente de OAuth**
5. Tipo de aplicación: **Aplicación web**
6. Nombre: `PokéNet OAuth Client`
7. **URIs de redirección autorizados**:
   ```
   http://localhost/ProyecteServidor1/controller/oauth.controller.php
   ```
   
   ⚠️ **IMPORTANTE**: Esta URL debe coincidir exactamente con tu proyecto local

8. Haz clic en "Crear"
9. Guarda el **ID de cliente** y el **Secreto del cliente**

## 🔐 Paso 3: Configurar las Credenciales en tu Proyecto

1. Abre el archivo: `env.php`
2. Busca estas líneas:

```php
if (!defined('GOOGLE_CLIENT_ID')) {
    define('GOOGLE_CLIENT_ID', 'TU_GOOGLE_CLIENT_ID_AQUI');
}
if (!defined('GOOGLE_CLIENT_SECRET')) {
    define('GOOGLE_CLIENT_SECRET', 'TU_GOOGLE_CLIENT_SECRET_AQUI');
}
```

3. Reemplaza con tus credenciales:

```php
if (!defined('GOOGLE_CLIENT_ID')) {
    define('GOOGLE_CLIENT_ID', '123456789-abcdefghijklmnop.apps.googleusercontent.com');
}
if (!defined('GOOGLE_CLIENT_SECRET')) {
    define('GOOGLE_CLIENT_SECRET', 'GOCSPX-tu_secreto_aqui');
}
```

## ✅ Paso 4: Probar la Integración

1. Inicia XAMPP (Apache + MySQL)
2. Ve a: http://localhost/ProyecteServidor1/view/login.vista.php
3. Deberías ver un botón "Continuar con Google"
4. Haz clic en el botón
5. Selecciona una cuenta de Google
6. Autoriza la aplicación
7. Deberías ser redirigido automáticamente y ver tu perfil

## 🐛 Solución de Problemas

### Error: "redirect_uri_mismatch"

**Problema**: La URI de redirección no coincide con la configurada en Google Console.

**Solución**:
1. Verifica que la URL en Google Console sea exactamente:
   ```
   http://localhost/ProyecteServidor1/controller/oauth.controller.php
   ```
2. La URL debe incluir el nombre exacto de tu carpeta de proyecto
3. Si tu proyecto está en otra ruta (ej: `htdocs/proyecto/`), actualiza la configuración

### Error: "invalid_client"

**Problema**: Client ID o Client Secret incorrectos.

**Solución**:
1. Verifica que hayas copiado correctamente las credenciales en `env.php`
2. Asegúrate de no tener espacios extra al inicio o final
3. Las credenciales NO deben tener comillas dentro del valor

### Error: "This app isn't verified"

**Problema**: Google muestra una advertencia porque la app no está verificada.

**Solución**:
1. Durante desarrollo, esto es normal
2. Haz clic en "Advanced" → "Go to PokéNet Social (unsafe)"
3. Para producción, necesitarás verificar tu aplicación con Google

### Error: "Access blocked: Authorization Error"

**Problema**: No se añadieron los scopes necesarios en la pantalla de consentimiento.

**Solución**:
1. Ve a **APIs y servicios** → **Pantalla de consentimiento de OAuth**
2. Edita la aplicación
3. En "Ámbitos", añade:
   - `.../auth/userinfo.email`
   - `.../auth/userinfo.profile`
4. Guarda los cambios

## 📊 Estructura de Archivos OAuth

```
ProyecteServidor1/
├── config/
│   └── hybridauth.config.php      # Configuración HybridAuth
├── controller/
│   └── oauth.controller.php       # Controlador OAuth
├── model/
│   ├── user.php                   # Funciones OAuth agregadas
│   └── migration_oauth.sql        # Migración base de datos
├── view/
│   ├── login.vista.php            # Con botón Google
│   └── register.vista.php         # Con botón Google
├── style/
│   └── styles.css                 # Estilos botones OAuth
├── vendor/                        # HybridAuth (Composer)
├── composer.json                  # Dependencias
└── env.php                        # Credenciales OAuth
```

## 🔒 Seguridad

⚠️ **IMPORTANTE**:

1. **NUNCA** subas tu archivo `env.php` a repositorios públicos (Git, GitHub, etc.)
2. Añade `env.php` a tu `.gitignore`:
   ```
   env.php
   vendor/
   composer.lock
   ```
3. Para producción, usa variables de entorno en lugar de archivos PHP
4. Cambia las credenciales si se exponen accidentalmente

## 🎉 ¡Listo!

Ya tienes OAuth con Google funcionando. Los usuarios pueden:

- ✅ Registrarse con Google (crea cuenta automáticamente)
- ✅ Iniciar sesión con Google (si ya existe la cuenta)
- ✅ Vincular su cuenta existente con Google

## 📝 Notas Adicionales

### Datos que se obtienen de Google:

- Nombre completo (displayName)
- Email
- Foto de perfil (se descarga automáticamente)
- ID único de Google (oauth_uid)

### Usuarios OAuth vs Usuarios Tradicionales:

- Usuarios OAuth: NO tienen `password_hash` (es NULL)
- Usuarios tradicionales: Pueden vincular OAuth más tarde
- Ambos tipos pueden coexistir en la misma tabla

### Agregar más proveedores (Facebook, GitHub, etc.):

1. Descomentar la configuración en `config/hybridauth.config.php`
2. Obtener credenciales del proveedor
3. Añadir constantes en `env.php`
4. Descomentar botones en `login.vista.php` y `register.vista.php`

## 📚 Recursos Útiles

- [HybridAuth Documentation](https://hybridauth.github.io/)
- [Google OAuth 2.0 Guide](https://developers.google.com/identity/protocols/oauth2)
- [Google Cloud Console](https://console.cloud.google.com/)

---

**Autor**: GitHub Copilot  
**Fecha**: 2026-03-10  
**Versión**: 1.0
