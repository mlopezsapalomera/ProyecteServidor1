# Apuntes examen - Flujo de trabajo (Proyecto PokéNet)

Este documento resume como funciona cada modulo pedido, que funciones intervienen y por que se implementa asi.

## 0) Arquitectura general que se repite en casi todo

Patron usado: MVC sencillo.

1. La vista muestra formulario o interfaz (por ejemplo login, editar perfil, index).
2. El controlador recibe GET/POST, valida datos y permisos.
3. El modelo ejecuta SQL con PDO + prepared statements.
4. El controlador redirige con mensajes por query string (`ok` o `error`).

Por que se hace asi y no todo en la vista:
- Separacion de responsabilidades: HTML no mezcla logica de negocio.
- Codigo mas mantenible y testeable.
- Seguridad mejor centralizada (validaciones, permisos, SQL seguro).

---

## 1) Ordenacio dels articles

### Flujo

1. En `view/index.php` hay dos selects:
   - `ordenarPor` (`id`, `titulo`, `created_at`)
   - `direccionOrden` (`ASC`, `DESC`)
2. Al cambiar un select, se envia GET automaticamente.
3. `controller/paginacio.controller.php` recoge estos parametros.
4. Llama a `obtenerPokemons($limit, $offset, $orderBy, $orderDir)` en el modelo.
5. `model/pokemon.php` valida los parametros y construye el `ORDER BY`.

### Funcion clave

- `obtenerPokemons(...)` en `model/pokemon.php`.

Puntos importantes:
- Usa whitelist para campos de orden: `['id', 'titulo', 'created_at']`.
- Normaliza direccion a `ASC` o `DESC`.
- `LIMIT` y `OFFSET` se pasan con bind (`PDO::PARAM_INT`).

### Por que esta implementacion

`ORDER BY` no permite bind de nombre de columna en muchos casos. Por eso:
- No se puede hacer `ORDER BY :campo` de forma segura y portable.
- Se valida contra lista cerrada (whitelist) y luego se concatena.
- Asi se evita SQL injection en ordenacion.

---

## 2) Remember me

### Flujo

1. En login (`view/login.vista.php`), el usuario marca `remember_me`.
2. En `controller/login.controller.php`:
   - Si credenciales correctas, llama `iniciarSesion($usuario)`.
   - Si `remember_me` esta activo, crea token y cookie.
3. En `model/user.php`:
   - `crearRemembertoken($userId, $dias)` genera token aleatorio.
   - Guarda SOLO hash SHA-256 del token en tabla `remember_tokens`.
4. En cada request, `security/auth.php` ejecuta `intentarLoginAutomatico()`:
   - Lee cookie `remember_token`.
   - Verifica token con `verificarRememberToken(...)`.
   - Si es valido, inicia sesion automaticamente.
5. Al cerrar sesion (`controller/logout.controller.php`):
   - Elimina token en BD.
   - Borra cookie.

### Funciones clave

- `crearRemembertoken(...)`
- `verificarRememberToken(...)`
- `renovarRememberToken(...)`
- `eliminarRememberToken(...)`
- `intentarLoginAutomatico()`

### Por que se hace asi

- Token aleatorio (`random_bytes`) > token predecible.
- Guardar hash del token en BD (no token plano):
  - Si filtran la BD, no pueden usar directamente los tokens.
- Expiracion (`expires_at`) limita ventana de riesgo.
- Rotacion parcial: al verificar, se renueva expiracion.

Nota tecnica:
- En el controlador se llama `crearRememberToken(...)` y en modelo esta como `crearRemembertoken(...)`.
- En PHP los nombres de funciones no distinguen mayus/minus, por eso funciona.

---

## 3) reCAPTCHA

### Flujo

1. En login se lleva conteo con `$_SESSION['intentos_login']`.
2. Desde el intento 3, la vista muestra widget Google reCAPTCHA.
3. Al enviar formulario, `controller/login.controller.php` ejecuta `validarRecaptcha()`.
4. Esa funcion llama a `https://www.google.com/recaptcha/api/siteverify` con:
   - `secret`
   - `response` del formulario
   - `remoteip`
5. Si falla, no permite login y aumenta intentos.

### Funcion clave

- `validarRecaptcha()` en `controller/login.controller.php`.

### Por que esta estrategia y no captcha siempre

- UX mejor: no molesta al usuario legitimo en primer intento.
- Seguridad adaptativa: se activa cuando detecta patron de riesgo (intentos repetidos).

---

## 4) Editar perfil personal

### Flujo

1. Usuario autenticado abre `view/modificarPerfil.vista.php`.
2. Envia POST a `controller/modificarPerfil.controller.php` con:
   - `username`
   - (opcional) `profile_image`
3. El controlador valida:
   - autenticacion
   - longitud username
   - username unico (`existeUsername`)
   - tipo y tamano de imagen
4. Si hay imagen valida:
   - crea nombre unico
   - mueve archivo a `assets/img/userImg/`
   - borra foto anterior si no era la default
5. Llama `actualizarPerfil(...)` en modelo.
6. Actualiza `$_SESSION['usuario']['username']` para que navbar quede sincronizada.

### Funciones clave

- `actualizarPerfil($userId, $username, $profileImage = null)`
- `existeUsername(...)`
- `obtenerUsuarioPorId(...)`

### Por que se hace asi

- Validar MIME con `finfo_file` es mas fiable que confiar en extension.
- Nombre de archivo unico evita colisiones.
- Borrar imagen antigua evita basura en disco.
- Actualizar sesion evita inconsistencia visual tras cambio de username.

---

## 5) Usuari Admin

### Flujo

1. El rol viene en tabla `users.role` (default `user`).
2. Al hacer login, `iniciarSesion()` guarda `role` en sesion.
3. `esAdmin()` valida si el usuario actual tiene rol `admin`.
4. Acceso a `view/adminPanel.vista.php` protegido por:
   - `estaIdentificado()`
   - `esAdmin()`
5. El panel lista usuarios no admin (`obtenerTodosLosUsuarios(true)`).
6. Acciones admin:
   - ver perfil
   - eliminar usuario (`controller/eliminarUsuario.controller.php`)

### Seguridad aplicada en borrado de usuarios

`eliminarUsuario.controller.php` comprueba:
- id valido
- usuario existe
- no sea admin
- no te elimines a ti mismo

`eliminarUsuario(...)` usa transaccion:
- borra publicaciones
- borra usuario
- commit o rollback

### Por que se hace asi

- Control por rol en backend (no solo ocultar botones en frontend).
- Reglas de negocio criticas en controlador (no autodestruir admin).
- Transaccion = consistencia (no dejar datos a medias).

---

## 6) Barra de cerca

### Flujo

1. En `view/index.php` hay input de busqueda con JS.
2. Al escribir:
   - aplica debounce de 300 ms
   - exige minimo 2 caracteres
3. JS llama `fetch('controller/buscar.controller.php?q=...')`.
4. `controller/buscar.controller.php`:
   - busca usuarios (`buscarUsuarios`)
   - busca publicaciones (`buscarPokemons`)
   - responde JSON unificado
5. Front renderiza bloques "Usuarios" y "Publicaciones".

### Funciones clave

- `buscarUsuarios($query, $limit)`
- `buscarPokemons($query, $limit)`

### Por que se hace asi

- AJAX evita recargar pagina completa.
- Debounce reduce llamadas y carga del servidor.
- Limite de resultados mejora rendimiento y UX.
- `LIKE` con prepared statements protege de inyeccion en query de texto.

---

## 7) Canvi de contrasenya (usuario logueado)

### Flujo

1. Desde perfil, usuario va a `view/cambiarContrasena.vista.php`.
2. Envia POST a `controller/cambiarContrasena.controller.php`.
3. El controlador valida:
   - autenticacion
   - campos completos
   - password actual correcta (`password_verify`)
   - nueva longitud minima
   - confirmacion coincide
   - nueva distinta de actual
4. Si todo ok:
   - hashea nueva clave con `password_hash(..., PASSWORD_DEFAULT)`
   - guarda con `actualizarContrasena(...)`

### Funciones clave

- `password_verify(...)`
- `password_hash(..., PASSWORD_DEFAULT)`
- `actualizarContrasena(...)`

### Como se hashea y por que asi

- Nunca se guarda password en texto plano.
- `password_hash(..., PASSWORD_DEFAULT)` usa algoritmo moderno (actualmente bcrypt en muchas instalaciones; puede cambiar en futuro).
- `password_verify` compara password en claro con hash guardado de forma segura.

Por que esto es mejor que `md5` o `sha1` directo:
- `md5/sha1` son rapidos y faciles de romper por fuerza bruta.
- `password_hash` incluye salt automaticamente y coste computacional.
- Es API nativa y recomendada por PHP para passwords.

---

## 8) Recuperacio de contrasenya

### Flujo completo

1. Usuario abre `view/recuperarContrasena.vista.php` e introduce email.
2. `controller/solicitarRecuperacion.controller.php` valida email y existencia.
3. Modelo genera token con `generarTokenRecuperacion($email)`:
   - `bin2hex(random_bytes(32))`
   - guarda token y expiracion (5 min) en `users`.
4. Controlador envia correo con PHPMailer con link:
   - `view/resetearContrasena.vista.php?token=...`
5. Esa vista valida token vigente (`verificarTokenRecuperacion`).
6. Usuario envia nueva password a `controller/resetearContrasena.controller.php`.
7. Modelo `resetearContrasenaConToken(...)`:
   - revalida token
   - hashea nueva password
   - limpia `reset_token` y `reset_token_expira`

### Funciones clave

- `generarTokenRecuperacion(...)`
- `verificarTokenRecuperacion(...)`
- `resetearContrasenaConToken(...)`
- `limpiarTokenRecuperacion(...)`

### Por que se hace asi

- Token aleatorio para evitar adivinacion.
- Expiracion corta (5 min) reduce riesgo si alguien roba el enlace.
- Invalidar token tras uso evita reutilizacion.
- Revalidar token en vista y controlador da defensa en profundidad.

---

## Funciones transversales importantes (para defender en examen)

1. Prepared statements (`prepare` + `execute`/`bindValue`)
   - Evitan SQL injection.
   - Separan SQL y datos.

2. Escape de salida HTML (`htmlspecialchars`)
   - Evita XSS al mostrar username, titulos, etc.

3. Control de sesion (`security/auth.php`)
   - `mantenerSesion()` corta sesion por inactividad.
   - `estaIdentificado()`, `idUsuarioActual()`, `esAdmin()` centralizan permisos.

4. Patron PRG (Post/Redirect/Get)
   - Tras POST se redirige.
   - Evita reenvio accidental al refrescar.

5. Validaciones en servidor (aunque haya JS)
   - Nunca confiar solo en validacion cliente.

---

## Preguntas tipicas de examen y respuesta corta

### "Por que guardas hash y no password?"
Porque si hay fuga de BD, el atacante no ve claves reales. Ademas `password_hash` mete salt y coste, haciendo crackeo mucho mas caro.

### "Por que whitelist para ORDER BY?"
Porque nombres de columna no se parametrizan como valores normales. La whitelist evita inyeccion cuando hay que interpolar SQL dinamico.

### "Por que token en remember me y no guardar usuario en cookie?"
La cookie del usuario seria facilmente manipulable. Con token aleatorio + hash en BD, el servidor valida autenticidad y expiracion.

### "Por que captcha a partir de 3 intentos?"
Balancea seguridad y usabilidad: frena bots sin penalizar cada login legitimo.

### "Por que transaccion al borrar usuario?"
Para mantener consistencia: o se borran usuario y posts juntos, o no se borra nada.

---

## Resumen ultra rapido (chuleta)

- Ordenacion segura: whitelist + `ORDER BY` controlado.
- Remember me: token aleatorio en cookie, hash en BD, expiracion, auto-login.
- reCAPTCHA: activacion por intentos fallidos.
- Editar perfil: validaciones + subida segura de imagen + update sesion.
- Admin: control por rol en backend + panel gestion usuarios.
- Busqueda: AJAX con debounce y respuesta JSON mixta.
- Cambio contrasena: verificar actual + hash nuevo.
- Recuperacion: token temporal por email + reset con invalidacion de token.
