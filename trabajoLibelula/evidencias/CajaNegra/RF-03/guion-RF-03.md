# Guion de ejecución manual — RF-03 (Devolver un activo asignado · checkin)

> Checklist para la sesión de pruebas funcionales de caja negra del **RF-03** (casos CPF-04 y subcasos .1–.3).
> Diseño asociado: `Diseno-de-Casos-de-Pruebas-Funcionales.md` · Resultados a volcar en `Informe-de-Casos-de-Pruebas-Funcionales.md` (sección RF-03).

| Campo | Detalle |
|-------|---------|
| Requisito | RF-03 — Devolver un activo asignado (checkin) |
| Casos | CPF-04 (+ .1 .2 .3) |
| Ambiente | Docker local — http://localhost:8000 |
| Ejecutor | Usuario admin/superusuario creado en el setup |
| Evidencia | Capturas en esta carpeta `trabajoLibelula/evidencias/CajaNegra/RF-03/` |
| Fecha de ejecución | __________ |
| Responsable | __________ |

---

## A) Acceso

| Campo | Valor |
|---|---|
| URL | http://localhost:8000 |
| Usuario admin (ejecutor) | __________ |
| Contraseña | __________ |

> El admin es superusuario: cubre CPF-04, .1 y .3. Solo **CPF-04.2** usa el usuario limitado `alimitada` creado en RF-02.

---

## B) Precondiciones y datos (respetar el orden)

> ✅ **Precondiciones ya sembradas en la BD** (sesión del 2026-06-24): `QA-D-004` (asignado a `jperez`), `QA-E-005` (sin asignar), `QA-F-006` (asignado a `jperez` + 1 asiento de la licencia `Office QA`), licencia `Office QA` (10 asientos) y la depreciación auxiliar. Si la BD se recrea (`docker compose down -v`), vuelve a sembrarlas con el script `seed_rf03_rf04.php` o créalas a mano con los pasos de abajo.

> Si ya ejecutaste RF-02, reutilizas los mismos catálogos (Status `Ready to Deploy`, Manufacturer `Dell`, Category `Laptops`, Model `Latitude 5540`, Location `Oficina Lima`, usuario `jperez`, usuario limitado `alimitada`). Solo necesitas dejar activos **asignados** para poder devolverlos.

### 1. Activo ASIGNADO (para CPF-04) — `/hardware/create` y luego checkout  ☐
| Asset Tag | Model | Status | Estado requerido antes del checkin |
|---|---|---|---|
| `QA-D-004` | `Latitude 5540` | `Ready to Deploy` | **Checkout a `jperez`** (Assets → QA-D-004 → Checkout → User → jperez) |

> Puedes reutilizar `QA-B-002` de RF-02 si sigue asignado a `jperez`. Para no romper la trazabilidad de RF-02, se recomienda crear `QA-D-004`.

### 2. Activo NO asignado (para CPF-04.1) — `/hardware/create`  ☐
| Asset Tag | Model | Status | Estado requerido |
|---|---|---|---|
| `QA-E-005` | `Latitude 5540` | `Ready to Deploy` | **sin asignar** (recién creado, nunca se le hizo checkout) |

### 3. Usuario SIN permiso de checkin (para CPF-04.2)  ☐
- Reutilizar `alimitada` / `Password123!` de RF-02 (sin Superuser, sin Admin; *Assets → Checkin* en **Deny** o sin conceder).
- Necesita existir un activo asignado para intentar devolverlo: usa `QA-D-004`.

### 4. Activo con ASIENTO de licencia (para CPF-04.3) — depende de RF-04  ☐
| Paso | Acción |
|---|---|
| a | Crea una licencia con asientos (ver RF-04: `Office QA`, Category tipo *License*, Seats `10`). |
| b | Crea/usa el activo `QA-F-006` (`Latitude 5540`, `Ready to Deploy`) y hazle **checkout a `jperez`**. |
| c | Ve a la licencia `Office QA` → pestaña **Seats** → en una fila libre, **Checkout** el asiento **al activo `QA-F-006`** (Checkout to = Asset → `QA-F-006`). |
| d | Verifica que la licencia muestra 1 asiento usado y `QA-F-006` aparece en la pestaña *Licenses* del activo. |

> Tras el checkin de `QA-F-006` en CPF-04.3, ese asiento de licencia debe **liberarse** automáticamente.

---

## C) Ejecución paso a paso

Flujo de checkin: **Assets → abrir el activo asignado → botón “Checkin” → (opcional) elegir ubicación/nota/fecha → Checkin.**
Verificación UI: la ficha pasa de “Checked out to …” a estado **Ready to Deploy / Deployable** sin asignación; la pestaña **History** registra una fila *checkin*.

### CPF-04 — Checkin de activo asignado → queda disponible
- **Activo:** `QA-D-004` (asignado a `jperez`)
- **Acción:** Assets → `QA-D-004` → **Checkin** → Note `CPF-04` → **Checkin**
- **Esperado:** ya no figura “Checked out to”; `assigned_to` vacío; **`location_id` vuelve a la RTD por defecto** del activo; History muestra fila *checkin*; queda disponible para nuevo checkout
- **Veredicto:** ☐ Conforme ☐ No conforme ☐ Bloqueado — Evidencia: `CPF-04.png`

### CPF-04.1 — Checkin de activo NO asignado → rechazo
- **Activo:** `QA-E-005` (nunca asignado)
- **Acción:** abrir su ficha; el botón **Checkin no debe estar disponible** (muestra “Checkout”). Si se fuerza la URL `/hardware/{id}/checkin`, debe redirigir con error y **sin cambios**.
- **Esperado:** no se permite el checkin de un activo que no está asignado
- **Veredicto:** ☐ Conforme ☐ No conforme ☐ Bloqueado — Evidencia: `CPF-04-1.png`

### CPF-04.2 — Sin permiso de checkin → 403
- **Acción:** cerrar sesión, entrar con `alimitada` / `Password123!`, intentar el checkin de `QA-D-004` (o abrir directo `/hardware/{id}/checkin`)
- **Esperado:** **403** / el botón Checkin no está disponible; sin cambios de asignación
- **Veredicto:** ☐ Conforme ☐ No conforme ☐ Bloqueado — Evidencia: `CPF-04-2.png`

### CPF-04.3 — Checkin libera la asignación de usuario del asiento de licencia
- **Precondición:** `QA-F-006` asignado a `jperez`, y su asiento de `Office QA` (id=1) con **`asset_id`=QA-F-006 Y `assigned_to`=jperez** (estado combinado que valida el test de referencia).
- **Acción:** Assets → `QA-F-006` → **Checkin** → Note `CPF-04.3` → **Checkin**
- **Esperado (comportamiento REAL verificado en código):** al hacer checkin del activo, el asiento ligado a ese activo **pierde su asignación de usuario** (`assigned_to` → NULL). El vínculo software↔activo (`asset_id`) **se mantiene**, por lo que el conteo **Avail NO sube** (sigue en 9). Es decir, “liberar” aquí = se quita el usuario del asiento, no que el asiento vuelva al pool.
- **Cómo observarlo:** antes del checkin, en `Office QA` → pestaña **Seats**, el asiento aparece asignado a **Juan Perez**; tras el checkin del activo, ese asiento **ya no muestra usuario** (Avail permanece en 9). Verificación fiable por BD (ver D): `license_seats.assigned_to` pasa de `4` a `NULL`.
- **Veredicto:** ☐ Conforme ☐ No conforme ☐ Bloqueado — Evidencia: `CPF-04-3.png`
- **Nota de diseño:** mi expectativa inicial de “+1 disponible” era incorrecta. Snipe-IT NO devuelve al pool un asiento dado *a un activo* cuando se hace checkin del activo (solo limpia `assigned_to`); coincide con `AssetCheckinController` líneas 162-164 y con el test `test_assets_license_seats_are_cleared_upon_checkin` (que solo afirma `assigned_to` = null). Para devolver el asiento al pool hay que hacer **Checkin del asiento** desde la pestaña *Seats* de la licencia.

---

## D) Verificación técnica (opcional, además de la captura)

```powershell
docker compose exec db mariadb -u snipeit -pchangeme1234 snipeit -e "SELECT asset_tag, assigned_to, assigned_type, location_id, rtd_location_id, last_checkin FROM assets WHERE asset_tag IN ('QA-D-004','QA-E-005','QA-F-006');"
```

```powershell
docker compose exec db mariadb -u snipeit -pchangeme1234 snipeit -e "SELECT action_type, item_id, target_id, target_type, created_at FROM action_logs WHERE action_type='checkin from' ORDER BY id DESC LIMIT 10;"
```

```powershell
# CPF-04.3: ANTES del checkin el asiento tiene assigned_to=4 y asset_id=6.
# DESPUES del checkin del activo: assigned_to -> NULL, asset_id sigue = 6 (Avail no cambia).
docker compose exec db mariadb -u snipeit -pchangeme1234 snipeit -e "SELECT id, license_id, assigned_to, asset_id FROM license_seats WHERE asset_id=6;"
```

- Checkin OK → `assigned_to` NULL, `last_checkin` con fecha, `location_id = rtd_location_id`, fila `checkin from` en `action_logs`.
- CPF-04.1/.2 → `assigned_to` sin cambios.
- CPF-04.3 → el `license_seat` pasa de `assigned_to=4` a `assigned_to=NULL`; **`asset_id` permanece = 6** (el conteo Avail de la licencia NO sube). Conforme = se limpió el usuario del asiento.

---

## E) Resumen de veredictos

| Caso | Veredicto | Evidencia | Defecto (Issue) |
|------|-----------|-----------|-----------------|
| CPF-04   | ☐ C ☐ NC ☐ B | `CPF-04.png`   | |
| CPF-04.1 | ☐ C ☐ NC ☐ B | `CPF-04-1.png` | |
| CPF-04.2 | ☐ C ☐ NC ☐ B | `CPF-04-2.png` | |
| CPF-04.3 | ☐ C ☐ NC ☐ B | `CPF-04-3.png` | |

C = Conforme · NC = No conforme · B = Bloqueado

---

*Fin del guion RF-03. Volcar los veredictos en `Informe-de-Casos-de-Pruebas-Funcionales.md` (sección RF-03).*
