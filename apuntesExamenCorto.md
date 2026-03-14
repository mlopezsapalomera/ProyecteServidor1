# Apuntes examen (version corta)

## 1) Ordenacio dels articles

Flujo rapido:
1. [view/index.php](view/index.php) envia `ordenarPor` y `direccionOrden` por GET.
2. [controller/paginacio.controller.php](controller/paginacio.controller.php) recoge parametros y calcula paginacion.
3. [model/pokemon.php](model/pokemon.php) usa `obtenerPokemons(...)` con `ORDER BY` controlado.

Idea clave:
- Se usa whitelist de campos (`id`, `titulo`, `created_at`) para evitar inyeccion en `ORDER BY`.

## 2) Remember me

Flujo rapido:
1. En [view/login.vista.php](view/login.vista.php) marcas checkbox `remember_me`.
2. [controller/login.controller.php](controller/login.controller.php) crea token si login correcto.
3. [model/user.php](model/user.php) guarda hash SHA-256 en `remember_tokens` y devuelve token plano para cookie.
4. [security/auth.php](security/auth.php) intenta login automatico leyendo cookie.
5. [controller/logout.controller.php](controller/logout.controller.php) borra token en BD y cookie.

Idea clave:
- En BD se guarda hash del token, no token plano.

## 3) reCAPTCHA

Flujo rapido:
1. [controller/login.controller.php](controller/login.controller.php) cuenta intentos fallidos en sesion.
2. Desde 3 intentos, [view/login.vista.php](view/login.vista.php) muestra widget.
3. `validarRecaptcha()` verifica en Google antes de autenticar.

Idea clave:
- Seguridad adaptativa: captcha solo cuando hay riesgo.

## 4) Editar perfil personal

Flujo rapido:
1. Formulario en [view/modificarPerfil.vista.php](view/modificarPerfil.vista.php).
2. POST a [controller/modificarPerfil.controller.php](controller/modificarPerfil.controller.php).
3. Validaciones: username, unicidad, tipo/tamano imagen.
4. Update en [model/user.php](model/user.php) con `actualizarPerfil(...)`.
5. Actualiza tambien username en sesion.

Idea clave:
- Se valida MIME real con `finfo_file`, no solo extension.

## 5) Usuari Admin

Flujo rapido:
1. Rol en `users.role` (SQL en [model/Pt03_Marcos_Lopez.sql](model/Pt03_Marcos_Lopez.sql)).
2. [security/auth.php](security/auth.php) define `esAdmin()`.
3. [view/adminPanel.vista.php](view/adminPanel.vista.php) exige usuario admin.
4. Eliminar usuario via [controller/eliminarUsuario.controller.php](controller/eliminarUsuario.controller.php).

Idea clave:
- Permisos siempre en backend, no solo ocultando botones.

## 6) Barra de cerca

Flujo rapido:
1. JS en [view/index.php](view/index.php) hace debounce (300 ms).
2. Llama `fetch` a [controller/buscar.controller.php](controller/buscar.controller.php).
3. Ese controlador consulta usuarios + publicaciones.
4. Devuelve JSON y la vista renderiza resultados.

Idea clave:
- Minimo 2 caracteres + limites de resultados = mejor rendimiento.

## 7) Canvi de contrasenya

Flujo rapido:
1. Formulario en [view/cambiarContrasena.vista.php](view/cambiarContrasena.vista.php).
2. POST a [controller/cambiarContrasena.controller.php](controller/cambiarContrasena.controller.php).
3. Verifica password actual (`password_verify`).
4. Hashea nueva (`password_hash`) y guarda con `actualizarContrasena(...)`.

Idea clave:
- Nunca se guarda contraseña en texto plano.

## 8) Recuperacio de contrasenya

Flujo rapido:
1. Solicitud en [view/recuperarContrasena.vista.php](view/recuperarContrasena.vista.php).
2. [controller/solicitarRecuperacion.controller.php](controller/solicitarRecuperacion.controller.php) genera token y envia email.
3. Usuario abre [view/resetearContrasena.vista.php](view/resetearContrasena.vista.php) con token.
4. [controller/resetearContrasena.controller.php](controller/resetearContrasena.controller.php) cambia contraseña.
5. [model/user.php](model/user.php) limpia token tras uso.

Idea clave:
- Token aleatorio + expiracion corta (5 min) + invalidacion tras reset.

## Funciones clave para memorizar

- `password_hash(...)`: genera hash seguro de password.
- `password_verify(...)`: compara password con hash.
- `prepare(...)` + `execute(...)`: SQL seguro con PDO.
- `esAdmin()`: control de permisos.
- `intentarLoginAutomatico()`: login por cookie remember me.

## Preguntas tipicas (respuesta corta)

Por que `password_hash` y no md5:
- Porque md5 es rapido de romper; `password_hash` usa algoritmo fuerte + salt.

Por que whitelist en ordenacion:
- Porque nombre de columna en `ORDER BY` no se parametriza igual que un valor normal.

Por que token en remember me:
- Evita guardar datos sensibles en cookie y permite expirar/revocar sesiones.
