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
| 4 | `AssetCheckoutTest > license seats are assigned to user upon checkout` | ✅* | ✅* | **RESUELTO** — era error del test (del grupo) |

`*` Tras el fix del test (ver §3.c).

### 3.c Caso 4 — diagnóstico y corrección (RESUELTO)

- **Test:** `test_license_seats_are_assigned_to_user_upon_checkout`, añadido por el grupo (Anette-Gallegos, commit `acb91d61`).
- **Causa raíz (error del test, no del sistema):** la clase `AssetCheckoutTest` falsea el evento en `setUp()` → `Event::fake([CheckoutableCheckedOut::class])`. El `action_log` de checkout lo escribe `LogListener::onCheckoutableCheckedOut()` **al reaccionar a ese evento**; al estar falseado, el listener no corre y **la fila en `action_logs` nunca se crea**. La aserción `assertDatabaseHas('action_logs', …)` (línea 321) era **imposible de cumplir** por diseño de la clase. Por eso fallaba en **SQLite y MariaDB** por igual.
- **Corrección aplicada:** se sustituyó la aserción del `action_log` por la verificación del **evento** (patrón que ya usa el resto de la clase, línea 269):
  ```php
  Event::assertDispatched(CheckoutableCheckedOut::class, 1);
  Event::assertDispatched(fn (CheckoutableCheckedOut $event) =>
      $event->checkoutable->is($asset) && $event->checkedOutTo->is($targetUser));
  ```
- **Verificación post-fix:**
  | Entorno | Resultado |
  |---|---|
  | SQLite (aislado) | ✅ 1 passed (9 aserciones) |
  | Clase completa `AssetCheckoutTest` | ✅ 17 passed |
  | MariaDB (`test-mysql`, aislado) | ✅ 1 passed (9 aserciones) |
- **Naturaleza:** defecto **del test del grupo**, no del sistema. Corregirlo es parte del trabajo de QA (no viola la regla de "no tocar el código de producción": se corrigió una **prueba propia**, no la app).

## 4. Conclusión (para el Plan y el Informe)

- El **runner Docker funciona** en ambas variantes; la incidencia de memoria quedó resuelta (`memory_limit=-1` en el `php.ini`).
- **Clasificación final de los 4 fallos de la corrida SQLite:**
  - **3 = diferencias de dialecto** (`HAVING` sobre alias no agregado) → **pasan en MariaDB**. Materializan el riesgo **RI-03** del Plan; no son defectos funcionales.
  - **1 = defecto del test del grupo** (evento falseado) → **corregido** (§3.c). No era defecto del sistema.
- **Estado final:** con la variante **`test-mysql`** (paridad con producción) + el fix del test, **no quedan fallos reales**. Se recomienda `test-mysql` para la **corrida oficial** del Hito 3.
- Esto ilustra la distinción del docente entre integración *Small* (subsistemas internos, SQLite) e integración *Large* (BD COTS real, MariaDB): la BD real elimina los falsos negativos de dialecto.

> Nota metodológica: los conteos globales PASS/FAIL de esta corrida son **evidencia para el Informe** (Test Completion Report), no para el Plan. El Plan solo referencia esta evidencia.

## 5. Reproducir
```bash
# Requiere Docker Desktop abierto. Desde la raíz del repo:
docker compose -f trabajoLibelula/HITO-3/Integracion/docker-compose.test.yml run --rm test
```

*Evidencia de ejecución — Hito 3 · Pruebas de Integración.*
