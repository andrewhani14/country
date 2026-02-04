# Symfony Assessment — Country API

## Requirements

- Docker & Docker Compose

## Setup

```bash
composer install
docker compose up -d
```

## Configuration

Optional: copy or create `.env.local` for overrides. Key variables:

| Variable | Description |
|----------|-------------|
| `ADMIN_PASSWORD` | Hashed password for API write operations (Basic Auth). Generate with: `docker compose exec php php bin/console security:hash-password <password>` |

## Database

Run migrations and sync countries from the REST Countries API:

```bash
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec php php bin/console countries:sync
```

The sync command will:

- Insert new countries from the API
- Update existing countries with current API data (resets any manual changes)
- Remove countries that no longer exist in the API

## Running the Application

API is available at **http://localhost:8084** 

## API

Base path: `/api/v1/countries`

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/list` | No | List all countries |
| GET | `/{uuid}` | No | Get one country by 2-letter code (e.g. DE, FR) |
| POST | `/` | Basic | Create a country |
| PATCH | `/{uuid}` | Basic | Update a country |
| DELETE | `/{uuid}` | Basic | Delete a country |

**Write operations (POST, PATCH, DELETE)** require HTTP Basic Auth. Default user: `admin`. Password: the one you hashed and set in `ADMIN_PASSWORD`.

**Example (list, no auth):**

```bash
curl http://localhost:8084/api/v1/countries/list
```

**Example (create, with Basic Auth):**

```bash
curl -u admin:yourpassword -X POST http://localhost:8084/api/v1/countries \
  -H "Content-Type: application/json" \
  -d '{"uuid":"XX","name":"Test","region":"Europe","subRegion":"","demonym":"Test","population":0,"independent":false,"flag":"https://example.com/flag.png"}'
```

### Country payload

- `uuid` (string, 2 chars) — required on create
- `name`, `region`, `subRegion`, `demonym` (string)
- `population` (integer)
- `independent` (boolean)
- `flag` (string, URL)
- `currency` (object with `name`, `symbol`)

## API documentation

Interactive OpenAPI docs: **http://localhost:8084/api/doc**
