# Proceso de Pruebas de Software — Snipe-IT

**Curso:** Pruebas de Software — Semestre 2026-A
**Producto bajo prueba:** [Snipe-IT](https://github.com/grokability/snipe-it) — sistema libre de gestión de activos y licencias de TI
**Fork de trabajo:** [`jhuamaniCond/snipe-it`](https://github.com/jhuamaniCond/snipe-it)
**Estándar de referencia:** ISO/IEC/IEEE 29119 (Parte 2: Procesos; Parte 3: Documentación)
**Última actualización del índice:** 2026-06-12

---

## 1. Propósito de esta Wiki

Esta Wiki centraliza toda la documentación del proceso de pruebas de software aplicado al producto open source **Snipe-IT**, organizada según los tres hitos del curso e integrando el marco ágil **Scrum** con prácticas **DevOps** automatizadas (GitHub Actions, Projects, Issues, Wiki y Pages).

Toda la información publicada está **verificada contra el repositorio real**. Cuando una fuente previa (el borrador del plan v2.0) contradice el código, prevalece el repositorio y la diferencia se documenta de forma explícita.

---

## 2. Índice general por hito

### Hito 1 — Sprint 1 (presentación y planificación inicial)
- [Hito 1 — Presentación del Producto de Software](Hito-1-Presentacion-del-Producto)

### Hito 2 — Sprint 2 (pruebas unitarias, funcionales, integración y CI/CD)
- [Plan de Pruebas Unitarias](Plan-de-Pruebas-Unitarias)
- [Informe de Pruebas Unitarias](Informe-de-Pruebas-Unitarias)
- [Diseño de Casos de Pruebas Funcionales](Diseno-de-Casos-de-Pruebas-Funcionales)
- [Informe de Casos de Pruebas Funcionales](Informe-de-Casos-de-Pruebas-Funcionales)
- [Plan de Pruebas de Integración](Plan-de-Pruebas-de-Integracion)
- [Pipeline CI/CD](Pipeline-CI-CD)
- [Matriz de Trazabilidad](Matriz-de-Trazabilidad)
- [Cobertura y Estado Real del Proyecto](Cobertura-y-Estado-del-Proyecto)

### Hito 3 — Sprint 3-4 (sistema, aceptación y cierre)
- [Plan de Pruebas de Sistema](Plan-de-Pruebas-de-Sistema)
- [Plan de Pruebas de Aceptación](Plan-de-Pruebas-de-Aceptacion)
- [Conclusiones y Recomendaciones](Conclusiones-y-Recomendaciones)

---

## 3. Hechos verificados del repositorio (línea base)

Los siguientes valores fueron medidos directamente sobre el árbol de código del fork y constituyen la **línea base factual** común a todos los documentos de esta Wiki.

| Atributo | Valor verificado |
|----------|------------------|
| Licencia | AGPL-3.0-or-later (familia GPL, copyleft fuerte) |
| Lenguaje / Framework | PHP 8.2+ / Laravel 12 |
| Herramienta de pruebas | PHPUnit `^11.0` |
| Driver de cobertura (CI) | PCOV (SQLite en memoria) |
| Modelos Eloquent | 41 |
| Controladores | 91 (61 web + 30 API) |
| Policies de autorización | 22 |
| Factories de datos de prueba | 29 |
| Migraciones de base de datos | 444 |
| Pruebas unitarias | 45 archivos · 279 métodos de prueba |
| Pruebas de integración (Feature) | 292 archivos · 1624 métodos de prueba |
| Workflows de CI/CD (GitHub Actions) | 11 |

> Nota sobre la licencia: el enunciado del curso exige "Licencia MIT/GPL verificada". Snipe-IT se distribuye bajo **AGPL-3.0-or-later**, una licencia de la familia GPL con copyleft de red, que permite fork, modificación y uso libre con fines académicos. El criterio del curso queda satisfecho.

---

## 4. Convenciones de la documentación

- **Trazabilidad:** todo caso de prueba se vincula a un requisito, un módulo, una evidencia y un resultado (ver [Matriz de Trazabilidad](Matriz-de-Trazabilidad)).
- **Campos pendientes de CI:** los valores que requieren ejecución real (porcentaje de cobertura, número de pruebas en verde/rojo) se marcan como `⟦PENDIENTE-CI⟧` y se completan a partir del artefacto `clover.xml` generado por el workflow de cobertura. **No se publican valores estimados como si fueran reales.**
- **Idioma:** español formal técnico-académico.
- **Nomenclatura de páginas:** nombres con guiones, compatibles con GitHub Wiki.

---

## 5. Herramientas Scrum + DevOps utilizadas

| Herramienta | Uso en el proyecto |
|-------------|--------------------|
| GitHub Projects | Tablero Kanban/Scrum, Product Backlog y Sprints |
| GitHub Issues | Historias de usuario, registro de defectos (`bug`) |
| GitHub Actions | Pipeline CI/CD: pruebas y cobertura automatizadas |
| GitHub Wiki | Esta documentación versionada |
| GitHub Pages | Presentación pública del producto y demo de staging |

---

*Documento índice — mantener sincronizado al añadir o renombrar páginas.*
