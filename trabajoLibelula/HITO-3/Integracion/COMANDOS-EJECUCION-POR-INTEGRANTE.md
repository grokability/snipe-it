# Comandos de ejecución por integrante (Docker · solo su parte)

> Objetivo: cada quien corre **solo sus pruebas**, no toda la suite `Feature` (que tarda ~32 min).
> Requisito: **Docker Desktop abierto**. Ejecutar **desde la raíz del repo** `snipe-it`.

---

## 0. Patrón general (dos variantes)

**Rápido — SQLite (para iterar mientras desarrollas):**
```bash
docker compose -f trabajoLibelula/HITO-3/Integracion/docker-compose.test.yml run --rm test \
  bash -lc "php artisan test <RUTA_O_FILTRO>"
```

**Oficial — MariaDB (para la evidencia final; apagar la BD al terminar):**
```bash
docker compose -f trabajoLibelula/HITO-3/Integracion/docker-compose.test.yml run --rm test-mysql \
  bash -lc "php artisan test <RUTA_O_FILTRO>"
docker compose -f trabajoLibelula/HITO-3/Integracion/docker-compose.test.yml down
```

Formas de acotar `<RUTA_O_FILTRO>`:
- **Un archivo:** `tests/Feature/Integracion/MiTest.php`
- **Una carpeta:** `tests/Feature/Integracion`
- **Varios a la vez:** `tests/Feature/Licenses tests/Feature/LicenseSeats`
- **Un solo método:** `--filter nombre_del_metodo`

> Atajo Windows (solo SQLite): `.\trabajoLibelula\HITO-3\Integracion\correr-tests.ps1 "tests/Feature/Integracion/MiTest.php"`

---

## 1. Anette — Licencias / Empresa (INT-04, CPF-08, INT-07 FMCS)

```bash
# Su test nuevo (agotar asientos)
docker compose -f trabajoLibelula/HITO-3/Integracion/docker-compose.test.yml run --rm test \
  bash -lc "php artisan test tests/Feature/Integracion/LicenseSeatExhaustionTest.php"

# Consolidar flujos de licencia heredados (INT-04)
docker compose -f trabajoLibelula/HITO-3/Integracion/docker-compose.test.yml run --rm test \
  bash -lc "php artisan test tests/Feature/Licenses tests/Feature/LicenseSeats"

# FMCS cross-company (INT-07)
docker compose -f trabajoLibelula/HITO-3/Integracion/docker-compose.test.yml run --rm test \
  bash -lc "php artisan test tests/Feature/Companies"
```

## 2. Jeanpiero — Fallas de interfaz FI-01 y FI-02

```bash
# Su archivo de fallas de interfaz (checkout de activo)
docker compose -f trabajoLibelula/HITO-3/Integracion/docker-compose.test.yml run --rm test \
  bash -lc "php artisan test tests/Feature/Integracion/AssetCheckoutInterfaceTest.php"

# Solo FI-01 (o FI-02) por nombre de método:
docker compose -f trabajoLibelula/HITO-3/Integracion/docker-compose.test.yml run --rm test \
  bash -lc "php artisan test --filter test_fi_01_sintactica"
```

## 3. Jherson — FI-03 + Depreciación (INT-12) + Consumibles/Componentes (INT-05/06)

```bash
# FI-03 (resiliencia/estado) — si comparte archivo con Jeanpiero, filtra por método
docker compose -f trabajoLibelula/HITO-3/Integracion/docker-compose.test.yml run --rm test \
  bash -lc "php artisan test --filter test_fi_03_resiliencia"

# INT-12 Depreciación (su test nuevo)
docker compose -f trabajoLibelula/HITO-3/Integracion/docker-compose.test.yml run --rm test \
  bash -lc "php artisan test tests/Feature/Integracion/DepreciacionIntegracionTest.php"

# INT-05/06 consumibles y componentes (heredados)
docker compose -f trabajoLibelula/HITO-3/Integracion/docker-compose.test.yml run --rm test \
  bash -lc "php artisan test tests/Feature/Consumables tests/Feature/Components"
```

## 4. Jhastyn — CustomFields (INT-11) + StatusLabel (INT-13)

```bash
# INT-11 CustomFields ↔ Asset (su test nuevo + heredados)
docker compose -f trabajoLibelula/HITO-3/Integracion/docker-compose.test.yml run --rm test \
  bash -lc "php artisan test tests/Feature/Integracion/CustomFieldAssetTest.php tests/Feature/CustomFields"

# INT-13 StatusLabel ↔ disponibilidad (su test nuevo + heredados)
docker compose -f trabajoLibelula/HITO-3/Integracion/docker-compose.test.yml run --rm test \
  bash -lc "php artisan test tests/Feature/Integracion/StatusLabelDisponibilidadTest.php tests/Feature/StatusLabels"
```

## 5. Wilson — CI/CD (corre la suite completa, pero en GitHub Actions, no local)

El workflow de GitHub Actions corre la suite en el servidor (no en la PC de nadie). Para una verificación local puntual del entorno:
```bash
docker compose -f trabajoLibelula/HITO-3/Integracion/docker-compose.test.yml run --rm test-mysql \
  bash -lc "php artisan test tests/Feature/Integracion"
docker compose -f trabajoLibelula/HITO-3/Integracion/docker-compose.test.yml down
```

---

## 6. Notas importantes

- **`test` (SQLite)** = rápido, para iterar. **`test-mysql` (MariaDB)** = evidencia oficial; **siempre** hacer `... down` al terminar.
- Los nombres de archivo `*.php` de la carpeta `Integracion/` son los **planificados**; existirán cuando cada autor los cree.
- La **1ª vez** que alguien lo usa, Docker construye la imagen (unos minutos, una sola vez).
- Al ejecutar, cada quien anota su **`Resultado Real`** en el **Informe** (no en el Plan).

*Comandos de ejecución por integrante — Hito 3 · Integración.*
