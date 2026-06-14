# Informe de Cobertura de Código — Snipe-IT

> Fecha: 2026-06-14 · Medición **real** (PCOV). Todos los porcentajes provienen de la ejecución; ninguno fue estimado.
> Este informe tiene **dos mediciones**:
> - **Parte I — Suite Unit** (315 tests).
> - **Parte II — Suite combinada Unit + Feature** (1979 tests) → ver [Adenda](#adenda--cobertura-combinada-unit--feature) al final.
>
> Fuente instrumentada: `app/` (definida en `phpunit.xml` → `<source>`).

---

# PARTE I — Cobertura suite Unit

---

## 1. Driver de cobertura

| Ítem | Estado |
|---|---|
| Xdebug | ❌ no estaba instalado |
| PCOV | ✅ **instalado en esta sesión** |
| phpdbg | ⚠️ existe, pero **PHPUnit 11 ya no lo soporta** como driver ("No code coverage driver available") |

**Qué se instaló (PCOV):**
- DLL: `php_pcov.dll` v**1.0.12** (PHP 8.4, NTS, VS17, x64) descargada de `downloads.php.net/~windows/pecl`.
- Ubicación: `~/.config/herd/bin/php84/ext/php_pcov.dll`
- `php.ini` de Herd: añadido `extension=pcov` + `pcov.enabled=1` (backup previo: `php.ini.bak-pre-pcov`).
- Verificado en runtime: `PCOV support => Enabled`, `PCOV version => 1.0.12`.
- Descarga ajustada por la intercepción TLS de Avast usando `--ssl-no-revoke` (se mantiene la validación del certificado).

---

## 2. Comando ejecutado

```bash
php -d memory_limit=-1 vendor/bin/phpunit --testsuite Unit \
  --coverage-html  tests/coverage/html \
  --coverage-clover tests/coverage/clover.xml \
  --coverage-text
```
> `memory_limit=-1` fue necesario: con el límite por defecto (128 MB) la instrumentación de cobertura agotaba la memoria (~60 % de la suite) en `serializable-closure/ReflectionClosure.php`.

**Resultado de la ejecución:** 315 pruebas, 600 assertions, 6 errores + 2 fallos (los mismos 8 ya documentados: LDAP y LicenseTest), en **01:08 min**.
**Artefactos generados:**
- HTML: `tests/coverage/html/index.html`
- Clover XML: `tests/coverage/clover.xml`
- Texto de consola: `tests/coverage/coverage-text.log`

---

## 3. Cobertura global (real, salida de consola)

```
Code Coverage Report:  2026-06-14
 Summary:
  Classes:  3.17% (16/504)
  Methods: 11.82% (352/2979)
  Lines:    8.49% (3008/35439)
```

| Métrica | Cobertura | Cubierto/Total |
|---|---|---|
| **Líneas** | **8.49 %** | 3008 / 35439 |
| Métodos | 11.82 % | 352 / 2979 |
| Clases | 3.17 % | 16 / 504 |

---

## 4. Cobertura por módulo (líneas)

| Módulo (`app/…`) | Cobertura | Líneas | Archivos |
|---|---:|---|---:|
| Providers | **69.36 %** | 600/865 | 12 |
| View | 65.28 % | 94/144 | 1 |
| Observers | 44.44 % | 168/378 | 10 |
| Events | 36.84 % | 7/19 | 7 |
| Helpers | 31.27 % | 369/1180 | 3 |
| Listeners | 24.32 % | 99/407 | 5 |
| Models | 21.22 % | 1340/6315 | 99 |
| Policies | 13.16 % | 10/76 | 22 |
| Mail | 12.92 % | 65/503 | 16 |
| Actions | 10.73 % | 19/177 | 8 |
| Services | 9.91 % | 23/232 | 3 |
| Console | 4.03 % | 107/2654 | 50 |
| Exceptions | 2.63 % | 2/76 | 12 |
| Importer | 2.09 % | 20/959 | 13 |
| Http | 0.46 % | 64/13981 | 181 |
| Presenters | 0.40 % | 21/5239 | 30 |
| Livewire | 0.00 % | 0/841 | 8 |
| Notifications | 0.00 % | 0/1287 | 25 |
| Rules | 0.00 % | 0/104 | 15 |
| Traits | 0.00 % | 0/2 | 1 |

> `app/Http` (controladores) y `app/Presenters` concentran la mayor parte del código (≈19.000 líneas) y casi no tienen cobertura desde la suite Unit — lógico, ya que esa lógica se ejercita principalmente en la suite **Feature** (no incluida en esta medición).

---

## 5. Cobertura por archivo

Distribución (sobre 500 archivos con sentencias ejecutables; 523 en total, 23 sin sentencias):

| Rango | Nº archivos |
|---|---:|
| ≥ 85 % | **20** |
| 50 %–84 % | 28 |
| > 0 % y < 50 % | 96 |
| 0 % | 356 |

### Archivos con mayor cobertura (≥ 85 %, los 20)
| % | Líneas | Archivo |
|---:|---|---|
| 100 % | 8/8 | app/Actions/Permissions/NormalizePermissionsPayloadAction.php |
| 100 % | 11/11 | app/Actions/Permissions/PreserveUnauthorizedPrivilegedPermissionsAction.php |
| 100 % | 3/3 | app/Console/Commands/SamlClearExpiredNonces.php |
| 100 % | 7/7 | app/Events/CheckoutableCheckedOut.php |
| 100 % | 2/2 | app/Http/Controllers/Controller.php |
| 100 % | 5/5 | app/Http/Traits/TwoColumnUniqueUndeletedTrait.php |
| 100 % | 3/3 | app/Http/Traits/UniqueUndeletedTrait.php |
| 100 % | 6/6 | app/Mail/BaseMailable.php |
| 100 % | 7/7 | app/Models/Labels/RectangleSheet.php |
| 100 % | 1/1 | app/Models/Traits/CompanyableChildTrait.php |
| 100 % | 1/1 | app/Observers/SettingObserver.php |
| 100 % | 1/1 | app/Policies/CategoryPolicy.php |
| 100 % | 2/2 | app/Providers/BladeServiceProvider.php |
| 100 % | 11/11 | app/Providers/LivewireServiceProvider.php |
| 100 % | 31/31 | app/Providers/SamlServiceProvider.php |
| 100 % | 8/8 | app/Providers/SnipeTranslationServiceProvider.php |
| 95.83 % | 23/24 | app/Services/SnipeTranslator.php |
| 95.06 % | 77/81 | app/Models/Depreciable.php |
| 86.49 % | 32/37 | app/Models/ReportTemplate.php |
| 85.33 % | 64/75 | app/Observers/AssetObserver.php |

> El listado por archivo completo (los 144 con cobertura > 0) está en el log `tests/coverage/coverage-text.log` y en el reporte navegable `tests/coverage/html/index.html`.

---

## 6. Archivos sin cobertura (0 %)

**356 archivos** con sentencias ejecutables tienen 0 % de cobertura desde la suite Unit. Agrupados por módulo:

| Módulo | Archivos en 0 % |
|---|---:|
| app/Http | 170 |
| app/Models | 38 |
| app/Presenters | 27 |
| app/Notifications | 25 |
| app/Policies | 19 |
| app/Rules | 15 |
| app/Mail | 14 |
| app/Console | 11 |
| app/Importer | 10 |
| app/Livewire | 8 |
| app/Actions | 6 |
| app/Events | 5 |
| app/Listeners | 2 |
| app/Services | 2 |
| app/Exceptions | 1 |
| app/Helpers | 1 |
| app/Providers | 1 |
| app/Traits | 1 |

Módulos **enteramente sin cobertura** (0 % en todos sus archivos): `app/Livewire`, `app/Notifications`, `app/Rules`, `app/Traits` (este último 1 archivo).

---

## 7. Archivos con cobertura menor al 50 %

**452 de 500** archivos ejecutables están por debajo del 50 % (356 en 0 % + 96 con cobertura parcial < 50 %).

Selección de archivos **grandes** con cobertura parcial < 50 % (mayor impacto potencial):

| % | Líneas | Archivo |
|---:|---|---|
| 43.61 % | 365/837 | app/Helpers/Helper.php |
| 41.03 % | 135/329 | app/Models/Labels/Label.php |
| 30.99 % | 75/242 | app/Listeners/CheckoutableListener.php |
| 29.45 % | 172/584 | app/Models/Asset.php |
| 28.99 % | 40/138 | app/Models/Actionlog.php |
| 26.67 % | 28/105 | app/Models/CustomField.php |
| 22.41 % | 91/406 | app/Models/User.php |
| 16.10 % | 19/118 | app/Models/Setting.php |
| 14.18 % | 37/261 | app/Models/Traits/Loggable.php |
| 13.33 % | 24/180 | app/Models/Ldap.php |
| 3.80 % | 6/158 | app/Importer/Importer.php |
| 3.11 % | 14/450 | app/Http/Controllers/Assets/BulkAssetsController.php |
| 0.66 % | 3/458 | app/Presenters/AssetPresenter.php |
| 0.46 % | 1/217 | app/Importer/ItemImporter.php |
| 0.34 % | 1/292 | app/Console/Commands/LdapSync.php |
| 0.22 % | 1/462 | app/Models/Traits/Searchable.php |

---

## 8. ¿Se alcanza la meta académica del 85 %?

### ❌ NO. La cobertura global de líneas es **8.49 %**, muy por debajo del **85 %** objetivo.

| | Valor |
|---|---|
| Meta | 85.00 % |
| Real (líneas) | 8.49 % |
| Brecha | **−76.51 puntos** |

**Contexto importante:** esta medición cubre **solo la suite Unit** (315 pruebas). El proyecto tiene además **292 archivos de pruebas Feature** que ejercitan controladores HTTP, presenters, notificaciones, etc. — justamente los módulos hoy en 0 % (`app/Http`, `app/Presenters`, `app/Notifications`). Una medición que incluya la suite **Feature** elevaría sustancialmente el porcentaje, aunque sin garantía de llegar al 85 %.

### Recomendaciones (no aplicadas)
- Medir cobertura **combinando Unit + Feature** para un número representativo:
  ```bash
  php -d memory_limit=-1 vendor/bin/phpunit --coverage-html tests/coverage/html
  ```
  (sin `--testsuite Unit`, ejecuta ambas suites).
- Priorizar pruebas en los archivos grandes con baja cobertura de la sección 7 (`Asset`, `User`, `Helper`, `BulkAssetsController`, `AssetPresenter`).
- Para CI ya existe `.github/workflows/tests-unit-coverage.yml` (PCOV, solo suite Unit) que publica Clover + HTML como artefactos.

---

# ADENDA — Cobertura combinada Unit + Feature

> Medición **real** ejecutando **ambas suites** (lo que faltaba en la Parte I). Datos del Clover `tests/coverage/clover-all.xml`.

## Comando ejecutado
```bash
php -d memory_limit=-1 vendor/bin/phpunit \
  --coverage-html  tests/coverage/html-all \
  --coverage-clover tests/coverage/clover-all.xml \
  --coverage-text
```

## Resultado de la ejecución
- **1979 pruebas · 6691 assertions · 12 min 17 s** · Memoria pico ~432 MB.
- Resultado: **6 errores + 5 fallos**, 3 skipped, 8 incomplete.
- ⚠️ El **Clover XML se generó correctamente**, pero la **generación del HTML falló** por agotar memoria (`php-code-coverage/.../Html/Renderer/File.php`) al renderizar 1979 tests — el `index.html` de `tests/coverage/html-all/` quedó **parcial**. La fuente autoritativa de esta adenda es el **Clover XML**, completo.

## Cobertura GLOBAL combinada (real)

| Métrica | Cobertura | Cubierto/Total |
|---|---|---|
| **Líneas** | **57.11 %** | 20190 / 35351 |
| Métodos | 39.31 % | 1171 / 2979 |

> Salto enorme respecto a la suite Unit sola (8.49 % → **57.11 %**), confirmando que la mayor parte del código (`Http`, `Presenters`, etc.) se ejercita en la suite Feature.

## Cobertura por módulo (líneas) — combinada

| Módulo (`app/…`) | Cobertura | Líneas | Archivos |
|---|---:|---|---:|
| Events | 100.00 % | 19/19 | 6 |
| Traits | 100.00 % | 2/2 | 1 |
| Observers | 93.39 % | 353/378 | 10 |
| Providers | 84.16 % | 728/865 | 11 |
| Listeners | 82.56 % | 336/407 | 5 |
| Presenters | 81.56 % | 4273/5239 | 30 |
| Mail | 81.11 % | 408/503 | 16 |
| Importer | 79.46 % | 762/959 | 13 |
| Helpers | 73.22 % | 864/1180 | 3 |
| Policies | 72.37 % | 55/76 | 22 |
| View | 68.75 % | 99/144 | 1 |
| Actions | 67.23 % | 119/177 | 8 |
| Exceptions | 65.79 % | 50/76 | 2 |
| Livewire | 65.52 % | 551/841 | 8 |
| Http | 55.22 % | 7672/13893 | 175 |
| Models | 47.14 % | 2977/6315 | 96 |
| Rules | 31.73 % | 33/104 | 15 |
| Console | 21.67 % | 575/2654 | 50 |
| Services | 20.69 % | 48/232 | 3 |
| Notifications | 20.67 % | 266/1287 | 25 |

## Distribución por archivo — combinada

| Rango | Nº archivos |
|---|---:|
| ≥ 85 % | **161** |
| 50 %–84 % | 129 |
| > 0 % y < 50 % | 112 |
| 0 % | 98 |
| (Total ejecutables) | 500 |

### Archivos en 0 % (98) por módulo
| Módulo | 0 % |
|---|---:|
| app/Http | 36 |
| app/Models | 27 |
| app/Notifications | 9 |
| app/Rules | 8 |
| app/Console | 6 |
| app/Presenters | 6 |
| app/Livewire | 3 |
| app/Actions | 1 |
| app/Providers | 1 |
| app/Services | 1 |

### Archivos grandes con < 50 % (mayor impacto)
| % | Líneas | Archivo |
|---:|---|---|
| 33.85 % | 262/774 | app/Http/Controllers/ReportsController.php |
| 30.11 % | 168/558 | app/Http/Controllers/SettingsController.php |
| 46.52 % | 227/488 | app/Http/Controllers/Assets/AssetsController.php |
| 0.30 % | 1/336 | app/Console/Commands/LdapTroubleshooter.php |
| 41.03 % | 135/329 | app/Models/Labels/Label.php |
| 0.34 % | 1/294 | app/Console/Commands/RestoreFromBackup.php |
| 0.34 % | 1/292 | app/Console/Commands/LdapSync.php |
| 0.00 % | 0/249 | app/Models/SnipeSCIMConfig.php |
| 0.00 % | 0/218 | app/Presenters/DepreciationReportPresenter.php |
| 12.20 % | 25/205 | app/Http/Controllers/Auth/LoginController.php |
| 13.33 % | 24/180 | app/Models/Ldap.php |
| 0.00 % | 0/162 | app/Http/Controllers/Api/SettingsController.php |
| 0.00 % | 0/159 | app/Livewire/SlackSettingsForm.php |

## Pruebas fallidas en la corrida combinada (11)

**6 errores + 5 fallos.** Los 8 ya conocidos de la suite Unit (LDAP + LicenseTest) **+ 3 nuevos de Feature**:

| # | Prueba | Origen |
|---|---|---|
| 1–6 | `LdapTest` (5) + `LicenseTest::remaincount...` | Unit (ya documentados — LDAP ausente / factory `withSeats()`) |
| 7–8 | `LdapTest::find_and_bind_cannot_find_self`, `LicenseTest::is_deletable...` | Unit (ya documentados) |
| 9 | `Tests\Feature\Accessories\Api\IndexAccessoryTest::test_can_filter_accessories_by_searchable_count_alias` | Feature |
| 10 | `Tests\Feature\AssetModels\Api\IndexAssetModelsTest::test_asset_model_index_filter_can_search_computed_count_aliases` | Feature |
| 11 | `Tests\Feature\Checkouts\Api\AssetCheckoutTest::test_license_seats_are_assigned_to_user_upon_checkout` | Feature |

> Los fallos 9 y 10 implican filtros por *count alias computados* y podrían ser específicos de **SQLite** (sintaxis SQL que difiere de MySQL/Postgres). El fallo 11 está relacionado con la asignación de *seats* de licencia (mismo dominio que los `LicenseTest` que ya fallan). Requieren revisión aparte; **no afectan la validez de la medición de cobertura**.

## ¿Se alcanza la meta del 85 % (combinada)?

### ❌ NO con cobertura combinada tampoco — pero mucho más cerca.

| | Valor |
|---|---|
| Meta | 85.00 % |
| Real líneas (Unit+Feature) | **57.11 %** |
| Brecha | **−27.89 puntos** |

**Para acercarse al 85 %**, priorizar (por volumen de líneas sin cubrir):
- `app/Notifications` (20.67 %) y `app/Console` (21.67 %) — módulos enteros con baja cobertura.
- Controladores grandes: `ReportsController`, `SettingsController`, `Api/SettingsController` (0 %), `LoginController`.
- `app/Models` (47.14 %) — segundo módulo más grande, justo por debajo del 50 %.

## Nota técnica (HTML combinado)
Si se quiere el reporte HTML navegable completo de la suite combinada, hay que elevar más la memoria del renderizador (el fatal ocurrió con ~500 MB durante el render). Alternativa: generar solo Clover (rápido y completo) y usar el HTML de la suite Unit (`tests/coverage/html/`) para navegación.
