# Resultado de ejecución — FI-01 / FI-02 (Inyección de fallas de interfaz)

> Evidencia de los casos **CP-FI-01** y **CP-FI-02** del Plan de Pruebas de Integración §4.1/§4.2.
> Autor: **Jeanpiero** · Sprint 3 · Fecha de ejecución: **2026-07-05**.

| Campo | Detalle |
|-------|---------|
| **Frontera (A→B)** | `AssetCheckoutController` / `Api\AssetsController` → `AssetCheckoutRequest` (validación) → `Asset::checkOut()` |
| **Endpoints** | `POST hardware/{assetId}/checkout` (UI) · `POST api/v1/hardware/{asset}/checkout` (API) |
| **Archivo de prueba** | `tests/Feature/Integracion/AssetCheckoutInterfaceTest.php` (4 métodos, aporte propio) |
| **Técnica** | Caja blanca/gris — inyección de **una sola falla por caso** en un payload por lo demás válido; se verifica respuesta + evento de dominio (stub `Event::fake`) + estado persistido en BD |

---

## 1. Resultados (Resultado Esperado vs Resultado Real)

| #CP | Método de prueba | Falla inyectada | Resultado Esperado | Resultado Real |
|-----|------------------|-----------------|--------------------|----------------|
| CP-FI-01a | `test_fi_01_sintactica_checkout_sin_destino_es_rechazado` | Sintáctica: `checkout_to_type=user` sin `assigned_user` | Error de validación; activo sin asignar; sin evento ni bitácora | ✅ **PASS** — rechazo correcto, `assigned_to` y `last_checkout` quedan `NULL` |
| CP-FI-01b | `test_fi_01_sintactica_status_id_no_numerico_es_rechazado` | Sintáctica: `status_id='no-numerico-<script>'` (petición manipulada) | Error de validación en `status_id`; sin asignación; estado del activo intacto | ✅ **PASS** — rechazo correcto, `status_id` no cambia |
| CP-FI-02 (UI) | `test_fi_02_semantica_expected_checkin_anterior_a_checkout_es_rechazado` | Semántica: `expected_checkin=2026-07-01` anterior a `checkout_at=2026-07-10` | Rechazo por validación de fecha; sin asignación | ⛔ **FAIL en la 1.ª corrida** → defecto real del sistema (**INC-02**) → ✅ **PASS tras el fix** |
| CP-FI-02 (API) | `test_fi_02_semantica_api_rechaza_expected_checkin_anterior_a_checkout` | La misma falla semántica por la capa REST | Respuesta `status=error`; sin asignación | ⛔ **FAIL en la 1.ª corrida** (la API respondía `success`) → ✅ **PASS tras el fix** |

**Corrida final (local, SQLite):** `Tests: 4 passed (18 assertions)` — ver `02-log-DESPUES-del-fix-todo-verde.txt` y captura `solucionado.png`.

---

## 2. Reporte de Incidente INC-02 — El sistema aceptaba una devolución anterior a la entrega (RESUELTO)

| Campo | Contenido |
|-------|-----------|
| **ID** | INC-02 |
| **Detectado por** | CP-FI-02 (inyección de falla semántica) — corrida del 2026-07-05 |
| **Frontera (A→B)** | Capa de control (UI y API) → `AssetCheckoutRequest` → `Asset::checkOut()` |
| **Entrada** | `POST .../checkout` con `checkout_at=2026-07-10` y `expected_checkin=2026-07-01` (fechas válidas pero cronológicamente incoherentes) |
| **Resultado ESPERADO** | Rechazo por validación (`expected_checkin` debe ser ≥ `checkout_at`); activo sin asignar |
| **Resultado REAL (antes del fix)** | El checkout **se completaba**: la UI redirigía con éxito y la API respondía `status: success`; el activo quedaba asignado con una fecha de devolución anterior a su entrega |
| **Causa raíz** | `AssetCheckoutRequest` validaba `expected_checkin` solo como `nullable\|date`; ni el controlador ni el modelo comparaban ambas fechas |
| **Alcance** | Las 3 capas de control que usan ese FormRequest: checkout web, checkout API (`api.asset.checkout` y por tag) y checkout masivo |
| **Impacto** | Integridad de datos: los reportes "due/overdue for checkin" (que consultan `expected_checkin`) reciben fechas incoherentes |
| **Naturaleza** | **Defecto del sistema** (no de la prueba) |
| **Corrección** | Regla condicional `after_or_equal:checkout_at` en `expected_checkin` (`app/Http/Requests/AssetCheckoutRequest.php`) — solo se aplica cuando la petición trae `checkout_at`; si no viene, el controlador usa la fecha actual. Ver `04-diff-fix-INC-02-AssetCheckoutRequest.txt` |
| **Verificación** | ✅ CP-FI-02 UI y API en verde tras el fix · ✅ **Regresión sin fallos: 140 tests / 488 aserciones** de todas las suites que usan el FormRequest (`tests/Feature/Checkouts` + notificaciones de checkout masivo) — ver `03-log-regresion-suites-checkout.txt` |
| **Veredicto** | **Resuelto** |

---

## 3. Evidencias adjuntas (carpeta `FI-01-FI-02/`)

| Archivo | Contenido |
|---------|-----------|
| `01-log-ANTES-del-fix-FI02-falla.txt` | Corrida inicial: FI-01a/b PASS, FI-02 UI/API FAIL → evidencia del defecto INC-02 |
| `02-log-DESPUES-del-fix-todo-verde.txt` | Corrida tras el fix: 4/4 PASS (18 aserciones) |
| `03-log-regresion-suites-checkout.txt` | Regresión: 140 passed / 488 assertions (el fix no rompe nada heredado) |
| `04-diff-fix-INC-02-AssetCheckoutRequest.txt` | Diff exacto del fix (git diff) |
| `error.png` | Captura: corrida ANTES del fix (FI-02 en rojo — incidente INC-02) |
| `solucionado.png` | Captura: corrida DESPUÉS del fix (4/4 en verde) |
| `fi_01.png` | Captura: ejecución de los casos FI-01 |
| `fi_02.png` | Captura: ejecución de los casos FI-02 |
| `05-log-corrida-oficial-mysql.txt` | Corrida oficial en runner Docker `test-mysql` (MariaDB) |

---

## 4. Cómo reproducir

```powershell
# Local rápido (SQLite) — desde la raíz de snipe-it
php artisan test tests/Feature/Integracion/AssetCheckoutInterfaceTest.php
# o por caso:
php artisan test --filter test_fi_01_sintactica
php artisan test --filter test_fi_02_semantica

# Corrida oficial (MariaDB) — Docker Desktop abierto, desde la raíz de snipe-it
docker compose -f trabajoLibelula/HITO-3/Integracion/docker-compose.test.yml run --rm test-mysql bash -lc "php artisan test tests/Feature/Integracion/AssetCheckoutInterfaceTest.php"
docker compose -f trabajoLibelula/HITO-3/Integracion/docker-compose.test.yml down
```

> Nota de entorno local (Windows): los tests de API requieren las claves OAuth de Passport
> (`php artisan passport:keys`); sin ellas fallan con `LogicException: Invalid key supplied`.
> El runner Docker no las necesita para esta suite si ya existen en el volumen montado.

*Fin del documento — Resultado FI-01/FI-02 · Inyección de fallas de interfaz.*
