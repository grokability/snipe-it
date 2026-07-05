# Plan de Pruebas de Integración

> Conforme a ISO/IEC/IEEE 29119-3. **Hito 2: solo el plan** (la ejecución se traslada al Hito 3, según indicación del docente).

| Campo | Detalle |
|-------|---------|
| **Documento** | Plan de Pruebas de Integración — Snipe-IT |
| **Versión** | 1.1 |
| **Hito / Sprint** | Hito 2 / Sprint 2 (plan) → ejecución en Hito 3 |
| **Nivel de prueba** | Integración (componentes e interfaces) — alcance *Small* (subsistemas internos) |
| **Herramienta** | PHPUnit (suite `Feature`) sobre SQLite en memoria — equivalente en el stack PHP/Laravel a Supertest (pruebas de integración HTTP por código, versionadas en el repo); Postman/Newman como complemento para el contrato de la API v1 |
| **Fecha de elaboración** | 2026-06-12 |
| **Última revisión** | 2026-07-04 (v1.1: correcciones de verificación, entorno común, matriz heredado/aporte, fallas de interfaz y formato de casos) |
| **Estándar** | ISO/IEC/IEEE 29119-3 |

---

## 1. Introducción y objetivos

Las pruebas de integración verifican que los **módulos del sistema interactúan correctamente** a través de sus interfaces: controladores ↔ modelos ↔ base de datos ↔ políticas de autorización ↔ transformers de la API. A diferencia de las unitarias (un método aislado), aquí se ejercita el **flujo completo** de una operación de negocio.

**Objetivos:**
1. Validar los flujos transversales multimodelo (checkout/checkin, asignación de licencias, descuento de stock).
2. Verificar las interfaces HTTP (rutas web y endpoints de API REST) y su contrato de respuesta.
3. Comprobar el respeto de las **políticas de autorización** y el **scoping multiempresa (FMCS)**.

> **Hallazgo verificado:** el proyecto original **ya incluye** una amplia suite de integración: **292 archivos / 1509 métodos** en `tests/Feature/` (medición 2026-07-04), organizados por subsistema. Este plan **aprovecha y documenta** esa base heredada e identifica **lo que falta** (áreas débiles y fallas de interfaz) como aporte del grupo; no parte de cero. Ver la **Matriz heredado vs aporte (§2.4)**.

---

## 2. Alcance

### 2.1 Estructura real de la suite de integración (`tests/Feature/`)
Carpetas verificadas relevantes al alcance:

`Assets/`, `AssetModels/`, `Accessories/`, `Components/`, `Consumables/`, `Licenses/`, `LicenseSeats/`, `Checkouts/`, `Checkins/`, `CheckoutAcceptances/`, `Categories/`, `Companies/`, `Users/`, `StatusLabels/`, `Requests/`, `Authentication/`, `Security/`, `Reporting/`.

### 2.2 Flujos de integración en alcance

| ID | Flujo de integración | Módulos integrados | Carpeta Feature |
|----|----------------------|--------------------|-----------------|
| INT-01 | Checkout de activo a usuario | Asset ↔ User ↔ Statuslabel ↔ ActionLog ↔ Policy | `Assets/`, `Checkouts/` |
| INT-02 | Checkin de activo | Asset ↔ User ↔ ActionLog | `Checkins/` |
| INT-03 | Aceptación de checkout | CheckoutAcceptance ↔ Asset ↔ User ↔ Notification | `CheckoutAcceptances/` |
| INT-04 | Asignación de asiento de licencia | License ↔ LicenseSeat ↔ User/Asset | `Licenses/`, `LicenseSeats/` |
| INT-05 | Checkout de consumible (descuento de stock) | Consumable ↔ User ↔ Category | `Consumables/` |
| INT-06 | Checkout de accesorio / componente | Accessory/Component ↔ User/Asset | `Accessories/`, `Components/` |
| INT-07 | Scoping multiempresa (FMCS) | Company ↔ {Asset, License, User} ↔ Policy | `Companies/` |
| INT-08 | Autorización por política | Policy ↔ Controller ↔ Modelo | `Security/`, `Authentication/` |
| INT-09 | Contrato de API (Transformer) | Controller API ↔ Transformer ↔ JSON | múltiples |
| INT-10 | Solicitud de activo (requestable) | CheckoutRequest ↔ Asset/AssetModel ↔ User | `Requests/` |
| INT-11 | Campos personalizados en creación de activo (**aporte grupo**) | CustomField/Fieldset ↔ Asset ↔ validación | `CustomFields/` |
| INT-12 | Cálculo de depreciación (**aporte grupo**) | Depreciation ↔ Asset/License | `Depreciations/` |
| INT-13 | Disponibilidad según status label (**aporte grupo**) | Statuslabel ↔ Asset (`availableForCheckout()`) | disperso |

### 2.3 Fuera del alcance de integración
Integraciones externas reales (servidor LDAP, IdP SAML/SCIM, envío de correo a un MTA real) — se simulan o se difieren a pruebas de sistema (Hito 3).

### 2.4 Matriz: lo que el proyecto YA TIENE vs lo que el GRUPO AÑADE

> "Heredado" = viene del proyecto original Snipe-IT. "Aporte del grupo" = lo que ejecutamos, documentamos y/o creamos en el Hito 3.

| Flujo | Heredado (`tests/Feature`) | Estado | Aporte del grupo (Hito 3) |
|-------|----------------------------|--------|---------------------------|
| INT-01 Checkout activo | `Checkouts/{Ui,Api}/AssetCheckoutTest`, `Assets/*` | 🟢 Amplio | Ejecutar + documentar; **fallas de interfaz FI-01/02/03** |
| INT-02 Checkin activo | `Checkins/{Ui,Api}/AssetCheckinTest` | 🟢 Amplio | Ejecutar; documentar liberación de asiento (CPF-04.3) |
| INT-03 Aceptación | `CheckoutAcceptances/*` | 🟢 | Ejecutar + documentar |
| INT-04 Asiento licencia | `Checkouts/Ui/LicenseCheckoutTest`, `LicenseSeats/*` | 🟢 | **Añadir caso "agotar asientos"** (sin cobertura previa) |
| INT-05 Consumible | `Checkouts/*/ConsumableCheckoutTest` | 🟢 | Ejecutar + documentar |
| INT-06 Accesorio/Componente | `Checkouts/*/AccessoryCheckoutTest`, `*/ComponentCheckoutTest` | 🟢 | Ejecutar + documentar |
| INT-07 FMCS | `Companies/*`, `Settings/*` | 🟡 Medio | **Reforzar**: checkout cruzado entre empresas |
| INT-08 Autorización | `Security/*`, transversal `*RequiresPermission*` | 🟢 | Ejecutar + documentar (403) |
| INT-09 Contrato API | `*/Api/*` (112 archivos) | 🟢 | Ejecutar + documentar JSON |
| INT-10 Requestable | `Requests/*` | 🟡 | Ejecutar + documentar |
| **INT-11 CustomFields↔Asset** | `CustomFields/*` (7 métodos) | 🔴 Débil | **Crear** test de validación dinámica |
| **INT-12 Depreciación** | `Depreciations/*` (9 métodos) | 🔴 Débil | **Crear** test de cálculo cross-módulo |
| **INT-13 StatusLabel↔disponibilidad** | disperso | 🔴 Débil | **Crear** test RF-08 (no deployable → no elegible) |

**Aporte del grupo, en concreto:** (1) ejecutar y documentar INT-01…INT-10 con resultados reales; (2) **crear tests nuevos** (código propio → rúbrica): FI-01/02/03 + INT-11/12/13; (3) **reforzar** FMCS (INT-07) y agotamiento de asientos (INT-04).

---

## 3. Estrategia de integración

- **Enfoque:** integración **incremental funcional** por subsistema (evita *Big Bang*), ejercitando la pila completa (ruta → controlador → modelo → BD).
- **Terminología (Modelo-V):** alcance **Small** (interfaces entre subsistemas internos); estrategia predominantemente **Bottom-Up**, donde las **factories actúan como *drivers*** de datos que disparan el flujo; los servicios externos se sustituyen con **mocks/stubs** de Laravel (`Mail::fake()`, `Notification::fake()`, `Http::fake()`).
- **Datos:** factories para construir el grafo de objetos relacionados.
- **Aislamiento:** `RefreshDatabase` para garantizar estado limpio entre pruebas.
- **Autenticación:** trait `InteractsWithAuthentication` y actuación como usuario con permisos definidos.
- **FMCS:** trait `ProvidesDataForFullMultipleCompanySupportTesting` para los casos multiempresa.

---

## 4. Casos de prueba de integración (especificación)

> Diseño para el Hito 3 (ejecución). Muchos cuentan con cobertura previa en la suite `Feature` existente, que se consolidará y ampliará.

| ID | Caso | Resultado esperado | Cobertura previa |
|----|------|--------------------|------------------|
| INT-01.1 | Checkout de activo disponible a usuario | 200/redirect; activo "Deployed"; log creado | Sí (`Assets/`, `Checkouts/`) |
| INT-01.2 | Checkout de activo no deployable | Rechazo; sin cambio de estado | Parcial |
| INT-02.1 | Checkin de activo asignado | Activo disponible; asignación liberada | Sí (`Checkins/`) |
| INT-03.1 | Aceptación de un checkout pendiente | Estado de aceptación actualizado; notificación | Sí (`CheckoutAcceptances/`) |
| INT-04.1 | Asignar asiento con disponibilidad | Asientos disponibles −1 | Sí (`LicenseSeats/`) |
| INT-04.2 | Asignar asiento sin disponibilidad | Rechazo | Parcial |
| INT-05.1 | Checkout de consumible con stock | Stock restante decrementa | Sí (`Consumables/`) |
| INT-06.1 | Checkout de accesorio a usuario | Accesorio asignado; conteo actualizado | Sí (`Accessories/`) |
| INT-07.1 | Acceso a entidad de otra empresa con FMCS activo | Acceso denegado | Sí (`Companies/`) |
| INT-08.1 | Acción sin permiso de la política | 403 / redirección | Sí (`Security/`) |
| INT-09.1 | Endpoint API devuelve estructura del Transformer | JSON con campos esperados | Sí (múltiples) |
| INT-10.1 | Solicitud de modelo requestable | Solicitud registrada | Sí (`Requests/`) |

### 4.1 Inyección de fallas de interfaz (aporte del grupo)

> Exigidas por la práctica del docente: por cada frontera A→B se diseñan 3 fallas. **Frontera:** `AssetCheckoutController` (Activos) → `Asset::checkOut()` + `User`/`Statuslabel`/`Actionlog`. Endpoint: `POST hardware/{assetId}/checkout`.

| ID | Tipo | Entrada inyectada | Resultado esperado |
|----|------|-------------------|--------------------|
| FI-01 | **Sintáctica** | Falta `assigned_user`; `status_id` con texto no numérico | Errores de validación; activo sin asignar |
| FI-02 | **Semántica** | Valores legales pero ilógicos: `expected_checkin` anterior a `checkout_at` | Rechazo por validación de fecha; sin asignación |
| FI-03 | **Resiliencia/estado** | Segundo checkout sobre un activo ya asignado (colisión) | Rechazo; sin doble asignación ni doble bitácora |

Cada falla que revele un defecto se documenta con la **plantilla de Reporte de Incidente** (Resultado Esperado vs Resultado Real) y se registra como GitHub Issue.

### 4.2 Casos de ejecución (formato del grupo)

> Formato: **#CP · Archivo de Prueba · Endpoint interceptado · Descripción de la Entrada · Resultado Esperado · Resultado Real**. `Resultado Real` se completa en la ejecución del Hito 3. Endpoints verificados en `routes/`.

| #CP | Archivo de Prueba | Endpoint interceptado | Descripción de la Entrada | Resultado Esperado | Resultado Real |
|-----|-------------------|-----------------------|---------------------------|--------------------|----------------|
| CP-01 | `Checkouts/Ui/AssetCheckoutTest.php` | `POST hardware/{assetId}/checkout` | Activo disponible + usuario + status deployable | 302; activo asignado; log `checkout` | ⟦pendiente⟧ |
| CP-02 | `Checkins/Ui/AssetCheckinTest.php` | `POST hardware/{assetId}/checkin` | Activo asignado | 302; `assigned_to` NULL; `location_id`=RTD; log `checkin` | ⟦pendiente⟧ |
| CP-03 | `Checkouts/Ui/LicenseCheckoutTest.php` | `POST licenses/{licenseId}/checkout/{seatId?}` | Licencia con asiento libre + usuario | Asiento asignado; disponibles −1 | ⟦pendiente⟧ |
| CP-04 | `Checkins/Ui/LicenseCheckinTest.php` | `POST licenses/{licenseId}/checkin/{backto?}` | Asiento asignado | Asiento liberado; disponibles +1 | ⟦pendiente⟧ |
| CP-05 | `Checkouts/Ui/ConsumableCheckoutTest.php` | `POST consumables/{consumablesID}/checkout` | Consumible con stock + usuario | Stock −cantidad; log | ⟦pendiente⟧ |
| CP-06 | `Checkouts/Ui/AccessoryCheckoutTest.php` | `POST accessories/{accessory}/checkout` | Accesorio con unidades + usuario + qty | Unidades −qty; fila en `accessories_checkout` | ⟦pendiente⟧ |
| CP-07 | `Checkins/Ui/AccessoryCheckinTest.php` | `POST accessories/{accessoryID}/checkin` | Accesorio entregado | Unidad devuelta; disponibles +1 | ⟦pendiente⟧ |
| CP-08 | `Checkouts/Ui/ComponentsCheckoutTest.php` | `POST components/{componentID}/checkout` | Componente + activo destino | Cantidad asignada al activo | ⟦pendiente⟧ |
| CP-09 | `Assets/Api/*` | `POST api/v1/hardware/{asset}/checkout` | Checkout vía API con token | JSON `status=success`; Transformer esperado | ⟦pendiente⟧ |
| CP-FI-01 | `Integracion/AssetCheckoutInterfaceTest.php` *(a crear)* | `POST hardware/{assetId}/checkout` | Sintáctica: falta destino; status no numérico | Errores de validación; sin asignación | ⟦pendiente⟧ |
| CP-FI-02 | `Integracion/AssetCheckoutInterfaceTest.php` *(a crear)* | `POST hardware/{assetId}/checkout` | Semántica: `expected_checkin` < `checkout_at` | Rechazo; sin asignación | ⟦pendiente⟧ |
| CP-FI-03 | `Integracion/AssetCheckoutInterfaceTest.php` *(a crear)* | `POST hardware/{assetId}/checkout` | Estado: segundo checkout sobre activo asignado | Rechazo; sin doble asignación | ⟦pendiente⟧ |
| CP-11 | `Integracion/FmcsCrossCompanyTest.php` *(a crear)* | `POST hardware/{assetId}/checkout` | FMCS ON; activo empresa A → usuario empresa B | Rechazo (bloqueo cross-company) | ⟦pendiente⟧ |
| CP-12 | `Integracion/LicenseSeatExhaustionTest.php` *(a crear)* | `POST licenses/{licenseId}/checkout` | Licencia con 0 asientos libres | Rechazo; sin asignación | ⟦pendiente⟧ |

---

## 5. Entorno y dependencias

### 5.1 Entorno común del grupo (mismas condiciones para todos)

| Elemento | Configuración |
|----------|---------------|
| PHP | 8.2 / 8.3 / 8.4 (matriz del CI) |
| Base de datos | `sqlite_testing` (`:memory:`), activada por `.env.testing` (`DB_CONNECTION=sqlite_testing`) |
| Driver de aislamiento | `RefreshDatabase` |
| Drivers de servicios | `array` (cache/session/mail), `sync` (queue) — fijados en `phpunit.xml` |
| Extensiones | mbstring, pdo_sqlite, sqlite3, bcmath, gd, intl, zip, curl, fileinfo, iconv, json |
| CI (fuente de verdad) | Workflows `tests-sqlite.yml`, `tests-mysql.yml`, `tests-postgres.yml`, `tests-unit-coverage.yml` |

### 5.2 Ejecución recomendada — contenedor Docker (Opción A)

> Para asegurar **paridad total** entre integrantes (mismo PHP, extensiones y BD que el CI), la ejecución oficial del grupo es vía un **runner Docker efímero**. No requiere levantar la app ni MariaDB: usa SQLite en memoria y se autodestruye al terminar.

| Opción | Qué es | Paridad con CI | Esfuerzo |
|--------|--------|----------------|----------|
| **A. Servicio Docker de tests (recomendada)** | `docker-compose.test.yml` con imagen PHP fijada que corre `php artisan test` sobre SQLite `:memory:`. Cada integrante hace `docker compose -f ... run --rm test`. | 🟢 Idéntica al CI | Bajo |
| B. Local con Herd | `php -d memory_limit=-1 artisan test --testsuite=Feature` | 🟡 Depende del PHP local | Muy bajo |

**Comando oficial (desde la raíz del repo, con Docker Desktop abierto):**
```bash
docker compose -f trabajoLibelula/HITO-3/Integracion/docker-compose.test.yml run --rm test
```
Atajo Windows: `\trabajoLibelula\HITO-3\Integracion\correr-tests.ps1`.
Detalle de uso y FAQ: `trabajoLibelula/HITO-3/Integracion/README-ENTORNO-DOCKER.md`.

**Alternativa local (Opción B):**
```bash
php -d memory_limit=-1 artisan test --testsuite=Feature      # suite completa
php artisan test tests/Feature/Checkouts                      # por subsistema
```

> **Nota (incidencia resuelta):** el error de "memoria insuficiente" al correr toda la suite **no** proviene de SQLite ni del hardware, sino del `memory_limit` de PHP local (por defecto 128M) al ejecutar ~1509 métodos en un solo proceso. Tanto el CI como el runner Docker usan `memory_limit=-1`.

---

## 6. Criterios de entrada y salida

### Entrada
- [ ] Suite `Unit` estable (Hito 2 cerrado).
- [ ] Factories de los módulos integrados verificadas.
- [ ] `.env.testing` y conexión `sqlite_testing` operativas.

### Salida (a verificar en Hito 3)
- [ ] 100 % de los flujos INT-01 a INT-10 (heredados) ejecutados y documentados.
- [ ] Tests **nuevos del grupo** creados y ejecutados: FI-01/02/03 e INT-11/12/13.
- [ ] Refuerzos ejecutados: FMCS cross-company (INT-07) y agotamiento de asientos (INT-04).
- [ ] Cero FAIL al cierre (o defectos registrados con Reporte de Incidente).
- [ ] Defectos de integración registrados en GitHub Issues.
- [ ] Tabla §4.2 con `Resultado Real` completa.
- [ ] Resultados documentados en el informe de integración (Hito 3).

---

## 7. Riesgos

| ID | Riesgo | Mitigación |
|----|--------|------------|
| RI-01 | Acoplamiento entre módulos dificulta aislar la causa de un fallo | Integración incremental por subsistema |
| RI-02 | Dependencia de `Setting` (singleton) en flujos | Inicializar settings con los traits de soporte |
| RI-03 | Diferencias de comportamiento entre SQLite y MySQL/PostgreSQL | Ejecutar la matriz de los tres motores en CI |
| RI-04 | Datos compartidos entre pruebas | `RefreshDatabase` por prueba |
| RI-05 | "Memoria insuficiente" al correr toda la suite | Ejecutar con `memory_limit=-1` (ver §5.2); no es limitación de SQLite |

---

## 8. Trazabilidad

Los flujos INT-XX se vinculan a los requisitos funcionales (RF-XX) y a los casos funcionales (CPF-XX) en la [Matriz de Trazabilidad](Matriz-de-Trazabilidad).

---

## 9. Historial de cambios

| Versión | Fecha | Cambios |
|---------|-------|---------|
| 1.0 | 2026-06-12 | Plan inicial (Hito 2). |
| 1.1 | 2026-07-04 | Cifra corregida (1509 métodos); §2.4 matriz heredado/aporte; §3 terminología Modelo-V (Small/Bottom-Up/mocks); §4.1 fallas de interfaz; §4.2 casos en formato del grupo; §5 entorno común + fix de memoria; flujos INT-11/12/13; criterios de salida y riesgo RI-05. |

---

*Fin del documento — Plan de Pruebas de Integración (ejecución en Hito 3).*
