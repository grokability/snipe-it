# Guion de ejecución manual — RF-04 (Crear una licencia con N asientos)

> Checklist para la sesión de pruebas funcionales de caja negra del **RF-04** (caso CPF-06 y subcasos .1–.5).
> Diseño asociado: `Diseno-de-Casos-de-Pruebas-Funcionales.md` · Resultados a volcar en `Informe-de-Casos-de-Pruebas-Funcionales.md` (sección RF-04).

| Campo | Detalle |
|-------|---------|
| Requisito | RF-04 — Crear una licencia con N asientos |
| Casos | CPF-06 (+ .1 .2 .3 .4 .5) |
| Ambiente | Docker local — http://localhost:8000 |
| Ejecutor | Usuario admin/superusuario creado en el setup |
| Evidencia | Capturas en esta carpeta `trabajoLibelula/evidencias/CajaNegra/RF-04/` |
| Fecha de ejecución | __________ |
| Responsable | __________ |

---

## A) Acceso

| Campo | Valor |
|---|---|
| URL | http://localhost:8000 |
| Usuario admin (ejecutor) | __________ |
| Contraseña | __________ |

---

## B) Datos previos (respetar el orden)

> Una licencia exige una **Category de tipo License**. La de RF-02 (`Laptops`) es de tipo *Asset* y **no** sirve aquí.

### 1. Category tipo License — `/categories/create`  ☐
| Campo | Valor |
|---|---|
| Name | `Software` |
| Category Type | **License** |

> ✅ **Ya existe** una categoría de tipo *License* llamada **"Misc Software"** (creada por el seed inicial). Puedes seleccionarla directamente en el formulario de licencia y **omitir** este paso.

### 2. (Solo para CPF-06.4) Depreciation — `/depreciations/create`  ☐
| Campo | Valor |
|---|---|
| Name | `Dep 36 meses` |
| Number of months | `36` |

> Necesaria porque `purchase_date` solo es obligatoria **cuando se selecciona una Depreciation** (regla `purchase_date: nullable|required_with:depreciation_id`). Sin depreciación, la fecha de compra es opcional y CPF-06.4 no fallaría.

---

## C) Ejecución paso a paso

Flujo: **Licenses (menú izquierdo) → “Create New” → llenar formulario → Save.**
Ruta directa del formulario: `http://localhost:8000/licenses/create`.
Campos mínimos del formulario: **Name** (obligatorio), **Category** (obligatorio, debe ser tipo *License* → elegir `Software`), **Seats** (obligatorio).
Verificación UI: tras *Save* redirige a la ficha de la licencia y la pestaña **Seats** muestra exactamente N filas.

### CPF-06 — Licencia con 1 asiento
- **Formulario:** Name `Lic QA 1`; Category `Software`; **Seats `1`**
- **Esperado:** licencia creada; pestaña **Seats** = 1 fila; `add seats` en el historial
- **Veredicto:** ☐ Conforme ☐ No conforme ☐ Bloqueado — Evidencia: `CPF-06.png`

### CPF-06.1 — Licencia con 10 asientos
- **Formulario:** Name `Lic QA 10`; Category `Software`; **Seats `10`**
- **Esperado:** licencia creada; **10** asientos
- **Veredicto:** ☐ Conforme ☐ No conforme ☐ Bloqueado — Evidencia: `CPF-06-1.png`

### CPF-06.2 — Licencia con 10000 asientos (tope superior)
- **Formulario:** Name `Lic QA 10000`; Category `Software`; **Seats `10000`**
- **Esperado:** licencia creada; **10000** asientos (la creación de tantos asientos puede tardar unos segundos)
- **Veredicto:** ☐ Conforme ☐ No conforme ☐ Bloqueado — Evidencia: `CPF-06-2.png`

### CPF-06.3 — Seats = 100000 → rechazo
- **Formulario:** Name `Lic QA 100000`; Category `Software`; **Seats `100000`**
- **Esperado:** **error de validación** en *Seats* (regla `limit_change:10000` ⇒ máx. 10000 para una licencia nueva); **no se crea**
- **Veredicto:** ☐ Conforme ☐ No conforme ☐ Bloqueado — Evidencia: `CPF-06-3.png`

### CPF-06.4 — Depreciation seleccionada y Purchase Date vacío → rechazo
- **Formulario:** Name `Lic QA Dep`; Category `Software`; **Seats `10`**; **Depreciation `Dep 36 meses`**; **Purchase Date = vacío**
- **Esperado:** **error de validación** en *Purchase Date* (`required_with:depreciation_id`); **no se crea**
- **Veredicto:** ☐ Conforme ☐ No conforme ☐ Bloqueado — Evidencia: `CPF-06-4.png`
- **Nota:** si dejas Depreciation en blanco, la fecha de compra es opcional y este caso **no aplica**; por eso se selecciona la depreciación.

### CPF-06.5 — Seats = 0 → rechazo
- **Formulario:** Name `Lic QA 0`; Category `Software`; **Seats `0`**
- **Esperado:** **error de validación** en *Seats* (regla `min:1`); **no se crea**
- **Veredicto:** ☐ Conforme ☐ No conforme ☐ Bloqueado — Evidencia: `CPF-06-5.png`

---

## D) Verificación técnica (opcional, además de la captura)

```powershell
# Licencias creadas y su nº de asientos real
docker compose exec db mariadb -u snipeit -pchangeme1234 snipeit -e "SELECT id, name, seats FROM licenses ORDER BY id DESC LIMIT 10;"
```

```powershell
# Conteo real de filas en license_seats por licencia (debe coincidir con 'seats')
docker compose exec db mariadb -u snipeit -pchangeme1234 snipeit -e "SELECT license_id, COUNT(*) AS asientos FROM license_seats GROUP BY license_id ORDER BY license_id DESC LIMIT 10;"
```

```powershell
# Historial: cada creación con asientos registra 'add seats'
docker compose exec db mariadb -u snipeit -pchangeme1234 snipeit -e "SELECT action_type, item_id, item_type, created_at FROM action_logs WHERE action_type='add seats' ORDER BY id DESC LIMIT 10;"
```

- Creación OK (CPF-06/.1/.2) → fila en `licenses` con `seats` = N y N filas en `license_seats`.
- Rechazo (CPF-06.3/.4/.5) → **no** aparece nueva fila en `licenses`.

---

## E) Resumen de veredictos

| Caso | Seats / condición | Veredicto | Evidencia | Defecto (Issue) |
|------|-------------------|-----------|-----------|-----------------|
| CPF-06   | seats=1       | ☐ C ☐ NC ☐ B | `CPF-06.png`   | |
| CPF-06.1 | seats=10      | ☐ C ☐ NC ☐ B | `CPF-06-1.png` | |
| CPF-06.2 | seats=10000   | ☐ C ☐ NC ☐ B | `CPF-06-2.png` | |
| CPF-06.3 | seats=100000  | ☐ C ☐ NC ☐ B | `CPF-06-3.png` | |
| CPF-06.4 | depreciación + fecha vacía | ☐ C ☐ NC ☐ B | `CPF-06-4.png` | |
| CPF-06.5 | seats=0       | ☐ C ☐ NC ☐ B | `CPF-06-5.png` | |

C = Conforme · NC = No conforme · B = Bloqueado

> Nota de diseño: `limit_change:10000` limita la **magnitud del cambio** de asientos (±10000). En una licencia **nueva** (parte de 0) equivale a un **tope superior de 10000**: por eso `10000` se acepta y `100000` se rechaza.

---

*Fin del guion RF-04. Volcar los veredictos en `Informe-de-Casos-de-Pruebas-Funcionales.md` (sección RF-04).*
