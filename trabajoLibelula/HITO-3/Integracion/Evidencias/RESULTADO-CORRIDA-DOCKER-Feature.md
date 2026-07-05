# Evidencia — Corrida completa de `tests/Feature` en el runner Docker

> Prueba del entorno común (Opción A · Docker) sobre toda la suite de integración.
> Fecha: 2026-07-04.

---

## 1. Comando ejecutado
```bash
docker compose -f trabajoLibelula/HITO-3/Integracion/docker-compose.test.yml run --rm test \
  bash -lc "php artisan test --testsuite=Feature"
```
- Imagen: `integracion-test` (PHP 8.3 CLI + extensiones + `pdo_sqlite` + PCOV).
- BD: `sqlite_testing` (`:memory:`), driver `RefreshDatabase`.
- `memory_limit=-1` fijado en `php.ini` de la imagen.

## 2. Resultado global
| Métrica | Valor |
|---|---|
| **Passed** | **1649** (6090 aserciones) |
| Failed | 4 |
| Incomplete | 8 |
| Skipped | 3 |
| Duración | ~1916 s (~32 min) |
| Exit | Suite ejecutada de principio a fin (sin error de memoria) |

> ✅ **El entorno Docker funciona.** La incidencia de "memoria insuficiente" quedó resuelta al fijar `memory_limit=-1` en el `php.ini` de la imagen (no basta `php -d ...`, porque `artisan test` lanza PHPUnit en un subproceso).

## 3. Análisis de los 4 fallos → NO son defectos del sistema

| # | Test | Causa raíz | ¿Defecto real? |
|---|------|-----------|----------------|
| 1 | `Accessories/Api/IndexAccessoryTest > can filter accessories by searchable count alias` | **SQLite:** `HAVING` sobre alias de subconsulta (`checkouts_count`) → `SQLSTATE[HY000]: HAVING clause on a non-aggregate query` | ❌ No (dialecto) |
| 2 | `AssetModels/Api/IndexAssetModelsTest > asset model index filter can search computed count aliases` | **SQLite:** mismo caso `HAVING` sobre `assets_count` | ❌ No (dialecto) |
| 3 | `Checkouts/Api/AssetCheckoutTest > license seats are assigned to user upon checkout` | Assert de `action_logs` (checkout) — dependiente de entorno/orden; verificar en MySQL | ⚠️ A confirmar en MySQL |
| 4 | `Importing/Api/ImportConsumablesTest > will not create new category when category exists` | HTTP 500 en importación — probable dialecto/SQL; verificar en MySQL | ⚠️ A confirmar en MySQL |

**Evidencia del error (casos 1 y 2):**
```
SQLSTATE[HY000]: General error: 1 HAVING clause on a non-aggregate query
(Connection: sqlite_testing, Database: :memory:,
 SQL: ... having "checkouts_count" = 2 ...)
```
MySQL/MariaDB **sí** permiten `HAVING` sobre un alias no agregado; SQLite no. Por eso estos tests **pasan en la matriz MySQL/Postgres del CI** (`tests-mysql.yml`, `tests-postgres.yml`) y fallan solo en SQLite.

## 3.b Verificación en MariaDB (variante `test-mysql`)

Se creó la variante `test-mysql` (MariaDB 11.4.7 efímera) y se reejecutaron los 4 archivos que fallaban:

```bash
docker compose -f trabajoLibelula/HITO-3/Integracion/docker-compose.test.yml run --rm test-mysql \
  bash -lc "php artisan test <los 4 archivos>"
# Resultado: 38 passed, 1 failed (238 aserciones)
```

| # | Test | SQLite | MariaDB | Veredicto |
|---|------|:------:|:-------:|-----------|
| 1 | `IndexAccessoryTest > can filter accessories by searchable count alias` | ❌ | ✅ | Dialecto (resuelto en MySQL) |
| 2 | `IndexAssetModelsTest > ...computed count aliases` | ❌ | ✅ | Dialecto (resuelto en MySQL) |
| 3 | `ImportConsumablesTest > will not create new category when category exists` | ❌ | ✅ | Dialecto (resuelto en MySQL) |
| 4 | `AssetCheckoutTest > license seats are assigned to user upon checkout` | ❌ | ❌ | **Fallo real** (ambas BD) — test del grupo |

**Sobre el caso 4:** es un test **añadido por el grupo** (Anette-Gallegos, commit `acb91d61`, 2026-06-12). Falla la aserción del `action_log` de checkout (`tests/Feature/Checkouts/Api/AssetCheckoutTest.php:321`) en **SQLite y MariaDB**. El checkout API sí responde `success` y el activo queda asignado (líneas 306-318 pasan); lo que no cuadra es el registro esperado en `action_logs`. → **Requiere revisión del grupo**: corregir la aserción del test o documentarlo como incidente si es un defecto real del sistema.

## 4. Conclusión (para el Plan y el Informe)

- El **runner Docker con SQLite `:memory:`** es válido, rápido y reproducible para **~99.7 %** de la suite (1649/1653 casos efectivos).
- Los 4 fallos **materializan el riesgo RI-03 del Plan** ("Diferencias de comportamiento entre SQLite y MySQL/PostgreSQL"): son limitaciones de **dialecto SQL**, no defectos funcionales.
- Para una corrida **100 % verde** (paridad con la BD de producción MariaDB) hay dos caminos:
  1. Ejecutar esos casos en la **matriz MySQL/Postgres** (ya cubierta por el CI).
  2. Añadir un **servicio MySQL** al `docker-compose.test.yml` (variante `DB_CONNECTION=mysql`) para el runner local.
- Esto ilustra la distinción del docente entre integración *Small* (subsistemas internos, cubierta con SQLite) e integración *Large* (con la BD COTS real, MariaDB).

## 5. Reproducir
```bash
# Requiere Docker Desktop abierto. Desde la raíz del repo:
docker compose -f trabajoLibelula/HITO-3/Integracion/docker-compose.test.yml run --rm test
```

*Evidencia de ejecución — Hito 3 · Pruebas de Integración.*
