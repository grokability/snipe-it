# Inventario de pruebas de integración existentes (`tests/Feature`)

> Base para (1) verificar el `Plan-de-Pruebas-de-Integracion.md` de la wiki y (2) redactar su Informe.
> Datos medidos sobre el repositorio el 2026-07-04. `tests/Feature` = **292 archivos** de prueba (nivel integración/funcional HTTP).
> Reparto por interfaz: **Ui = 130 archivos · Api = 112 · otros = 50**.

---

## 1. Cobertura por módulo (archivos / métodos)

| Módulo (carpeta) | Archivos | Métodos | Densidad |
|---|---:|---:|---|
| Assets | 27 | 212 | 🟢 Muy alta |
| Users | 23 | 160 | 🟢 Muy alta |
| Checkouts | 11 | 116 | 🟢 Alta |
| Checkins | 9 | 79 | 🟢 Alta |
| Notifications | 12 | 77 | 🟢 Alta |
| Maintenances | 15 | 65 | 🟢 Alta |
| Locations | 12 | 62 | 🟢 Alta |
| Accessories | 15 | 57 | 🟢 Alta |
| Console (comandos) | 9 | 54 | 🟢 Alta |
| Licenses | 11 | 52 | 🟢 Alta |
| Search | 1 | 50 | 🟢 Alta |
| Settings | 7 | 48 | 🟢 Alta |
| AssetModels | 9 | 40 | 🟡 Media |
| Categories | 10 | 37 | 🟡 Media |
| Consumables | 12 | 35 | 🟡 Media |
| History | 1 | 31 | 🟡 Media |
| Components | 10 | 29 | 🟡 Media |
| Departments | 8 | 27 | 🟡 Media |
| ActionLogs | 3 | 24 | 🟡 Media |
| ReportTemplates | 5 | 23 | 🟡 Media |
| Companies | 8 | 22 | 🟡 Media (¡clave FMCS!) |
| CheckoutAcceptances | 3 | 22 | 🟡 Media |
| Manufacturers | 9 | 21 | 🟡 Media |
| LicenseSeats | 1 | 20 | 🟡 Media |
| Groups | 7 | 19 | 🟡 Media |
| Reporting | 3 | 15 | 🟠 Baja |
| Importing | 14 | 15 | 🟠 Baja (muchos archivos, aserciones ligeras) |
| Suppliers | 6 | 10 | 🟠 Baja |
| Depreciations | 6 | 9 | 🟠 Baja |
| Requests | 2 | 9 | 🟠 Baja |
| CustomFields | 2 | 7 | 🔴 Muy baja |
| StatusLabels | 5 | 7 | 🔴 Muy baja |
| PredefinedKits | 5 | 7 | 🔴 Muy baja |
| Notes | 1 | 7 | 🔴 Muy baja |
| Modals | 1 | 7 | 🔴 Muy baja |
| Setup | 1 | 7 | 🔴 Muy baja |
| Redirects | 1 | 6 | 🔴 Muy baja |
| Security | 1 | 5 | 🔴 Muy baja |
| CustomFieldsets | 1 | 4 | 🔴 Muy baja |
| Livewire | 2 | 4 | 🔴 Muy baja |
| Authentication | 1 | 3 | 🔴 Muy baja |
| (raíz) ApiRateLimit, Dashboard | 2 | — | — |

---

## 2. Flujos de integración YA cubiertos (entre módulos)

Estos son los flujos multi-módulo que `tests/Feature` ya ejercita (por HTTP, con BD real y factories). Sirven de **evidencia de integración existente**:

| Flujo de integración | Módulos que conecta | Evidencia (carpeta) |
|---|---|---|
| Checkout de activo | Assets ↔ Users/Locations/Assets ↔ ActionLog/History ↔ Notifications | `Checkouts/{Ui,Api}/AssetCheckoutTest` |
| Checkin de activo | Assets ↔ LicenseSeats (limpia `assigned_to`) ↔ Location RTD ↔ History | `Checkins/{Ui,Api}/AssetCheckinTest` |
| Checkout/Checkin de licencia | Licenses ↔ LicenseSeats ↔ Users/Assets ↔ ActionLog | `Checkouts/Ui/LicenseCheckoutTest`, `Checkins/*/License*` |
| Checkout de accesorio | Accessories ↔ Users/Assets/Locations ↔ pivote `accessories_checkout` ↔ Mail | `Checkouts/{Ui,Api}/AccessoryCheckoutTest` |
| Checkout de consumible | Consumables ↔ Users ↔ decremento de stock ↔ ActionLog | `Checkouts/{Ui,Api}/ConsumableCheckoutTest` |
| Checkout de componente | Components ↔ Assets ↔ cantidades | `Checkouts/{Ui,Api}/ComponentCheckoutTest` |
| Checkout masivo (bulk) | Assets ↔ múltiples destinos en una operación | `Checkouts/Ui/BulkAssetCheckoutTest` |
| Borrado con dependencias | Categories/Manufacturers/Models ↔ Assets/Models (integridad referencial) | `Categories/*`, `Manufacturers/*`, `AssetModels/*` |
| Importación | Importer ↔ creación masiva de Assets/Users/etc. | `Importing/*` |
| Búsqueda | Search ↔ múltiples modelos y relaciones | `Search/*` |
| Reportes | Reporting/ReportTemplates ↔ datos de varios módulos | `Reporting/*`, `ReportTemplates/*` |
| Aceptación de checkout | CheckoutAcceptances ↔ Assets ↔ Users ↔ Notifications | `CheckoutAcceptances/*` |
| Autenticación y permisos | Auth ↔ Policies ↔ acceso a recursos (403) | transversal (`*RequiresPermission*`) |
| Multi-empresa (FMCS) | Companies ↔ scoping en Assets/Users/etc. | `Companies/*`, `Settings/*` |
| API v1 | Controllers Api ↔ Transformers ↔ persistencia | `*/Api/*` (112 archivos) |

---

## 3. Huecos / candidatos a reforzar en el Hito 3

Priorizar **nuevas pruebas de integración** donde la densidad es baja **y** el módulo participa en flujos multi-módulo:

| Prioridad | Área | Motivo de integración |
|---|---|---|
| 🔴 Alta | **Custom Fields / Fieldsets ↔ Assets** (7+4 métodos) | Validación dinámica, campos encriptados y de tipo fecha en el checkout/creación de activos; poca cobertura para su impacto transversal. |
| 🔴 Alta | **FMCS (Companies) ↔ scoping cross-módulo** (22 métodos) | El bloqueo por compañía afecta a Assets, Users, Licenses, Accessories… conviene integración explícita del scoping en cada flujo. |
| 🟠 Media | **Depreciations ↔ Assets/Licenses** (9 métodos) | Cálculo de valor depreciado en el tiempo atraviesa activos y licencias. |
| 🟠 Media | **Importing end-to-end** (15 métodos/14 archivos) | Muchos archivos pero aserciones ligeras; verificar creación real + relaciones tras importar. |
| 🟠 Media | **Reporting/ReportTemplates ↔ datos multi-módulo** (15+23) | Integración de datos de varios módulos en un reporte. |
| 🟡 Baja | **StatusLabels ↔ disponibilidad de Assets** (7) | Regla `availableForCheckout()` (deployable/archived) — enlaza con RF-08 de caja negra. |
| 🟡 Baja | **PredefinedKits ↔ Assets/Accessories/Consumables** (7) | Un kit reparte varios tipos de ítems; flujo compuesto poco cubierto. |

---

## 4. Uso previsto

1. **Verificación del Plan** (`documentacionWiki/Plan-de-Pruebas-de-Integracion.md`): contrastar que los casos/alcance del plan coincidan con (a) los flujos ya cubiertos (§2) y (b) los huecos priorizados (§3). Marcar en el plan lo que ya está automatizado vs. lo que se añadirá.
2. **Informe de Integración**: reportar cobertura de integración existente (292 archivos, métodos por módulo), los flujos verificados y los nuevos casos que el grupo agregue en el Hito 3, con su resultado.

---

*Inventario técnico — Hito 3 · Pruebas de Integración. Curso de Pruebas de Software.*
