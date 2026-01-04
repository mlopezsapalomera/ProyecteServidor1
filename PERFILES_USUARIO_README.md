# Sistema de Perfiles de Usuario - PokéNet Social

## 📋 Cambios Implementados

### 1. Base de Datos
- **Nuevo campo**: `profile_image` en la tabla `users`
  - Valor por defecto: `'userDefaultImg.jpg'`
  - Almacena el nombre del archivo de la foto de perfil

### 2. Estructura de Archivos
```
assets/img/imgProfileuser/
├── userDefaultImg.jpg (imagen por defecto para todos los usuarios)
└── [futuras fotos de perfil de usuarios]
```

### 3. Nueva Vista
- **perfilUsuario.vista.php**: Muestra el perfil de cualquier usuario
  - Foto de perfil
  - Nombre de usuario
  - Estadísticas (número de publicaciones)
  - Lista de posts del usuario
  - Paginación de posts

### 4. Funcionalidades Añadidas

#### En index.php:
- ✅ Nombre de usuario en navbar es clickeable → va a tu perfil
- ✅ Fotos de perfil en posts (en lugar del círculo con letra)
- ✅ "Publicado por..." es clickeable → va al perfil del autor

#### En perfilUsuario.vista.php:
- ✅ Vista del perfil con foto y estadísticas
- ✅ Lista de posts del usuario
- ✅ Si es tu perfil, puedes editar/eliminar tus posts
- ✅ Si es perfil de otro usuario, solo puedes ver

## 🚀 Instrucciones de Instalación

### Opción A: Base de datos nueva
Si vas a crear la base de datos desde cero:
```sql
-- Ejecutar el archivo actualizado:
source model/Pt03_Marcos_Lopez.sql
```

### Opción B: Base de datos existente
Si ya tienes la base de datos creada:
```sql
-- Ejecutar el script de migración:
source model/migracion_profile_image.sql
```

O ejecutar manualmente:
```sql
USE `pt03_marcos_lopez`;
ALTER TABLE `users` 
ADD COLUMN `profile_image` VARCHAR(255) DEFAULT 'userDefaultImg.jpg' AFTER `password_hash`;
```

## 📸 Imagen por Defecto
La imagen por defecto `userDefaultImg.jpg` ya está creada en:
```
assets/img/imgProfileuser/userDefaultImg.jpg
```

Todos los usuarios nuevos recibirán automáticamente esta imagen al registrarse.

## 🔗 Rutas de Navegación

### Acceder a tu perfil:
1. Click en tu nombre de usuario en el navbar (esquina superior derecha)
2. URL directa: `view/perfilUsuario.vista.php?id=TU_ID`

### Acceder al perfil de otro usuario:
1. Click en "Publicado por: [nombre]" en cualquier post
2. Click en la foto de perfil de cualquier post
3. URL directa: `view/perfilUsuario.vista.php?id=ID_DEL_USUARIO`

## 🎨 Estilos CSS Añadidos
- Estilos para fotos de perfil circulares en posts
- Estilos para la cabecera del perfil
- Responsive design para dispositivos móviles

## 📝 Modelos Actualizados

### model/user.php
- `crearUsuario()`: Ahora acepta parámetro `$profileImage`

### model/pokemon.php
- `obtenerPokemons()`: Incluye `autor_profile_image` y `autor_id`
- `obtenerPokemonsPorUsuario()`: Nueva función para obtener posts de un usuario
- `contarPokemonsPorUsuario()`: Nueva función para contar posts de un usuario

## 🔮 Próximas Mejoras (Opcional)
- Permitir a usuarios editar su perfil y cambiar foto
- Subir fotos personalizadas
- Eliminar fotos antiguas al cambiar
- Validación de formatos de imagen
