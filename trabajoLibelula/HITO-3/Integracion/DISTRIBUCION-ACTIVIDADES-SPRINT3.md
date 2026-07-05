# Distribución de actividades — Sprint 3 (Pruebas de Integración)

> Foco: presentar **solo la parte de Integración** en el Sprint 3. Estándar **ISO/IEC/IEEE 29119**. Fork del grupo: `jhuamaniCond/snipe-it`.
> Fecha de armado: 2026-07-04 · **Límite Hito 3: 08.JUL.2026** (sprint corto → tareas priorizadas).

---

## 1. Estado actual (línea base ya lograda)

| Entregable | Estado |
|-----------|--------|
| Plan de Pruebas de Integración (`Plan-de-Pruebas-de-Integracion.md`) | ✅ v1.2 (verificado ISO 29119-3) |
| Informe de Pruebas de Integración (`Informe-de-Pruebas-de-Integracion.md`) | ✅ Borrador v1.0 |
| Entorno reproducible Docker (`test` SQLite / `test-mysql` MariaDB) | ✅ Funciona (memoria resuelta) |
| Corrida completa `Feature` (1649 PASS) + análisis de 4 fallos | ✅ Evidenciado |
| Fix del test del grupo (INC-01) | ✅ Verde en SQLite y MariaDB |
| **Pendiente (aporte propio):** FI-01/02/03 + INT-11/12/13 | 🕗 Por implementar |
| Publicación en Wiki + Project/Issues + CI de integración | 🕗 Por consolidar |

---

## 2. Distribución por integrante

> Roles ajustables. Cada integrante **crea su GitHub Issue**, trabaja en una **rama propia** (`git push origin <rama>`, PR **dentro del fork**), y mueve su tarjeta en el **GitHub Project**.

| # | Integrante | Responsabilidad principal | Tareas concretas | Entregable / Herramienta |
|---|-----------|---------------------------|------------------|--------------------------|
| 1 | **Wilson** *(líder Sprint 3 · CI/CD + entorno)* | DevOps de integración | • Mantener runner Docker (`test`/`test-mysql`).<br>• **GitHub Actions:** asegurar workflow que ejecute la suite `Feature` (integración) en MySQL y publique estado/badge.<br>• Coordinar merges/PR dentro del fork. | GitHub Actions · docker-compose.test.yml · **co-presenta** |
| 2 | **Juan** *(Wiki + Scrum artifacts)* | Documentación y gestión | • Publicar **Plan + Informe** de Integración en **GitHub Wiki** (índice, formato, enlaces).<br>• **GitHub Project:** tablero Sprint 3 con tarjetas.<br>• **GitHub Issues:** una por tarea del sprint.<br>• Actualizar **Matriz de Trazabilidad** (INT ↔ RF/CPF). | GitHub Wiki · Projects · Issues · **co-presenta** |
| 3 | **Anette** *(Licencias/Empresa)* | Flujos de licencias + FMCS | • Consolidar **INT-04** (asiento de licencia, ya corregido).<br>• Nuevo caso **CPF-08 "agotar asientos"** (`LicenseSeatExhaustionTest`).<br>• **INT-07 FMCS**: checkout cruzado entre empresas → rechazo.<br>• Redactar **Reporte de Incidente INC-01** en el Informe. | `tests/Feature/Integracion/` · Wiki · **co-presenta** |
| 4 | **Jeanpiero** *(Fallas de interfaz 1)* | Inyección de fallas | • **FI-01 (sintáctica):** checkout sin destino / `status_id` no numérico → validación.<br>• **FI-02 (semántica):** `expected_checkin` anterior a `checkout_at` → rechazo.<br>• Evidencia + reporte de incidente si aplica. | `tests/Feature/Integracion/AssetCheckoutInterfaceTest.php` |
| 5 | **Jherson** *(Fallas de interfaz 2 + Depreciación)* | Resiliencia + cálculo | • **FI-03 (resiliencia/estado):** segundo checkout sobre activo ya asignado → sin doble asignación.<br>• **INT-12 Depreciación** ↔ Asset/License (cálculo de valor).<br>• Consolidar INT-05/06 (consumibles/componentes). | `tests/Feature/Integracion/` |
| 6 | **Jhastyn** *(Refuerzo de áreas débiles)* | Campos y estados | • **INT-11 CustomFields ↔ Asset** (validación dinámica en creación).<br>• **INT-13 StatusLabel ↔ disponibilidad** (`availableForCheckout()`, RF-08).<br>• Evidencia de ambos. | `tests/Feature/Integracion/` |

---

## 3. Mapa entregable → herramienta (exigencia del curso)

| Herramienta | Uso en la parte de Integración | Responsable |
|-------------|-------------------------------|-------------|
| **GitHub Wiki** | Plan + Informe de Integración + Matriz de Trazabilidad (con índice) | Juan |
| **GitHub Actions** | Pipeline CI que corre la suite `Feature` (integración) en MySQL; badge de estado | Wilson |
| **GitHub Projects** | Tablero Scrum Sprint 3 con las tarjetas de la §2 | juan |
| **GitHub Issues** | 1 issue por tarea (FI-01, FI-02, FI-03, INT-04, INT-07, INT-11, INT-12, INT-13, docs, CI) | Cada autor |
| **GitHub Pages** | (Opcional) enlazar el Informe/estado del Sprint | Juan |

---

## 4. Convención técnica para los tests nuevos

- Carpeta: **`tests/Feature/Integracion/`** (nuevos archivos del grupo → evidencia de código propio).
- Patrón: extender `Tests\TestCase`, usar **factories** (drivers) + `RefreshDatabase`; `Event::fake()`/`Mail::fake()` como stubs.
- Ejecutar en el runner oficial:
  ```bash
  docker compose -f trabajoLibelula/HITO-3/Integracion/docker-compose.test.yml run --rm test-mysql \
    bash -lc "php artisan test tests/Feature/Integracion"
  docker compose -f trabajoLibelula/HITO-3/Integracion/docker-compose.test.yml down
  ```
- Cada test corresponde a un `#CP` del **Plan §4.2**; al ejecutarlo, llenar su **`Resultado Real`** en el **Informe** (no en el Plan).

---

## 5. Cronograma corto (04 → 08 JUL)

| Día | Hito |
|-----|------|
| 04–05 JUL | Cada quien crea su Issue + rama; implementa sus tests (FI-01/02/03, INT-11/12/13); consolida INT-04/07. |
| 06 JUL | Ejecución en `test-mysql`; captura de evidencia; PR dentro del fork. |
| 07 JUL | Jeanpiero publica Plan+Informe en Wiki; Wilson deja verde el CI; se llenan `Resultado Real` en el Informe. |
| 08 JUL | Ensayo de presentación; revisión final del tablero e Issues cerrados. |

---

## 6. Presentación del Sprint 3 (integración)

- **Formato:** video ≤ 10 min, 2–3 integrantes con **cámara encendida**.
- **Propuesta de expositores (ajustable):** **Wilson** (entorno Docker + CI/CD), **Anette** (tests de integración + incidente INC-01), **Jeanpiero** (documentación Wiki + Scrum/Projects).
- **Guion sugerido:**
  1. Qué es integración y por qué (Modelo-V, *Small*, herramienta según stack: PHPUnit `Feature`).
  2. Entorno común reproducible (Docker `test`/`test-mysql`, fix de memoria).
  3. Resultados: 1649 PASS; clasificación de fallos (3 dialecto + 1 defecto de prueba corregido).
  4. Aporte propio: FI-01/02/03 + INT-11/12/13; reporte de incidente.
  5. CI/CD y trazabilidad; conclusiones.

---

## 7. Checklist de cierre (Definition of Done del Sprint)

- [ ] FI-01, FI-02, FI-03 implementados, verdes y evidenciados.
- [ ] INT-11, INT-12, INT-13 implementados, verdes y evidenciados.
- [ ] INT-04 consolidado; INT-07 (FMCS) y CPF-08 (agotar asientos) ejecutados.
- [ ] `Resultado Real` completo en el **Informe** (no en el Plan).
- [ ] Plan + Informe publicados en **GitHub Wiki** con índice.
- [ ] CI (GitHub Actions) de integración **en verde**.
- [ ] GitHub **Project** actualizado y **Issues** cerrados.
- [ ] Video de presentación grabado (2–3 con cámara).

---

*Distribución de actividades — Sprint 3 · Pruebas de Integración. Curso de Pruebas de Software.*
