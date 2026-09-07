# LongitudeOne Doctrine Spatial Demo

A Symfony application demonstrating spatial data storage and queries with
`longitude-one/doctrine-spatial`, Doctrine ORM and PostgreSQL/PostGIS.

Cities are stored as polygons and heroes as points. Two examples show how to
display these geometries on a map and select heroes located inside a city.
Symfony UX Map and Leaflet render the maps, while Twig displays the names and
descriptions below them.

## Prerequisites

- **Docker with Docker Compose**, running before you start the installation.
- **Symfony CLI**, available as `symfony` in your terminal.
- **PHP 8.4.1 or later**, compatible with the locked Symfony 8.1 dependencies,
  with the PostgreSQL PDO driver (`pdo_pgsql`), `ctype`, `iconv` and the
  extensions required by Composer dependencies.
- **Composer 2**, available locally.

PHP, Composer and the Symfony server run on your machine. Docker runs the
database. Frontend assets use AssetMapper and import maps, so no Node.js build
step is needed.

On Apple Silicon, including a MacBook Pro with an M4 chip,
`compose.override.yaml` selects `linux/amd64` for the PostGIS container so
Docker can run the image through emulation.

## Installation

Run the following commands from the project root after obtaining the source.

### 1. Start the database container

```bash
docker compose up -d --wait database
```

The default image is `postgis/postgis:16-3.5-alpine`: PostgreSQL 16 with
PostGIS 3.5. Its data is stored in the persistent `database_data` volume.
Only the `database` service is needed for these examples.

Compose publishes PostgreSQL on a dynamically assigned host port. Run database
commands through `symfony console` so the Symfony CLI discovers the running
container's connection settings. Do not assume the host port is `5432`.

### 2. Install the application dependencies

```bash
symfony composer install
symfony composer check-platform-reqs
```

Install the development dependencies as well: they include the fixtures bundle.
Composer's installation scripts clear the cache and install the application
assets and import-map dependencies.

### 3. Create the database and apply migrations

```bash
symfony console doctrine:database:create --if-not-exists
symfony console dbal:run-sql 'CREATE EXTENSION IF NOT EXISTS postgis'
symfony console doctrine:migrations:migrate --no-interaction
```

On its first start, the PostGIS container normally creates the default `app`
database with PostGIS enabled. The first two commands also handle an existing
database or one that still needs the extension. PostGIS must be available before
the migrations create the geometry columns.

Migrations create the application tables, including `city`, `hero` and
`messenger_messages`. The latter belongs to Symfony Messenger's Doctrine
transport; it is not a city or hero entity.

The committed `.env` file contains local defaults. Put any local overrides in
the ignored `.env.local` file. The steps above assume the default Compose
database settings.

### 4. Load the example data

```bash
symfony console doctrine:fixtures:load --no-interaction
```

This loads two cities and eight heroes from `AppFixtures`. Loading fixtures
replaces the existing data in the mapped application tables, so use this command
on the demo database.

### 5. Start the Symfony server

```bash
symfony serve -d
```

Open the local URL printed by Symfony, usually `https://127.0.0.1:8000` when
local HTTPS is configured, or `http://127.0.0.1:8000` otherwise. Use the actual
scheme and port shown by the command.

The navigation menu switches between **All cities and heroes** (`/`) and
**Gotham City** (`/who-are-in-city`).

## Examples

### 1. All cities and heroes

**Path:** `/`

`IndexController` retrieves every city and hero, ordered by name, and passes
them to `CityMapFactory`. This example performs no spatial filtering: heroes
inside a city, on its boundary and outside both cities are all displayed.

The map shows Gotham City, an island-shaped polygon with three bridge
extensions, and Metropolis, located southeast of Gotham. Each hero appears as
a marker. Clicking a city shows its name; clicking a hero shows its name and
description. Lists below the map provide the cities and hero descriptions.

The fixtures contain the following positions in the fictional coordinate plane:

| Hero      | X     | Y     | Position |
| --------- | ----: | ----: | ----------------------------------------------------------------- |
| Batman    | 28000 | 58000 | Inside Gotham's main area.                                        |
| Catwoman  | 42000 | 59500 | Inside Gotham, on the East Bridge.                                |
| Nightwing | 20000 | 60000 | Exactly on Gotham's western boundary.                             |
| Robin     | 23500 | 76000 | Outside Gotham, 1000 units beyond the North Bridge.               |
| Superman  | 72000 | 20000 | Inside Metropolis, midway between its north and south boundaries. |
| Supergirl | 70000 | 28000 | Inside Metropolis, 2000 units south of its northern boundary.     |
| Lois Lane | 75000 | 12000 | Inside Metropolis, 2000 units north of its southern boundary.     |
| The Flash | 48000 | 38000 | Outside both cities, between Gotham and Metropolis.               |

The factory creates one polygon per city and one marker per hero. A Stimulus
controller fits the viewport to all polygons and markers, including heroes
outside the cities. Empty datasets are supported.

These are Cartesian coordinates, not real latitude and longitude. The factory
divides both axes by 1000 and passes them to UX Map in Y/X order. Leaflet uses
`CRS.Simple` with no background tiles. This display scaling preserves relative
positions and does not change the coordinates stored or queried in PostGIS.

### 2. Heroes inside Gotham City

**Path:** `/who-are-in-city`

`WhoAreInCityController` loads Gotham City and calls
`HeroRepository::findHeroesInCity()`. The repository applies this DQL predicate:

```sql
ST_Within(h.position, :cityGeometry) = true
```

The city's surface is bound with the Doctrine `polygon` type. Doctrine Spatial
converts the parameter for PostGIS, where the containment test runs. Results
are ordered by hero name, then displayed on a map of Gotham and in a list below
it with their descriptions.

With the supplied fixtures, the result contains exactly **Batman and Catwoman**.
The East Bridge is part of Gotham's polygon, so Catwoman is included even
though she is outside the island's main area.

For a point tested against a polygon, `ST_Within` requires the point to lie
strictly inside the polygon. **Nightwing is excluded** because his position is
exactly on the western boundary. Robin is beyond the North Bridge, the three
Metropolis residents are in another city, and The Flash is outside both cities;
none of them is returned.

This example illustrates the difference between being inside a polygon and
merely touching its boundary. If Gotham City is missing from the database,
the page returns HTTP 404; load the fixtures before opening it.

## Suggested exercise: include boundary points with ST_Covers

Create a third example that includes heroes on the city's boundary as well as
those inside it. For a polygon and a point, `ST_Covers` accepts both cases.
This makes Nightwing appear alongside Batman and Catwoman while keeping Robin
and the other heroes outside Gotham excluded. Keep the existing `ST_Within`
example so you can compare the two results.

1. **Create a controller.** Add a controller extending `AbstractController`,
   with an attribute route such as `/who-is-covered-by-city`. Load Gotham City
   through `CityRepository`, return HTTP 404 if it is missing, and delegate the
   spatial query to the new repository method described below.

2. **Add a method to `CityRepository`.** For this exercise, introduce
   `findHeroesCoveredByCity(City $city): array`, returning `Hero` entities
   ordered by name. Build the query from `Hero::class` using the entity
   manager's query builder: `CityRepository::createQueryBuilder()` would
   otherwise start from `City`. Apply the following predicate and bind
   `$city->getSurface()` as `cityGeometry` with the Doctrine `polygon` type:

   ```sql
   ST_Covers(:cityGeometry, h.position) = true
   ```

   Argument order matters: the city polygon covers the hero's point.
   This reverses the order used by `ST_Within(h.position, :cityGeometry)`.

3. **Register the DQL function.** In `config/packages/doctrine.yaml`, add the
   following entry alongside `ST_Within`, under
   `doctrine.orm.dql.numeric_functions`:

   ```yaml
   ST_Covers: LongitudeOne\Spatial\ORM\Query\AST\Functions\PostgreSql\SpCovers
   ```

   `SpCovers` is the implementation provided by the installed Doctrine Spatial
   version. This registration lets Doctrine translate the DQL call into
   PostGIS SQL; it does not require a database schema migration.

4. **Create a similar template.** Use `templates/who_are_in_city.html.twig`
   as a starting point. Update its title and explanatory text to say that
   boundary points are included. In the controller, pass the city, the new
   query's heroes and the map from
   `CityMapFactory::create([$city], $heroes)` to this template. Reuse
   `map/_city_map.html.twig` and retain the hero list below the map.

5. **Compare the results.** Add a link to the new route in the shared menu
   using Twig's `path()` function. With the fixtures loaded, the new page
   should show **Batman, Catwoman and Nightwing**, in that order. Nightwing's
   marker should lie on Gotham's western boundary. The original page should
   still show only Batman and Catwoman.

6. **Verify the behavior with a functional test.** Load the fixtures in the
   test database, call the new repository method and assert the exact three
   names. Add an HTTP test for the new route that checks the successful
   response and Nightwing's presence in the hero list. This demonstrates that
   changing the spatial predicate changes the result without moving any point
   or modifying Gotham's polygon.

The first contributor to submit a pull request with a working solution to this
exercise will have earned a good coffee! ☕ ^^
