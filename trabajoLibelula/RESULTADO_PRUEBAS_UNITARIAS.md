# Resultado de Ejecución — Pruebas Unitarias (Snipe-IT)

> Fecha: 2026-06-13 · Datos 100% reales obtenidos de la ejecución (sin estimaciones).
> Se realizaron 2 ejecuciones consecutivas con resultados idénticos (8 fallos / 307 OK / 600 assertions).

---

## 1. Comando ejecutado

```bash
php artisan test --testsuite=Unit
```
(PHP 8.4.22 vía Herd · conexión `sqlite_testing` → SQLite `:memory:`)

---

## 2–6. Métricas reales

| Métrica | Valor |
|---|---|
| **Total de pruebas** | **315** |
| **Assertions** | **600** |
| **Tiempo de ejecución** | **53.95 s** (1ª corrida: 65.85 s) |
| **Pruebas exitosas** | **307** ✅ |
| **Pruebas fallidas** | **8** ❌ |
| Código de salida | `2` (PHPUnit: hubo fallos) |

> El tiempo varía entre corridas (53.95 s vs 65.85 s) por carga de máquina; el resto de métricas es estable.

---

## 7. Errores encontrados (las 8 pruebas fallidas)

### Grupo A — `Tests\Unit\LdapTest` (6 fallos) — **causa: entorno, no código**

Todas fallan por el mismo motivo: la **extensión PHP `ldap` NO está cargada** en el PHP de Herd, por lo que la constante `LDAP_OPT_REFERRALS` no existe.

- Verificado: `php -m` → LDAP no aparece · `defined("LDAP_OPT_REFERRALS")` → **NO definida**.
- Punto de fallo: [app/Models/Ldap.php:95](app/Models/Ldap.php#L95) → `ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);`

| # | Prueba | Tipo | Mensaje |
|---|---|---|---|
| 1 | `LdapTest > connect` | Error | `Undefined constant "App\Models\LDAP_OPT_REFERRALS"` |
| 2 | `LdapTest > find and bind` | Error | `Undefined constant "App\Models\LDAP_OPT_REFERRALS"` |
| 3 | `LdapTest > find and bind bad password` | Error | `Undefined constant "App\Models\LDAP_OPT_REFERRALS"` |
| 4 | `LdapTest > find and bind cannot find self` | Failure | `Failed asserting that exception message 'Undefined constant "App\Models\LDAP_OPT_REFERRALS"' contains 'Could not search LDAP:'` |
| 5 | `LdapTest > find ldap users` | Error | `Undefined constant "App\Models\LDAP_OPT_REFERRALS"` |
| 6 | `LdapTest > find ldap users paginated` | Error | `Undefined constant "App\Models\LDAP_OPT_REFERRALS"` |

> **Nota:** `ext-ldap` figura como **`suggest`** (opcional) en `composer.json`, no como requerido. Estos fallos desaparecen habilitando la extensión LDAP en `php.ini`.

### Grupo B — `Tests\Unit\Models\LicenseTest` (2 fallos) — **posibles bugs de test/código**

| # | Prueba | Tipo | Mensaje / Detalle |
|---|---|---|---|
| 7 | `LicenseTest > remaincount returns correct available seats` | BadMethodCallException | `Call to undefined method Database\Factories\LicenseFactory::withSeats()` — el test llama a un método de factory `withSeats()` que no existe en [database/factories/LicenseFactory.php](database/factories/LicenseFactory.php). Origen: `vendor/.../ForwardsCalls.php:67`. |
| 8 | `LicenseTest > is deletable returns true when all seats are free` | Failure | `Failed asserting that false is true.` en [tests/Unit/Models/LicenseTest.php:543](tests/Unit/Models/LicenseTest.php#L543). El test crea `License::factory()->create(['seats' => 2])` y espera `isDeletable() === true`, pero devuelve `false`. |

> Los tests del Grupo B están en `tests/Unit/Models/LicenseTest.php`, archivo de trabajo propio del proyecto (no parte del núcleo upstream histórico). Requieren revisión: o falta el método `withSeats()` en la factory, o la lógica de `isDeletable()` no coincide con la expectativa del test.

---

## 8. Resumen / Conclusión

- La suite **Unit se ejecutó realmente** sobre SQLite in-memory: **315 pruebas, 600 assertions, 53.95 s**.
- **307 pasaron (97,5 %)**, **8 fallaron**.
- **6 de 8 fallos** son del **entorno** (extensión `ldap` ausente, opcional según `composer.json`) — no indican un defecto del código de la aplicación.
- **2 de 8 fallos** están en `LicenseTest` y apuntan a inconsistencias reales entre el test y el código/factory:
  1. método de factory `withSeats()` inexistente,
  2. `isDeletable()` devuelve `false` donde el test espera `true`.

### Recomendaciones (no aplicadas; solo reporte)
- **Para eliminar los 6 fallos LDAP:** habilitar `extension=ldap` en el `php.ini` de Herd (`C:\Users\danie\.config\herd\bin\php84\php.ini`) y reiniciar. Es opcional según el proyecto.
- **Para los 2 fallos de LicenseTest:** revisar `LicenseFactory` (agregar/estado `withSeats()`) y la expectativa de `isDeletable()` con seats libres.
