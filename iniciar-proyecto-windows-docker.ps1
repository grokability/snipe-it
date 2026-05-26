# =============================================================================
# iniciar-snipeit.ps1
# Script para levantar el proyecto Snipe-IT usando Docker Compose
# Universidad 2026-B - Pruebas de Software
# =============================================================================

$Host.UI.RawUI.WindowTitle = "Snipe-IT Docker Launcher"

function Write-Header {
    Clear-Host
    Write-Host ""
    Write-Host "  +==================================================+" -ForegroundColor Cyan
    Write-Host "  |       SNIPE-IT  --  DOCKER LAUNCHER              |" -ForegroundColor Cyan
    Write-Host "  |       Sistema de Gestion de Activos IT            |" -ForegroundColor Cyan
    Write-Host "  +==================================================+" -ForegroundColor Cyan
    Write-Host ""
}

function Write-Step {
    param([string]$Number, [string]$Message)
    Write-Host "  [$Number] " -ForegroundColor Yellow -NoNewline
    Write-Host $Message -ForegroundColor White
}

function Write-OK {
    param([string]$Message)
    Write-Host "  [OK] " -ForegroundColor Green -NoNewline
    Write-Host $Message -ForegroundColor Green
}

function Write-Warn {
    param([string]$Message)
    Write-Host "  [!]  " -ForegroundColor Yellow -NoNewline
    Write-Host $Message -ForegroundColor Yellow
}

function Write-Err {
    param([string]$Message)
    Write-Host "  [X]  " -ForegroundColor Red -NoNewline
    Write-Host $Message -ForegroundColor Red
}

function Write-Info {
    param([string]$Message)
    Write-Host "  [i]  " -ForegroundColor Cyan -NoNewline
    Write-Host $Message -ForegroundColor Cyan
}

function Pause-Exit {
    Write-Host ""
    Write-Host "  Presiona cualquier tecla para salir..." -ForegroundColor DarkGray
    $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
    exit 1
}

# =============================================================================
# INICIO
# =============================================================================

Write-Header

# El script esta dentro de la carpeta del proyecto
$ScriptDir  = Split-Path -Parent $MyInvocation.MyCommand.Definition
$ProjectDir = $ScriptDir

Write-Info "Directorio del proyecto: $ProjectDir"
Write-Host ""

# -----------------------------------------------------------------------------
# PASO 1 - Verificar Docker instalado
# -----------------------------------------------------------------------------
Write-Step "1/6" "Verificando instalacion de Docker..."

$dockerCmd = Get-Command docker -ErrorAction SilentlyContinue
if (-not $dockerCmd) {
    Write-Err "Docker no esta instalado o no esta en el PATH."
    Write-Host ""
    Write-Host "  Descarga Docker Desktop desde:" -ForegroundColor White
    Write-Host "  https://www.docker.com/products/docker-desktop/" -ForegroundColor Cyan
    Pause-Exit
}

$dockerVersion = (docker --version 2>&1)
Write-OK "Docker encontrado: $dockerVersion"

# -----------------------------------------------------------------------------
# PASO 2 - Verificar Docker Compose
# -----------------------------------------------------------------------------
Write-Step "2/6" "Verificando Docker Compose..."

docker compose version 2>&1 | Out-Null
if ($LASTEXITCODE -ne 0) {
    Write-Err "Docker Compose no esta disponible. Actualiza Docker Desktop."
    Pause-Exit
}

$composeVersion = (docker compose version 2>&1)
Write-OK "Docker Compose disponible: $composeVersion"

# -----------------------------------------------------------------------------
# PASO 3 - Verificar que el daemon de Docker este activo
# -----------------------------------------------------------------------------
Write-Step "3/6" "Verificando que Docker este corriendo..."

docker info 2>&1 | Out-Null
if ($LASTEXITCODE -ne 0) {
    Write-Err "El daemon de Docker no esta activo."
    Write-Warn "Abre Docker Desktop y espera a que este completamente iniciado."
    Pause-Exit
}
Write-OK "Docker daemon esta activo."

# -----------------------------------------------------------------------------
# PASO 4 - Verificar carpeta del proyecto
# -----------------------------------------------------------------------------
Write-Step "4/6" "Verificando archivos del proyecto..."

$dockerComposeFile = Join-Path $ProjectDir "docker-compose.yml"
if (-not (Test-Path $dockerComposeFile)) {
    Write-Err "No se encontro docker-compose.yml en:"
    Write-Host "    $ProjectDir" -ForegroundColor Red
    Write-Warn "Asegurate de ejecutar el script desde dentro de la carpeta snipe-it-tests."
    Pause-Exit
}
Write-OK "Proyecto verificado: $ProjectDir"

Set-Location $ProjectDir

# -----------------------------------------------------------------------------
# PASO 5 - Preparar el archivo .env
# -----------------------------------------------------------------------------
Write-Step "5/6" "Preparando archivo de configuracion .env..."

$envFile       = Join-Path $ProjectDir ".env"
$envDockerFile = Join-Path $ProjectDir ".env.docker"

if (Test-Path $envFile) {
    Write-OK "El archivo .env ya existe - se usara el existente."
}
elseif (Test-Path $envDockerFile) {
    Copy-Item $envDockerFile $envFile
    Write-OK "Archivo .env creado desde .env.docker"
    Write-Warn "Revisa .env si deseas cambiar puertos o contrasenas."
}
else {
    Write-Err "No se encontro .env ni .env.docker en el proyecto."
    Pause-Exit
}

# Leer el puerto configurado (default 8000)
$appPort = "8000"
$envContent = Get-Content $envFile -ErrorAction SilentlyContinue
foreach ($line in $envContent) {
    if ($line -match "^APP_PORT=(\d+)") {
        $appPort = $Matches[1]
        break
    }
}
Write-Info "Puerto configurado: $appPort"

# -----------------------------------------------------------------------------
# PASO 6 - Levantar los contenedores
# -----------------------------------------------------------------------------
Write-Step "6/6" "Levantando contenedores con Docker Compose..."
Write-Host ""

$containersRunning = docker compose ps --status running -q 2>$null

if ($containersRunning) {
    Write-Warn "Los contenedores ya estan corriendo."
    Write-Host ""
    Write-Host "  Que deseas hacer?" -ForegroundColor White
    Write-Host "  [1] Abrir el navegador directamente" -ForegroundColor Yellow
    Write-Host "  [2] Reiniciar los contenedores (down + up)" -ForegroundColor Yellow
    Write-Host "  [3] Ver logs en tiempo real" -ForegroundColor Yellow
    Write-Host "  [4] Detener los contenedores" -ForegroundColor Yellow
    Write-Host "  [5] Salir" -ForegroundColor Yellow
    Write-Host ""
    $choice = Read-Host "  Selecciona una opcion (1-5)"

    switch ($choice) {
        "1" {
            Write-Info "Abriendo navegador..."
        }
        "2" {
            Write-Info "Reiniciando contenedores..."
            docker compose down
            Write-Host ""
            docker compose up -d
        }
        "3" {
            Write-Info "Mostrando logs (Ctrl+C para salir)..."
            docker compose logs -f
            exit 0
        }
        "4" {
            Write-Info "Deteniendo contenedores..."
            docker compose stop
            Write-OK "Contenedores detenidos."
            Write-Host ""
            Write-Host "  Presiona cualquier tecla para salir..." -ForegroundColor DarkGray
            $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
            exit 0
        }
        default {
            Write-Info "Saliendo..."
            exit 0
        }
    }
}
else {
    Write-Info "Iniciando contenedores (puede tardar varios minutos la primera vez)..."
    Write-Host ""
    docker compose up -d

    if ($LASTEXITCODE -ne 0) {
        Write-Host ""
        Write-Err "Error al levantar los contenedores."
        Write-Info "Revisa los logs con: docker compose logs"
        Pause-Exit
    }
}

# =============================================================================
# ESPERAR A QUE LA APP ESTE LISTA
# =============================================================================
Write-Host ""
Write-Info "Esperando a que Snipe-IT responda en http://localhost:$appPort ..."
Write-Host ""

$maxAttempts = 30
$attempt     = 0
$ready       = $false

while ($attempt -lt $maxAttempts) {
    $attempt++
    try {
        $response = Invoke-WebRequest -Uri "http://localhost:$appPort" `
                                      -TimeoutSec 3 `
                                      -UseBasicParsing `
                                      -ErrorAction Stop
        if ($response.StatusCode -lt 500) {
            $ready = $true
            break
        }
    }
    catch {
        # Todavia no esta lista la app
    }

    Write-Host "`r  Intento $attempt/$maxAttempts - esperando...   " -NoNewline -ForegroundColor DarkGray
    Start-Sleep -Seconds 3
}

Write-Host ""
Write-Host ""

# =============================================================================
# RESULTADO FINAL
# =============================================================================
Write-Host "  +==================================================+" -ForegroundColor Cyan

if ($ready) {
    Write-Host "  |   SNIPE-IT ESTA LISTO Y CORRIENDO               |" -ForegroundColor Green
}
else {
    Write-Host "  |   La app puede seguir iniciando, intenta en 30s  |" -ForegroundColor Yellow
}

Write-Host "  +==================================================+" -ForegroundColor Cyan
Write-Host ""
Write-Host "  URL de acceso:" -ForegroundColor White
Write-Host "  http://localhost:$appPort" -ForegroundColor Cyan
Write-Host ""
Write-Host "  Comandos utiles:" -ForegroundColor White
Write-Host "    docker compose ps           -> ver estado" -ForegroundColor DarkGray
Write-Host "    docker compose logs -f      -> ver logs en vivo" -ForegroundColor DarkGray
Write-Host "    docker compose stop         -> detener sin borrar datos" -ForegroundColor DarkGray
Write-Host "    docker compose down -v      -> eliminar TODO (incluye BD)" -ForegroundColor DarkGray
Write-Host ""

Start-Process "http://localhost:$appPort"

Write-Host ""
Write-Host "  Presiona cualquier tecla para salir..." -ForegroundColor DarkGray
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
