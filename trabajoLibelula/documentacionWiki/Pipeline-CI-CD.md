# Pipeline CI/CD

> Documentación del proceso de Integración y Entrega Continua (GitHub Actions) que automatiza la ejecución de pruebas y la cobertura.

| Campo | Detalle |
|-------|---------|
| **Documento** | Documentación del Pipeline CI/CD — Snipe-IT |
| **Versión** | 1.0 |
| **Hito / Sprint** | Hito 2 / Sprint 2 |
| **Plataforma** | GitHub Actions |
| **Fecha de elaboración** | 2026-06-12 |

---

## 1. Visión general

El repositorio cuenta con **11 workflows** en `.github/workflows/`, verificados directamente. Cubren cuatro propósitos: **pruebas automatizadas**, **cobertura**, **seguridad/análisis estático** y **publicación/mantenimiento**. La práctica DevOps central del curso —ejecutar pruebas automáticamente ante cada cambio— está implementada y operativa.

---

## 2. Inventario de workflows (verificado)

| Workflow (archivo) | Nombre | Propósito | Disparadores |
|--------------------|--------|-----------|--------------|
| `tests-unit-coverage.yml` | Unit Tests + Coverage | **Suite Unit + cobertura (PCOV)**, publica Clover/HTML/JUnit | push (`master`,`develop`,`dev_J`), PR, manual |
| `tests-sqlite.yml` | Tests in SQLite | Suite completa sobre SQLite | push (`master`,`develop`), PR |
| `tests-mysql.yml` | Tests in MySQL | Suite completa sobre MySQL | push (`master`,`develop`), PR |
| `tests-postgres.yml` | Tests in Postgres | Suite completa sobre PostgreSQL | manual (`workflow_dispatch`) |
| `SA-codeql.yml` | CodeQL Security Scan | Análisis estático de seguridad | push/PR a `master` |
| `ethicalcheck.yml` | EthicalCheck-Workflow | Chequeo de seguridad de API | manual |
| `stale.yml` | Close stale issues | Cierre de issues inactivos | programado (cron diario) |
| `docker-ubuntu.yml` | Docker images (Ubuntu) | Build/publicación de imagen | push (`master`,`develop`), tags `v**` |
| `docker-alpine.yml` | Docker images (Alpine) | Build/publicación de imagen | push (`master`,`develop`), tags `v**` |
| `dockerhub-description.yml` | Update Docker Hub Description | Sincroniza descripción Docker Hub | push con cambios en `README.md` |
| `crowdin-upload.yml` | Crowdin Action | Subida de cadenas de traducción | push (`develop`) |

> Nota de actividad del equipo: el workflow `ethicalcheck.yml` se ajustó a ejecución manual y se desactivó una acción inexistente (commit `7fe0b4ccd`), evidencia de mantenimiento real del pipeline por parte del grupo.

---

## 3. Workflow de cobertura unitaria (núcleo del proceso de pruebas)

**Archivo:** `tests-unit-coverage.yml` — *"Unit Tests + Coverage (SQLite in-memory, PCOV)"*.

### 3.1 Características verificadas
- **Matriz de PHP:** 8.2, 8.3 y 8.4 (con `fail-fast: false`).
- **Driver de cobertura:** PCOV (más rápido que Xdebug), `pcov.enabled=1`.
- **Base de datos:** `sqlite_testing` (`:memory:`).
- **Extensiones:** mbstring, pdo_sqlite, bcmath, gd, intl, zip, curl, etc.

### 3.2 Pasos del job
1. Configurar PHP + PCOV (`shivammathur/setup-php`).
2. Checkout del repositorio.
3. Cachear dependencias de Composer.
4. Preparar `.env` y `.env.testing` desde `.env.testing.example`.
5. `composer install`.
6. Generar `APP_KEY` y claves de Passport.
7. Ejecutar:
   ```bash
   vendor/bin/phpunit --testsuite Unit \
     --coverage-clover coverage/clover.xml \
     --coverage-html coverage/html \
     --log-junit coverage/junit.xml
   ```
8. Publicar artefactos: `coverage-php-8.x` (Clover + HTML) y logs (retención 7 días).

### 3.3 Artefactos generados (fuente de evidencia)
| Artefacto | Contenido | Uso en la documentación |
|-----------|-----------|--------------------------|
| `clover.xml` | Cobertura de líneas | Completar [Cobertura y Estado Real](Cobertura-y-Estado-del-Proyecto) |
| `html/` | Reporte navegable | Evidencia visual |
| `junit.xml` | Resultados PASS/FAIL | Completar [Informe de Pruebas Unitarias](Informe-de-Pruebas-Unitarias) |

> **Estos artefactos son la fuente oficial y verificable** de los porcentajes de cobertura y los resultados de ejecución. Los documentos de esta Wiki que dependen de ellos los marcan como `⟦PENDIENTE-CI⟧` hasta su transcripción.

---

## 4. Flujo DevOps integrado con Scrum

```
Desarrollador (DEV)
   └─ commit/push en rama feature
        └─ Pull Request
             ├─ tests-sqlite / tests-mysql  (suite completa)
             ├─ tests-unit-coverage         (Unit + cobertura)
             └─ SA-codeql                   (seguridad)
                  └─ Revisión + merge a master
                       └─ Docker images (publicación)
```

- **GitHub Projects:** organiza el Sprint y las historias.
- **GitHub Issues:** registra defectos detectados por el pipeline o las pruebas manuales.
- **GitHub Actions:** ejecuta y verifica de forma automática.
- **GitHub Wiki/Pages:** documentación y publicación de resultados.

---

## 5. Recomendaciones de mejora del pipeline

1. **Reporte de cobertura por módulo:** añadir un paso que extraiga del `clover.xml` el porcentaje de `app/Models/` en alcance (métrica oficial del [Plan de Pruebas Unitarias](Plan-de-Pruebas-Unitarias) §6).
2. **Umbral de cobertura (gate):** configurar fallo del job si la cobertura de los modelos en alcance cae por debajo del 80 %.
3. **Publicación del HTML de cobertura en GitHub Pages** para consulta del equipo.
4. **Badge de estado** de los workflows en el `README` para visibilidad.

---

## 6. Trazabilidad

Los artefactos de este pipeline son la evidencia que cierra el [Informe de Pruebas Unitarias](Informe-de-Pruebas-Unitarias) y la [Cobertura y Estado Real del Proyecto](Cobertura-y-Estado-del-Proyecto).

---

*Fin del documento — Pipeline CI/CD.*
