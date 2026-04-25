# README del proyecto (explicado por mi)

Hola profe,

este README lo he escrito de forma más simple para contar lo que he hecho en el proyecto, sin tanto lenguaje técnico.

## Índice

1. [Cómo organicé el proyecto](#cómo-organicé-el-proyecto)
2. [Cómo uso GET y POST](#cómo-uso-get-y-post)
3. [Por qué uso require_once y require](#por-qué-uso-require_once-y-require)
4. [Recuperar contraseña con token y PHPMailer](#recuperar-contraseña-con-token-y-phpmailer)
5. [OAuth: lo que hice a mano y lo que hice con HybridAuth](#oauth-lo-que-hice-a-mano-y-lo-que-hice-con-hybridauth)
6. [Cierre](#cierre)

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

## Cierre

En resumen, con este proyecto he practicado:

Marcos López
