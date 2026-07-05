# CURSO – PRUEBAS DE SOFTWARE · HITO 2 – TRABAJO FINAL

Proyecto: **Pruebas de Software sobre Snipe-IT** (sistema open source de gestión de activos y licencias IT · Laravel 12 / PHP 8.2+).
Este documento describe **dónde se encuentra cada artefacto** del Hito 2 y las **URLs** de los entregables generados con GitHub Pages, GitHub Project, GitHub Wiki y GitHub Actions.

---

## 1. Integrantes y autoevaluación

| Nombres y Apellidos | Autoevaluación (% de esfuerzo en este hito, 1–100) |
|---------------------|-----------------------------------------------------|
| JHASTYN JEFFERSON PAYEHUANCA RIQUELME | 40 % |
| TURPO HUANCA WILSON JOSUE | 70 % |
| ANETTE ISABEL GALLEGOS CONDORI | 40 % |
| JEANPIERO SIXTO HUAMANI CONDORI| 50 % |
| JHERSON DAVID INCA MONCCA| 30 % |
| JUAN SERGIO ZEBALLOS PEREZ| 40 % |

> ⚠️ Si el porcentaje de esfuerzo es CERO, el estudiante no participó en ninguna tarea de este hito.

---

## 2. Repositorio y accesos

- **URL del repositorio en GitHub:** https://github.com/jhuamaniCond/snipe-it
- **Acceso para revisión del docente:** se otorgaron los permisos necesarios a la cuenta **https://github.com/robert-arisaca** (colaborador del repositorio, del Project y de la Wiki).

---

## 3. Mapa de artefactos del Hito 2

### 3.1. GitHub Wiki — Documentación de pruebas
**URL:** https://github.com/jhuamaniCond/snipe-it/wiki
Documentación central del proyecto, estructurada por hitos (ISO/IEC/IEEE 29119). La sección **Hito 2** agrupa los entregables de pruebas unitarias, funcionales, de integración y CI/CD:

| Documento | URL |
|-----------|-----|
| Plan de Pruebas Unitarias | https://github.com/jhuamaniCond/snipe-it/wiki/Plan-de-Pruebas-Unitarias |
| Informe de Pruebas Unitarias | https://github.com/jhuamaniCond/snipe-it/wiki/Informe-de-Pruebas-Unitarias |
| Diseño de Casos de Pruebas Funcionales | https://github.com/jhuamaniCond/snipe-it/wiki/Diseno-de-Casos-de-Pruebas-Funcionales |
| Informe de Casos de Pruebas Funcionales | https://github.com/jhuamaniCond/snipe-it/wiki/Informe-de-Casos-de-Pruebas-Funcionales |
| Plan de Pruebas de Integración | https://github.com/jhuamaniCond/snipe-it/wiki/Plan-de-Pruebas-de-Integracion |
| Pipeline CI/CD | https://github.com/jhuamaniCond/snipe-it/wiki/Pipeline-CI-CD |
| Matriz de Trazabilidad | https://github.com/jhuamaniCond/snipe-it/wiki/Matriz-de-Trazabilidad |
| Cobertura y Estado del Proyecto | https://github.com/jhuamaniCond/snipe-it/wiki/Cobertura-y-Estado-del-Proyecto |

### 3.2. GitHub Pages — Sitio de presentación
**URL:** https://jhuamanicond.github.io/snipe-it/
Sitio web publicado con la presentación del proyecto: introducción a Snipe-IT, stack tecnológico, diseño técnico (arquitectura MVC, API REST, capas de seguridad), estructura del código fuente, módulos y evaluación de riesgos, arquitectura DevOps y cronograma de hitos. Sirve como portada navegable de los entregables.

### 3.3. GitHub Project — Tablero Scrum
**URL:** https://github.com/users/jhuamaniCond/projects/2
Tablero **"Tablero Scrum-Snipe-IT"** con la gestión ágil del proyecto (historias de usuario, tareas e issues por sprint). Evidencia la planificación y el seguimiento del trabajo del Hito 2.

### 3.4. GitHub Actions — Integración Continua (CI/CD)
**URL:** https://github.com/jhuamaniCond/snipe-it/actions
Workflows de integración continua que ejecutan automáticamente la suite de pruebas y análisis en cada push/PR:

| Workflow | Archivo | Propósito |
|----------|---------|-----------|
| Tests in MySQL | `.github/workflows/tests-mysql.yml` | Ejecuta PHPUnit contra MySQL |
| Tests in Postgres | `.github/workflows/tests-postgres.yml` | Ejecuta PHPUnit contra PostgreSQL |
| Tests in SQLite | `.github/workflows/tests-sqlite.yml` | Ejecuta PHPUnit contra SQLite |
| Tests Unit + Coverage | `.github/workflows/tests-unit-coverage.yml` | Ejecuta la suite **Unit** y reporta cobertura (PCOV) |
| CodeQL Security Scan | `.github/workflows/SA-codeql.yml` | Análisis estático de seguridad |

> URLs directas por workflow, p. ej.: https://github.com/jhuamaniCond/snipe-it/actions/workflows/tests-unit-coverage.yml

### 3.5. Evidencias de pruebas funcionales (caja negra)
Capturas de la ejecución manual de los casos funcionales (CPF-xx), organizadas por requisito en la Wiki y en el repositorio:
- **En la Wiki:** imágenes adjuntas referenciadas desde el *Informe de Casos de Pruebas Funcionales*.
https://github.com/jhuamaniCond/snipe-it/wiki

Seccion Hito 2

---

## 4. Entregable 02 — Presentación (video)

- **Enlace / grabación de la presentación del Hito 2:** _________________________ (≤ 10 minutos)
- Presentada por 2–3 integrantes con cámara encendida, explicando los entregables más importantes (pruebas unitarias y cobertura, casos funcionales y evidencias, plan de integración y pipeline CI/CD).

---

## 5. Ubicación de este entregable

- **Entregable 01:** este `README.md` en la carpeta compartida de Google Drive `/HITO-2/`.
- **Entregable 02:** enlace/grabación del video en la misma carpeta `/HITO-2/`.

---

_Documento de entrega del Hito 2 — Curso de Pruebas de Software._
