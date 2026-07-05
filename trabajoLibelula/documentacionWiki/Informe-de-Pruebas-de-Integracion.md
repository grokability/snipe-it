# Informe de Pruebas de Integración

> **Test Completion Report** conforme a **ISO/IEC/IEEE 29119-3**. Reporta la **ejecución** de las pruebas de integración planificadas en el [Plan de Pruebas de Integración](Plan-de-Pruebas-de-Integracion). El Plan planifica; este Informe reporta resultados, defectos y métricas.

| Campo | Detalle |
|-------|---------|
| **Documento** | Informe de Pruebas de Integración — Snipe-IT |
| **Versión** | 1.1 |
| **Hito / Sprint** | Hito 3 (ejecución) |
| **Nivel de prueba** | Integración (componentes e interfaces) — alcance *Small* |
| **Herramienta** | PHPUnit (suite `Feature`) — rol de Supertest en el stack PHP/Laravel |
| **Entorno** | Runner Docker (`docker-compose.test.yml`): SQLite `:memory:` y MariaDB 11.4.7 |
| **Fecha de ejecución** | 2026-07-04 (suite heredada) · 2026-07-05 (FI-01/FI-02) |
| **Estándar** | ISO/IEC/IEEE 29119-3 (Test Completion Report) |

---

## 1. Resumen ejecutivo

Se ejecutó la **suite de integración** de Snipe-IT (`tests/Feature`, nivel integración/funcional HTTP) en un **entorno Docker reproducible**. De **1653 casos efectivos**, **1649 resultaron PASS** en SQLite. Los **4 fallos** se analizaron y clasificaron por causa raíz:

- **3 fallos = diferencias de dialecto SQL** (SQLite no soporta `HAVING` sobre alias no agregado). **Pasan en MariaDB** (BD de producción). No son defectos funcionales → confirman el riesgo **RI-03** del Plan.
- **1 fallo = defecto en un test del propio grupo** (aserción incompatible con un evento falseado). **Corregido**; verificado verde en SQLite y MariaDB.

**Veredicto global: la integración entre subsistemas es correcta.** Tras el fix y usando la variante MariaDB, **no quedan fallos reales**. Se detectó y documentó **1 incidente** (defecto de prueba, resuelto).

**Actualización v1.1 (2026-07-05):** se implementaron y ejecutaron los casos de **inyección de fallas de interfaz FI-01 y FI-02** (aporte propio, `tests/Feature/Integracion/AssetCheckoutInterfaceTest.php`, 4 métodos). **FI-02 reveló un defecto real del sistema (INC-02):** Snipe-IT aceptaba un checkout con fecha esperada de devolución anterior a la fecha de entrega. Se corrigió con una regla de validación y se verificó con una regresión de 140 tests sin fallos (§7).

---

## 2. Base de la prueba

- **Plan de referencia:** [Plan de Pruebas de Integración](Plan-de-Pruebas-de-Integracion) v1.2.
- **Trazabilidad:** los flujos INT-XX se vinculan a los RF-XX y CPF-XX en la [Matriz de Trazabilidad](Matriz-de-Trazabilidad).
- **Suite base heredada:** el proyecto original ya incluye **292 archivos / 1509 métodos** en `tests/Feature/` (nivel integración). Este Informe documenta su **ejecución**, la **clasificación de fallos** y el **aporte del grupo**.

---

## 3. Alcance ejecutado

| Flujo | Descripción | Estado de ejecución |
|-------|-------------|---------------------|
| INT-01…INT-10 | Flujos de integración heredados (checkout/checkin de activos, licencias, accesorios, consumibles, componentes; aceptación; FMCS; autorización; contrato API; requestable) | ✅ Ejecutados en la corrida completa |
| INT-04 (aporte) | Asiento de licencia asignado en checkout — test del grupo | ✅ Ejecutado y **corregido** |
| FI-01/FI-02 (aporte) | Inyección de fallas de interfaz sintáctica y semántica — `Integracion/AssetCheckoutInterfaceTest.php` | ✅ **Ejecutados** (2026-07-05) — FI-02 detectó el defecto **INC-02**, corregido y verificado |
| FI-03 | Inyección de falla de resiliencia/estado (doble checkout) | 🕗 Diseñado en Plan §4.1 — pendiente de implementación |
| INT-11/12/13 | Refuerzos (CustomFields, Depreciación, StatusLabels) | 🕗 Diseñados en Plan §2.4 — **pendientes de implementación** |

---

## 4. Herramientas y entorno (justificación por stack)

El laboratorio del docente usa **Supertest/Postman (Node)**, pero permite elegir la herramienta **según el stack**. Snipe-IT es **PHP 8.2+/Laravel 12**; el rol de Supertest (pruebas de integración HTTP por código, versionadas en el repo) lo cumple la **suite `Feature` de PHPUnit** (`$this->post(route(...))`, factories, `RefreshDatabase`). Los servicios externos se sustituyen con **mocks/fakes** de Laravel (`Mail::fake()`, `Notification::fake()`, `Event::fake()`).

**Entorno de ejecución — runner Docker** (`trabajoLibelula/HITO-3/Integracion/docker-compose.test.yml`):

| Variante | BD | Uso |
|----------|----|----|
| `test` | SQLite `:memory:` | Rápida (día a día) |
| `test-mysql` | MariaDB 11.4.7 (efímera, `tmpfs`) | **Oficial** (paridad con producción/CI) |

- `memory_limit=-1` fijado en el `php.ini` de la imagen (resuelve la incidencia de "memoria insuficiente" al correr ~1509 métodos; ver Anexo B).
- Reproduce el entorno del CI (`tests-sqlite.yml`, `tests-mysql.yml`, `tests-postgres.yml`).

---

## 5. Estrategia aplicada

- **Alcance:** integración *Small* (interfaces entre subsistemas internos: ruta → controlador → modelo → BD → policy → transformer).
- **Estrategia:** **Bottom-Up** — las **factories** actúan como *drivers* de datos que disparan el flujo; los servicios externos (correo, notificaciones, eventos) se sustituyen con **mocks/fakes**.
- **Aislamiento:** `RefreshDatabase` (estado limpio por prueba).
- **Se evita Big Bang:** ejecución incremental por subsistema.

---

## 6. Resultados de ejecución

**Corrida completa `--testsuite=Feature` en el runner Docker (2026-07-04):**

| BD | Passed | Failed | Duración |
|----|-------:|-------:|---------:|
| SQLite `:memory:` | **1649** | 4 | ~32 min |
| MariaDB (`test-mysql`) | +3 recuperados de los 4 | — | — |

**Casos en formato del grupo (extracto — heredados ejecutados):**

| #CP | Archivo de Prueba | Endpoint interceptado | Entrada | Esperado | Real |
|-----|-------------------|-----------------------|---------|----------|------|
| CP-01 | `Checkouts/Ui/AssetCheckoutTest.php` | `POST hardware/{assetId}/checkout` | Activo disponible + usuario | Activo asignado; evento/log de checkout | ✅ PASS |
| CP-02 | `Checkins/Ui/AssetCheckinTest.php` | `POST hardware/{assetId}/checkin` | Activo asignado | `assigned_to` NULL; ubicación RTD | ✅ PASS |
| CP-03 | `Checkouts/Ui/LicenseCheckoutTest.php` | `POST licenses/{licenseId}/checkout` | Asiento libre + usuario | Disponibles −1 | ✅ PASS |
| CP-06 | `Checkouts/Ui/AccessoryCheckoutTest.php` | `POST accessories/{accessory}/checkout` | Accesorio + usuario + qty | Unidades −qty | ✅ PASS |
| CP-INT04 | `Checkouts/Api/AssetCheckoutTest.php` | `POST api/v1/hardware/{asset}/checkout` | Activo con asiento de licencia → usuario | Activo y asiento coherentes; evento de checkout | ✅ PASS (tras fix) |

**Casos nuevos del grupo — inyección de fallas de interfaz (2026-07-05, autor: Jeanpiero):**

| #CP | Archivo de Prueba | Endpoint interceptado | Entrada (falla inyectada) | Esperado | Real |
|-----|-------------------|-----------------------|---------------------------|----------|------|
| CP-FI-01a | `Integracion/AssetCheckoutInterfaceTest.php` | `POST hardware/{assetId}/checkout` | Sintáctica: `checkout_to_type=user` **sin** `assigned_user` | Error de validación; activo sin asignar | ✅ PASS |
| CP-FI-01b | `Integracion/AssetCheckoutInterfaceTest.php` | `POST hardware/{assetId}/checkout` | Sintáctica: `status_id` no numérico (petición manipulada) | Error de validación; estado del activo intacto | ✅ PASS |
| CP-FI-02 | `Integracion/AssetCheckoutInterfaceTest.php` | `POST hardware/{assetId}/checkout` | Semántica: `expected_checkin` **anterior** a `checkout_at` | Rechazo por validación de fecha; sin asignación | ⛔ FAIL 1.ª corrida → **INC-02** → ✅ PASS tras fix |
| CP-FI-02-API | `Integracion/AssetCheckoutInterfaceTest.php` | `POST api/v1/hardware/{asset}/checkout` | La misma falla semántica por la capa REST | `status=error`; sin asignación | ⛔ FAIL 1.ª corrida (respondía `success`) → ✅ PASS tras fix |

> La tabla completa de casos y su mapeo a endpoints está en el Plan §4.2. La evidencia de la corrida está en `HITO-3/Integracion/Evidencias/RESULTADO-CORRIDA-DOCKER-Feature.md`.

**Matriz heredado vs aporte del grupo (síntesis):**

| | Heredado (proyecto original) | Aporte del grupo (Hito 3) |
|--|------------------------------|---------------------------|
| Flujos | INT-01…INT-10 (292 archivos / 1509 métodos) | Ejecución + documentación + entorno Docker reproducible |
| Tests propios | — | 1 test de integración (`AssetCheckoutTest::test_license_seats_are_assigned_to_user_upon_checkout`), **corregido** · **FI-01/FI-02** (`Integracion/AssetCheckoutInterfaceTest.php`, 4 métodos / 18 aserciones) |
| Análisis | — | Clasificación de 4 fallos (3 dialecto + 1 defecto de prueba) · **1 defecto del sistema encontrado y corregido (INC-02)** |
| Pendiente | — | FI-03 + INT-11/12/13 (diseñados, por implementar) |

---

## 7. Defectos e incidencias

### Incidente INC-01 — Aserción de `action_log` incompatible con evento falseado (RESUELTO)

| Campo | Contenido |
|-------|-----------|
| **ID** | INC-01 |
| **Test** | `Checkouts/Api/AssetCheckoutTest::test_license_seats_are_assigned_to_user_upon_checkout` (autoría del grupo, commit `acb91d61`) |
| **Frontera (A→B)** | API de Activos → `Asset::checkOut()` + `LogListener` (bitácora) |
| **Entrada** | `POST api/v1/hardware/{asset}/checkout` con `checkout_to_type=user` |
| **Resultado ESPERADO (del test)** | Fila en `action_logs` con `action_type=checkout`, target=usuario, item=activo |
| **Resultado REAL** | La fila no existe: el `setUp()` de la clase hace `Event::fake([CheckoutableCheckedOut::class])`; el log lo escribe `LogListener` al reaccionar a ese evento, que al estar falseado no se dispara |
| **Naturaleza** | **Defecto de la prueba (del grupo)**, no del sistema |
| **Corrección** | Sustituir la aserción del `action_log` por `Event::assertDispatched(CheckoutableCheckedOut::class, …)` (patrón de la propia clase) |
| **Verificación** | ✅ SQLite (aislado) 1 passed · ✅ clase completa 17 passed · ✅ MariaDB 1 passed |
| **Veredicto** | **Resuelto** |

### Incidente INC-02 — El sistema aceptaba una fecha de devolución anterior a la entrega (RESUELTO)

| Campo | Contenido |
|-------|-----------|
| **ID** | INC-02 |
| **Detectado por** | CP-FI-02 — inyección de falla semántica (autoría del grupo, Sprint 3) |
| **Frontera (A→B)** | Capa de control (UI y API) → `AssetCheckoutRequest` (validación) → `Asset::checkOut()` |
| **Entrada** | `POST .../checkout` con `checkout_at=2026-07-10` y `expected_checkin=2026-07-01` (fechas sintácticamente válidas pero cronológicamente incoherentes) |
| **Resultado ESPERADO (del test)** | Rechazo por validación (`expected_checkin` ≥ `checkout_at`); activo sin asignar |
| **Resultado REAL (antes del fix)** | El checkout **se completaba**: la UI redirigía con éxito y la API respondía `status: success`; el activo quedaba asignado con devolución anterior a su entrega |
| **Causa raíz** | `AssetCheckoutRequest` validaba `expected_checkin` solo como `nullable\|date`; ninguna capa (FormRequest, controlador, modelo) comparaba ambas fechas |
| **Alcance** | Checkout web, checkout API (`api.asset.checkout` y por tag) y checkout masivo (todos usan el mismo FormRequest) |
| **Impacto** | Integridad de datos: los reportes "due/overdue for checkin" consumen `expected_checkin` incoherentes |
| **Naturaleza** | **Defecto del sistema** (hallazgo de la inyección de fallas) |
| **Corrección** | Regla condicional `after_or_equal:checkout_at` en `expected_checkin` (`app/Http/Requests/AssetCheckoutRequest.php`), aplicada solo cuando la petición trae `checkout_at` |
| **Verificación** | ✅ CP-FI-02 UI y API en verde tras el fix · ✅ regresión de **140 tests / 488 aserciones** sin fallos sobre las suites que usan el FormRequest |
| **Veredicto** | **Resuelto** |

### Observación OBS-01 — Diferencias de dialecto SQLite (NO defecto)

3 tests (`IndexAccessoryTest`, `IndexAssetModelsTest`, `ImportConsumablesTest`) fallan en SQLite por `SQLSTATE[HY000]: HAVING clause on a non-aggregate query`. **Pasan en MariaDB.** Es una limitación de dialecto de SQLite, no un defecto funcional. **Mitigación:** usar la variante `test-mysql` para la corrida oficial (riesgo RI-03 del Plan).

---

## 8. Métricas

| Métrica | Valor |
|---------|-------|
| Casos ejecutados (Feature) | 1653 efectivos |
| PASS (SQLite) | 1649 (99.76 %) |
| PASS (tras fix + MariaDB) | 1653 (100 % de dialecto/lógica) |
| Defectos de prueba encontrados | 1 (INC-01, resuelto) |
| **Defectos del sistema encontrados** | **1 (INC-02, detectado por CP-FI-02, resuelto)** |
| Tests nuevos del grupo ejecutados | 4 métodos / 18 aserciones (FI-01a/b, FI-02 UI/API) — 4/4 PASS tras fix |
| Regresión del fix INC-02 | 140 tests / 488 aserciones — 0 fallos |
| Observaciones de entorno | 1 (OBS-01, dialecto) |
| Aserciones totales (corrida) | 6090 |
| Duración corrida completa | ~32 min |

---

## 9. Evaluación de criterios de salida (del Plan §6)

| Criterio de salida | Estado |
|--------------------|--------|
| Flujos INT-01…INT-10 ejecutados y documentados | ✅ |
| Cero FAIL al cierre (o defectos registrados) | ✅ (INC-01 e INC-02 resueltos; 3 dialecto documentados) |
| Defectos registrados con Reporte de Incidente | ✅ (INC-01, INC-02) |
| Tests nuevos del grupo: FI-01/02/03 e INT-11/12/13 | 🟡 Parcial — **FI-01 y FI-02 ✅ ejecutados**; FI-03 e INT-11/12/13 pendientes |
| Resultados documentados en el Informe | ✅ (este documento) |

---

## 10. Conclusiones y recomendaciones

1. **La integración entre subsistemas de Snipe-IT es correcta:** 1649/1653 en SQLite y 100 % tras el fix + MariaDB.
2. **Las pruebas unitarias exitosas no bastan:** la corrida reveló un defecto de prueba (INC-01) que solo emerge al ejercitar la pila completa con eventos/listeners reales.
3. **El motor de BD importa (RI-03):** SQLite genera falsos negativos por dialecto; la variante **`test-mysql`** debe ser la **corrida oficial** (paridad con producción/COTS = integración *Large*).
4. **Entorno reproducible logrado:** el runner Docker garantiza mismas condiciones para todo el grupo y resuelve la incidencia de memoria.
5. **La inyección de fallas rinde frutos (v1.1):** los casos **FI-01/FI-02** validaron el manejo de peticiones erróneas o malintencionadas en la capa de control, y **FI-02 destapó un defecto real del sistema (INC-02)** — Snipe-IT aceptaba una devolución anterior a la entrega — corregido y verificado con regresión completa. Esto confirma que las suites heredadas, aun siendo amplias, no cubrían la coherencia semántica entre fechas.
6. **Recomendación / trabajo siguiente:** implementar y ejecutar **FI-03** (resiliencia/doble checkout) e **INT-11/12/13** (áreas débiles), ya diseñados en el Plan, para completar el aporte propio del grupo.

---

*(Referencias: Spillner, A., Software Testing Foundations, 5th Ed., 2021; Myers, G., The Art of Software Testing, 3rd Ed., 2012.)*

## Anexo B — Incidencia de memoria (resuelta)

El error "Allowed memory size exhausted" al correr toda la suite se debía al `memory_limit=128M` de PHP, **no** a SQLite ni al hardware. `artisan test` lanza PHPUnit en un **subproceso** que no hereda `php -d memory_limit=-1`; el fix es fijar `memory_limit=-1` en el `php.ini` (como el CI). El runner Docker ya lo trae.

## Anexo C — Evidencias
- `HITO-3/Integracion/Evidencias/RESULTADO-CORRIDA-DOCKER-Feature.md` — corrida completa + análisis de fallos + fix INC-01.
- `HITO-3/Integracion/Evidencias/INVENTARIO-INTEGRACION-tests-Feature.md` — inventario de la suite heredada.
- `HITO-3/Integracion/docker-compose.test.yml`, `Dockerfile.test`, `README-ENTORNO-DOCKER.md` — entorno reproducible.
- `HITO-3/Integracion/Evidencias/RESULTADO-FI01-FI02-InyeccionFallas.md` — resultados FI-01/FI-02, reporte INC-02 y fix (con logs y capturas en `Evidencias/FI-01-FI-02/`).

---

## Historial de cambios

| Versión | Fecha | Cambios |
|---------|-------|---------|
| 1.0 | 2026-07-04 | Informe inicial: ejecución de la suite de integración en Docker, clasificación de 4 fallos (3 dialecto + INC-01 resuelto), matriz heredado/aporte, métricas, cuestionario. |
| 1.1 | 2026-07-05 | Resultado Real de FI-01/FI-02 (inyección de fallas de interfaz, `AssetCheckoutInterfaceTest.php`); reporte de incidente **INC-02** (defecto del sistema: `expected_checkin` anterior a `checkout_at` aceptado) con fix verificado y regresión de 140 tests. Autor: Jeanpiero. |

*Fin del documento — Informe de Pruebas de Integración (Hito 3).*
