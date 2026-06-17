# Diseño de Casos de Pruebas Funcionales

> Conforme a **ISO/IEC/IEEE 29119-3** (*Test Design Specification* / *Test Case Specification*). Diseño de pruebas **funcionales, de caja negra y de ejecución manual**, organizado **por requisito funcional** del producto Snipe-IT.

| Campo | Detalle |
|-------|---------|
| **Documento** | Diseño de Casos de Pruebas Funcionales — Snipe-IT |
| **Versión** | **2.1 (amplía el alcance de usuario: + RF-09 Login, RF-10 Usuarios, RF-11 Accesorios)** |
| **Reemplaza a** | v2.0 (8 requisitos) · v1.0 (lista plana CPF-01…CPF-11) |
| **Hito / Sprint** | Hito 2 / Sprint 2 |
| **Tipo de prueba** | Funcional / Caja negra / Manual |
| **Ambiente de ejecución** | QA (despliegue único compartido por el equipo) |
| **Repositorio** | `jhuamaniCond/snipe-it` (PHP 8.2+ / Laravel 12) |
| **Fecha de elaboración** | 2026-06-12 |
| **Estándar** | ISO/IEC/IEEE 29119-3 |

---

## 1. Enunciado del problema y contexto

Snipe-IT es un sistema de gestión de activos de TI cuyo valor depende de la **integridad de las operaciones de inventario**: alta de activos, asignación (checkout) y devolución (checkin) de equipos, control de licencias por asientos, consumo de stock y reglas de borrado referencial. Un fallo funcional en estas operaciones se traduce directamente en **inventario incorrecto** (activos duplicados, asientos sobreasignados, stock negativo o categorías huérfanas).

Este documento **especifica el diseño** de los casos de prueba funcionales de **caja negra** que validan, desde la interfaz de usuario y sin acceso al código, que el comportamiento observable del sistema cumple cada requisito funcional. La validación se realiza **manualmente** en un ambiente de **QA** compartido.

La **ejecución, los veredictos y las evidencias** se consignan en el [Informe de Casos de Pruebas Funcionales](Informe-de-Casos-de-Pruebas-Funcionales). Este documento **no** registra resultados de ejecución.

### 1.1 Trazabilidad del diseño contra el comportamiento real

A diferencia de la v1.0, cada requisito de esta versión se ancló al **comportamiento verificable del producto**, corroborado por inspección de la suite de pruebas automatizadas existente (`tests/Feature/**`). Esa suite **no sustituye** la prueba funcional manual, pero confirma cuál es el resultado esperado correcto y evita diseñar contra supuestos. Las referencias a archivos reales aparecen en cada requisito como *"Comportamiento corroborado en"*.

### 1.2 Distinción frente a las pruebas unitarias

| Aspecto | Pruebas unitarias | Pruebas funcionales (este documento) |
|---------|-------------------|----------------------------------------|
| Caja | Blanca (acceso al código) | **Negra** (sin acceso al código) |
| Ambiente | DEV (desarrollador) | **QA** (equipo) |
| Ejecución | Automatizada (PHPUnit) | **Manual** |
| Objetivo | Lógica interna de métodos | **Cumplimiento de requisitos del usuario** |
| Documento | [Plan de Pruebas Unitarias](Plan-de-Pruebas-Unitarias) | Este documento |

---

## 2. Entorno de pruebas

| Elemento | Definición |
|----------|------------|
| Aplicación bajo prueba (AUT) | Snipe-IT desplegado (Docker Compose del repositorio o instancia QA equivalente) |
| Tipo de acceso | Navegador web, interfaz administrativa (AdminLTE 2 / Bootstrap 3) |
| Perfil de ejecución | Usuario con permisos adecuados por caso (superusuario o permiso granular: `checkoutAssets`, `checkinAssets`, `checkoutConsumables`, `deleteCategories`, etc.) |
| Datos base | Modelos de activo, status labels, categorías, usuarios y ubicaciones poblados mediante seeders/factories antes de la sesión |
| Motor de base de datos | El del despliegue QA (MySQL/PostgreSQL); el comportamiento funcional es independiente del motor |
| Configuración FMCS | `full_multiple_companies_support` desactivado por defecto; se activa solo en los casos que lo requieren (RF-02 variante multiempresa) |
| Registro de evidencia | Capturas de pantalla por caso, adjuntas en el informe; defectos en GitHub Issues (etiqueta `bug`) |

> **Precondición global del entorno:** la instancia de QA debe estar accesible, migrada y con datos de demostración cargados antes de iniciar la sesión de ejecución. Mientras no exista evidencia de ejecución manual, los casos figuran como **pendientes de validación** en el informe.

---

## 3. Matriz de requisitos funcionales

> **Delimitación de alcance.** Estos 11 requisitos cubren los **subsistemas núcleo de cara al usuario** seleccionados como alcance académico en [Hito 1 — Presentación del Producto](Hito-1-Presentacion-del-Producto) §4 (Acceso, Activos, Licencias, Inventario, Usuarios, Accesorios y Checkout). **No** representan la totalidad funcional de Snipe-IT, que comprende ~20 subsistemas (componentes, mantenimientos, importación, reportes, custom fields, etc.). La selección es deliberada y se sostiene a lo largo de los tres hitos.
>
> **Nota de la v2.1:** la v2.0 declaraba "Usuarios" dentro del alcance pero **ningún requisito lo probaba realmente**. Esta versión corrige esa incoherencia añadiendo **RF-10 (gestión de usuarios)**, e incorpora **RF-09 (autenticación)** —la primera acción que ejecuta cualquier usuario— y **RF-11 (checkout/checkin de accesorio)** —para cubrir el patrón de asignación en un segundo módulo de inventario.

| ID Req. | Requisito funcional | Subsistema | Ruta / acción verificada | Técnicas de caja negra seleccionadas |
|---------|---------------------|------------|---------------------------|--------------------------------------|
| **RF-01** | Registrar un activo con etiqueta (*asset tag*) única | Activos | `hardware.store` | PE + AVL + TD |
| **RF-02** | Asignar un activo a un destino (checkout) | Activos / Checkout | `hardware.checkout.store` | TE + PE + TD |
| **RF-03** | Devolver un activo asignado (checkin) | Activos / Checkout | `hardware.checkin.store` | TE + caso negativo |
| **RF-04** | Crear una licencia con un número definido de asientos | Licencias | `licenses.store` | AVL + PE |
| **RF-05** | Asignar un asiento de licencia a un usuario o activo | Licencias | `licenses.checkout` | TE + PE + AVL |
| **RF-06** | Descontar stock de un consumible al asignarlo | Inventario | `consumables.checkout.store` | AVL + PE |
| **RF-07** | Impedir la eliminación de una categoría con elementos asociados | Categorías | `categories.destroy` | TD |
| **RF-08** | Reflejar la disponibilidad del activo según su *status label* | Activos | `availableForCheckout()` / `getStatuslabelType()` | TD |
| **RF-09** | Autenticar a un usuario (login / logout) | Acceso | `login` (POST) / `logout` | PE + TE + caso negativo |
| **RF-10** | Registrar y editar un usuario | Usuarios | `users.store` / `users.update` | PE + AVL |
| **RF-11** | Asignar y devolver un accesorio (checkout/checkin) | Accesorios / Checkout | `accessories.checkout.store` / `accessories.checkin.store` | TE + PE + AVL |

**Leyenda de técnicas:** PE = Partición de equivalencia · AVL = Análisis de valores límite · TD = Tabla de decisión · TE = Transición de estados.

> **Criterio de selección de técnicas (no se fuerza una única técnica):** la combinación se eligió según la naturaleza de cada requisito. Los requisitos con **ciclo de vida** (RF-02, RF-03, RF-05) priorizan transición de estados; los de **rangos numéricos** (RF-04, RF-06) priorizan valores límite; los gobernados por **reglas combinadas** (RF-01 serial, RF-07 permiso×contenido, RF-08 banderas de estado) priorizan tablas de decisión. La justificación detallada se incluye en cada requisito (campo *Técnicas de prueba aplicadas*).

---

## 4. Catálogo de técnicas de caja negra

| Técnica | Abrev. | Cuándo se aplica en este diseño |
|---------|--------|----------------------------------|
| Partición de equivalencia | **PE** | Clases válidas/inválidas de entradas de formulario (tag, destino de checkout, presencia de stock) |
| Análisis de valores límite | **AVL** | Bordes numéricos: longitud de tag (1/255), asientos de licencia (1/10000/10001), stock de consumible (2→1→0) |
| Tabla de decisión | **TD** | Reglas con varias condiciones combinadas: serial requerido, eliminación de categoría, disponibilidad por banderas de estado |
| Transición de estados | **TE** | Ciclo de vida del activo y del asiento de licencia (disponible ⇄ asignado) |

---

## 5. Diseño de pruebas funcionales por requisito

> Cada caso especifica: **ID, funcionalidad, descripción, requisito asociado, precondiciones, datos de entrada, pasos de ejecución, técnicas aplicadas, prioridad, subcasos/catálogo y resultado esperado.** El campo **Evidencia** queda como *pendiente de validación* hasta su ejecución en QA (ver informe). Los IDs CPF-01…CPF-11 se conservan respecto a la v1.0 para no romper la [Matriz de Trazabilidad](Matriz-de-Trazabilidad); las variantes se modelan como **subcasos** (CPF-XX.n).

---

### RF-01 — Registrar un activo con asset tag único

**Contexto del requisito:** el alta de un activo exige una etiqueta (`asset_tag`) **obligatoria y única** entre activos no eliminados; según el modelo del activo, puede exigirse también el número de serie. Regla de validación verificada: `asset_tag` → `required | min:1 | max:255 | unique_undeleted:assets,asset_tag | not_array`.

**Comportamiento corroborado en:** `tests/Feature/Assets/Ui/StoreAssetsTest.php`.

**Técnicas de prueba aplicadas y justificación:**
- **PE** para clasificar la etiqueta en válida / vacía / duplicada.
- **AVL** sobre la longitud del campo (límite inferior `min:1`).
- **TD** para la regla condicional *serial requerido × serial provisto*, que combina dos condiciones y produce dos acciones distintas.

#### CPF-01 — Alta de activo con asset tag único válido
| Campo | Detalle |
|-------|---------|
| **Funcionalidad** | Registro de activo |
| **Descripción** | Crear un activo nuevo con un asset tag inexistente, modelo y estado válidos |
| **Precondiciones** | Existe al menos un modelo de activo y un *status label*; usuario con permiso de creación |
| **Datos de entrada** | `model_id` válido; `status_id` válido; `asset_tags[1] = "A-1001"` (no usado antes) |
| **Pasos** | 1) Assets → *Create New*. 2) Seleccionar modelo y estado. 3) Ingresar tag `A-1001`. 4) *Save* |
| **Prioridad** | Alta |
| **Resultado esperado** | El activo se crea, redirige con mensaje de éxito y aparece en el listado con `asset_tag = A-1001` |
| **Evidencia** | ⟦PENDIENTE-QA⟧ |

#### CPF-02 — Rechazo de asset tag duplicado
| Campo | Detalle |
|-------|---------|
| **Funcionalidad** | Validación de unicidad |
| **Descripción** | Intentar crear un activo con un asset tag ya existente |
| **Precondiciones** | Existe un activo activo con tag `A-1001` (CPF-01) |
| **Datos de entrada** | `asset_tags[1] = "A-1001"` (repetido) |
| **Pasos** | Repetir el alta con el mismo tag |
| **Prioridad** | Alta |
| **Resultado esperado** | El sistema **rechaza** el registro y muestra error de unicidad; no se crea un segundo activo |
| **Evidencia** | ⟦PENDIENTE-QA⟧ |

#### Subcasos derivados de RF-01
| Subcaso | Técnica | Datos de entrada | Resultado esperado |
|---------|---------|------------------|--------------------|
| **CPF-01.1** Serial requerido y provisto | TD (R1) | Modelo con `require_serial=1`; `serials[1]="ABC123"`; `asset_tags[1]="1234"` | Activo creado con serie y tag |
| **CPF-01.2** Serial requerido y ausente | TD (R2) | Modelo con `require_serial=1`; `serials=[]`; `asset_tags[1]="1234"` | **Rechazo** con error en `serials.1`; no se crea el activo |
| **CPF-02.1** Tag vacío (AVL inferior) | AVL/PE | `asset_tags[1]=""` | **Rechazo**: el tag es obligatorio (`min:1`) |
| **CPF-01.3** Sin permiso de creación | PE inválida | Usuario sin permiso | Acceso **prohibido** (403) al formulario/acción de alta |

**Tabla de decisión — Serial requerido (CPF-01.1 / CPF-01.2):**

| Condición | R1 | R2 |
|-----------|----|----|
| ¿El modelo exige serial (`require_serial=1`)? | Sí | Sí |
| ¿Se proporcionó el serial? | Sí | No |
| **Acción esperada** | Crear activo | Rechazar (error `serials.1`) |

---

### RF-02 — Asignar un activo a un destino (checkout)

**Contexto del requisito:** un activo **disponible** puede asignarse a un **usuario, otro activo o una ubicación**. El sistema rechaza el checkout si el activo no está disponible, si se intenta asignar a sí mismo, si faltan datos obligatorios, si el usuario no tiene permiso, o —con FMCS activo— si el destino pertenece a otra empresa. Disponibilidad definida por `availableForCheckout()` (no asignado, no eliminado, estado no archivado y `deployable=1`).

**Comportamiento corroborado en:** `tests/Feature/Checkouts/Ui/AssetCheckoutTest.php`.

**Técnicas de prueba aplicadas y justificación:**
- **TE** porque el checkout es una **transición de estado** del activo (Disponible → Asignado).
- **PE** para particionar el destino (usuario/activo/ubicación) y las entradas válidas/inválidas.
- **TD** para la regla de aislamiento multiempresa (FMCS activo × empresas distintas).

#### CPF-03 — Checkout de activo disponible a un usuario
| Campo | Detalle |
|-------|---------|
| **Funcionalidad** | Asignación de activo |
| **Descripción** | Asignar un activo disponible a un usuario activo |
| **Precondiciones** | Activo disponible (estado *deployable*, sin asignar); usuario destino activo; ejecutor con permiso `checkoutAssets` |
| **Datos de entrada** | `checkout_to_type = user`; `assigned_user = {id}`; `status_id` *deployable*; `checkout_at`, `expected_checkin` válidos |
| **Pasos** | 1) Activo → *Checkout*. 2) Tipo "User". 3) Elegir usuario. 4) Fechas y nota. 5) *Checkout* |
| **Técnicas** | TE |
| **Prioridad** | Alta |
| **Resultado esperado** | El activo queda **asignado** al usuario; su ubicación pasa a la del usuario; se registra `last_checkout`, `expected_checkin` y un asiento en el historial (acción *checkout*) |
| **Evidencia** | ⟦PENDIENTE-QA⟧ |

#### Subcasos derivados de RF-02
| Subcaso | Técnica | Condición de entrada | Resultado esperado |
|---------|---------|----------------------|--------------------|
| **CPF-03.1** Checkout a otro activo | PE/TE | `checkout_to_type=asset`; activo destino válido | Activo asignado al activo destino; ubicación heredada |
| **CPF-03.2** Checkout a ubicación | PE/TE | `checkout_to_type=location`; ubicación válida | Activo asignado a la ubicación |
| **CPF-03.3** Activo ya asignado (no disponible) | PE inválida/TE | Activo ya checked-out | **Rechazo** con mensaje de error; redirección a índice; sin cambio de asignación |
| **CPF-03.4** Activo asignado a sí mismo | PE inválida | `checkout_to_type=asset`, `assigned_asset = id del propio activo` | **Rechazo** con error |
| **CPF-03.5** Datos obligatorios ausentes | PE inválida | Sin destino, `status_id` inexistente, fechas inválidas | **Errores de validación** en destino, `status_id`, `checkout_to_type`, fechas |
| **CPF-03.6** Sin permiso | PE inválida | Usuario sin `checkoutAssets` | Acceso **prohibido** (403) |
| **CPF-03.7** Checkout cruzado entre empresas (FMCS) | TD | FMCS activo; activo y usuario de empresas distintas | **Rechazo**: la asignación no se ejecuta |

**Diagrama de transición de estados — Activo (RF-02 / RF-03 / RF-08):**

```
                 checkout (destino válido)
   [Disponible] ───────────────────────────▶ [Asignado]
        ▲                                          │
        │            checkin                       │
        └──────────────────────────────────────────┘
        │
        └─(status no deployable / archivado)─▶ [No disponible para checkout]
```
Cobertura: CPF-03 (transición directa), CPF-04 (transición inversa), CPF-05 (rama no disponible).

---

### RF-03 — Devolver un activo asignado (checkin)

**Contexto del requisito:** un activo **asignado** puede devolverse, lo que libera la asignación, limpia `expected_checkin`, fija `last_checkin`, restablece la ubicación por defecto (RTD), libera los asientos de licencia asociados y elimina las aceptaciones pendientes. No puede hacerse checkin de un activo que **no** está asignado.

**Comportamiento corroborado en:** `tests/Feature/Checkins/Ui/AssetCheckinTest.php`.

**Técnicas de prueba aplicadas y justificación:**
- **TE** porque el checkin es la **transición inversa** (Asignado → Disponible).
- **Caso negativo** para la precondición incumplida (activo no asignado).

#### CPF-04 — Checkin de activo asignado
| Campo | Detalle |
|-------|---------|
| **Funcionalidad** | Devolución de activo |
| **Descripción** | Registrar la devolución de un activo previamente asignado |
| **Precondiciones** | Activo asignado a un usuario (CPF-03); ejecutor con permiso `checkinAssets` |
| **Datos de entrada** | `status_id` válido; `location_id` (opcional); `note` (opcional) |
| **Pasos** | 1) Activo → *Checkin*. 2) Confirmar estado/ubicación. 3) *Checkin* |
| **Técnicas** | TE |
| **Prioridad** | Alta |
| **Resultado esperado** | El activo queda **sin asignar** (`assigned_to` nulo); `expected_checkin` nulo; `last_checkin` registrado; ubicación restablecida a la RTD; asientos de licencia liberados; historial actualizado (*checkin from*) |
| **Evidencia** | ⟦PENDIENTE-QA⟧ |

#### Subcasos derivados de RF-03
| Subcaso | Técnica | Condición de entrada | Resultado esperado |
|---------|---------|----------------------|--------------------|
| **CPF-04.1** Checkin de activo no asignado | Caso negativo | Activo disponible (sin asignar) | **Rechazo** con mensaje de error; redirección a índice |
| **CPF-04.2** Sin permiso | PE inválida | Usuario sin `checkinAssets` | Acceso **prohibido** (403) |
| **CPF-04.3** Liberación de asientos de licencia | TE | Activo con asiento de licencia asignado | Tras el checkin, el asiento queda **libre** (`assigned_to` nulo) |

---

### RF-04 — Crear una licencia con un número definido de asientos

**Contexto del requisito:** al crear una licencia con `seats = N`, el sistema genera **N asientos** y registra las acciones *create* y *add seats*. Regla verificada: `seats → required | min:1 | integer | limit_change:10000`. La pseudo-regla `limit_change` (ver `prepareLimitChangeRule()`) limita la **magnitud del cambio** de asientos a ±10000 respecto al conteo actual; para una licencia **nueva** (0 asientos) esto equivale a `between:1,10000`, es decir, un **tope superior de 10000** asientos. La fecha de compra (`purchase_date`) es obligatoria.

**Comportamiento corroborado en:** `tests/Feature/Licenses/Ui/CreateLicenseTest.php`.

**Técnicas de prueba aplicadas y justificación:**
- **AVL** porque el número de asientos tiene un **rango con borde superior** explícito (1 … 10000).
- **PE** para la presencia/ausencia de campos obligatorios (`purchase_date`).

#### CPF-06 — Crear licencia con N asientos
| Campo | Detalle |
|-------|---------|
| **Funcionalidad** | Alta de licencia |
| **Descripción** | Crear una licencia con un número válido de asientos |
| **Precondiciones** | Existe una categoría de tipo licencia; usuario con permiso |
| **Datos de entrada** | `name`; `seats = 10`; `category_id` (licencia); `purchase_date` válida |
| **Pasos** | 1) Licenses → *Create*. 2) Completar formulario. 3) *Save* |
| **Técnicas** | AVL |
| **Prioridad** | Alta |
| **Resultado esperado** | La licencia se crea con **10 asientos**; se registran las acciones *create* y *add seats* |
| **Evidencia** | ⟦PENDIENTE-QA⟧ |

#### Subcasos derivados de RF-04 — Análisis de valores límite
| Subcaso | Clase / borde | `seats` | Resultado esperado |
|---------|---------------|---------|--------------------|
| **CPF-06.1** Borde inferior válido | mínimo | `1` | Licencia creada con 1 asiento |
| **CPF-06** Valor nominal | dentro de rango | `10` | Licencia creada con 10 asientos |
| **CPF-06.2** Borde superior válido | máximo permitido | `10000` | Licencia creada con 10000 asientos |
| **CPF-06.3** Sobre el borde superior | inválido | `100000` | **Rechazo**: no se crea la licencia (excede `limit_change:10000`) |
| **CPF-06.4** Sin fecha de compra | PE inválida | `10`, sin `purchase_date` | **Rechazo** con error de validación en `purchase_date`; no se crea la licencia |
| **CPF-06.5** Asientos = 0 | borde por debajo del mínimo | `0` | **Rechazo** (`min:1`) |

---

### RF-05 — Asignar un asiento de licencia a un usuario o activo

**Contexto del requisito:** un asiento **libre** de una licencia puede asignarse (checkout) a un usuario o a un activo, lo que reduce la disponibilidad y registra la acción *checkout*. Cuando no quedan asientos libres, el sistema no debe permitir nuevas asignaciones.

**Comportamiento corroborado en:** `tests/Feature/Checkouts/Ui/LicenseCheckoutTest.php` (asignación a usuario y a activo verificada). El **agotamiento de asientos** (CPF-08) **no cuenta con prueba automatizada** en el repositorio: se diseña como verificación manual y queda **pendiente de validación**.

**Técnicas de prueba aplicadas y justificación:**
- **TE** porque el asiento transita de Libre → Ocupado.
- **PE** para el destino (usuario/activo).
- **AVL** para el borde superior de ocupación (último asiento / exceso).

#### CPF-07 — Asignar un asiento de licencia
| Campo | Detalle |
|-------|---------|
| **Funcionalidad** | Asignación de asiento de licencia |
| **Descripción** | Asignar un asiento libre a un usuario |
| **Precondiciones** | Licencia con asientos libres (CPF-06); usuario con permiso |
| **Datos de entrada** | `checkout_to_type = user`; `assigned_to = {id}` |
| **Pasos** | 1) Licencia → *Checkout*. 2) Tipo "User". 3) Elegir usuario. 4) *Checkout* |
| **Técnicas** | TE / PE |
| **Prioridad** | Alta |
| **Resultado esperado** | Los asientos disponibles disminuyen en 1; el usuario figura como asignado; se registra la acción *checkout* |
| **Evidencia** | ⟦PENDIENTE-QA⟧ |

#### Subcasos derivados de RF-05
| Subcaso | Técnica | Condición de entrada | Resultado esperado | Cobertura automatizada |
|---------|---------|----------------------|--------------------|------------------------|
| **CPF-07.1** Asignar asiento a un activo | PE/TE | `checkout_to_type=asset`; `asset_id` válido | Asiento asignado al activo; acción *checkout* | Sí (corroborado) |
| **CPF-08** Asignar el último asiento | AVL (borde) | Licencia con 1 asiento libre | El asiento se asigna; disponibilidad llega a 0 | Pendiente (manual) |
| **CPF-08.1** Exceder asientos | AVL (sobre borde) | Licencia con 0 asientos libres | **Rechazo / sin asientos ofrecidos** para nueva asignación | Pendiente (manual) |

> **Constancia de divergencia con la v1.0:** la v1.0 presentaba CPF-08 como caso de ejecución directa. Tras la auditoría, no existe prueba automatizada del agotamiento de asientos en la interfaz; por integridad se reclasifica como **verificación manual pendiente**, no como comportamiento ya cubierto.

---

### RF-06 — Descontar stock de un consumible al asignarlo

**Contexto del requisito:** al asignar un consumible a un usuario, el **stock disponible disminuye**; cuando no quedan unidades, el sistema impide la asignación. El checkout exige destino (`assigned_to`) y permiso (`checkoutConsumables`); registra la acción *checkout* con la cantidad entregada y notifica al usuario.

**Comportamiento corroborado en:** `tests/Feature/Checkouts/Ui/ConsumableCheckoutTest.php`.

**Técnicas de prueba aplicadas y justificación:**
- **AVL** porque el stock recorre un **rango decreciente con borde inferior** (2 → 1 → 0) en el que cambia el comportamiento.
- **PE** para la clase "con stock" vs. "sin stock" y para la ausencia de destino.

#### CPF-09 — Descuento de stock de consumible
| Campo | Detalle |
|-------|---------|
| **Funcionalidad** | Asignación de consumible |
| **Descripción** | Entregar un consumible y verificar el descuento de stock |
| **Precondiciones** | Consumible con stock disponible (`qty` ≥ 1); usuario con permiso `checkoutConsumables` |
| **Datos de entrada** | `assigned_to = {id usuario}`; `checkout_qty` según subcaso |
| **Pasos** | 1) Consumible → *Checkout*. 2) Elegir usuario y cantidad. 3) *Checkout* |
| **Técnicas** | AVL |
| **Prioridad** | Media-Alta |
| **Resultado esperado** | El stock disponible disminuye en la cantidad entregada; el consumible aparece asignado al usuario; se registra la acción *checkout* con la cantidad |
| **Evidencia** | ⟦PENDIENTE-QA⟧ |

#### Subcasos derivados de RF-06 — Análisis de valores límite sobre el stock
| Subcaso | Borde | Estado inicial | Acción | Resultado esperado |
|---------|-------|----------------|--------|--------------------|
| **CPF-09.1** Descuento nominal | dentro de rango | `qty = 2` | Checkout de 1 unidad | Stock restante = 1 |
| **CPF-09.2** Último ítem | borde inferior | `qty = 1` | Checkout de 1 unidad | Stock restante = 0 |
| **CPF-09.3** Sin stock | bajo el borde | `qty = 0` (sin unidades) | Intento de checkout | **Rechazo**: no se permite la asignación |
| **CPF-09.4** Destino ausente | PE inválida | `qty ≥ 1` | Checkout sin `assigned_to` | **Rechazo** con error de validación |
| **CPF-09.5** Sin permiso | PE inválida | cualquiera | Usuario sin permiso | Acceso **prohibido** (403) |

---

### RF-07 — Impedir la eliminación de una categoría con elementos asociados

**Contexto del requisito:** una categoría **solo** puede eliminarse si no tiene elementos asociados (modelos de activo, activos, etc.) y el usuario tiene permiso `deleteCategories`. Si tiene elementos asociados, la eliminación se **rechaza** (la categoría no se marca como borrada). Es la regla de integridad referencial más sensible del subsistema de catálogos.

**Comportamiento corroborado en:** `tests/Feature/Categories/Ui/DeleteCategoriesTest.php`.

**Técnicas de prueba aplicadas y justificación:**
- **TD** porque la acción depende de la **combinación** de dos condiciones (¿tiene elementos? × ¿tiene permiso?), produciendo acciones distintas. Es el caso natural para una tabla de decisión.

#### CPF-10 — Impedir eliminación de categoría con elementos
| Campo | Detalle |
|-------|---------|
| **Funcionalidad** | Eliminación de categoría (integridad referencial) |
| **Descripción** | Intentar eliminar una categoría con elementos asociados |
| **Precondiciones** | Categoría con al menos un modelo o activo asociado; usuario con permiso `deleteCategories` |
| **Datos de entrada** | Identificador de la categoría con elementos |
| **Pasos** | Categorías → seleccionar categoría con elementos → *Delete* |
| **Técnicas** | TD (R1) |
| **Prioridad** | Alta |
| **Resultado esperado** | El sistema **impide** la eliminación, informa que la categoría no está vacía y la categoría **no** se elimina |
| **Evidencia** | ⟦PENDIENTE-QA⟧ |

#### CPF-11 — Eliminar categoría vacía
| Campo | Detalle |
|-------|---------|
| **Funcionalidad** | Eliminación de categoría |
| **Descripción** | Eliminar una categoría sin elementos asociados |
| **Precondiciones** | Categoría sin elementos; usuario con permiso `deleteCategories` |
| **Datos de entrada** | Identificador de la categoría vacía |
| **Pasos** | Categorías → seleccionar categoría vacía → *Delete* |
| **Técnicas** | TD (R3) |
| **Prioridad** | Media |
| **Resultado esperado** | La categoría se **elimina** (borrado lógico) y se muestra mensaje de éxito |
| **Evidencia** | ⟦PENDIENTE-QA⟧ |

#### Subcaso derivado de RF-07
| Subcaso | Técnica | Condición | Resultado esperado |
|---------|---------|-----------|--------------------|
| **CPF-10.1** Eliminación sin permiso | TD (R2) | Categoría vacía o no; usuario sin `deleteCategories` | Acceso **prohibido** (403); no se elimina |

**Tabla de decisión — Eliminación de categoría (RF-07):**

| Condición | R1 | R2 | R3 |
|-----------|----|----|----|
| ¿Tiene elementos asociados? | Sí | — | No |
| ¿Usuario con permiso de borrado? | Sí | No | Sí |
| **Acción esperada** | Rechazar (no vacía) | Prohibir (403) | Eliminar |
| **Caso que la cubre** | CPF-10 | CPF-10.1 | CPF-11 |

---

### RF-08 — Reflejar la disponibilidad del activo según su status label

**Contexto del requisito:** la disponibilidad de un activo para checkout depende de su *status label*. Solo un activo con estado **desplegable** (`deployable = 1`) y **no archivado** (`archived = 0`), sin asignación previa, es elegible. La clasificación del tipo de estado la resuelve `getStatuslabelType()` en cuatro categorías: *pending*, *archived*, *undeployable*, *deployable*.

**Comportamiento corroborado en:** `app/Models/Asset.php::availableForCheckout()` y `app/Models/Statuslabel.php::getStatuslabelType()`; uso de estados desplegables/no desplegables en `tests/Feature/Checkins/Ui/AssetCheckinTest.php` y `tests/Feature/Checkouts/Ui/AssetCheckoutTest.php`. **Nota de honestidad:** no existe una prueba automatizada de UI que bloquee explícitamente el checkout de un activo *archived/undeployable*; CPF-05 se diseña como verificación manual basada en la regla `availableForCheckout()` y queda **pendiente de validación**.

**Técnicas de prueba aplicadas y justificación:**
- **TD** porque la disponibilidad surge de la **combinación de banderas** (`pending`, `archived`, `deployable`); cada combinación define un tipo de estado y una acción de elegibilidad.

#### CPF-05 — Checkout sobre activo no desplegable
| Campo | Detalle |
|-------|---------|
| **Funcionalidad** | Disponibilidad por status label |
| **Descripción** | Intentar asignar un activo cuyo estado no es desplegable |
| **Precondiciones** | Activo con *status label* no desplegable (p. ej. *Archived* o estado con `deployable=0`) |
| **Datos de entrada** | Activo en estado no desplegable; destino de usuario válido |
| **Pasos** | Intentar checkout del activo |
| **Técnicas** | TD |
| **Prioridad** | Media-Alta |
| **Resultado esperado** | El sistema **impide** la asignación o **no ofrece** el activo como disponible (`availableForCheckout()` = falso) |
| **Evidencia** | ⟦PENDIENTE-QA⟧ |

**Tabla de decisión — Tipo de estado y elegibilidad para checkout (RF-08):**

| Condición | Deployable | Pending | Archived | Undeployable |
|-----------|:---------:|:-------:|:--------:|:------------:|
| `pending` | 0 | 1 | 0 | 0 |
| `archived` | 0 | 0 | 1 | 0 |
| `deployable` | 1 | 0 | 0 | 0 |
| **¿Elegible para checkout?** (`availableForCheckout`) | **Sí** | No | No | No |
| **Caso que lo cubre** | CPF-03 | CPF-05 (variante *pending*) | CPF-05 (variante *archived*) | CPF-05 (variante *undeployable*) |

> **Subcasos de CPF-05** (una variante por estado no desplegable): **CPF-05.1** *pending*, **CPF-05.2** *archived*, **CPF-05.3** *undeployable*. Las tres comparten el resultado esperado: el activo no es elegible para checkout.

---

### RF-09 — Autenticar a un usuario (login / logout)

**Contexto del requisito:** el acceso a Snipe-IT exige autenticación. El usuario inicia sesión con **usuario + contraseña**; con credenciales válidas y cuenta activada queda autenticado y se registra el evento; con credenciales inválidas se rechaza y se registra el intento fallido. Existe **límite de intentos** (throttling) configurable que bloquea tras N fallos. El *logout* cierra la sesión.

**Comportamiento corroborado en:** `tests/Feature/Authentication/LoginTest.php` (`test_logs_successful_login`, `test_logs_failed_login_attempt`, `test_login_throttle_config_is_respected`).

**Técnicas de prueba aplicadas y justificación:**
- **TE** porque la autenticación es una **transición de estado de sesión** (Anónimo → Autenticado → Anónimo tras logout).
- **PE** para particionar credenciales válidas / inválidas.
- **Caso negativo** para el bloqueo por exceso de intentos.

#### CPF-12 — Inicio de sesión con credenciales válidas
| Campo | Detalle |
|-------|---------|
| **Funcionalidad** | Autenticación |
| **Descripción** | Iniciar sesión con un usuario activo y contraseña correcta |
| **Precondiciones** | Existe un usuario activado (`activated=1`) con contraseña conocida |
| **Datos de entrada** | `username` válido; `password` correcta |
| **Pasos** | 1) Ir a *Login*. 2) Ingresar usuario y contraseña. 3) *Login* |
| **Técnicas** | TE / PE |
| **Prioridad** | Alta |
| **Resultado esperado** | El usuario queda **autenticado**, es redirigido al *dashboard* y se registra el inicio de sesión exitoso |
| **Evidencia** | ⟦PENDIENTE-QA⟧ |

#### Subcasos derivados de RF-09
| Subcaso | Técnica | Condición de entrada | Resultado esperado |
|---------|---------|----------------------|--------------------|
| **CPF-12.1** Credenciales inválidas | PE inválida / caso negativo | `password` incorrecta | **Rechazo**: no autentica, muestra error y registra intento fallido |
| **CPF-12.2** Bloqueo por throttling | Caso negativo (borde) | Superar el nº máximo de intentos fallidos | El sistema **bloquea** temporalmente nuevos intentos |
| **CPF-12.3** Cierre de sesión (logout) | TE | Usuario autenticado pulsa *Logout* | La sesión se cierra; el acceso a páginas internas vuelve a exigir login |
| **CPF-12.4** Acceso sin autenticar | PE inválida | Solicitar una página interna sin sesión | **Redirección** a la pantalla de login |

---

### RF-10 — Registrar y editar un usuario

**Contexto del requisito:** un administrador puede crear y editar usuarios. El alta exige **nombre** (`first_name → required|min:1`) y **usuario** (`username → required` salvo importación LDAP); la **contraseña** debe cumplir las reglas de complejidad y venir **confirmada** (`confirmed`). Crear/editar exige permiso; un usuario sin permiso recibe 403. Un no-administrador no puede otorgar permisos de admin/superusuario ni asignar grupos.

**Comportamiento corroborado en:** `tests/Feature/Users/Ui/CreateUserTest.php` (`test_can_create_user`, `test_permission_required_to_create_user`, `test_non_admin_cannot_grant_admin_or_superuser_permissions_when_creating_user_via_ui`) y `tests/Feature/Users/Ui/UpdateUserTest.php`.

**Técnicas de prueba aplicadas y justificación:**
- **PE** para clases válidas/inválidas de los campos obligatorios y del permiso.
- **AVL** sobre la longitud mínima del nombre/usuario (`min:1`).

#### CPF-13 — Alta de usuario válido
| Campo | Detalle |
|-------|---------|
| **Funcionalidad** | Registro de usuario |
| **Descripción** | Crear un usuario con nombre, usuario y contraseña válidos |
| **Precondiciones** | Ejecutor con permiso de creación de usuarios |
| **Datos de entrada** | `first_name="Jane"`; `username="jdoe"`; `password` y `password_confirmation` que cumplen la complejidad |
| **Pasos** | 1) People → *Create New*. 2) Completar nombre, usuario y contraseña. 3) *Save* |
| **Técnicas** | PE |
| **Prioridad** | Alta |
| **Resultado esperado** | El usuario se crea, redirige con mensaje de éxito y aparece en el listado |
| **Evidencia** | ⟦PENDIENTE-QA⟧ |

#### Subcasos derivados de RF-10
| Subcaso | Técnica | Condición de entrada | Resultado esperado |
|---------|---------|----------------------|--------------------|
| **CPF-13.1** Nombre ausente | PE/AVL inválida | `first_name=""` | **Rechazo** con error en `first_name` (`required\|min:1`) |
| **CPF-13.2** Contraseña sin confirmar | PE inválida | `password` sin `password_confirmation` coincidente | **Rechazo** con error de confirmación |
| **CPF-13.3** Sin permiso de creación | PE inválida | Usuario sin permiso | Acceso **prohibido** (403) |
| **CPF-13.4** Edición de usuario | PE válida | Modificar `first_name`/`jobtitle` de un usuario existente | Cambios guardados; mensaje de éxito |
| **CPF-13.5** No-admin intenta otorgar superusuario | PE inválida (escalada) | Usuario no-admin marca `superuser=1` al crear | El permiso de superusuario **no** se concede |

---

### RF-11 — Asignar y devolver un accesorio (checkout / checkin)

**Contexto del requisito:** un accesorio con unidades disponibles puede **asignarse** (checkout) a un usuario, ubicación u otro activo, descontando la cantidad entregada; cuando no quedan unidades disponibles, el sistema **impide** la asignación. El **checkin** devuelve la unidad y la vuelve a poner disponible. Ambas acciones exigen permiso (`checkoutAccessories` / `checkinAccessories`) y registran la acción en el historial.

**Comportamiento corroborado en:** `tests/Feature/Checkouts/Ui/AccessoryCheckoutTest.php` (`test_accessory_can_be_checked_out_with_quantity`, `test_accessory_must_have_available_items_for_checkout_when_checking_out`, `test_checking_out_accessory_requires_correct_permission`, `test_validation_when_checking_out_accessory`) y `tests/Feature/Checkins/Ui/AccessoryCheckinTest.php` (`test_accessory_can_be_checked_in`, `test_checking_in_accessory_requires_correct_permission`).

**Técnicas de prueba aplicadas y justificación:**
- **TE** porque el accesorio transita entre disponible y asignado (checkout ⇄ checkin).
- **PE** para el destino (usuario/ubicación/activo) y la validación de entradas.
- **AVL** para el borde de disponibilidad (última unidad / sin unidades).

#### CPF-14 — Checkout de accesorio a un usuario
| Campo | Detalle |
|-------|---------|
| **Funcionalidad** | Asignación de accesorio |
| **Descripción** | Asignar una unidad de un accesorio con stock a un usuario |
| **Precondiciones** | Accesorio con unidades disponibles; ejecutor con permiso `checkoutAccessories` |
| **Datos de entrada** | `checkout_to_type=user`; `assigned_to={id}`; `checkout_qty=1` |
| **Pasos** | 1) Accessory → *Checkout*. 2) Elegir usuario y cantidad. 3) *Checkout* |
| **Técnicas** | TE / PE |
| **Prioridad** | Alta |
| **Resultado esperado** | El accesorio queda asignado; las unidades disponibles disminuyen en la cantidad entregada; se registra la acción *checkout* y se notifica al usuario |
| **Evidencia** | ⟦PENDIENTE-QA⟧ |

#### CPF-15 — Checkin de accesorio
| Campo | Detalle |
|-------|---------|
| **Funcionalidad** | Devolución de accesorio |
| **Descripción** | Registrar la devolución de una unidad de accesorio asignada |
| **Precondiciones** | Accesorio asignado a un usuario (CPF-14); ejecutor con permiso `checkinAccessories` |
| **Datos de entrada** | Identificador de la asignación de accesorio |
| **Pasos** | 1) Accessory → *Checkin*. 2) Confirmar. 3) *Checkin* |
| **Técnicas** | TE |
| **Prioridad** | Alta |
| **Resultado esperado** | La unidad vuelve a estar **disponible**; el accesorio deja de figurar asignado a ese usuario; se registra la acción *checkin* |
| **Evidencia** | ⟦PENDIENTE-QA⟧ |

#### Subcasos derivados de RF-11
| Subcaso | Técnica | Condición de entrada | Resultado esperado |
|---------|---------|----------------------|--------------------|
| **CPF-14.1** Checkout a ubicación | PE/TE | `checkout_to_type=location`; ubicación válida | Accesorio asignado a la ubicación |
| **CPF-14.2** Checkout a activo | PE/TE | `checkout_to_type=asset`; activo válido | Accesorio asignado al activo |
| **CPF-14.3** Sin unidades disponibles | AVL (bajo borde) | Accesorio con 0 unidades disponibles | **Rechazo**: no se permite la asignación |
| **CPF-14.4** Destino ausente | PE inválida | Checkout sin `assigned_to` | **Rechazo** con error de validación |
| **CPF-14.5** Sin permiso | PE inválida | Usuario sin `checkoutAccessories` | Acceso **prohibido** (403) |
| **CPF-15.1** Checkin sin permiso | PE inválida | Usuario sin `checkinAccessories` | Acceso **prohibido** (403) |

---

## 6. Catálogo consolidado de casos diseñados

| Caso | Requisito | Técnica(s) | Prioridad | Tipo | Cobertura automatizada de referencia |
|------|-----------|-----------|-----------|------|--------------------------------------|
| CPF-01 (+.1/.2/.3) | RF-01 | PE/AVL/TD | Alta | Positivo + negativos | `StoreAssetsTest` |
| CPF-02 (+.1) | RF-01 | PE inválida/AVL | Alta | Negativo | (regla `unique_undeleted`) |
| CPF-03 (+.1….7) | RF-02 | TE/PE/TD | Alta | Positivo + negativos | `AssetCheckoutTest` |
| CPF-04 (+.1….3) | RF-03 | TE | Alta | Positivo + negativo | `AssetCheckinTest` |
| CPF-05 (+.1….3) | RF-08 | TD | Media-Alta | Negativo | (`availableForCheckout`) — manual |
| CPF-06 (+.1….5) | RF-04 | AVL/PE | Alta | Positivo + límites | `CreateLicenseTest` |
| CPF-07 (+.1) | RF-05 | TE/PE | Alta | Positivo | `LicenseCheckoutTest` |
| CPF-08 (+.1) | RF-05 | AVL | Media | Negativo (borde) | Pendiente (manual) |
| CPF-09 (+.1….5) | RF-06 | AVL/PE | Media-Alta | Positivo + límites | `ConsumableCheckoutTest` |
| CPF-10 (+.1) | RF-07 | TD | Alta | Negativo | `DeleteCategoriesTest` |
| CPF-11 | RF-07 | TD | Media | Positivo | `DeleteCategoriesTest` |
| CPF-12 (+.1….4) | RF-09 | TE/PE | Alta | Positivo + negativos | `LoginTest` |
| CPF-13 (+.1….5) | RF-10 | PE/AVL | Alta | Positivo + negativos | `Users/Ui/CreateUserTest`, `UpdateUserTest` |
| CPF-14 (+.1….5) | RF-11 | TE/PE/AVL | Alta | Positivo + negativos | `Checkouts/Ui/AccessoryCheckoutTest` |
| CPF-15 (+.1) | RF-11 | TE | Alta | Positivo + negativo | `Checkins/Ui/AccessoryCheckinTest` |

---

## 7. Trazabilidad

Cada caso `CPF-XX` (y sus subcasos `CPF-XX.n`) se vincula a su requisito `RF-XX` y a su evidencia de ejecución en la [Matriz de Trazabilidad](Matriz-de-Trazabilidad) y en el [Informe de Casos de Pruebas Funcionales](Informe-de-Casos-de-Pruebas-Funcionales). La cobertura directa (requisito → caso) e inversa (caso → requisito) queda asegurada: los 11 requisitos RF-01…RF-11 tienen al menos un caso funcional diseñado.

---

*Fin del documento — Diseño de Casos de Pruebas Funcionales v2.1.*
