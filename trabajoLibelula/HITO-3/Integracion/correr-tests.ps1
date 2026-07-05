# =============================================================================
# correr-tests.ps1  —  Corre la suite Feature (integración) en Docker.
# Entorno común del grupo: mismas condiciones para todos, igual que el CI.
#
# Requisito: Docker Desktop ABIERTO (no hace falta levantar la app ni MariaDB).
#
# Ejecutar DESDE LA RAÍZ del repositorio snipe-it:
#   .\trabajoLibelula\HITO-3\Integracion\correr-tests.ps1
#
# Correr un subconjunto (opcional):
#   .\trabajoLibelula\HITO-3\Integracion\correr-tests.ps1 "tests/Feature/Checkouts"
#   .\trabajoLibelula\HITO-3\Integracion\correr-tests.ps1 "--testsuite=Feature --filter=Checkout"
# =============================================================================
param([string]$Target = "--testsuite=Feature")

$compose = "trabajoLibelula/HITO-3/Integracion/docker-compose.test.yml"

if (-not (Test-Path "artisan")) {
    Write-Host "  [X] Ejecuta este script desde la RAIZ del repo (donde esta 'artisan')." -ForegroundColor Red
    exit 1
}

Write-Host "  Levantando contenedor de pruebas (efimero) y corriendo: $Target" -ForegroundColor Cyan
docker compose -f $compose run --rm test bash -lc "([ -d vendor ] || composer install --no-interaction --prefer-dist --no-progress); php -d memory_limit=-1 artisan test $Target"
