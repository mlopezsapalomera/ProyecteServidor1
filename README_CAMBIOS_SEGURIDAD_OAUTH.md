# Cambios aplicados (OAuth + seguridad)

Este archivo resume lo que se ha cambiado y por que.

## 1) HybridAuth en modo seguro

Archivo tocado: `config/hybridauth.config.php`

Cambio:
- `debug_mode` paso de `true` a `false`.

Por que:
- Evita dejar trazas de depuracion activas por defecto.
- Reduce riesgo de exponer datos de errores o tokens en logs.

## 2) Proteccion de archivos sensibles por Apache

Archivo tocado: `.htaccess`

Cambios:
- Se deniega acceso web a:
  - `env.php`
  - `.env`
  - `composer.json`
  - `composer.lock`
  - `.gitignore`
- Se bloquea acceso web a:
  - cualquier ruta bajo `logs/`
  - cualquier archivo `*.log`

Por que:
- Aunque los secretos se cargan por PHP internamente, no deben poder pedirse por URL.
- Si algun log se genera, no debe ser descargable desde navegador.

## 3) Plantilla de entorno actualizada

Archivo tocado: `env.php.template`

Cambios:
- Se quito referencia a documentacion OAuth que ya no existe.
- Se dejo clara la callback recomendada para GitHub HybridAuth:
  - `http://localhost/ProyecteServidor1/controller/oauthHybridGithub.controller.php`

Por que:
- Evitar confusiones de configuracion.
- Tener una referencia directa y util en el propio template.

## 4) Regla de base de datos fijada en el SQL principal

Archivo tocado: `model/Pt03_Marcos_Lopez.sql`

Cambio:
- Se anadio cabecera con regla del proyecto:
  - El archivo `Pt03_Marcos_Lopez.sql` es la fuente unica de esquema + datos iniciales.
  - Los cambios de BD se hacen ahi directamente.
  - No se crean `migration_*.sql`.

Por que:
- Encaja con tu flujo de trabajo: borrar BD y recrearla desde un unico archivo actualizado.

## 5) Estado actual esperado para OAuth GitHub

Para que funcione, en `env.php` (local, no en Git) deben existir:
- `GITHUB_CLIENT_ID`
- `GITHUB_CLIENT_SECRET`

Y en GitHub OAuth App:
- Homepage URL: `http://localhost/ProyecteServidor1/`
- Authorization callback URL: `http://localhost/ProyecteServidor1/controller/oauthHybridGithub.controller.php`

## 6) Seguridad de claves (resumen rapido)

Ahora mismo quedan mejor protegidas porque:
- `env.php` ya esta ignorado por Git (`.gitignore`).
- acceso web directo a `env.php` esta bloqueado por `.htaccess`.
- logs y `*.log` bloqueados por `.htaccess`.
- HybridAuth debug desactivado por defecto.

Si alguna clave se llego a compartir fuera de tu equipo, recomendacion:
- regenerar `Client Secret` en Google/GitHub.
