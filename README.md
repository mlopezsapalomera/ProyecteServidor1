## Índice

1. [Cómo organicé el proyecto](#cómo-organicé-el-proyecto)
2. [Cómo uso GET y POST](#cómo-uso-get-y-post)
3. [Por qué uso require_once y require](#por-qué-uso-require_once-y-require)
4. [Recuperar contraseña con token y PHPMailer](#recuperar-contraseña-con-token-y-phpmailer)
5. [OAuth: lo que hice a mano y lo que hice con HybridAuth](#oauth-lo-que-hice-a-mano-y-lo-que-hice-con-hybridauth)
6. [Trabajar con la API de Pokémon](#trabajar-con-la-api-de-pokémon)
7. [API pública con tokens](#api-pública-con-tokens)
8. [Guía técnica resumida](#guía-técnica-resumida)
9. [Cierre](#cierre)

## Cómo organicé el proyecto

Lo separé en carpetas para no tener todo mezclado:

- controller: aquí van los archivos que reciben acciones del usuario (login, registro, borrar, etc).
- model: aquí van funciones de base de datos y lógica.
- view: aquí están las pantallas que ve el usuario.

No he montado un router súper complejo. He ido trabajando con controladores concretos para cada acción.

## Cómo uso GET y POST

Yo lo planteé así:

- GET: para mostrar páginas o traer cosas por URL.
- POST: para enviar formularios y hacer cambios.

Ejemplo real en login, donde solo dejo pasar POST:

```php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/login.vista.php');
    exit;
}

$campUsuari = isset($_POST['user']) ? trim($_POST['user']) : '';
$contrasenya = isset($_POST['password']) ? $_POST['password'] : '';
```

Esto lo hice para que no se pueda usar ese controlador mal por URL cuando realmente espera datos de formulario.

## Por qué uso require_once y require

En casi todo uso require_once, porque así me aseguro de que no se cargue el mismo archivo dos veces y no pete el código.

Ejemplo:

```php
require_once __DIR__ . '/../security/csrf.php';
require_once __DIR__ . '/../env.php';
require_once __DIR__ . '/../model/user.php';
```

Y en PHPMailer usé require para cargar sus clases:

```php
if (file_exists(__DIR__ . '/../PHPMailer/src/PHPMailer.php')) {
    require __DIR__ . '/../PHPMailer/src/Exception.php';
    require __DIR__ . '/../PHPMailer/src/PHPMailer.php';
    require __DIR__ . '/../PHPMailer/src/SMTP.php';
}
```

## Recuperar contraseña con token y PHPMailer

Aquí intenté hacerlo seguro y práctico:

1. El usuario pone su correo.
2. Si existe en BD, genero un token temporal.
3. Guardo token + caducidad.
4. Mando un correo con PHPMailer con un enlace para cambiar contraseña.
5. Cuando entra al enlace, valido token y si está bien, dejo cambiar contraseña.
6. Al final borro token para que no se pueda reutilizar.

Parte del código que valida token:

```php
function verificarTokenRecuperacion($token) {
    if (empty($token)) {
        return false;
    }

    $nom_variable_connexio = userDbConnection();
    $sql = "SELECT * FROM users WHERE reset_token = :token AND reset_token_expira > NOW() LIMIT 1";
    $stmt = $nom_variable_connexio->prepare($sql);
    $stmt->execute([':token' => $token]);
    return $stmt->fetch();
}
```

## OAuth: lo que hice a mano y lo que hice con HybridAuth

En esta parte hice dos cosas distintas y eso me ayudó bastante a aprender.

### Google OAuth (hecho por mi, sin librería)

Con Google lo hice más "a mano":

- Creo la URL de autorización.
- Guardo y reviso el state para seguridad.
- Recibo el code de vuelta.
- Cambio ese code por access_token.
- Pido los datos del usuario.
- Si existe, inicia sesión; si no, lo registro.

Esto me sirvió para entender de verdad el flujo OAuth.

### GitHub OAuth (con HybridAuth)

Con GitHub usé HybridAuth porque quería comparar y ver la diferencia.

- Configuro credenciales en el archivo de config.
- Llamo a authenticate('GitHub').
- La librería hace casi todo el flujo.
- Recojo perfil y hago login/registro.

La diferencia principal es que con Google hice OAuth puro por mi cuenta, y con GitHub usé HybridAuth para simplificar trabajo.

## Trabajar con la API de Pokémon

Aquí he añadido una parte nueva para que el usuario no tenga que escribir todos los datos a mano cuando captura un Pokémon.

El funcionamiento es este:

1. El usuario empieza a escribir el nombre.
2. Con AJAX se consulta una API pública de Pokémon.
3. Se muestran sugerencias que coinciden con lo que está escribiendo.
4. Al elegir una sugerencia, se rellenan automáticamente el tipo, la vida, el daño, la defensa y la velocidad.
5. El usuario solo añade una descripción opcional y guarda el Pokémon.

La parte importante es que el formulario no inventa los datos, sino que los trae de la API y luego los guarda en la base de datos:

```php
$nombreApi = isset($_POST['pokemon_api_name']) ? trim($_POST['pokemon_api_name']) : '';
$pokemonApi = obtenerPokemonApiPorNombre($nombreApi);

$ok = insertarPokemon(
    $pokemonApi['display_name'],
    $descripcion,
    $idUsuario,
    $pokemonApi['api_id'],
    $pokemonApi['api_name'],
    $pokemonApi['primary_type'],
    $pokemonApi['secondary_type'],
    $pokemonApi['vida'],
    $pokemonApi['ataque'],
    $pokemonApi['defensa'],
    $pokemonApi['ataque_especial'],
    $pokemonApi['defensa_especial'],
    $pokemonApi['velocidad'],
    $pokemonApi['sprite_url']
);
```

También he actualizado la tabla de `pokemons` para guardar esos datos nuevos, así no se pierden cuando el usuario crea la publicación.

## API pública con tokens

Para que mis compañeros de clase puedan consumir los datos de mi aplicación, he creado una API pública REST que devuelve JSON.

La idea es simple: el usuario que quiere acceder a mis datos solicita un token, lo guarda en un lugar seguro, y luego lo usa en cada petición.

### ¿Por qué tokens en lugar de JWT u OAuth?

Porque es más simple para un proyecto de clase:

- **JWT**: son tokens que se validan por firma, son más complejos.
- **OAuth**: es un estándar completo de autenticación, pero demasiado para esto.
- **Token opaco**: es solo un token aleatorio que guardo en BD con caducidad. Fácil de entender, fácil de revocar.

### Cómo funciona

1. El usuario compañero solicita un token.
2. Yo creo un token aleatorio, lo guardo en BD con una caducidad (ejemplo: 30 días).
3. Le doy el token (solo una vez, no lo vuelvo a mostrar).
4. Él lo usa en el header `Authorization: Bearer <token>` en cada petición.
5. Mi API valida que el token sea válido, no haya expirado y esté activo.
6. Si todo está bien, devuelve los datos; si no, devuelve un error.

### Endpoints disponibles

#### 1. Crear un token (POST)

```bash
curl -X POST http://localhost/ProyecteServidor1/controller/apiTokens.controller.php?action=create \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Token del compañero",
    "description": "Para consumir mi API desde clase",
    "dias_expiracion": 30
  }'
```

**Respuesta exitosa:**

```json
{
  "success": true,
  "message": "Token creado correctamente. Guarda este token en un lugar seguro, no podrás verlo de nuevo.",
  "token": "a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6",
  "id": 1,
  "expires_at": "2026-06-01 12:00:00",
  "instructions": "Usa este token en el header: Authorization: Bearer a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6"
}
```

#### 2. Listar todos los tokens (GET)

```bash
curl http://localhost/ProyecteServidor1/controller/apiTokens.controller.php
```

Devuelve todos los tokens sin mostrar su hash (solo datos públicos).

#### 3. Revocar un token (DELETE)

```bash
curl -X DELETE "http://localhost/ProyecteServidor1/controller/apiTokens.controller.php?action=revoke&id=1"
```

El token sigue existiendo pero ya no funciona.

#### 4. Obtener lista de pokémons (GET) - Requiere token

```bash
curl -H "Authorization: Bearer a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6" \
  http://localhost/ProyecteServidor1/controller/apiPokemons.controller.php
```

**Parámetros opcionales:**
- `limit`: cantidad de resultados (default: 20, máx: 100)
- `offset`: desde dónde empezar (default: 0)

Ejemplo:

```bash
curl -H "Authorization: Bearer <token>" \
  "http://localhost/ProyecteServidor1/controller/apiPokemons.controller.php?limit=10&offset=0"
```

#### 5. Obtener un pokémon específico (GET) - Requiere token

```bash
curl -H "Authorization: Bearer a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6" \
  "http://localhost/ProyecteServidor1/controller/apiPokemons.controller.php?id=1"
```

### Estructura de respuesta

Cuando consultas la lista de pokémons:

```json
{
  "success": true,
  "meta": {
    "limit": 20,
    "offset": 0,
    "count": 4,
    "total": 4
  },
  "data": [
    {
      "id": 1,
      "titulo": "Pikachu",
      "descripcion": "Mi primer Pokémon eléctrico...",
      "user_id": 1,
      "autor": {
        "id": 1,
        "username": "ash_ketchum",
        "profile_image": "userDefaultImg.jpg"
      },
      "pokemon_api": {
        "id": 25,
        "name": "pikachu",
        "sprite_url": "https://..."
      },
      "stats": {
        "tipo_principal": "electric",
        "tipo_secundario": null,
        "vida": 35,
        "ataque": 55,
        "defensa": 40,
        "ataque_especial": 50,
        "defensa_especial": 50,
        "velocidad": 90
      },
      "created_at": "2026-05-02 10:30:00",
      "updated_at": "2026-05-02 10:30:00"
    }
  ]
}
```

### Uso en Postman

1. En tu perfil de usuario, pulsa `Solicitar acceso endpoint`.
2. Pulsa `Generar token` y guarda el valor que aparece una sola vez.
3. Crea una petición GET a `http://localhost/ProyecteServidor1/controller/apiPokemons.controller.php`
4. En Headers, añade:
   - Key: `Authorization`
   - Value: `Bearer <tu_token>`
5. Envía y verás los pokémons en JSON.

### Solicitud desde el perfil

En la vista de perfil añadí un botón llamado `Solicitar acceso endpoint`.

Cuando el usuario pulsa ese botón:

1. Se abre una ventana con el endpoint exacto que debe usar.
2. La app genera un token temporal desde el navegador.
3. El token se guarda hasheado en la base de datos.
4. El usuario copia el token y lo usa en Postman con la cabecera `Authorization`.

Así el flujo se parece más a un caso real y no depende de dejar un token fijo metido en el SQL.

### Cómo está implementado internamente

En la BD, la tabla `api_tokens` guarda:

```sql
CREATE TABLE `api_tokens` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `token_hash` VARCHAR(255) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `expires_at` DATETIME NOT NULL,
  `last_used_at` DATETIME DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_token_hash` (`token_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

El flujo de validación:

1. El cliente envía el token en `Authorization: Bearer <token>`
2. Extraigo el token del header.
3. Lo hasheo con SHA256.
4. Busco el hash en BD.
5. Verifico que no haya expirado.
6. Verifico que esté activo.
7. Actualizo `last_used_at`.
8. Si todo va bien, devuelvo los datos; si no, error 401 o 403.

La idea es que nunca almaceno el token en claro, solo su hash. Así, aunque alguien acceda a mi BD, no puede usar los tokens.


Marcos López
