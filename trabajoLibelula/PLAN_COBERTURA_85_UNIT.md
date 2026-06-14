# Plan para alcanzar >85 % de cobertura — Suite Unit (estricto)

> Fecha: 2026-06-14 · Alcance fijado: la cobertura se mide ejecutando **solo `--testsuite Unit`**.
> Punto de partida real (medido con PCOV): **8.49 % de líneas** (3008 / 35439).
> Meta: **≥ 85 %** → ≈ **30 123 líneas** cubiertas → hay que **añadir cobertura a ≈ 27 100 líneas**.

---

## 0. Aclaración clave sobre "prueba unitaria" en este proyecto

En Snipe-IT, `tests/Unit` **sí tiene acceso a base de datos**: `Tests\TestCase` usa `LazilyRefreshDatabase` y los tests Unit existentes ya usan factories (`License::factory()->create()`, etc.). Por tanto "unit" aquí significa *"test ubicado en `tests/Unit`"*, no *"test sin BD"*. Esto **amplía** mucho lo que es viable testear como unit (modelos, presenters, helpers, notifications, importers…).

Lo que **sí** queda fuera del estilo unit son las pruebas que necesitan el ciclo HTTP completo (rutas + middleware + auth): `$this->getJson()`, `actingAs()`, etc. — eso es el dominio de `tests/Feature`.

---

## 1. Restricción crítica: el "techo de `app/Http`"

`app/Http` (controladores) representa **13 981 de 35 439 líneas = 39,5 %** de TODO el código instrumentado, y hoy tiene **0,46 %** de cobertura desde Unit. Esa lógica se ejercita vía peticiones HTTP (suite Feature), no de forma unitaria.

**Cálculo del techo:** si lleváramos al 85 % **todo lo demás** (los 21 458 líneas que NO son Http) y dejáramos Http como está:

```
Cubierto = 0,85 × 21 458 (resto)  +  64 (Http actual)  ≈ 18 303 líneas
% global = 18 303 / 35 439 ≈ 51,6 %
```

> **Conclusión dura: es imposible superar ~52 % en la suite Unit sin cubrir `app/Http`.** Para llegar a 85 % hay que actuar sobre los controladores. Hay 3 caminos (sección 3).

---

## 2. Diagnóstico por módulo (cobertura Unit actual y potencial)

| Módulo | Líneas | Unit hoy | Unit-testeable sin refactor | Comentario |
|---|---:|---:|:--:|---|
| app/Http | 13 981 | 0,46 % | ❌ (necesita HTTP) | **El bloqueante.** Ver sección 3 |
| app/Models | 6 315 | 21,2 % | ✅ Alta | Lógica de negocio, scopes, accessors |
| app/Presenters | 5 239 | 0,40 % | ✅ **Muy alta** | Transforman datos; ideales para unit. Gran botín |
| app/Console | 2 654 | 4,0 % | ⚠️ Media | Commands; testeables con `Artisan::call` (semi-integración) |
| app/Notifications | 1 287 | 0,0 % | ✅ Alta | `toArray()`/`toMail()` se prueban sin enviar |
| app/Helpers | 1 180 | 31,3 % | ✅ Muy alta | Funciones puras / casi puras |
| app/Importer | 959 | 2,1 % | ✅ Media-alta | Usa BD, pero cabe en Unit |
| app/Providers | 865 | 69,4 % | ✅ | Ya bien cubierto |
| app/Livewire | 841 | 0,0 % | ⚠️ (`Livewire::test`) | Hoy se prueba en Feature |
| app/Mail | 503 | 12,9 % | ✅ Alta | Construcción de mailables |
| app/Listeners | 407 | 24,3 % | ✅ | Eventos/listeners |
| app/Observers | 378 | 44,4 % | ✅ | Hooks de modelo |
| app/Services | 232 | 9,9 % | ✅ | |
| app/Actions | 177 | 10,7 % | ✅ | Patrón ya unit-friendly |
| app/View | 144 | 65,3 % | ✅ | |
| app/Policies | 76 | 13,2 % | ✅ Alta | Autorización, fácil de testear |
| app/Exceptions | 76 | 2,6 % | ✅ | |
| app/Rules | 104 | 0,0 % | ✅ **Muy alta** | Reglas de validación: `passes()` puro |
| app/Events | 19 | 36,8 % | ✅ | |
| app/Traits | 2 | 0,0 % | ✅ | |

---

## 3. Decisión estratégica sobre `app/Http` (elegir 1)

Para superar el techo del ~52 % hay que tomar una de estas vías. **Recomiendo la A**, con B como complemento.

### ▶ Opción A (recomendada) — Acotar el `<source>` de cobertura a lo unit-testeable
Editar `phpunit.xml` para **excluir de la medición** el código que por diseño no se prueba unitariamente (controladores Http, Console, Livewire), dejando la métrica sobre el "núcleo de dominio" (Models, Presenters, Helpers, Notifications, Rules, Services, Actions, Policies, Observers, Listeners, Mail, Importer).
```xml
<source>
  <include><directory suffix=".php">app/</directory></include>
  <exclude>
    <directory suffix=".php">app/Http</directory>
    <directory suffix=".php">app/Console</directory>
    <directory suffix=".php">app/Livewire</directory>
  </exclude>
</source>
```
- **Pros:** la métrica de "cobertura unitaria" pasa a ser honesta y alcanzable; 85 % sobre el dominio es un objetivo de calidad real.
- **Contras:** cambia la base de cálculo; hay que documentarlo claramente (no es "trampa" si se explica que Http/Console se cubren en Feature).
- **Nuevo denominador** ≈ 35 439 − 13 981 (Http) − 2 654 (Console) − 841 (Livewire) = **17 963 líneas**. Meta 85 % ≈ **15 269** cubiertas.

### Opción B — Refactor: extraer lógica de controladores a Actions/Services
Mover la lógica de los controladores grandes a clases `app/Actions/*` y `app/Services/*` (patrón que el proyecto ya usa) y unit-testar esas clases. Reduce el peso "no testeable" de Http dejando los controladores como orquestadores delgados.
- **Pros:** mejora la arquitectura de verdad; sube cobertura unit legítima.
- **Contras:** esfuerzo alto y toca código de producción (riesgo de regresión).

### Opción C — Replicar pruebas de request dentro de `tests/Unit`
Escribir tests con `actingAs()/getJson()` pero ubicados en `tests/Unit`.
- **No recomendado:** infla la métrica sin valor real (son tests Feature renombrados) y duplica la suite.

---

## 4. Plan por fases (asumiendo Opción A + tests nuevos)

> Denominador objetivo ≈ 17 963 líneas (sin Http/Console/Livewire). Meta 85 % ≈ 15 269 cubiertas. Hoy en ese subconjunto: ≈ 2 773 (3008 − 64 Http − 107 Console − 0 Livewire). Falta ≈ **12 500 líneas**.

### Fase 1 — Presenters (botín mayor) · +~4 200 líneas
- **Objetivo:** `app/Presenters` 0,40 % → ≥ 85 % (de 5 239 líneas).
- Archivos clave: `AssetPresenter.php` (3/458 → objetivo), `Presenter.php` (14/47), `PredefinedKitPresenter`, `DepreciationReportPresenter` (0 %), y el resto de `*Presenter`.
- Enfoque: crear modelo con factory, instanciar presenter, afirmar columnas/estructura de cada método `dataTableLayout()`, accessors, formato.
- Estimación: ~25–35 archivos de test, ~150–250 casos.

### Fase 2 — Models de dominio · +~3 700 líneas
- **Objetivo:** `app/Models` 21,2 % → ≥ 80–85 % (de 6 315).
- Prioridad por tamaño/baja cobertura: `Asset.php` (172/584), `User.php` (91/406), `Setting.php` (19/118), `Actionlog.php` (40/138), `CustomField.php` (28/105), `CustomFieldset.php` (5/80), `CheckoutAcceptance.php` (4/116), `Traits/Searchable.php` (1/462 ⚠️ enorme y casi sin cubrir), `Traits/Loggable.php` (37/261), `LicenseSeat`, `Accessory`, `Consumable`, `Statuslabel`, `Maintenance`.
- Enfoque: scopes, accessors/mutators, métodos de negocio (`availableSeats`, `isDeletable`, `checkout`-helpers), relaciones con factories.
- Estimación: ~30 archivos de test, ~250–350 casos.

### Fase 3 — Notifications + Mail · +~1 400 líneas
- **Objetivo:** `app/Notifications` 0 % → ≥ 85 % (1 287) y `app/Mail` 12,9 % → ≥ 85 % (503).
- Enfoque: construir la notificación/mailable con sus datos y afirmar `via()`, `toArray()`, `toMail()`, `toSlack()` sin envío real (`Notification::fake()` no hace falta para cubrir el `toX()` directo).
- Estimación: ~30 archivos, ~120–180 casos.

### Fase 4 — Rules, Policies, Services, Actions, Observers, Listeners · +~1 000 líneas
- `app/Rules` 0 % → ~100 % (104 líneas, `passes()` puro — rápido y barato).
- `app/Policies` 13 % → ≥ 85 % (76): cada método con usuario con/sin permiso.
- `app/Services` 9,9 %, `app/Actions` 10,7 %, `app/Observers` 44 %, `app/Listeners` 24 % → ≥ 85 %.
- Estimación: ~25 archivos, ~150 casos.

### Fase 5 — Helpers + Importer · +~1 700 líneas
- `app/Helpers/Helper.php` (365/837) y `IconHelper.php` (1/275) → ≥ 85 %.
- `app/Importer/*` (20/959) → ≥ 70–85 % con CSV de prueba y factories.
- Estimación: ~12 archivos, ~120 casos.

### (Opcional) Fase 6 — si NO se acota el `<source>`: atacar Http vía Opción B
Solo si se decide medir Http: refactor incremental de los controladores más grandes (`ReportsController` 774, `SettingsController` 558, `AssetsController` 488…) extrayendo Actions unit-testables. Esfuerzo muy alto.

---

## 5. Infraestructura / tooling necesario

1. **Memoria:** ejecutar siempre con `php -d memory_limit=-1` (la instrumentación PCOV agota 128 MB).
2. **Comando de medición Unit:**
   ```bash
   php -d memory_limit=-1 vendor/bin/phpunit --testsuite Unit \
     --coverage-text --coverage-html tests/coverage/html
   ```
3. **Helpers de test:** reutilizar factories existentes; crear *data providers* para cubrir ramas (boolean/null/permiso sí-no) y subir cobertura de líneas por caso.
4. **CI:** ya existe `.github/workflows/tests-unit-coverage.yml` (PCOV, suite Unit, Clover+HTML). Añadir un **umbral mínimo** que rompa el build si baja del objetivo:
   ```bash
   vendor/bin/phpunit --testsuite Unit --coverage-clover coverage/clover.xml
   # luego validar el % del clover (script o herramienta tipo coverage-check)
   ```
5. **Pint/larastan:** correr sobre los tests nuevos para mantener estilo.

---

## 6. Antes de empezar: corregir los 8 fallos Unit actuales

El plan parte de una suite **verde**. Resolver primero:
- **6 fallos LDAP** → habilitar `extension=ldap` en el `php.ini` de Herd (o marcar los tests como *skip* si LDAP no aplica al entorno académico).
- **2 fallos `LicenseTest`** → añadir el estado `withSeats()` a `LicenseFactory` y revisar la expectativa de `isDeletable()` con seats libres.

---

## 7. Estimación global y riesgos

| | Valor |
|---|---|
| Líneas a cubrir (Opción A) | ≈ 12 500 |
| Archivos de test nuevos | ≈ 120–135 |
| Casos de prueba nuevos | ≈ 800–1 100 |
| Esfuerzo orientativo | Alto (varias semanas-persona) |

**Riesgos:**
- **Sin Opción A/B no se llega a 85 %** (techo ~52 %). Es la decisión más importante.
- Tests de Models/Importer dependientes de BD → más lentos; vigilar tiempo de suite (hoy Unit ≈ 1 min; crecerá).
- Algunos fallos pueden ser específicos de **SQLite** (como los `count alias` vistos en Feature). Si aparecen en Unit, decidir si se prueba contra MySQL.
- Cobertura de líneas ≠ calidad: complementar con aserciones significativas, no solo "tocar" líneas.

---

## 8. Métrica de seguimiento sugerida

Tras cada fase, registrar el % real (no estimado) en una tabla:

| Hito | % Unit (líneas) objetivo |
|---|---|
| Base actual | 8,49 % |
| Acotar `<source>` (Opción A) | ~15–16 % (mismo nº cubierto, menor denominador) |
| Fin Fase 1 (Presenters) | ~40 % |
| Fin Fase 2 (Models) | ~62 % |
| Fin Fase 3 (Notifications/Mail) | ~70 % |
| Fin Fase 4 (Rules/Policies/…) | ~77 % |
| Fin Fase 5 (Helpers/Importer) | **≥ 85 %** ✅ |

> Los porcentajes de los hitos son **proyecciones de planificación**, no mediciones; cada hito debe verificarse ejecutando la cobertura real.
