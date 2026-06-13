# Plan de Pruebas de Integración

> Conforme a ISO/IEC/IEEE 29119-3. **Hito 2: solo el plan** (la ejecución se traslada al Hito 3, según indicación del docente).

| Campo | Detalle |
|-------|---------|
| **Documento** | Plan de Pruebas de Integración — Snipe-IT |
| **Versión** | 1.0 |
| **Hito / Sprint** | Hito 2 / Sprint 2 (plan) → ejecución en Hito 3 |
| **Nivel de prueba** | Integración (componentes e interfaces) |
| **Herramienta** | PHPUnit (suite `Feature`) sobre SQLite en memoria |
| **Fecha de elaboración** | 2026-06-12 |
| **Estándar** | ISO/IEC/IEEE 29119-3 |

---

## 1. Introducción y objetivos

Las pruebas de integración verifican que los **módulos del sistema interactúan correctamente** a través de sus interfaces: controladores ↔ modelos ↔ base de datos ↔ políticas de autorización ↔ transformers de la API. A diferencia de las unitarias (un método aislado), aquí se ejercita el **flujo completo** de una operación de negocio.

**Objetivos:**
1. Validar los flujos transversales multimodelo (checkout/checkin, asignación de licencias, descuento de stock).
2. Verificar las interfaces HTTP (rutas web y endpoints de API REST) y su contrato de respuesta.
3. Comprobar el respeto de las **políticas de autorización** y el **scoping multiempresa (FMCS)**.

> **Hallazgo verificado:** el repositorio ya dispone de una amplia suite de integración: **292 archivos / 1624 métodos** en `tests/Feature/`, organizados por subsistema. Este plan **aprovecha y documenta** esa base existente, identificando los flujos prioritarios a consolidar; no parte de cero.

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

### 2.3 Fuera del alcance de integración
Integraciones externas reales (servidor LDAP, IdP SAML/SCIM, envío de correo a un MTA real) — se simulan o se difieren a pruebas de sistema (Hito 3).

---

## 3. Estrategia de integración

- **Enfoque:** integración **incremental funcional** por subsistema, ejercitando la pila completa (ruta → controlador → modelo → BD).
- **Datos:** factories (29 disponibles) para construir el grafo de objetos relacionados.
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

---

## 5. Entorno y dependencias

| Elemento | Configuración |
|----------|---------------|
| Base de datos | `sqlite_testing` (`:memory:`) |
| Ejecución | `php artisan test --testsuite=Feature` |
| CI | Workflows `tests-sqlite.yml`, `tests-mysql.yml`, `tests-postgres.yml` |
| Driver de aislamiento | `RefreshDatabase` |

---

## 6. Criterios de entrada y salida

### Entrada
- [ ] Suite `Unit` estable (Hito 2 cerrado).
- [ ] Factories de los módulos integrados verificadas.
- [ ] `.env.testing` y conexión `sqlite_testing` operativas.

### Salida (a verificar en Hito 3)
- [ ] 100 % de los flujos INT-01 a INT-10 ejecutados.
- [ ] Cero FAIL al cierre.
- [ ] Defectos de integración registrados en GitHub Issues.
- [ ] Resultados documentados en el informe de integración (Hito 3).

---

## 7. Riesgos

| ID | Riesgo | Mitigación |
|----|--------|------------|
| RI-01 | Acoplamiento entre módulos dificulta aislar la causa de un fallo | Integración incremental por subsistema |
| RI-02 | Dependencia de `Setting` (singleton) en flujos | Inicializar settings con los traits de soporte |
| RI-03 | Diferencias de comportamiento entre SQLite y MySQL/PostgreSQL | Ejecutar la matriz de los tres motores en CI |
| RI-04 | Datos compartidos entre pruebas | `RefreshDatabase` por prueba |

---

## 8. Trazabilidad

Los flujos INT-XX se vinculan a los requisitos funcionales (RF-XX) y a los casos funcionales (CPF-XX) en la [Matriz de Trazabilidad](Matriz-de-Trazabilidad).

---

*Fin del documento — Plan de Pruebas de Integración (ejecución en Hito 3).*
