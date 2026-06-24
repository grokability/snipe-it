# Guion de ejecución manual — RF-05 (Asignar un asiento de licencia)

> Checklist para la sesión de pruebas funcionales de caja negra del **RF-05** (casos CPF-07, CPF-07.1, CPF-08 y CPF-08.1).
> Diseño asociado: `Diseno-de-Casos-de-Pruebas-Funcionales.md` · Resultados a volcar en `Informe-de-Casos-de-Pruebas-Funcionales.md` (sección RF-05).

| Campo | Detalle |
|-------|---------|
| Requisito | RF-05 — Asignar un asiento de licencia (checkout de seat) |
| Casos | CPF-07, CPF-07.1, CPF-08, CPF-08.1 |
| Ambiente | Docker local — http://localhost:8000 |
| Ejecutor | Usuario admin/superusuario creado en el setup |
| Evidencia | Capturas en esta carpeta `trabajoLibelula/evidencias/CajaNegra/RF-05/` |
| Fecha de ejecución | __________ |
| Responsable | __________ |

> ⚠️ **CPF-08 y CPF-08.1 NO tienen cobertura automatizada** en el repositorio: su validación es **exclusivamente manual**. No darlos por válidos sin evidencia (captura).

---

## A) Acceso

| Campo | Valor |
|---|---|
| URL | http://localhost:8000 |
| Usuario admin (ejecutor) | __________ |
| Contraseña | __________ |

---

## B) Precondiciones y datos

> ✅ **Ya sembrado en BD** (sesión 2026-06-24): licencia **`RF05 License`** (id 5) con **2 asientos libres**. Se eligió a propósito un número pequeño de asientos para poder **agotarlos** y probar CPF-08 en pocos pasos.

| Recurso | Valor | Uso |
|---|---|---|
| Licencia | `RF05 License` — `http://localhost:8000/licenses/5` | sujeto de la prueba |
| Usuario destino | `jperez` (Juan Perez) | CPF-07 |
| Activo destino | `QA-A-001` (libre) | CPF-07.1 |

> Si la BD se recrea (`docker compose down -v`), vuelve a sembrar con `seed_rf05_rf11.php` o crea la licencia a mano: Licenses → Create New → Name `RF05 License`, Category `Misc Software` (tipo License), Seats `2`.

---

## C) Ejecución paso a paso

Flujo: **Licenses → abrir `RF05 License` → pestaña `Seats` → en una fila de asiento libre, botón `Checkout` → elegir destino → Checkout.**
Ruta directa: `http://localhost:8000/licenses/5` → pestaña **Seats**.
Verificación UI: el contador **Avail** de la licencia baja en 1 por cada asiento asignado; la fila del asiento muestra el destino; **History** registra `checkout`.

### CPF-07 — Asiento asignado a un usuario (disponibles −1)
- **Asiento:** primera fila libre de `RF05 License`
- **Formulario:** Checkout to = **User** → `jperez`; Note `CPF-07`
- **Esperado:** asiento asignado a Juan Perez; **Avail pasa de 2 a 1**; History muestra `checkout`
- **Veredicto:** ☐ Conforme ☐ No conforme ☐ Bloqueado — Evidencia: `CPF-07.png`

### CPF-07.1 — Asiento asignado a un activo
- **Asiento:** segunda fila libre de `RF05 License`
- **Formulario:** Checkout to = **Asset** → `QA-A-001`; Note `CPF-07.1`
- **Esperado:** asiento asignado al activo `QA-A-001`; **Avail pasa de 1 a 0**
- **Veredicto:** ☐ Conforme ☐ No conforme ☐ Bloqueado — Evidencia: `CPF-07-1.png`

### CPF-08 — Agotar asientos → no se puede asignar más
- **Precondición:** tras CPF-07 y CPF-07.1, **Avail = 0** (los 2 asientos ocupados)
- **Acción:** intentar hacer **Checkout de otro asiento** en `RF05 License`
- **Esperado:** **no hay asiento libre** que ofrezca el botón Checkout (la licencia muestra 0 disponibles); el sistema **no permite** asignar más allá del total de asientos
- **Veredicto:** ☐ Conforme ☐ No conforme ☐ Bloqueado — Evidencia: `CPF-08.png`

### CPF-08.1 — Exceder el total de asientos → rechazo
- **Acción:** forzar un checkout adicional (p. ej. abrir directamente la URL de checkout de seat de esta licencia agotada `http://localhost:8000/licenses/5/checkout`)
- **Esperado:** rechazo / mensaje de error (no hay asientos disponibles); **no se crea** asignación; Avail sigue en 0
- **Veredicto:** ☐ Conforme ☐ No conforme ☐ Bloqueado — Evidencia: `CPF-08-1.png`

---

## D) Verificación técnica (opcional, además de la captura)

```powershell
# Asientos de RF05 License (id 5): assigned_to=usuario, asset_id=activo
docker compose exec db mariadb -u snipeit -pchangeme1234 snipeit -e "SELECT id, license_id, assigned_to, asset_id FROM license_seats WHERE license_id=5;"
```

```powershell
# Historial de asignación de asientos
docker compose exec db mariadb -u snipeit -pchangeme1234 snipeit -e "SELECT action_type, item_id, target_id, target_type, note, created_at FROM action_logs WHERE item_type='App\\\\Models\\\\License' ORDER BY id DESC LIMIT 10;"
```

- CPF-07 OK → un `license_seat` con `assigned_to` = id de jperez.
- CPF-07.1 OK → un `license_seat` con `asset_id` = id de QA-A-001.
- CPF-08/.1 → **no** aparecen más asientos ocupados (siguen 2 ocupados como máximo).

---

## E) Resumen de veredictos

| Caso | Destino / condición | Veredicto | Evidencia | Defecto (Issue) |
|------|---------------------|-----------|-----------|-----------------|
| CPF-07   | seat → usuario (Avail 2→1) | ☐ C ☐ NC ☐ B | `CPF-07.png`   | |
| CPF-07.1 | seat → activo (Avail 1→0)  | ☐ C ☐ NC ☐ B | `CPF-07-1.png` | |
| CPF-08   | agotar (Avail 0)           | ☐ C ☐ NC ☐ B | `CPF-08.png`   | |
| CPF-08.1 | exceder total              | ☐ C ☐ NC ☐ B | `CPF-08-1.png` | |

C = Conforme · NC = No conforme · B = Bloqueado

---

*Fin del guion RF-05. Volcar los veredictos en `Informe-de-Casos-de-Pruebas-Funcionales.md` (sección RF-05).*
