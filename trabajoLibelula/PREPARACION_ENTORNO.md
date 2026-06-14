# Reporte de Preparación del Entorno de Pruebas — Snipe-IT

> Fecha: 2026-06-13 · Objetivo: dejar el proyecto listo para ejecutar la suite **Unit** con SQLite in-memory.
> **No se ejecutaron pruebas** (solo verificación de que pueden ejecutarse).

---

## 1. Verificaciones previas

| Ítem | Resultado |
|---|---|
| **PHP** | ✅ PHP **8.4.22** (Herd) — `~/.config/herd/bin/php.bat` |
| **Composer** | ✅ **2.9.5** — `~/.config/herd/bin/composer.phar` (se invoca como `php composer.phar`) |
| **Extensiones requeridas** | ✅ curl, fileinfo, iconv, json, mbstring, PDO, **pdo_sqlite**, **sqlite3**, gd, openssl, tokenizer, xml |
| **PDO SQLite `:memory:`** | ✅ Probado con un INSERT/SELECT real → OK |

---

## 2. Qué se configuró

### a) Dependencias (`vendor/`)
- Ejecutado: `composer install --no-interaction --no-progress --prefer-dist --no-scripts --no-ansi`
- **148 paquetes** instalados.
- Ejecutado `php artisan package:discover` (porque se usó `--no-scripts`) → descubrimiento OK.
- `vendor/bin/phpunit` presente → **PHPUnit 11.5.55**.

### b) Resolución del bloqueo SSL (Avast)
- **Problema:** `composer install` fallaba con `curl error 60: unable to get local issuer certificate`.
- **Causa:** **Avast Antivirus** intercepta el tráfico TLS y re-firma los certificados con su CA raíz (`CN=Avast Web/Mail Shield Root`), que **no estaba** en el bundle de CA de PHP.
- **Solución (reversible):**
  1. Se exportó el CA raíz de Avast desde el almacén de Windows (`Cert:\LocalMachine\Root`, thumbprint `013A314D...`).
  2. Se respaldó el bundle original → `~/.config/herd/config/php/cacert.pem.bak-pre-avast`.
  3. Se añadió el CA de Avast al bundle `~/.config/herd/config/php/cacert.pem` (de 145 → 146 certificados).
  4. Verificado: petición HTTPS desde PHP a `api.github.com` → **HTTP 200 / SSL OK**.
- Esta es una configuración a nivel de Herd (global del usuario), no del repositorio. Para revertir: restaurar el `.bak-pre-avast`.

### c) Archivos de entorno
- **`.env.testing`** (creado) — configurado para **SQLite in-memory**:
  - `APP_ENV=testing`, `DB_CONNECTION=sqlite_testing`, `MAIL_MAILER=log`, throttling de login alto.
  - Necesario porque `TestCase::guardAgainstMissingEnv()` aborta la suite si el archivo no existe.
- **`.env`** (creado) — copia de `.env.testing.example`, con `APP_KEY` ya presente (para comandos artisan).

### d) Llaves de Passport
- `php artisan passport:keys --force` → generadas:
  - `storage/oauth-private.key`
  - `storage/oauth-public.key`
- Necesarias porque los tests de API usan `Passport::actingAs()`.

### e) Verificación de configuración SQLite (entorno testing)
```
default  = sqlite_testing
driver   = sqlite
database = :memory:
```
Resuelto correctamente vía `config('database.*')` bajo `APP_ENV=testing`.

---

## 3. Verificación de que PHPUnit puede ejecutarse (sin correr pruebas)

- `vendor/bin/phpunit --version` → **PHPUnit 11.5.55** ✅
- `vendor/bin/phpunit --testsuite Unit --list-tests` → **315 casos** detectados y framework booteado sin errores ✅
  - (El listado obliga a cargar `bootstrap/autoload.php`, el autoloader y los service providers; que liste sin excepción confirma que el bootstrap funciona.)

---

## 4. Problemas encontrados y su estado

| Problema | Severidad | Estado |
|---|---|---|
| `vendor/` no instalado | 🔴 Bloqueo | ✅ Resuelto (composer install) |
| Falta `.env.testing` (guardarraíl) | 🔴 Bloqueo | ✅ Resuelto (creado, SQLite) |
| Avast intercepta SSL → composer falla | 🔴 Bloqueo | ✅ Resuelto (CA añadido al bundle) |
| Llaves de Passport ausentes | 🟠 Medio | ✅ Resuelto (generadas) |
| Composer/PHP no en PATH global | 🟡 Menor | ⚠️ Convivencia: se usan rutas de Herd |
| Schema `phpunit.xml` 10.5 vs PHPUnit 11 | 🟡 Menor | ⚠️ No bloquea; cosmético |
| Cobertura requiere driver (PCOV/Xdebug) | 🟡 Menor | ⏳ Pendiente: usar `herd coverage ...` cuando se desee |

---

## 5. ¿Está listo para ejecutar pruebas?

### ✅ SÍ — el proyecto está listo para ejecutar la suite **Unit** (y Feature) con SQLite in-memory.

Todos los bloqueos están resueltos: dependencias instaladas, `.env.testing` presente, conexión `sqlite_testing` (`:memory:`) verificada, llaves de Passport generadas y PHPUnit booteando 315 casos en la suite Unit.

### Comando para ejecutar cuando lo autorices
```bash
# Usando PHP de Herd
php artisan test --testsuite=Unit
# o directamente
vendor/bin/phpunit --testsuite Unit
```

### Notas operativas
- PHP y Composer se invocan con las rutas de Herd:
  - `PHP = ~/.config/herd/bin/php.bat`
  - `COMPOSER = php ~/.config/herd/bin/composer.phar`
- Para cobertura: `composer run coverage:herd:html` (requiere ejecutar a través de Herd para activar el driver de cobertura), o el workflow CI con PCOV.
- `.env`, `.env.testing`, `vendor/` y las llaves OAuth están en `.gitignore` (no se versionan — comportamiento esperado).
