# COMO_EJECUTAR.md — Snipe-IT en Windows con Docker

Guia paso a paso para iniciar el proyecto **Snipe-IT** en Windows usando
el script automatizado `iniciar-proyecto-windows-docker.ps1`.

---

## Requisito previo: Docker Desktop

El script usa Docker para levantar la aplicacion y la base de datos
sin necesidad de instalar PHP, MySQL ni ninguna dependencia adicional.

1. Descarga Docker Desktop desde:
   https://www.docker.com/products/docker-desktop/

2. Instala y reinicia Windows si se solicita.

3. Abre Docker Desktop y espera a que el icono de la ballena en la
   barra de tareas muestre el estado **"Engine running"**.

4. Verifica en PowerShell que funciona:

```powershell
docker --version
docker compose version
```

---

## Estructura de archivos relevantes

```
snipe-it/
|-- iniciar-proyecto-windows-docker.ps1   <- script de inicio
|-- docker-compose.yml                    <- define los contenedores
|-- .env.docker                           <- configuracion base para Docker
|-- .env                                  <- archivo activo (se crea automaticamente)
```

---

## Paso 1 — Abrir PowerShell en la carpeta del proyecto

Tienes dos opciones:

**Opcion A — desde el Explorador de archivos:**
1. Navega hasta la carpeta `snipe-it`.
2. Haz clic en la barra de direcciones, escribe `powershell` y presiona Enter.

**Opcion B — desde PowerShell:**
```powershell
cd "c:\Users\JeanpieroHC\Downloads\UNIVERSIDAD 2026-B\PRUEBAS DE SOFTWARE\TEORIA\Proyecto final\snipe-it"
```

---

## Paso 2 — Permitir la ejecucion de scripts (solo la primera vez)

Por defecto Windows bloquea la ejecucion de scripts `.ps1`.
Ejecuta este comando una sola vez para habilitarlos en tu usuario:

```powershell
Set-ExecutionPolicy -Scope CurrentUser -ExecutionPolicy RemoteSigned
```

Cuando pregunte, escribe `S` y presiona Enter para confirmar.

---

## Paso 3 — Ejecutar el script

```powershell
.\iniciar-proyecto-windows-docker.ps1
```

---

## Que hace el script automaticamente

El script ejecuta **6 pasos** de forma automatica:

```
[1/6] Verifica que Docker este instalado y en el PATH
[2/6] Verifica que Docker Compose este disponible
[3/6] Verifica que el daemon de Docker este activo
[4/6] Verifica que docker-compose.yml exista en la carpeta
[5/6] Crea el archivo .env desde .env.docker (si no existe)
[6/6] Levanta los contenedores con: docker compose up -d
```

Despues de levantar los contenedores, el script espera a que la
aplicacion responda en `http://localhost:8000` y luego abre el
navegador automaticamente.

---

## Menu interactivo (si los contenedores ya estan corriendo)

Si el script detecta que los contenedores ya estan activos, muestra
este menu en lugar de volver a iniciarlos:

```
  Que deseas hacer?
  [1] Abrir el navegador directamente
  [2] Reiniciar los contenedores (down + up)
  [3] Ver logs en tiempo real
  [4] Detener los contenedores
  [5] Salir
```

Escribe el numero de tu eleccion y presiona Enter.

---

## Acceso a la aplicacion

Una vez que el script termine exitosamente, abre en tu navegador:

```
http://localhost:8000
```

**Primera vez:** aparece el asistente de configuracion de Snipe-IT
donde debes crear el usuario administrador y configurar el sitio.

---

## Contenedores que se crean

| Contenedor | Descripcion | Puerto |
|---|---|---|
| `snipe-it-app-1` | Aplicacion Snipe-IT (PHP + Apache) | 8000 -> 80 |
| `snipe-it-db-1` | Base de datos MariaDB 11.4 | interno |

---

## Comandos Docker utiles (desde PowerShell)

```powershell
# Ver el estado de los contenedores
docker compose ps

# Ver logs en tiempo real (Ctrl+C para salir)
docker compose logs -f

# Ver solo los logs de la aplicacion
docker compose logs -f app

# Detener los contenedores SIN borrar datos
docker compose stop

# Volver a levantar despues de detener
docker compose start

# Detener y eliminar contenedores (datos persisten en volumenes)
docker compose down

# Eliminar TODO, incluidos los datos de la base de datos (IRREVERSIBLE)
docker compose down -v
```

---

## Cambiar el puerto de acceso

Si el puerto 8000 esta ocupado en tu maquina:

1. Abre el archivo `.env` con un editor de texto.
2. Cambia la linea:
   ```
   APP_PORT=8000
   ```
   por el puerto que prefieras, por ejemplo:
   ```
   APP_PORT=8080
   ```
3. Reinicia los contenedores (opcion 2 del menu o `docker compose down` + script).
4. Accede en `http://localhost:8080`.

---

## Solucion de problemas

### El script dice "Docker no esta instalado"
- Verifica que Docker Desktop este instalado.
- Cierra y vuelve a abrir PowerShell despues de instalar Docker.
- Comprueba con: `docker --version`

### El script dice "El daemon de Docker no esta activo"
- Abre Docker Desktop desde el menu Inicio.
- Espera a que el icono de la ballena muestre "Engine running".
- Vuelve a ejecutar el script.

### El script dice "No se encontro docker-compose.yml"
- Asegurate de estar ejecutando el script desde dentro de la carpeta `snipe-it`.
- Verifica que el archivo `docker-compose.yml` existe en esa carpeta.

### La aplicacion no carga en el navegador
- Espera 30 segundos adicionales; la primera vez puede tardar mas.
- Revisa los logs: `docker compose logs -f app`
- Asegurate de que ningun otro programa usa el puerto 8000.

### Error de permisos al ejecutar el script
Ejecuta este comando y vuelve a intentarlo:
```powershell
Set-ExecutionPolicy -Scope CurrentUser -ExecutionPolicy RemoteSigned
```

---

*Proyecto: Pruebas de Software — Universidad 2026-B*
