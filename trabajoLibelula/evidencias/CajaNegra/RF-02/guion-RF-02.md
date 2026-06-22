# Guion de ejecución manual — RF-02 (Asignar un activo a un destino · checkout)

> Checklist para la sesión de pruebas funcionales de caja negra del **RF-02** (casos CPF-03 y subcasos .1–.7).
> Diseño asociado: `Diseno-de-Casos-de-Pruebas-Funcionales.md` · Resultados a volcar en `Informe-de-Casos-de-Pruebas-Funcionales.md` (sección RF-02).

| Campo | Detalle |
|-------|---------|
| Requisito | RF-02 — Asignar un activo a un destino (checkout) |
| Casos | CPF-03 (+ .1 … .7) |
| Ambiente | Docker local — http://localhost:8000 |
| Ejecutor | Usuario admin/superusuario creado en el setup |
| Evidencia | Capturas en esta carpeta `trabajoLibelula/evidencias/CajaNegra/RF-02/` |
| Fecha de ejecución | __________ |
| Responsable | __________ |

---

## A) Acceso

| Campo | Valor |
|---|---|
| URL | http://localhost:8000 |
| Usuario admin (ejecutor) | __________ |
| Contraseña | __________ |

> El admin es superusuario: sirve como ejecutor con permiso para CPF-03, .1, .2, .3, .4, .5 y .7. Solo CPF-03.6 usa el usuario limitado.

---

## B) Datos a crear (valores reales) — respetar el orden por dependencias

> Cómo distinguir formularios: **Manufacturer** tiene *Warranty Lookup URL / Support Phone/Email*; **Category** tiene el desplegable *Category Type*; **Model** pide *Manufacturer + Category*.

### 1. Status Label — `/statuslabels/create`  ☐
| Campo | Valor |
|---|---|
| Name | `Ready to Deploy` |
| Type | **Deployable** |

*(Suele venir creado por defecto; si existe, no duplicar.)*

### 2. Manufacturer — `/manufacturers/create`  ☐
| Campo | Valor |
|---|---|
| Name | `Dell` |
| URL / Support URL / Warranty / Phone / Email | dejar vacíos (son placeholders) |

### 3. Category — `/categories/create`  ☐
| Campo | Valor |
|---|---|
| Name | `Laptops` |
| Category Type | **Asset** |

### 4. Model — `/models/create`  ☐
| Campo | Valor |
|---|---|
| Model Name | `Latitude 5540` |
| Manufacturer | `Dell` |
| Category | `Laptops` |

### 5. Location — `/locations/create`  ☐
| Campo | Valor |
|---|---|
| Name | `Oficina Lima` |

### 6. Usuario destino — `/users/create`  ☐
| Campo | Valor |
|---|---|
| First Name | `Juan` |
| Last Name | `Perez` |
| Username | `jperez` |
| Email | `juan.perez@qa.com` |
| Password / Confirm | `Password123!` |
| Activated | Sí |

### 7. Usuario SIN permiso de checkout — `/users/create`  ☐
| Campo | Valor |
|---|---|
| First Name | `Ana` |
| Last Name | `Limitada` |
| Username | `alimitada` |
| Email | `ana.limitada@qa.com` |
| Password / Confirm | `Password123!` |
| Permissions | **NO** marcar Superuser ni Admin. *Assets → Checkout* en **Deny** o sin conceder |

### 8. Activos — `/hardware/create`  ☐ ☐ ☐
| Asset Tag | Model | Status | Asignación inicial |
|---|---|---|---|
| `QA-A-001` | `Latitude 5540` | `Ready to Deploy` | sin asignar |
| `QA-B-002` | `Latitude 5540` | `Ready to Deploy` | luego checkout a `jperez` |
| `QA-C-003` | `Latitude 5540` | `Ready to Deploy` | sin asignar (destino) |

### 9. Solo para CPF-03.7 (FMCS) — dejar al final  ☐
- Settings → General → **Full Multiple Companies Support = ON** (guardar).
- Settings → Companies → Create: `Empresa Alfa` y `Empresa Beta`.
- Editar `QA-A-001` → **Company = `Empresa Alfa`**.
- Crear usuario destino en otra empresa: First `Carlos`, Last `Beta`, Username `cbeta`, Password `Password123!`, **Company = `Empresa Beta`**.

---

## C) Ejecución paso a paso

Flujo: **Assets → abrir el activo → botón “Checkout” → llenar formulario → Checkout.**
Verificación UI: la ficha muestra “Checked out to” y la pestaña **History** registra una fila *checkout*.

### CPF-03 — Checkout de activo disponible a un usuario
- **Activo:** `QA-A-001`
- **Formulario:** Checkout to = **User** → `jperez`; Status `Ready to Deploy`; Checkout Date = hoy; Expected Checkin = +30 días; Note `CPF-03`
- **Esperado:** asignado a Juan Perez; ubicación = la de Juan; History muestra *checkout*; `last_checkout` con fecha
- **Veredicto:** ☐ Conforme ☐ No conforme ☐ Bloqueado — Evidencia: `CPF-03.png`

### CPF-03.1 — Checkout a otro activo
- **Activo:** `QA-C-003`
- **Formulario:** Checkout to = **Asset** → `QA-A-001`; Note `CPF-03.1`
- **Esperado:** `QA-C-003` asignado a `QA-A-001`; hereda ubicación
- **Veredicto:** ☐ Conforme ☐ No conforme ☐ Bloqueado — Evidencia: `CPF-03-1.png`

### CPF-03.2 — Checkout a ubicación
- **Activo:** `QA-A-001` (debe estar libre; hacer Checkin si quedó asignado en CPF-03)
- **Formulario:** Checkout to = **Location** → `Oficina Lima`; Note `CPF-03.2`
- **Esperado:** asignado a la ubicación `Oficina Lima`
- **Veredicto:** ☐ Conforme ☐ No conforme ☐ Bloqueado — Evidencia: `CPF-03-2.png`

### CPF-03.3 — Activo ya asignado (no disponible)
- **Activo:** `QA-B-002` (previamente asignado a `jperez`)
- **Acción:** abrir su ficha e intentar checkout
- **Esperado:** no se ofrece “Checkout” (muestra “Checkin”) o se rechaza; sin cambio de asignación
- **Veredicto:** ☐ Conforme ☐ No conforme ☐ Bloqueado — Evidencia: `CPF-03-3.png`

### CPF-03.4 — Activo asignado a sí mismo
- **Activo:** `QA-A-001`
- **Formulario:** Checkout to = **Asset** → seleccionar **el propio `QA-A-001`**
- **Esperado:** error (no puede asignarse a sí mismo)
- **Veredicto:** ☐ Conforme ☐ No conforme ☐ Bloqueado — Evidencia: `CPF-03-4.png`

### CPF-03.5 — Datos obligatorios ausentes
- **Activo:** `QA-C-003`
- **Acción:** pulsar Checkout sin elegir destino, dejar Status vacío / fecha inválida
- **Esperado:** errores de validación (destino, status, fecha)
- **Veredicto:** ☐ Conforme ☐ No conforme ☐ Bloqueado — Evidencia: `CPF-03-5.png`

### CPF-03.6 — Sin permiso de checkout
- **Acción:** cerrar sesión, entrar con `alimitada` / `Password123!`, intentar checkout de cualquier activo
- **Esperado:** 403 / el botón Checkout no está disponible
- **Veredicto:** ☐ Conforme ☐ No conforme ☐ Bloqueado — Evidencia: `CPF-03-6.png`

### CPF-03.7 — Checkout cruzado entre empresas (FMCS)
- **Precondición:** FMCS ON; `QA-A-001` en `Empresa Alfa`; `cbeta` en `Empresa Beta`
- **Activo:** `QA-A-001`
- **Formulario:** Checkout to = **User** → `cbeta`
- **Esperado:** rechazo; no se ejecuta la asignación
- **Veredicto:** ☐ Conforme ☐ No conforme ☐ Bloqueado — Evidencia: `CPF-03-7.png`

---

## D) Verificación técnica (opcional, además de la captura)

```powershell
docker compose exec db mariadb -u snipeit -pchangeme1234 snipeit -e "SELECT asset_tag, assigned_to, assigned_type, location_id, last_checkout FROM assets WHERE asset_tag IN ('QA-A-001','QA-B-002','QA-C-003');"
```

```powershell
docker compose exec db mariadb -u snipeit -pchangeme1234 snipeit -e "SELECT action_type, item_id, target_id, target_type, created_at FROM action_logs ORDER BY id DESC LIMIT 10;"
```

- Checkout OK → `assigned_to` lleno, `last_checkout` con fecha, fila `checkout` en `action_logs`.
- Rechazo (CPF-03.3/.4/.5/.6/.7) → `assigned_to` sin cambios.

---

## E) Resumen de veredictos

| Caso | Veredicto | Evidencia | Defecto (Issue) |
|------|-----------|-----------|-----------------|
| CPF-03   | ☐ C ☐ NC ☐ B | `CPF-03.png`   | |
| CPF-03.1 | ☐ C ☐ NC ☐ B | `CPF-03-1.png` | |
| CPF-03.2 | ☐ C ☐ NC ☐ B | `CPF-03-2.png` | |
| CPF-03.3 | ☐ C ☐ NC ☐ B | `CPF-03-3.png` | |
| CPF-03.4 | ☐ C ☐ NC ☐ B | `CPF-03-4.png` | |
| CPF-03.5 | ☐ C ☐ NC ☐ B | `CPF-03-5.png` | |
| CPF-03.6 | ☐ C ☐ NC ☐ B | `CPF-03-6.png` | |
| CPF-03.7 | ☐ C ☐ NC ☐ B | `CPF-03-7.png` | |

C = Conforme · NC = No conforme · B = Bloqueado

---

*Fin del guion RF-02. Volcar los veredictos en `Informe-de-Casos-de-Pruebas-Funcionales.md` (sección RF-02).*
