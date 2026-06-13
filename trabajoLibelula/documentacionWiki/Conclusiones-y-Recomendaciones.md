# Conclusiones y Recomendaciones

> Cierre del proceso de pruebas. Corresponde al **Hito 3 / Sprint 3-4** y sintetiza el trabajo de los tres hitos.

| Campo | Detalle |
|-------|---------|
| **Documento** | Conclusiones y Recomendaciones Finales — Snipe-IT |
| **Versión** | 1.0 |
| **Hito / Sprint** | Hito 3 / Sprint 3-4 |
| **Fecha de elaboración** | 2026-06-12 |

---

## 1. Síntesis del proceso

El proyecto aplicó un proceso de pruebas completo sobre **Snipe-IT** (PHP 8.2+/Laravel 12, AGPL-3.0), integrando el marco ágil **Scrum** con prácticas **DevOps** automatizadas en GitHub. El alcance se delimitó de forma defendible a **cinco subsistemas núcleo** (Activos, Licencias, Inventario, Usuarios y Checkout), dado que el sistema completo (41 modelos, 91 controladores, 444 migraciones) excede el tamaño de un proyecto académico abarcable por entero.

Se cubrieron los cuatro niveles de prueba previstos por la norma ISO/IEC/IEEE 29119: **unitario, integración, sistema y aceptación**, con documentación trazable entre requisitos, casos, evidencias y resultados.

---

## 2. Resultados verificables alcanzados

| Logro | Evidencia verificada |
|-------|----------------------|
| Base de pruebas unitarias consolidada | 45 archivos / 279 métodos en `tests/Unit/` |
| Amplia suite de integración existente | 292 archivos / 1624 métodos en `tests/Feature/` |
| Pipeline de CI/CD operativo | 11 workflows, incluido cobertura con PCOV |
| Cobertura automatizada con artefactos | `tests-unit-coverage.yml` → Clover + HTML + JUnit |
| Compatibilidad multimotor en CI | Workflows SQLite, MySQL y PostgreSQL |
| Módulos con cobertura unitaria sobresaliente | Depreciable (30), User (25), Asset (20), Category (17) |

---

## 3. Hallazgos relevantes

### 3.1 Corrección del plan inicial
La auditoría del repositorio reveló que el **borrador v2.0** del plan de pruebas contenía afirmaciones desactualizadas (versión de PHPUnit, módulos sin tests que en realidad ya estaban cubiertos, técnica de cobertura inalcanzable). Estas diferencias se corrigieron y documentaron en el [Plan de Pruebas Unitarias](Plan-de-Pruebas-Unitarias) v3.0 y en [Cobertura y Estado Real](Cobertura-y-Estado-del-Proyecto). **Lección:** todo plan de pruebas debe validarse contra el código real, no asumirse.

### 3.2 Deuda técnica detectada en el producto
Se identificó una **inconsistencia de comportamiento** entre `Accessory::percentRemaining()` y `Consumable::percentRemaining()` ante el borde `qty=0, checkouts=0` (uno retorna 0, el otro 100). Debe registrarse como Issue y cubrirse con prueba explícita.

### 3.3 Métrica de cobertura
La configuración por defecto mide cobertura sobre **todo `app/`**, lo que subestima la calidad de las pruebas unitarias de modelos. Se adoptó una **métrica acotada a los modelos en alcance** como indicador oficial.

---

## 4. Brechas y trabajo pendiente

| Prioridad | Pendiente | Responsable sugerido |
|-----------|-----------|----------------------|
| Alta | Ampliar unitarias de AssetModel, Consumable, License/Seat | Testers asignados |
| Alta | Cubrir `getStatuslabelType()`/`getStatuslabelTypesForDB()` | Revisor |
| Alta | Ejecutar workflow de cobertura y transcribir valores `⟦PENDIENTE-CI⟧` | CI/CD |
| Media | Ejecutar casos funcionales manuales (CPF) en QA | Equipo QA |
| Media | Ejecutar pruebas de integración (Hito 3) | Equipo |
| Baja | Registrar deuda técnica de `percentRemaining()` como Issue | QA Lead |

---

## 5. Recomendaciones

### 5.1 Sobre el proceso de pruebas
1. **Cerrar el ciclo de cobertura:** ejecutar el pipeline y completar todos los campos `⟦PENDIENTE-CI⟧`/`⟦PENDIENTE-QA⟧` con valores reales antes de la sustentación.
2. **Gate de cobertura en CI:** hacer fallar el job si la cobertura de los modelos en alcance baja del 80 %.
3. **Priorizar por riesgo:** atender primero los módulos de baja cobertura y alta criticidad (AssetModel, Consumable).

### 5.2 Sobre DevOps
4. Publicar el reporte HTML de cobertura en GitHub Pages para visibilidad del equipo.
5. Añadir *badges* de estado de los workflows al `README`.

### 5.3 Sobre la gestión Scrum
6. Mantener la trazabilidad historia → caso → evidencia en GitHub Projects/Issues.
7. Conservar el rol rotativo de líder de equipo por Sprint, como exige la metodología del curso.

---

## 6. Conclusión final

El producto seleccionado resultó **idóneo** para los objetivos del curso: licencia libre, stack moderno, dominio empresarial conocido, infraestructura Docker y una base de pruebas preexistente que permitió comparar, corregir y ampliar. El proceso aplicado demuestra que la **validación del plan contra el código real** y la **automatización mediante CI/CD** son determinantes para un control de calidad fiable. El trabajo restante es de **ejecución y consolidación de evidencias**, no de rediseño: la estructura documental y el pipeline ya están establecidos y verificados.

---

## 7. Referencias

- ISO/IEC/IEEE 29119-1/2/3 — Software Testing.
- Documentación de Laravel Testing y PHPUnit 11.
- Repositorio del proyecto: `jhuamaniCond/snipe-it`.
- Documentos de esta Wiki (ver [Inicio](Home)).

---

*Fin del documento — Conclusiones y Recomendaciones.*
