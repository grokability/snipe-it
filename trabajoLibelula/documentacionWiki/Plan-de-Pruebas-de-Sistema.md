# Plan de Pruebas de Sistema

> Conforme a ISO/IEC/IEEE 29119-3. Corresponde al **Hito 3 / Sprint 3-4**.

| Campo | Detalle |
|-------|---------|
| **Documento** | Plan de Pruebas de Sistema — Snipe-IT |
| **Versión** | 1.0 (planificación) |
| **Hito / Sprint** | Hito 3 / Sprint 3-4 |
| **Nivel de prueba** | Sistema (extremo a extremo) |
| **Fecha de elaboración** | 2026-06-12 |
| **Estándar** | ISO/IEC/IEEE 29119-3 |

---

## 1. Introducción y objetivos

Las pruebas de sistema validan el **comportamiento del producto completo** desplegado en un entorno representativo del de producción, verificando requisitos **funcionales y no funcionales** de forma extremo a extremo. A diferencia de la integración (interfaces entre módulos), aquí se ejercita el sistema **como un todo** desde la perspectiva externa.

**Objetivos:**
1. Verificar los flujos de negocio completos sobre el sistema desplegado (Docker).
2. Validar atributos no funcionales: seguridad/autorización, rendimiento básico, usabilidad y compatibilidad multimotor de base de datos.
3. Confirmar que el despliegue automatizado (CI/CD) produce un sistema operativo.

---

## 2. Alcance

### 2.1 En alcance
- Flujos extremo a extremo de los subsistemas núcleo: Activos, Licencias, Inventario, Usuarios, Checkout.
- Atributos no funcionales medibles en el entorno del curso (ver §4).
- Despliegue del sistema mediante Docker Compose.

### 2.2 Fuera de alcance
- Pruebas de carga a gran escala con infraestructura productiva real.
- Integraciones externas que requieran credenciales corporativas (LDAP/SAML reales) salvo simulación.

---

## 3. Estrategia y entorno

| Elemento | Definición |
|----------|------------|
| Entorno | Despliegue Docker Compose del repositorio (entorno tipo staging) |
| Datos | Conjunto de datos de demostración poblado mediante seeders/factories |
| Tipo de ejecución | Combinada: manual (flujos de negocio) + automatizada (suite completa en CI sobre los 3 motores) |
| Publicación | Demo de staging vía GitHub Pages (cuando aplique) |

---

## 4. Categorías de prueba de sistema

| ID | Categoría | Descripción | Técnica/Herramienta |
|----|-----------|-------------|---------------------|
| SYS-FUN | Funcional E2E | Flujos completos (alta de activo → checkout → aceptación → checkin → baja) | Manual + Feature suite |
| SYS-SEC | Seguridad/Autorización | Acceso por roles, FMCS, protección de rutas | `tests/Feature/Security`, CodeQL |
| SYS-COMPAT | Compatibilidad de BD | Comportamiento idéntico en SQLite/MySQL/PostgreSQL | Workflows `tests-*.yml` |
| SYS-PERF | Rendimiento básico | Tiempos de respuesta de listados y búsquedas con datos de demo | Observación manual |
| SYS-USAB | Usabilidad | Navegación, mensajes de error, formularios | Inspección heurística |
| SYS-DEPLOY | Despliegue | El sistema arranca correctamente vía Docker Compose | Verificación de arranque |

---

## 5. Casos de prueba de sistema (especificación)

| ID | Caso | Resultado esperado |
|----|------|--------------------|
| SYS-01 | Ciclo de vida completo de un activo | Alta → checkout → aceptación → checkin → baja sin errores; historial coherente |
| SYS-02 | Gestión completa de licencia | Crear → asignar asientos → liberar → reportar disponibilidad |
| SYS-03 | Acceso por rol no autorizado | El sistema bloquea acciones fuera de los permisos del rol |
| SYS-04 | Aislamiento multiempresa (FMCS) | Un usuario solo accede a entidades de su empresa |
| SYS-05 | Compatibilidad multimotor | La suite pasa en SQLite, MySQL y PostgreSQL |
| SYS-06 | Arranque por Docker Compose | El sistema queda accesible en el navegador tras `docker compose up` |
| SYS-07 | Búsqueda y reportes con datos de demo | Resultados correctos en tiempos aceptables |

---

## 6. Criterios de entrada y salida

### Entrada
- [ ] Pruebas de integración del Hito 3 ejecutadas y estables.
- [ ] Entorno de staging desplegado vía Docker Compose.
- [ ] Datos de demostración cargados.

### Salida
- [ ] 100 % de los casos SYS-01 a SYS-07 ejecutados.
- [ ] Cero defectos de severidad alta abiertos.
- [ ] Suite automatizada en verde en los tres motores de base de datos.
- [ ] Defectos registrados en GitHub Issues.

---

## 7. Riesgos

| ID | Riesgo | Mitigación |
|----|--------|------------|
| RS-01 | Diferencias de entorno staging vs. local | Usar el Docker Compose del repositorio como referencia única |
| RS-02 | Datos de demo insuficientes para flujos completos | Poblar con seeders/factories antes de ejecutar |
| RS-03 | Tiempos de respuesta no comparables sin línea base | Definir umbrales orientativos, no absolutos |

---

## 8. Trazabilidad

Los casos SYS-XX se enlazan con los requisitos (RF-XX) y los niveles inferiores (CPF-XX, INT-XX) en la [Matriz de Trazabilidad](Matriz-de-Trazabilidad).

---

*Fin del documento — Plan de Pruebas de Sistema (Hito 3).*
