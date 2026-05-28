# Snipe-IT QA Landing Page - Hito 1

Esta carpeta contiene la documentación y presentación web estática para el Hito 1 del curso **Pruebas de Software**.

## Estructura del Sitio Estático
```text
docs/
├── index.html              # Código HTML5 principal con la estructura semántica
└── assets/
    ├── css/
    │   └── styles.css      # Hoja de estilos CSS3 moderna (Responsiva y accesible)
    └── img/                # Carpeta para almacenar imágenes adicionales o capturas de pantalla
```

## Instrucciones de Despliegue en GitHub Pages
Para habilitar la visualización del sitio en la web a través del servicio de GitHub Pages del fork del proyecto:

1. Ve al repositorio en GitHub (`JhossepV/SO_FinalProyect`).
2. Entra a **Settings** (Configuración) > **Pages** en la barra lateral.
3. En la sección **Build and deployment**:
   - Source: **Deploy from a branch**
   - Branch: Selecciona tu rama principal (ej. `master` o `main`) y cambia la carpeta `/ (root)` por `/docs`.
4. Haz clic en **Save** (Guardar).
5. En unos minutos, el sitio web estará disponible públicamente en `https://JhossepV.github.io/SO_FinalProyect/`.

## Características de Diseño
* **Semántica Correcta**: Uso estricto de etiquetas HTML5 (`<header>`, `<nav>`, `<main>`, `<section>`, `<article>`, `<footer>`).
* **Visual Premium**: Gradientes modernos, componentes responsivos simulados de Dashboard de métricas y tuberías CI/CD, además de tarjetas y badges con sombras suaves.
* **Sin Dependencias Externas**: No utiliza librerías externas ni CDNs (Bootstrap, Tailwind, FontAwesome, etc.) cumpliendo con la restricción académica.
* **Accesible y Responsivo**: Menú adaptativo en pantallas móviles, contraste óptimo y tipografía legible importada de Google Fonts.
