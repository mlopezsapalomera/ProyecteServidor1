# Proyecto CRUD con PDO y Prepared Statements
**Marcos López** - Gestión de artículos con PHP, MySQL y PDO

## Descripción del Proyecto
Sistema web CRUD (Create, Read, Update, Delete) para gestionar artículos usando PHP con PDO y Prepared Statements, cumpliendo los objetivos de aprendizaje sobre conexiones seguras a bases de datos.

## Objetivos Cumplidos
1. ✅ **Conocer el tratamiento de conexiones con PDO y consultas QUERY**
2. ✅ **Trabajar con Prepared Statements**
3. ✅ **Crear una BDD MySQL desde phpMyAdmin**
4. ✅ **Gestionar tabla de artículos con ID, título y descripción**
5. ✅ **Implementar CRUD completo con Prepared Statements**

## Pasos del Desarrollo

### 1. Diseño de la Base de Datos
- **Diseño conceptual**: Tabla `pokemons` con campos `id`, `titulo` y `descripcion`
- **Creación del esquema**: Archivo `Pt03_Marcos_Lopez.sql` con:
  ```sql
  DROP DATABASE IF EXISTS pt03_marcos_lopez;
  CREATE DATABASE IF NOT EXISTS pt03_marcos_lopez;
  USE pt03_marcos_lopez;
  ```
- **Tabla normalizada**: ID autoincremental, título obligatorio, descripción opcional

### 2. Estructura del Proyecto (MVC)
```
ProyecteServidor1/
├── model/           # Lógica de datos y conexión
│   ├── db.php       # Conexión PDO
│   └── pokemon.php  # Funciones CRUD
├── view/            # Interfaces de usuario
│   ├── index.php    # Listado principal
│   ├── insertar.vista.php
│   └── modificar.vista.php
├── controller/      # Lógica de negocio
│   ├── insertar.controller.php
│   ├── modificar.controller.php
│   └── eliminar.controller.php
├── env.php          # Configuración
└── Pt03_Marcos_Lopez.sql  # Esquema entregable
```

### 3. Conexión PDO con la Base de Datos
**Archivo: `model/db.php`**
```php
try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=pt03_marcos_lopez;charset=utf8mb4';
    $nom_variable_connexio = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Error de conexión: ' . $e->getMessage());
}
```

### 4. Mostrar Artículos (READ)
- **Vista**: `view/index.php` con tabla HTML
- **Función**: `getAllPokemons()` usando prepared statements
- **Técnicas**: `foreach` para iterar, `htmlspecialchars()` para seguridad

### 5. Insertar Artículos (CREATE)
- **Vista**: Formulario en `insertar.vista.php`
- **Controlador**: `insertar.controller.php` valida y procesa POST
- **Función**: `insertPokemon()` con prepared statement

### 6. Eliminar Artículos (DELETE)
- **Confirmación**: JavaScript `confirm()` antes de eliminar
- **Controlador**: `eliminar.controller.php` procesa GET con ID
- **Función**: `deletePokemon()` con prepared statement

### 7. Modificar Artículos (UPDATE)
- **Vista**: Formulario precargado en `modificar.vista.php`
- **Controlador**: `modificar.controller.php` valida y actualiza
- **Función**: `updatePokemon()` con prepared statement

## Tecnologías y Métodos Utilizados

### HTTP Methods
- **GET**: Para mostrar formularios y listados (`$_GET['id']`)
- **POST**: Para enviar datos de formularios (`$_POST['titulo']`)

### Inclusión de Archivos
- **`require_once`**: Para incluir archivos críticos (conexión, funciones)
- **`include_once`**: Para archivos opcionales (no utilizado en este proyecto)

### Prepared Statements
**¿Qué son?**: Consultas SQL precompiladas que separan el código SQL de los datos, evitando inyecciones SQL.

**Ejemplo de uso**:
```php
function insertPokemon($titulo, $descripcion = null) {
    global $nom_variable_connexio;
    $sql = "INSERT INTO pokemons (titulo, descripcion) VALUES (:titulo, :descripcion)";
    $stmt = $nom_variable_connexio->prepare($sql);
    return $stmt->execute([
        ':titulo' => $titulo,
        ':descripcion' => $descripcion
    ]);
}
```

**Ventajas**:
- Seguridad contra inyección SQL
- Mejor rendimiento con consultas repetitivas
- Separación clara entre lógica y datos

### PDO (PHP Data Objects)
**¿Qué es PDO?**: Extensión de PHP que proporciona una interfaz consistente para acceder a bases de datos.

**Características utilizadas**:
- Conexión con `new PDO()`
- Prepared statements con `prepare()` y `execute()`
- Manejo de errores con excepciones
- Fetch modes para obtener resultados

## CRUD - ¿Qué significa?
**CRUD** es un acrónimo de las cuatro operaciones básicas en bases de datos:

- **C**reate (Crear): Insertar nuevos registros
- **R**ead (Leer): Consultar y mostrar datos existentes  
- **U**pdate (Actualizar): Modificar registros existentes
- **D**elete (Eliminar): Borrar registros

## Funcionamiento del Código

### Flujo de una Consulta (READ)
1. `view/index.php` incluye `model/pokemon.php`
2. Llama a `getAllPokemons()` que usa la conexión global `$nom_variable_connexio`
3. Ejecuta prepared statement con `LIMIT` y `OFFSET`
4. Retorna array de registros que se muestran en tabla HTML

### Flujo de Inserción (CREATE)
1. Usuario completa formulario en `insertar.vista.php`
2. Form envía POST a `insertar.controller.php`
3. Controlador valida datos y llama `insertPokemon()`
4. Función ejecuta prepared statement con parámetros seguros
5. Redirección con mensaje de éxito/error

### Seguridad Implementada
- **Prepared Statements**: Prevención de inyección SQL
- **Validación de entrada**: Verificación de tipos y campos obligatorios
- **Escapado HTML**: `htmlspecialchars()` contra XSS
- **Redirecciones**: Patrón POST/Redirect/GET para evitar reenvíos

## Instalación y Uso

### Requisitos
- XAMPP (Apache + MySQL + PHP)
- phpMyAdmin para gestión de BD

### Configuración
1. Importar `Pt03_Marcos_Lopez.sql` en phpMyAdmin
2. Iniciar Apache y MySQL en XAMPP
3. Acceder a: `http://localhost/ProyecteServidor1/`

### Funcionalidades
- **Listado**: Ver todos los artículos
- **Insertar**: Formulario para nuevos artículos
- **Editar**: Modificar artículos existentes
- **Eliminar**: Borrar con confirmación JavaScript