# Hito 1 — Presentación del Producto de Software

| Campo | Detalle |
|-------|---------|
| **Hito / Sprint** | Hito 1 / Sprint 1 |
| **Entregable** | Presentación del producto seleccionado, planificación inicial y documentación base |
| **Producto** | Snipe-IT |
| **Repositorio original** | https://github.com/grokability/snipe-it |
| **Fork de trabajo** | https://github.com/jhuamaniCond/snipe-it |
| **Fecha de elaboración** | 2026-05-27 |
| **Estándar** | ISO/IEC/IEEE 29119 |

---

## 1. Identificación del producto

**Snipe-IT** es un sistema web libre y de código abierto para la **gestión de activos de TI** (IT Asset Management) y administración de licencias de software. Permite a las organizaciones registrar, asignar (checkout/checkin), auditar y dar de baja activos físicos, licencias, accesorios, componentes y consumibles a lo largo de su ciclo de vida.

| Atributo | Valor verificado en el repositorio |
|----------|-----------------------------------|
| Dominio | Sistema empresarial de gestión de activos de TI (rubro comercial/administrativo) |
| Licencia | **AGPL-3.0-or-later** (familia GPL, copyleft fuerte) |
| Lenguaje | PHP 8.2+ |
| Framework backend | Laravel 12 |
| Capa de presentación | Blade + AdminLTE 2 + Bootstrap 3, compilada con Laravel Mix |
| Base de datos | MySQL / MariaDB / PostgreSQL / SQLite |
| Infraestructura | Docker y Docker Compose disponibles en el repositorio |
| Actividad | Proyecto activo y mantenido; el fork registra actividad reciente del equipo (PRs y ramas de prueba) |

---

## 2. Cumplimiento de los criterios de selección del curso

| Criterio del enunciado | Estado | Evidencia en el repositorio |
|------------------------|--------|------------------------------|
| Licencia MIT/GPL verificada | ✅ Cumple | Archivo `LICENSE` (AGPL-3.0-or-later, familia GPL) |
| Dominio conocido (sistema empresarial PYME) | ✅ Cumple | Gestión de activos de TI |
| Stack tecnológico moderno y vigente | ✅ Cumple | PHP 8.2 / Laravel 12 / PHPUnit 11 |
| Complejidad mediana (3–5 módulos) | ✅ Cumple (con delimitación de alcance) | 41 modelos; se delimitan 5 subsistemas núcleo (ver §4) |
| Infraestructura DevOps adaptable (Docker Compose) | ✅ Cumple | `docker-compose.yml` y workflows de Docker en `.github/workflows/` |
| Documentación técnica y actividad reciente | ✅ Cumple | `README.md`, `TESTING.md`, `CONTRIBUTING.md`, Docker README |

> **Observación de alcance:** Snipe-IT supera el rango de 10–50 KLOC indicado para un proyecto "de complejidad mediana" si se contabiliza el sistema completo (41 modelos, 91 controladores, 444 migraciones). Por ello, el proceso de pruebas del curso **delimita un alcance académico defendible** centrado en los subsistemas núcleo (§4). Esta delimitación se sostiene a lo largo de los tres hitos.

---

## 3. Arquitectura del producto (verificada)

Snipe-IT sigue una arquitectura **MVC** sobre Laravel, con separación entre la capa web (Blade) y una **API REST** que alimenta las tablas dinámicas (datatables) y los selectores AJAX (select2).

```
app/
├── Models/            41 modelos Eloquent (dominio)
├── Http/
│   ├── Controllers/   61 controladores web (vistas Blade)
│   └── Controllers/Api/  30 controladores REST (JSON)
│   └── Transformers/  capa de serialización de la API
├── Policies/          22 políticas de autorización
└── Helpers/           utilidades transversales (Helper.php)
database/
├── factories/         29 factories de datos de prueba
└── migrations/        444 migraciones
tests/
├── Unit/              45 archivos / 279 métodos
└── Feature/           292 archivos / 1624 métodos
.github/workflows/     11 pipelines de CI/CD
```

**Patrones arquitectónicos relevantes para las pruebas:**
- **Transformers obligatorios:** los controladores de API nunca devuelven atributos crudos; serializan mediante una clase Transformer.
- **Autorización vía Policies:** toda comprobación de permisos se canaliza por políticas.
- **Full Multiple Company Support (FMCS):** filtrado multiempresa condicionado por configuración global.
- **Soft deletes:** los modelos principales usan borrado lógico.
- **Validación automática:** el paquete `watson/validating` valida los modelos antes de cada `save()`.

---

## 4. Subsistemas núcleo seleccionados para el proceso de pruebas

A partir del análisis del repositorio se seleccionan los siguientes subsistemas de negocio como alcance académico:

| # | Subsistema | Modelos principales | Justificación |
|---|------------|---------------------|----------------|
| 1 | **Gestión de Activos** | `Asset`, `AssetModel`, `Statuslabel` | Núcleo del producto; mayor LOC y lógica de negocio |
| 2 | **Gestión de Licencias** | `License`, `LicenseSeat` | Control de asientos, expiración y asignación |
| 3 | **Inventario consumible** | `Accessory`, `Component`, `Consumable` | Lógica de stock y disponibilidad |
| 4 | **Gestión de Usuarios** | `User`, `Company` | Jerarquías, permisos y multiempresa |
| 5 | **Flujo de Checkout** | `CheckoutRequest`, `CheckoutAcceptance`, `Category`, `Depreciable` | Transversal a todos los activos |

Estos subsistemas se mantienen como unidad de planificación en los planes de pruebas unitarias, funcionales, de integración, de sistema y de aceptación.

---

## 5. Planificación inicial (Sprint 1)

| Elemento | Herramienta | Estado |
|----------|-------------|--------|
| Tablero Kanban/Scrum | GitHub Projects | Establecido |
| Product Backlog e historias de usuario | GitHub Issues | Establecido |
| Plan inicial de pruebas | GitHub Wiki | Borrador v1.0 → consolidado en Hito 2 |
| Presentación pública del producto | GitHub Pages | Publicada |
| Repositorio de código y documentación | GitHub (fork) | Activo |

---

## 6. Trazabilidad hacia hitos posteriores

La presentación del producto en este hito establece la **línea base** sobre la que se construyen:
- El [Plan de Pruebas Unitarias](Plan-de-Pruebas-Unitarias) (Hito 2).
- El [Plan de Pruebas de Integración](Plan-de-Pruebas-de-Integracion) (Hito 2).
- Los planes de [Sistema](Plan-de-Pruebas-de-Sistema) y [Aceptación](Plan-de-Pruebas-de-Aceptacion) (Hito 3).

---

*Fin del documento — Hito 1.*
