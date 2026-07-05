# Entorno común de pruebas en Docker (grupo) — Opción A

> Objetivo: que **todos** ejecutemos las pruebas de integración (`tests/Feature`) bajo **las mismas condiciones**, idénticas al CI, sin depender del PHP local de cada quien (que causaba el error de "memoria insuficiente").

---

## 1. Modelo mental: son DOS Docker distintos

| | App para caja negra (ya lo usan) | **Runner de tests (esto)** |
|---|---|---|
| Archivo | `docker-compose.yml` (raíz) | `docker-compose.test.yml` (esta carpeta) |
| Qué es | La app **corriendo** + MariaDB | Contenedor de **un solo uso** que corre los tests y se apaga |
| Comando | `docker compose up` (queda levantado) | `... run --rm test` (corre → resultados → se borra) |
| ¿BD? | MariaDB | **SQLite en memoria** (no necesita servicio de BD) |
| ¿Queda corriendo? | Sí | **No**, es efímero |

**No necesitas levantar la app ni la BD para correr los tests.** `run --rm test` ya "levanta y baja" en un solo paso.

---

## 2. Requisito único

- **Docker Desktop abierto** (el daemon corriendo). Nada más.

---

## 3. Cómo se corre (paso a paso)

Desde la **raíz** del repositorio `snipe-it`:

**Opción rápida (wrapper):**
```powershell
.\trabajoLibelula\HITO-3\Integracion\correr-tests.ps1
```

**Opción directa (docker compose):**
```powershell
docker compose -f trabajoLibelula/HITO-3/Integracion/docker-compose.test.yml run --rm test
```

- **La 1ª vez:** construye la imagen (descarga PHP + extensiones). Tarda unos minutos. **Solo una vez.**
- **Siguientes veces:** usa la imagen cacheada → arranca en segundos.
- Al terminar, imprime el resumen `PASS/FAIL` y **borra** el contenedor (`--rm`).

### Correr un subconjunto (más rápido para depurar)
```powershell
.\trabajoLibelula\HITO-3\Integracion\correr-tests.ps1 "tests/Feature/Checkouts"
```
o directo:
```powershell
docker compose -f trabajoLibelula/HITO-3/Integracion/docker-compose.test.yml run --rm test `
  bash -lc "php -d memory_limit=-1 artisan test tests/Feature/Checkins"
```

---

## 4. Qué hace por dentro

1. Monta la raíz del repo dentro del contenedor en `/app` (tu código, sin copiar).
2. Si falta `vendor/`, corre `composer install` dentro del contenedor.
3. Ejecuta `php -d memory_limit=-1 artisan test --testsuite=Feature`.
4. Usa `.env.testing` → conexión `sqlite_testing` (`:memory:`) y drivers `array`/`sync` de `phpunit.xml`.

Esto reproduce **exactamente** el entorno del workflow de GitHub Actions.

---

## 5. Preguntas frecuentes

- **¿Tengo que hacer `docker compose up` primero?** No. Eso es para la app. Para tests solo usas `run --rm test`.
- **¿Choca con la app que uso en localhost:8000?** No. Es otro archivo compose, sin puertos ni BD; pueden convivir.
- **¿Y el error de memoria?** Resuelto: el contenedor corre con `memory_limit=-1` (como el CI). El problema era el `memory_limit=128M` de PHP local, no SQLite ni el hardware.
- **¿Puedo seguir usando `php artisan test` con Herd?** Sí, pero recuerda `php -d memory_limit=-1 ...`. Docker es para garantizar que **todos** corran igual.
- **¿Cambiar la versión de PHP?** Edita `FROM php:8.3-cli` en `Dockerfile.test` (8.2 / 8.3 / 8.4, la matriz del CI) y reconstruye con `--build`.

---

## 6. Archivos de este entorno
- `Dockerfile.test` — imagen PHP + extensiones + SQLite + Composer.
- `docker-compose.test.yml` — servicio `test` efímero (monta el repo, corre la suite).
- `correr-tests.ps1` — atajo para Windows.
- `README-ENTORNO-DOCKER.md` — este documento.

*Entorno común de integración — Hito 3. Curso de Pruebas de Software.*
