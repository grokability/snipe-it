# Guion de ejecución manual — RF-11 (Asignar y devolver un accesorio · checkout / checkin)

> Checklist para la sesión de pruebas funcionales de caja negra del **RF-11** (casos CPF-14, .1–.5 y CPF-15, .1).
> Diseño asociado: `Diseno-de-Casos-de-Pruebas-Funcionales.md` · Resultados a volcar en `Informe-de-Casos-de-Pruebas-Funcionales.md` (sección RF-11).

| Campo | Detalle |
|-------|---------|
| Requisito | RF-11 — Asignar y devolver un accesorio (checkout / checkin) |
| Casos | CPF-14 (+ .1 .2 .3 .4 .5) · CPF-15 (+ .1) |
| Ambiente | Docker local — http://localhost:8000 |
| Ejecutor | Usuario admin/superusuario creado en el setup |
| Evidencia | Capturas en esta carpeta `trabajoLibelula/evidencias/CajaNegra/RF-11/` |
| Fecha de ejecución | __________ |
| Responsable | __________ |

---

## A) Acceso

| Campo | Valor |
|---|---|
| URL | http://localhost:8000 |
| Usuario admin (ejecutor) | __________ |
| Contraseña | __________ |

> El admin (superusuario) cubre CPF-14, .1, .2, .3, .4 y CPF-15. Solo **CPF-14.5** y **CPF-15.1** usan el usuario limitado `alimitada`.

---

## B) Precondiciones y datos

> ✅ **Ya sembrado en BD** (sesión 2026-06-24):

| Recurso | Valor | Uso |
|---|---|---|
| Categoría | `Accesorios` (tipo accessory) | requisito de los accesorios |
| Accesorio con stock | `Mouse Logitech` — qty **5**, **1 ya entregado a jperez**, **4 disponibles** | CPF-14/.1/.2/.4/.5 y CPF-15 |
| Accesorio agotado | `Teclado QA` — qty **1**, **1 entregado**, **0 disponibles** | CPF-14.3 |
| Usuario destino | `jperez` (Juan Perez) | CPF-14 |
| Ubicación destino | `Oficina Lima` | CPF-14.1 |
| Activo destino | `QA-A-001` | CPF-14.2 |
| Usuario sin permiso | `alimitada` / `Password123!` (sin Superuser/Admin) | CPF-14.5, CPF-15.1 |

> Lista de accesorios: `http://localhost:8000/accessories`.
> Si la BD se recrea, vuelve a sembrar con `seed_rf05_rf11.php` o crea a mano: Category `Accesorios` (tipo Accessory) → Accessory `Mouse Logitech` qty 5 → Accessory `Teclado QA` qty 1 (y entrega su única unidad para dejarlo en 0).

---

## C) Ejecución paso a paso

Flujo checkout: **Accessories → abrir el accesorio → botón `Checkout` → elegir destino + cantidad + nota → Checkout.**
Flujo checkin: **Accessories → abrir el accesorio → lista de “Checked Out” → botón `Checkin` en la fila del destino.**
Verificación UI: el contador **Remaining/Disponibles** del accesorio baja en la cantidad entregada y sube al hacer checkin; **History** registra `checkout`/`checkin`.

### CPF-14 — Checkout de accesorio a un usuario (unidades −cantidad)
- **Accesorio:** `Mouse Logitech` (4 disponibles)
- **Formulario:** Checkout to = **User** → `jperez`; Qty `1`; Note `CPF-14`
- **Esperado:** entrega registrada; **disponibles 4 → 3**; History muestra `checkout`
- **Veredicto:** ☐ Conforme ☐ No conforme ☐ Bloqueado — Evidencia: `CPF-14.png`

### CPF-14.1 — Checkout a una ubicación
- **Accesorio:** `Mouse Logitech`
- **Formulario:** Checkout to = **Location** → `Oficina Lima`; Qty `1`; Note `CPF-14.1`
- **Esperado:** entrega a la ubicación; **disponibles −1**
- **Veredicto:** ☐ Conforme ☐ No conforme ☐ Bloqueado — Evidencia: `CPF-14-1.png`

### CPF-14.2 — Checkout a un activo
- **Accesorio:** `Mouse Logitech`
- **Formulario:** Checkout to = **Asset** → `QA-A-001`; Qty `1`; Note `CPF-14.2`
- **Esperado:** entrega al activo; **disponibles −1**
- **Veredicto:** ☐ Conforme ☐ No conforme ☐ Bloqueado — Evidencia: `CPF-14-2.png`

### CPF-14.3 — Sin unidades disponibles → rechazo
- **Accesorio:** `Teclado QA` (**0 disponibles**)
- **Acción:** intentar Checkout de `Teclado QA`
- **Esperado:** **rechazo** (no hay unidades disponibles); el botón Checkout no procede o muestra error; sin nueva entrega
- **Veredicto:** ☐ Conforme ☐ No conforme ☐ Bloqueado — Evidencia: `CPF-14-3.png`

### CPF-14.4 — Destino ausente → error de validación
- **Accesorio:** `Mouse Logitech`
- **Acción:** pulsar Checkout **sin elegir destino** (dejar “Checkout to” vacío)
- **Esperado:** **error de validación** (destino obligatorio); no se entrega
- **Veredicto:** ☐ Conforme ☐ No conforme ☐ Bloqueado — Evidencia: `CPF-14-4.png`

### CPF-14.5 — Sin permiso de checkout → 403
- **Acción:** cerrar sesión, entrar con `alimitada` / `Password123!`. Con esa cuenta el listado **Accessories aparece vacío/oculto** (ya no tiene permiso). Para forzar el **403 explícito**, abrir directamente la URL del formulario de checkout: `http://localhost:8000/accessories/1/checkout` (Mouse Logitech = id 1).
- **Esperado:** página **403 / sin permiso** (o redirección con error); el botón Checkout no está disponible
- **Veredicto:** ☐ Conforme ☐ No conforme ☐ Bloqueado — Evidencia: `CPF-14-5.png`
- **Nota:** que `alimitada` no vea accesorios en el listado **ya es evidencia** de la falta de permiso; el 403 al entrar a la URL directa lo confirma.

### CPF-15 — Checkin de accesorio (devuelve la unidad)
- **Accesorio:** `Mouse Logitech` → fila de **Juan Perez** ya entregada (precondición)
- **Acción:** Accessories → `Mouse Logitech` → en la fila de `jperez`, botón **Checkin** → confirmar
- **Esperado:** la unidad se devuelve; **disponibles +1**; History muestra `checkin`
- **Veredicto:** ☐ Conforme ☐ No conforme ☐ Bloqueado — Evidencia: `CPF-15.png`

### CPF-15.1 — Checkin sin permiso → 403
- **Acción:** con `alimitada` / `Password123!`, intentar el checkin de una unidad de `Mouse Logitech`. URL directa: `http://localhost:8000/accessories/1/checkin` (Mouse Logitech = id 1).
- **Esperado:** **403** / sin permiso; el botón Checkin no está disponible
- **Veredicto:** ☐ Conforme ☐ No conforme ☐ Bloqueado — Evidencia: `CPF-15-1.png`

---

## D) Verificación técnica (opcional, además de la captura)

```powershell
# Unidades entregadas por accesorio (filas en accessories_checkout)
docker compose exec db mariadb -u snipeit -pchangeme1234 snipeit -e "SELECT a.name, a.qty, COUNT(ac.id) AS entregados, (a.qty - COUNT(ac.id)) AS disponibles FROM accessories a LEFT JOIN accessories_checkout ac ON ac.accessory_id=a.id WHERE a.deleted_at IS NULL GROUP BY a.id;"
```

```powershell
# Historial de accesorios
docker compose exec db mariadb -u snipeit -pchangeme1234 snipeit -e "SELECT action_type, item_id, target_id, target_type, note, created_at FROM action_logs WHERE item_type='App\\\\Models\\\\Accessory' ORDER BY id DESC LIMIT 10;"
```

- CPF-14/.1/.2 OK → nueva fila en `accessories_checkout`; `disponibles` baja.
- CPF-14.3/.4/.5 → **no** se crea fila; `disponibles` sin cambios.
- CPF-15 OK → desaparece la fila de jperez; `disponibles` sube; `checkin` en `action_logs`.

---

## E) Resumen de veredictos

| Caso | Destino / condición | Veredicto | Evidencia | Defecto (Issue) |
|------|---------------------|-----------|-----------|-----------------|
| CPF-14   | Mouse → usuario (disp. −1) | ☐ C ☐ NC ☐ B | `CPF-14.png`   | |
| CPF-14.1 | Mouse → ubicación          | ☐ C ☐ NC ☐ B | `CPF-14-1.png` | |
| CPF-14.2 | Mouse → activo             | ☐ C ☐ NC ☐ B | `CPF-14-2.png` | |
| CPF-14.3 | Teclado QA (0 disp.) → rechazo | ☐ C ☐ NC ☐ B | `CPF-14-3.png` | |
| CPF-14.4 | sin destino → validación   | ☐ C ☐ NC ☐ B | `CPF-14-4.png` | |
| CPF-14.5 | sin permiso → 403          | ☐ C ☐ NC ☐ B | `CPF-14-5.png` | |
| CPF-15   | checkin (disp. +1)         | ☐ C ☐ NC ☐ B | `CPF-15.png`   | |
| CPF-15.1 | checkin sin permiso → 403  | ☐ C ☐ NC ☐ B | `CPF-15-1.png` | |

C = Conforme · NC = No conforme · B = Bloqueado

---

*Fin del guion RF-11. Volcar los veredictos en `Informe-de-Casos-de-Pruebas-Funcionales.md` (sección RF-11).*
