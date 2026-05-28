# Puzzle Manager - Proyecto 03 BD2

Aplicacion web para registrar rompecabezas, piezas y conexiones entre piezas, y generar una guia paso a paso para armar el rompecabezas desde una pieza inicial elegida por el usuario o seleccionada al azar.

El proyecto fue desarrollado para CC3089 Base de Datos 2. Usa Laravel, MySQL, Inertia, Vue y una visualizacion tipo grafo para representar las relaciones entre piezas.

## Tecnologias

- Backend: Laravel 13 con PHP 8.3.
- Frontend: Vue 3, Inertia, Bootstrap, FontAwesome y Vite.
- Base de datos: MySQL 8.
- Visualizacion: `d3-force` para dibujar el grafo de piezas y conexiones.
- Entorno recomendado: Docker Compose.

## Modelo de datos

El dominio se representa como un grafo almacenado en tablas relacionales:

- `rompecabezas`: informacion general del rompecabezas, como nombre, tipo, tematica, marca, material, dificultad y total declarado de piezas.
- `piezas`: cada pieza pertenece a un rompecabezas, tiene una etiqueta, cantidad de conexiones y un estado de disponibilidad.
- `pieza_conexiones`: representa una arista entre dos piezas. Guarda que conector numerado de una pieza encaja con que conector numerado de la otra.

La columna `disponible` permite simular piezas faltantes sin borrar la informacion del modelo. El algoritmo usa solo piezas disponibles, pero conserva el total general para reportar avance parcial.

## Por que MySQL y no Neo4j

Neo4j era una opcion natural porque el problema puede verse como un grafo: piezas como nodos y conexiones como aristas. De hecho, la interfaz visual usa esta idea para mostrar y editar las conexiones.

Se eligio MySQL por estas razones:

1. El modelo tambien es altamente estructurado: rompecabezas, piezas y conexiones tienen campos claros, validaciones simples y relaciones estables.
2. MySQL permite integridad referencial con llaves foraneas, cascadas y restricciones de unicidad, lo cual ayuda a evitar piezas huerfanas o conexiones inconsistentes.
3. Las consultas necesarias para el algoritmo son simples: cargar piezas de un rompecabezas, filtrar piezas disponibles y cargar conexiones entre ellas. No se requieren recorridos complejos directamente en la base de datos.
4. El BFS se implementa en la capa de aplicacion, donde tambien se generan instrucciones humanas paso a paso. Por eso no era indispensable usar un motor de grafos.
5. MySQL facilita despliegue, documentacion, migraciones con Laravel y reproduccion del entorno con Docker.

En resumen: aunque conceptualmente el problema es un grafo, el volumen y las operaciones requeridas se resuelven bien con SQL. Neo4j seria mas atractivo si el sistema creciera hacia analisis de rutas complejas, recomendaciones, multiples tipos de relaciones, consultas profundas entre rompecabezas o exploracion masiva de grafos.

## Montaje con Docker

Requisitos:

- Docker Desktop con integracion WSL habilitada, o Docker instalado en Linux.
- Git.

Pasos:

```bash
git clone <url-del-repositorio>
cd proyecto03
cp .env.example .env
docker compose up -d --build
```

El contenedor PHP instala dependencias si hacen falta, genera la llave de Laravel si esta vacia, crea el enlace de storage y corre migraciones.

La aplicacion queda disponible en:

```text
http://localhost:8500
```

Comandos utiles:

```bash
make up       # levantar contenedores
make down     # detener contenedores
make logs     # ver logs
make shell    # entrar al contenedor app
make fresh    # reiniciar base de datos con seeders
make rebuild  # reconstruir contenedores
```

Para cargar datos de demostracion:

```bash
docker compose exec app php artisan db:seed --class=DemoSeeder
```

## Montaje local sin Docker

Requisitos:

- PHP 8.3 con extensiones requeridas por Laravel, incluyendo `mbstring`, `pdo_mysql`, `xml`, `dom` y `xmlwriter`.
- Composer.
- Node.js y npm.
- MySQL 8.

Pasos generales:

```bash
cp .env.example .env
composer install
npm install
php artisan key:generate
php artisan migrate
php artisan db:seed --class=DemoSeeder
npm run build
php artisan serve
```

Si se usa servidor local de Laravel, ajustar `APP_URL` segun el puerto usado, por ejemplo:

```env
APP_URL=http://localhost:8000
```

## Uso del sistema

1. Abrir la aplicacion.
2. Crear un rompecabezas desde "Nuevo".
3. Registrar sus datos generales: nombre, marca, tematica, tipo, material, total declarado y dificultad.
4. Entrar al detalle del rompecabezas.
5. Agregar piezas con una etiqueta visible, por ejemplo `P1`, `P2`, `A3`.
6. Indicar cuantas conexiones numeradas tiene cada pieza.
7. Crear conexiones entre piezas:
   - Manualmente, eligiendo pieza 1, conector de pieza 1, pieza 2 y conector de pieza 2.
   - Visualmente, usando el grafo para conectar nodos.
8. Marcar piezas como faltantes cuando se quiera simular un rompecabezas incompleto.
9. Antes de iniciar el algoritmo, elegir una pieza inicial disponible o dejar la opcion "Al azar".
10. Presionar "Iniciar Algoritmo".

## Algoritmo de armado

El algoritmo carga todas las piezas del rompecabezas y separa las disponibles de las faltantes. Luego:

1. Construye una lista de adyacencia con las conexiones entre piezas disponibles.
2. Elige la pieza inicial indicada por el usuario. Si no se eligio ninguna, usa una pieza aleatoria.
3. Ejecuta BFS para recorrer las conexiones y generar instrucciones de armado.
4. Si hay piezas faltantes que separan el grafo, crea nuevas islas de armado.
5. Reporta porcentaje de avance, total de piezas disponibles, total general, cantidad de islas y piezas sin conexion registrada.

Esto permite armar el rompecabezas completo cuando todas las piezas estan disponibles, o armar parcialmente los grupos posibles cuando faltan piezas.

## Pruebas y build

Pruebas PHP:

```bash
php artisan test
```

Build frontend:

```bash
npm run build
```

En Docker:

```bash
docker compose exec app php artisan test
docker compose run --rm node npm run build
```

## Estructura principal

```text
app/Models
app/Http/Controllers
app/Services/AlgoritmoService.php
database/migrations
database/seeders/DemoSeeder.php
resources/js/Pages/Rompecabezas
docker-compose.yml
```

## Notas para la presentacion

- El modelo relacional guarda el grafo con integridad referencial.
- El frontend muestra ese grafo para facilitar la captura de conexiones.
- El algoritmo no depende de la visualizacion: usa los datos persistidos en MySQL.
- La seleccion de pieza inicial permite demostrar el requisito de comenzar desde una pieza cualquiera.
- Las piezas faltantes se manejan sin perder informacion, usando el campo `disponible`.
