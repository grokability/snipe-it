# Informe de Casos de Pruebas Funcionales

> Conforme a **ISO/IEC/IEEE 29119-3** (*Test Execution Documentation* / *Test Completion Report*). Registra la **ejecución manual** del [Diseño de Casos de Pruebas Funcionales](Diseno-de-Casos-de-Pruebas-Funcionales), organizado **por requisito funcional**.

| Campo | Detalle |
|-------|---------|
| **Documento** | Informe de Casos de Pruebas Funcionales — Snipe-IT |
| **Versión** | **2.1 (alineado al diseño v2.1: + RF-09 Login, RF-10 Usuarios, RF-11 Accesorios)** |
| **Diseño asociado** | [Diseño de Casos de Pruebas Funcionales](Diseno-de-Casos-de-Pruebas-Funcionales) v2.1 |
| **Hito / Sprint** | Hito 2 / Sprint 2 |
| **Tipo** | Funcional / Caja negra / Manual |
| **Ambiente** | QA (despliegue compartido) |
| **Fecha de elaboración** | 2026-06-12 |
| **Estado** | Diseño cerrado; **ejecución manual en QA pendiente de validación** |

---

## 1. Nota metodológica — separación entre diseñado y ejecutado

Este informe distingue de forma estricta tres tipos de información:

1. **Diseñado (factual):** los casos `CPF-XX` y sus subcasos, ya especificados en el documento de diseño. Es información cerrada.
2. **Cobertura automatizada de referencia (factual):** la existencia, en el repositorio, de pruebas automatizadas (`tests/Feature/**`) que corroboran el **resultado esperado** de cada requisito. **No es** la ejecución funcional manual, pero es un dato verificable que respalda el diseño y reduce el riesgo de diseñar contra supuestos.
3. **Ejecutado en QA (pendiente):** el **veredicto manual** (Conforme / No conforme / Bloqueado) y la **evidencia** (capturas) de cada caso. Mientras no se realice la sesión de pruebas en QA, estos campos figuran como `⟦PENDIENTE-QA⟧`. **No se consignan resultados no ejecutados como si hubieran pasado.**

> **Procedimiento de ejecución:** desplegar Snipe-IT en QA (Docker Compose del repositorio) → poblar datos base → ejecutar cada caso `CPF-XX` del diseño → capturar evidencia → registrar veredicto y, si falla, abrir GitHub Issue con etiqueta `bug` y enlazarlo aquí.

---

## 2. Entorno de ejecución

| Elemento | Definición |
|----------|------------|
| Aplicación | Snipe-IT (PHP 8.2+ / Laravel 12) desplegado en QA |
| Acceso | Navegador, interfaz administrativa |
| Datos base | Seeders/factories (modelos, status labels, categorías, usuarios, ubicaciones) |
| Roles | Superusuario y permisos granulares según el caso |
| Evidencia | Capturas por caso + GitHub Issues para defectos |

---

## 3. Resumen de ejecución

| Métrica | Valor |
|---------|-------|
| Requisitos funcionales cubiertos | 11 (RF-01 a RF-11) |
| Casos principales diseñados | 15 (CPF-01 a CPF-15) |
| Subcasos derivados diseñados | 46 (CPF-XX.n) |
| Casos con cobertura automatizada de referencia | 13 de 15 (CPF-05 y CPF-08 sin cobertura automatizada) |
| Casos ejecutados manualmente en QA | `⟦PENDIENTE-QA⟧` |
| Conformes | `⟦PENDIENTE-QA⟧` |
| No conformes | `⟦PENDIENTE-QA⟧` |
| Bloqueados | `⟦PENDIENTE-QA⟧` |
| Defectos registrados (Issues) | `⟦PENDIENTE-QA⟧` |

---

## 4. Informe de ejecución por requisito funcional

> Para cada requisito: **resultado obtenido, estado, defectos, observaciones y evidencia**. La columna *Cobertura automatizada de referencia* es factual (archivo real); el *Veredicto manual* es el resultado de la sesión de QA y figura como pendiente hasta su ejecución.

### RF-01 — Registrar un activo con asset tag único

| Caso | Resultado esperado (resumen) | Cobertura automatizada de referencia | Veredicto manual | Evidencia |
|------|------------------------------|--------------------------------------|------------------|-----------|
| CPF-01 | Activo creado con tag único | `StoreAssetsTest` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-01.1 | Serial requerido y provisto → creado | `StoreAssetsTest::test_asset_can_be_stored_with_serial_required_and_serial_provided` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-01.2 | Serial requerido y ausente → rechazo (`serials.1`) | `StoreAssetsTest::test_asset_cannot_be_stored_if_serial_required_and_missing` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-01.3 | Sin permiso → 403 | `StoreAssetsTest::test_permission_required_to_store_asset` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-02 / CPF-02.1 | Tag duplicado/vacío → rechazo | Regla `unique_undeleted` / `required\|min:1` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |

- **Estado:** `⟦PENDIENTE-QA⟧` · **Defectos:** — · **Observaciones:** la regla de unicidad aplica a activos **no eliminados** (`unique_undeleted`); validar que un tag de un activo borrado lógicamente pueda reutilizarse es una verificación adicional recomendada.

### RF-02 — Asignar un activo a un destino (checkout)

| Caso | Resultado esperado (resumen) | Cobertura automatizada de referencia | Veredicto manual | Evidencia |
|------|------------------------------|--------------------------------------|------------------|-----------|
| CPF-03 | Activo asignado a usuario; historial actualizado | `AssetCheckoutTest::test_asset_can_be_checked_out` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-03.1/.2 | Checkout a activo / ubicación | `AssetCheckoutTest` (data provider de destinos) | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-03.3 | Activo no disponible → rechazo | `test_asset_not_available_for_checkout_cannot_be_checked_out` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-03.4 | Activo a sí mismo → rechazo | `test_asset_cannot_be_checked_out_to_itself` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-03.5 | Datos obligatorios ausentes → errores | `test_validation_when_checking_out_asset` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-03.6 | Sin permiso → 403 | `test_checking_out_asset_requires_correct_permission` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-03.7 | Checkout cruzado (FMCS) → rechazo | `test_cannot_checkout_across_companies_when_full_company_support_enabled` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |

- **Estado:** `⟦PENDIENTE-QA⟧` · **Defectos:** — · **Observaciones:** el subcaso FMCS exige activar `full_multiple_companies_support` en la instancia de QA antes de ejecutarlo.

### RF-03 — Devolver un activo asignado (checkin)

| Caso | Resultado esperado (resumen) | Cobertura automatizada de referencia | Veredicto manual | Evidencia |
|------|------------------------------|--------------------------------------|------------------|-----------|
| CPF-04 | Activo devuelto; queda disponible | `AssetCheckinTest::test_asset_can_be_checked_in` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-04.1 | Checkin de activo no asignado → rechazo | `test_cannot_check_in_asset_that_is_not_checked_out` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-04.2 | Sin permiso → 403 | `test_checking_in_asset_requires_correct_permission` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-04.3 | Asientos de licencia liberados | `test_assets_license_seats_are_cleared_upon_checkin` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |

- **Estado:** `⟦PENDIENTE-QA⟧` · **Defectos:** — · **Observaciones:** verificar que la ubicación se restablece a la RTD por defecto (`test_location_is_set_to_rtd_location_by_default_upon_checkin`).

### RF-04 — Crear una licencia con N asientos

| Caso | Resultado esperado (resumen) | Cobertura automatizada de referencia | Veredicto manual | Evidencia |
|------|------------------------------|--------------------------------------|------------------|-----------|
| CPF-06 / CPF-06.1/.2 | Licencia con N asientos (1/10/10000) | `CreateLicenseTest::test_license_create` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-06.3 | seats=100000 → no se crea | `test_too_many_seats_license_create` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-06.4 | Sin `purchase_date` → rechazo | `test_license_without_purchase_date_fails_validation` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-06.5 | seats=0 → rechazo (`min:1`) | Regla `seats: required\|min:1\|integer` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |

- **Estado:** `⟦PENDIENTE-QA⟧` · **Defectos:** — · **Observaciones:** `limit_change:10000` limita la **magnitud del cambio** de asientos (±10000); para una licencia nueva equivale a un **tope superior de 10000**. Por eso `seats=100000` se rechaza y `seats=10000` se acepta.

### RF-05 — Asignar un asiento de licencia

| Caso | Resultado esperado (resumen) | Cobertura automatizada de referencia | Veredicto manual | Evidencia |
|------|------------------------------|--------------------------------------|------------------|-----------|
| CPF-07 | Asiento asignado a usuario; disponibles −1 | `LicenseCheckoutTest::test_notes_are_stored_in_action_log_on_checkout_to_user` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-07.1 | Asiento asignado a activo | `test_notes_are_stored_in_action_log_on_checkout_to_asset` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-08 / CPF-08.1 | Agotar asientos / exceder → rechazo | **Sin cobertura automatizada** | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |

- **Estado:** `⟦PENDIENTE-QA⟧` · **Defectos:** — · **Observaciones:** CPF-08 y CPF-08.1 **no** tienen prueba automatizada de UI en el repositorio; su verificación es **exclusivamente manual** en QA. No deben darse por válidos sin evidencia.

### RF-06 — Descontar stock de un consumible

| Caso | Resultado esperado (resumen) | Cobertura automatizada de referencia | Veredicto manual | Evidencia |
|------|------------------------------|--------------------------------------|------------------|-----------|
| CPF-09 / CPF-09.1/.2 | Stock decrementa al entregar | `ConsumableCheckoutTest::test_consumable_can_be_checked_out` · `test_quantity_stored_in_action_log` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-09.3 | Sin stock → rechazo | `test_consumable_must_be_available_when_checking_out` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-09.4 | Sin `assigned_to` → rechazo | `test_validation_when_checking_out_consumable` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-09.5 | Sin permiso → 403 | `test_checking_out_consumable_requires_correct_permission` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |

- **Estado:** `⟦PENDIENTE-QA⟧` · **Defectos:** — · **Observaciones:** confirmar que la notificación por correo al usuario se emite (`test_user_sent_notification_upon_checkout`); en QA depende de la configuración de correo (driver `array`/SMTP).

### RF-07 — Impedir la eliminación de categoría con elementos

| Caso | Resultado esperado (resumen) | Cobertura automatizada de referencia | Veredicto manual | Evidencia |
|------|------------------------------|--------------------------------------|------------------|-----------|
| CPF-10 | Categoría con modelos → no se elimina | `DeleteCategoriesTest::test_cannot_delete_category_that_still_has_associated_models` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-10 (variante activos) | Categoría con activos → no se elimina | `test_cannot_delete_category_that_still_has_associated_assets` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-10.1 | Sin permiso → 403 | `test_permission_needed_to_delete_category` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-11 | Categoría vacía → se elimina | `test_can_delete_category` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |

- **Estado:** `⟦PENDIENTE-QA⟧` · **Defectos:** — · **Observaciones:** la eliminación es **borrado lógico** (*soft delete*); en QA verificar que la categoría desaparece del listado activo pero conserva su registro.

### RF-08 — Disponibilidad del activo según status label

| Caso | Resultado esperado (resumen) | Cobertura automatizada de referencia | Veredicto manual | Evidencia |
|------|------------------------------|--------------------------------------|------------------|-----------|
| CPF-05 / CPF-05.1/.2/.3 | Estado no desplegable → no elegible | **Sin prueba de UI directa**; regla `availableForCheckout()` (deployable=1, archived=0) | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |

- **Estado:** `⟦PENDIENTE-QA⟧` · **Defectos:** — · **Observaciones:** no existe prueba automatizada que bloquee el POST de checkout para un estado *archived/undeployable*; la verificación es **manual** y se apoya en la regla `availableForCheckout()`. Diseñar las tres variantes (*pending*, *archived*, *undeployable*) con estados creados explícitamente.

### RF-09 — Autenticar a un usuario (login / logout)

| Caso | Resultado esperado (resumen) | Cobertura automatizada de referencia | Veredicto manual | Evidencia |
|------|------------------------------|--------------------------------------|------------------|-----------|
| CPF-12 | Login válido → autenticado, evento registrado | `LoginTest::test_logs_successful_login` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-12.1 | Credenciales inválidas → rechazo + intento registrado | `LoginTest::test_logs_failed_login_attempt` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-12.2 | Exceso de intentos → bloqueo (throttling) | `LoginTest::test_login_throttle_config_is_respected` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-12.3 / .4 | Logout / acceso sin sesión → redirección a login | (middleware `auth`) | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |

- **Estado:** `⟦PENDIENTE-QA⟧` · **Defectos:** — · **Observaciones:** el throttling en QA depende de `LOGIN_MAX_ATTEMPTS`/`LOGIN_LOCKOUT_DURATION`; usar valores bajos para poder observar el bloqueo en una sesión manual razonable.

### RF-10 — Registrar y editar un usuario

| Caso | Resultado esperado (resumen) | Cobertura automatizada de referencia | Veredicto manual | Evidencia |
|------|------------------------------|--------------------------------------|------------------|-----------|
| CPF-13 | Usuario creado con datos válidos | `Users/Ui/CreateUserTest::test_can_create_user` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-13.1 | `first_name` vacío → rechazo | Regla `first_name: required\|min:1` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-13.2 | Contraseña sin confirmar → rechazo | Regla `password: …\|confirmed` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-13.3 | Sin permiso → 403 | `test_permission_required_to_create_user` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-13.4 | Edición de usuario → cambios guardados | `Users/Ui/UpdateUserTest` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-13.5 | No-admin no escala a superusuario | `test_non_admin_cannot_grant_admin_or_superuser_permissions_when_creating_user_via_ui` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |

- **Estado:** `⟦PENDIENTE-QA⟧` · **Defectos:** — · **Observaciones:** la complejidad de contraseña depende de los ajustes de seguridad (`Setting::passwordComplexityRulesSaving`); fijar una política conocida en QA antes de ejecutar CPF-13.2.

### RF-11 — Asignar y devolver un accesorio (checkout / checkin)

| Caso | Resultado esperado (resumen) | Cobertura automatizada de referencia | Veredicto manual | Evidencia |
|------|------------------------------|--------------------------------------|------------------|-----------|
| CPF-14 | Accesorio asignado; unidades −cantidad; historial | `Checkouts/Ui/AccessoryCheckoutTest::test_accessory_can_be_checked_out_with_quantity` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-14.1 / .2 | Checkout a ubicación / activo | `test_accessory_can_be_checked_out_to_location_with_quantity` · `..._to_asset_with_quantity` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-14.3 | Sin unidades disponibles → rechazo | `test_accessory_must_have_available_items_for_checkout_when_checking_out` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-14.4 | Destino ausente → error de validación | `test_validation_when_checking_out_accessory` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-14.5 | Sin permiso → 403 | `test_checking_out_accessory_requires_correct_permission` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |
| CPF-15 / .1 | Checkin de accesorio / sin permiso → 403 | `Checkins/Ui/AccessoryCheckinTest::test_accessory_can_be_checked_in` · `test_checking_in_accessory_requires_correct_permission` | `⟦PENDIENTE-QA⟧` | `⟦captura⟧` |

- **Estado:** `⟦PENDIENTE-QA⟧` · **Defectos:** — · **Observaciones:** la notificación al usuario en el checkout (`test_user_sent_notification_upon_checkout`) y el correo de checkin (`test_email_sent_to_user_if_setting_enabled`) dependen de la configuración de correo en QA.

---

## 5. Defectos funcionales encontrados

| ID Issue | Caso origen | Descripción | Severidad | Estado |
|----------|-------------|-------------|-----------|--------|
| `⟦PENDIENTE-QA⟧` | — | — | — | — |

Los defectos se registran en **GitHub Issues** con etiqueta `bug` y se enlazan en esta tabla durante la sesión de ejecución.

---

## 6. Trazabilidad requisito ↔ caso ↔ evidencia

| Requisito | Casos principales | Subcasos | Evidencia funcional (QA) | Resultado |
|-----------|-------------------|----------|--------------------------|-----------|
| RF-01 | CPF-01, CPF-02 | .1 .2 .3 / .1 | `⟦captura⟧` | `⟦PENDIENTE-QA⟧` |
| RF-02 | CPF-03 | .1 … .7 | `⟦captura⟧` | `⟦PENDIENTE-QA⟧` |
| RF-03 | CPF-04 | .1 .2 .3 | `⟦captura⟧` | `⟦PENDIENTE-QA⟧` |
| RF-04 | CPF-06 | .1 … .5 | `⟦captura⟧` | `⟦PENDIENTE-QA⟧` |
| RF-05 | CPF-07, CPF-08 | .1 / .1 | `⟦captura⟧` | `⟦PENDIENTE-QA⟧` |
| RF-06 | CPF-09 | .1 … .5 | `⟦captura⟧` | `⟦PENDIENTE-QA⟧` |
| RF-07 | CPF-10, CPF-11 | .1 | `⟦captura⟧` | `⟦PENDIENTE-QA⟧` |
| RF-08 | CPF-05 | .1 .2 .3 | `⟦captura⟧` | `⟦PENDIENTE-QA⟧` |
| RF-09 | CPF-12 | .1 … .4 | `⟦captura⟧` | `⟦PENDIENTE-QA⟧` |
| RF-10 | CPF-13 | .1 … .5 | `⟦captura⟧` | `⟦PENDIENTE-QA⟧` |
| RF-11 | CPF-14, CPF-15 | .1 … .5 / .1 | `⟦captura⟧` | `⟦PENDIENTE-QA⟧` |

La trazabilidad consolidada (con los niveles unitario e integración) se mantiene en la [Matriz de Trazabilidad](Matriz-de-Trazabilidad).

---

## 7. Conclusión

El **diseño** de pruebas funcionales está **cerrado y verificado** contra el comportamiento real del producto: 15 casos principales y 46 subcasos cubren los 11 requisitos RF-01…RF-11 mediante una combinación de técnicas seleccionada por requisito (partición de equivalencia, valores límite, tablas de decisión y transición de estados). La v2.1 amplió el alcance de cara al usuario con **RF-09 (login)**, **RF-10 (gestión de usuarios)** —que además corrige la incoherencia de la v2.0, que declaraba "Usuarios" en el alcance sin probarlo— y **RF-11 (checkout/checkin de accesorio)**. Trece de los quince casos principales cuentan con **cobertura automatizada de referencia** que respalda el resultado esperado.

La actividad **se cierra formalmente** cuando se ejecute la sesión manual en QA y se completen los campos `⟦PENDIENTE-QA⟧` con veredicto y evidencia. Dos casos —**CPF-05** (disponibilidad por status label) y **CPF-08** (agotamiento de asientos)— **carecen de cobertura automatizada** y requieren verificación manual obligatoria; se han marcado explícitamente como **pendientes de validación** para no consignar resultados no ejecutados.

---

*Fin del documento — Informe de Casos de Pruebas Funcionales v2.1.*
