![](_page_0_Picture_0.jpeg)

![](_page_0_Picture_2.jpeg)

Formato: Guía de Práctica de Laboratorio / Talleres / Centros de Simulación

Aprobación: 2022/03/01 Código: GUIA-PRLD-001 Página: 1

# GUÍA DE LABORATORIO

# (formato docente)

| INFORMACIÓN BÁSICA        |                                                             |              |                       |                   |         |  |  |  |
|---------------------------|-------------------------------------------------------------|--------------|-----------------------|-------------------|---------|--|--|--|
| ASIGNATURA:               | PRUEBAS DE SOFTWARE                                         |              |                       |                   |         |  |  |  |
| TÍTULO DE LA<br>PRÁCTICA: | Pruebas de Integración: API Testing con Postman y Supertest |              |                       |                   |         |  |  |  |
| NÚMERO DE<br>PRÁCTICA:    | 08                                                          | AÑO LECTIVO: | 2026                  | NRO.<br>SEMESTRE: | VII     |  |  |  |
| TIPO DE<br>PRÁCTICA:      | INDIVIDUAL                                                  |              |                       |                   |         |  |  |  |
|                           | GRUPAL                                                      | X            | MÁXIMO DE ESTUDIANTES |                   | 4       |  |  |  |
| FECHA INICIO:             | 25/06/2026                                                  | FECHA FIN:   | 02/07/2026            | DURACIÓN:         | 100 min |  |  |  |

## RECURSOS A UTILIZAR:

- Una computadora personal
- Node.js / Supertest
- Visual Studio Code
- Postman

#### DOCENTE(s):

● Prof. Robert Arisaca / Prof. Diego Iquira / Prof. Lino Pinto

#### OBJETIVOS/TEMAS Y COMPETENCIAS

## OBJETIVOS:

- Validar la comunicación y el flujo de datos entre diferentes módulos o servicios mediante Pruebas de Integración.
- Automatizar la verificación de contratos de API y protocolos de comunicación en arquitecturas distribuidas utilizando Postman y Supertest.

## TEMAS:

- Pruebas de Integración (Small & Large)
- API Testing
- Automatización con Supertest
- Gestión de Colecciones en Postman.

![](_page_1_Picture_0.jpeg)

![](_page_1_Picture_2.jpeg)

Formato: Guía de Práctica de Laboratorio / Talleres / Centros de Simulación

Aprobación: 2022/03/01 Código: GUIA-PRLD-001 Página: 2

COMPETENCIAS C.m. Construye responsablemente soluciones siguiendo un proceso adecuado llevando a cabo las pruebas ajustadas a los recursos disponibles del cliente

# CONTENIDO DE LA GUÍA

#### I. MARCO CONCEPTUAL

## 1. Pruebas de Integración

Las pruebas de integración constituyen el segundo nivel fundamental del Modelo en V, ejecutándose una vez que las unidades de software individuales han sido validadas. Su objetivo principal no es verificar la lógica interna de un módulo, sino exponer defectos en las interfaces y en la interacción entre componentes integrados. Mientras que las pruebas unitarias aíslan el código, la integración busca detectar problemas de interoperabilidad, desajustes de datos y fallos en el flujo de trabajo entre servicios.

Existen dos alcances definidos para este nivel:

- Integración "en pequeña" (Small): Se enfoca en las interfaces entre componentes internos o subsistemas del propio software.
- Integración "en grande" (Large): También llamada integración de sistemas, verifica la comunicación con servicios externos, bases de datos o productos COTS (Commercial Off-the-Shelf).

#### 2. El Dominio de las Pruebas de API (API Testing)

En el ecosistema tecnológico actual, dominado por arquitecturas de microservicios y computación serverless, las pruebas de API han superado en importancia a las de interfaz de usuario (UI).

- Estabilidad y Velocidad: Las pruebas de API son significativamente más rápidas de ejecutar y más estables que las de UI, ya que no dependen de cambios cosméticos en el diseño.
- Validación de Contratos: El software moderno se construye como módulos débilmente acoplados que dependen de contratos explícitos. Las pruebas de integración aseguran que cada componente cumpla con su parte del contrato, evitando regresiones silenciosas.
- Seguridad y Robustez: Permiten validar protocolos de comunicación (REST/GraphQL) y el manejo de errores ante datos malformados antes de que lleguen a la capa de presentación.

#### 3. Estrategias de Integración e Infraestructura Técnica

Para evitar el enfoque de "Big Bang" (unir todo al final), que dificulta enormemente la depuración de fallos, se utilizan estrategias incrementales:

 Top-Down (De arriba hacia abajo): Comienza con los módulos superiores. Requiere el uso de Stubs, que son simuladores rudimentarios de componentes inferiores aún no desarrollados.

![](_page_2_Picture_0.jpeg)

![](_page_2_Picture_2.jpeg)

Formato: Guía de Práctica de Laboratorio / Talleres / Centros de Simulación

Aprobación: 2022/03/01 Código: GUIA-PRLD-001 Página: 3

- Bottom-Up (De abajo hacia arriba): Comienza con los componentes básicos. Utiliza Drivers, programas controladores que simulan las llamadas de módulos superiores.
- Mocks y Dummies: A diferencia de los stubs, los Mocks u objetos simulados ofrecen una funcionalidad más avanzada para disparar comportamientos específicos durante la prueba.

#### 4. Herramientas Actuales: Postman y Supertest

La elección de herramientas impacta directamente en la capacidad de escalar la calidad:

- Postman: Es la plataforma líder para el diseño y prueba de APIs. Permite la colaboración mediante Colecciones compartidas y la gestión de múltiples entornos (Desarrollo, Staging, Producción) mediante variables. Su integración en pipelines de CI/CD se realiza a través de Newman, asegurando que las pruebas corran automáticamente con cada commit.
- Supertest: Es una biblioteca de Node.js que proporciona una abstracción de alto nivel para probar servidores HTTP. Es ideal para equipos que prefieren pruebas basadas en código que puedan versionarse en el mismo repositorio de la aplicación. Permite realizar aserciones complejas sobre el cuerpo de la respuesta, cabeceras y estados HTTP de forma programática.

#### 5. Calidad Moderna: Shift-Left y Uso de la IA

La ingeniería de software actual exige un desplazamiento a la izquierda (Shift-Left), integrando la validación desde la definición de los requisitos.

- Observabilidad y Retroalimentación: Los defectos detectados en producción se alimentan de vuelta a los pipelines automatizados, creando bucles de calidad continua.
- IA Generativa en el Testing: Para 2026, la IA automatiza la generación de datos sintéticos realistas para evitar el uso de datos productivos sensibles. Herramientas avanzadas permiten ahora la autocuración (Self-healing) de scripts de prueba, donde el sistema detecta cambios en las estructuras de los datos y ajusta los casos de prueba automáticamente, reduciendo el mantenimiento manual en un 60-70%.
- Análisis Predictivo: Se utilizan algoritmos para identificar qué módulos tienen mayor probabilidad de fallo basándose en el historial de cambios, priorizando así los esfuerzos de integración en las áreas de mayor riesgo.

#### II. EJERCICIO/PROBLEMA RESUELTO POR EL DOCENTE

## Ejercicio 1: Pruebas de Integración en Postman (Flujo CRUD)

Para este ejercicio, construiremos un Flujo CRUD completo de Productos. Primero montaremos un servidor rápido en Node.js + Express y luego configuraremos la estrategia de pruebas encadenadas en Postman.

![](_page_3_Picture_0.jpeg)

![](_page_3_Picture_2.jpeg)

Formato: Guía de Práctica de Laboratorio / Talleres / Centros de Simulación

Aprobación: 2022/03/01 Código: GUIA-PRLD-001 Página: 4

## Parte 1: El Servidor Backend

Para que Postman tenga una API real a la cual trabajar, crearemos un servidor local que gestione productos en memoria.

## Paso 1: Configuración del proyecto Node.js

- 1. Crea una carpeta vacía en tu computadora.
- 2. Abre una terminal dentro de esa carpeta y ejecuta:

npm init -y

npm install express

- 3. Creamos un archivo server.js y utilizamos el archivo compartido
- 4. Para levantar el servidor ejecutamos el comando

node server.js

## Parte 2: Configuración en Postman

El objetivo de la prueba de integración es validar que el ciclo de vida del producto funcione de inicio a fin utilizando variables de entorno para pasar el ID del producto entre peticiones.

## Paso 1: Crear el Entorno (Environment)

- 1. En Postman, ve a la barra lateral izquierda y haz clic en Environments -> Create Environment.
- 2. Nómbralo como Desarrollo Local.
- 3. Añade una variable llamada productoId.
- 4. Hagan clic en Save y asegurence de seleccionar este entorno en la esquina superior derecha de Postman.

#### Paso 2: El Flujo de Peticiones (CRUD)

Añadiremos 4 peticiones consecutivas dentro de una nueva colección en Postman:

#### 1. POST - Crear Producto

Método: POST

URL: http://localhost:3000/api/productos

Body (JSON):

![](_page_4_Picture_0.jpeg)

![](_page_4_Picture_2.jpeg)

Formato: Guía de Práctica de Laboratorio / Talleres / Centros de Simulación

Aprobación: 2022/03/01 Código: GUIA-PRLD-001 Página: 5

Pestaña Tests: Aquí capturamos el ID dinámico que nos da Node.js y lo guardamos en nuestro entorno.

## 2. GET - Leer Producto Creado

- Método: GET
- URL: http://localhost:3000/api/productos/{{productoId}}
- Pestaña Tests: Validamos que el servidor nos devuelva exactamente el producto que acabamos de registrar.

#### 3. PUT - Actualizar Producto

- Método: PUT
- URL: http://localhost:3000/api/productos/{{productoId}}
- Body (JSON):

![](_page_5_Picture_0.jpeg)

![](_page_5_Picture_2.jpeg)

Formato: Guía de Práctica de Laboratorio / Talleres / Centros de Simulación

Aprobación: 2022/03/01 Código: GUIA-PRLD-001 Página: 6

Pestaña Tests: Verificamos que el cambio de estado se haya procesado de forma correcta en el servidor.

## 4. DELETE - Eliminar Producto

- Método: DELETE
- URL: http://localhost:3000/api/productos/{{productoId}}
- Pestaña Tests: Cerramos el ciclo eliminando el recurso y limpiando nuestro entorno de pruebas.

## Ejercicio 2: Pruebas de Integración con Supertest

Utilizando un entorno de pruebas con Jest y Supertest en Node.js, realiza una prueba de integración para una aplicación Express que simule la gestion de un carrito de compra, se va a verificar

- Añadir un producto al carrito.
- Verificar que el carrito ya no esté vacío y contenga ese producto.

#### 1. El Servidor Express (app.js)

Crea un archivo llamado app.js. Este código configura la API y guarda el carrito en la memoria del servidor.

![](_page_6_Picture_0.jpeg)

![](_page_6_Picture_2.jpeg)

Formato: Guía de Práctica de Laboratorio / Talleres / Centros de Simulación

Aprobación: 2022/03/01 Código: GUIA-PRLD-001 Página: 7

## 2. La Prueba de Integración (carrito.test.js)

Creamos un archivo llamado carrito.test.js. Aquí es donde Supertest actúa simulando los clics de un usuario real.

![](_page_7_Picture_0.jpeg)

![](_page_7_Picture_2.jpeg)

Formato: Guía de Práctica de Laboratorio / Talleres / Centros de Simulación

Aprobación: 2022/03/01 Código: GUIA-PRLD-001 Página: 8

#### III. EJERCICIOS/PROBLEMAS PROPUESTOS

#### 1. Automatización con Supertest

Elijan una temática de su preferencia (ej. catálogo de videojuegos, gestión de una biblioteca musical, reserva de películas, etc.) e implementen una API REST básica en Node.js + Express.

Posteriormente, diseñen y ejecuten una suite completa de pruebas de integración utilizando Supertest y Jest/Mocha.

#### Requerimientos de la Prueba:

- Flujo de Persistencia Cruzada: Validar la integración secuencial entre la creación de un recurso (POST /recurso) y su posterior lectura (GET /recurso/:id), asegurando que el ID generado dinámicamente en el primer endpoint sea el puente de entrada para el segundo.
- Simulación de Modificación de Estado: Implementar y verificar un endpoint que altere una propiedad cuantitativa del recurso (ej. restar stock, cambiar nivel, actualizar reproducciones) y confirmar mediante un GET posterior que el cambio se consolidó en el mock de persistencia o base de datos de pruebas.
- Validación de Robustez (Edge Cases): Asegurar que el sistema maneje correctamente los desajustes de datos o tipos inválidos (ej. enviar un texto en un campo numérico o dejar campos obligatorios vacíos) devolviendo exactamente el código de estado HTTP 400 (Bad Request) junto con su respectivo mensaje de error.

Entregables: \* Archivo de la API (app.js).

- Archivo de pruebas (tema\_libre.test.js).
- Captura de pantalla o reporte en texto del resultado de la ejecución en la terminal usando Jest/Mocha.

## 2. Pruebas de Integración del Proyecto Final

El grupo debe aplicar pruebas de integración sobre la arquitectura de su proyecto de fin de curso. El enfoque debe centrarse en verificar la cohesión y el flujo de datos entre los componentes que han sido desarrollados, El grupo puede elegir la herramienta (Requests, Supertest, REST Assured, Playwright, etc.) que mejor se adapte a su lenguaje de programación.

## Tarea de Análisis de Errores:

- 1. Mapeo de la Frontera: Identifique el punto donde el Subsistema A entrega el control o los datos al Subsistema B.
- 2. Inyección de Fallas de Interfaz: Diseñe al menos 3 casos de prueba orientados a "romper" la comunicación:
  - Caso 1 (Sintáctico): Envíe un objeto con campos faltantes o tipos de datos erróneos.

![](_page_8_Picture_0.jpeg)

![](_page_8_Picture_2.jpeg)

Formato: Guía de Práctica de Laboratorio / Talleres / Centros de Simulación

Aprobación: 2022/03/01 Código: GUIA-PRLD-001 Página: 9

- Caso 2 (Semántico): Envíe valores legales pero fuera de lógica para el receptor (ej. una fecha de entrega anterior a la de pedido).
- Caso 3 (Resiliencia): Simule una latencia alta en la respuesta del subsistema B y analice si el subsistema A maneja el timeout o colapsa.
- 3. Documentación de Discrepancias: Por cada falla encontrada, complete un Reporte de Incidente que detalle el "Resultado Esperado" vs. el "Resultado Real" observado en la interfaz.

## I. CUESTIONARIO

- 1. ¿Por qué las pruebas unitarias exitosas no garantizan que la integración será exitosa?
- 2. Explique la diferencia entre un Stub y un Driver y proporcione un ejemplo de cuándo usó uno de ellos en su proyecto.
- 3. Según Myers, ¿por qué es arriesgado que el mismo desarrollador que escribió el código de los módulos diseñe también las pruebas de integración?
- 4. Defina "Integración Incremental" y explique por qué el enfoque "Big Bang" debe evitarse en proyectos complejos
- 5. ¿Cómo ayuda la herramienta seleccionada por su grupo a detectar "defectos enmascarados" entre sus subsistemas?

## II. REFERENCIAS Y BIBLIOGRAFÍA RECOMENDADAS:

- [1] Spillner, A., Software Testing Foundations, 5th Ed., 2021.
- [2] Myers, G., The Art of Software Testing, 3rd Ed., 2012.

| TÉCNICAS E INSTRUMENTOS DE EVALUACIÓN   |               |  |  |  |  |
|-----------------------------------------|---------------|--|--|--|--|
| TÉCNICAS:                               | INSTRUMENTOS: |  |  |  |  |
| Ejercicios propuestos /<br>Cuestionario | Rúbrica       |  |  |  |  |

![](_page_9_Picture_0.jpeg)

![](_page_9_Picture_2.jpeg)

Formato: Guía de Práctica de Laboratorio / Talleres / Centros de Simulación

Aprobación: 2022/03/01 Código: GUIA-PRLD-001 Página: 10

## CRITERIOS DE EVALUACIÓN

| Criterio                                                                                           | Excelente<br>(100%)                                                                                                           | Bueno<br>(80%)                                                                                                                                | Suficiente<br>(55%)                                                                                                                                       | Insuficiente<br>(30%)                                                                                                                                                                     | No<br>presenta<br>(0%)                                                                                                               |
|----------------------------------------------------------------------------------------------------|-------------------------------------------------------------------------------------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------|
| Claridad<br>y<br>completitud de<br>la<br>explicación<br>de los ejercicios<br>propuestos.<br>(30 %) | Todos<br>los<br>ejercicios<br>propuestos han<br>sido explicados<br>de forma muy<br>clara y con la<br>profundidad<br>adecuada. | Los<br>ejercicios<br>propuestos han<br>sido resueltos y<br>explicados, pero<br>de manera muy<br>superficial<br>o<br>general.                  | Los<br>ejercicios<br>propuestos han<br>sido<br>resueltos,<br>sin<br>embargo,<br>algunos de ellos<br>no<br>han<br>sido<br>explicados.                      | Los<br>ejercicios<br>han<br>sido<br>resueltos<br>parcialmente y<br>no<br>tienen<br>explicaciones de<br>lo realizado.                                                                      | No<br>presentan<br>los<br>ejercicios<br>resueltos, o la<br>versión final del<br>informe<br>omite<br>por<br>completo<br>esta sección. |
| Ejercicios<br>Propuestos<br>(Código fuente)<br>(20 %)                                              | Todos<br>los<br>ejercicios<br>propuestos<br>se<br>ejecutan<br>correctamente<br>y devuelven los<br>resultados<br>esperados.    | Los<br>ejercicios<br>propuestos han<br>sido<br>implementados<br>pero<br>con<br>los<br>resultados<br>esperados<br>parciales.                   | Por lo menos el<br>2/3<br>de<br>los<br>ejercicios<br>propuestos han<br>sido<br>implementados<br>con resultados                                            | Por lo menos el<br>1/3<br>de<br>los<br>ejercicios<br>propuestos han<br>sido<br>implementados<br>con resultados                                                                            | No<br>se<br>han<br>implementado<br>los<br>ejercicios<br>propuestos.                                                                  |
| Cuestionario<br>(30%)                                                                              | Todas<br>las<br>respuestas son<br>completas,<br>correctas<br>y<br>proporcionan<br>explicaciones<br>claras, incluyen<br>citas. | La mayoría de<br>las<br>respuestas<br>son<br>correctas,<br>pero<br>algunas<br>carecen<br>de<br>la<br>profundidad<br>esperada, pocas<br>citas. | Algunas<br>respuestas son<br>correctas, pero<br>incompletas<br>o<br>con<br>falta<br>de<br>claridad en las<br>explicaciones,<br>citas<br>no<br>relevantes. | La mayoría de<br>las<br>respuestas<br>son<br>escuetas<br>y/o<br>incompletas, no<br>hay<br>evidencia<br>de<br>haber<br>buscado<br>información<br>que<br>las<br>respalden,<br>sin<br>citas. | No presenta el<br>cuestionario.                                                                                                      |
| Presentación<br>del<br>Informe<br>(20%)                                                            | El informe está<br>bien<br>estructurado,<br>con<br>excelente<br>redacción,<br>sin<br>errores                                  | El informe está<br>bien<br>estructurado,<br>con<br>algunos<br>errores<br>menores<br>de<br>redacción                                           | El<br>informe<br>presenta<br>problemas en la<br>organización<br>o<br>redacción,<br>con<br>varios errores                                                  | El informe está<br>desorganizado,<br>con<br>múltiples<br>errores<br>de<br>redacción<br>y<br>formato                                                                                       | No presenta el<br>informe.                                                                                                           |