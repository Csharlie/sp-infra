# docker/

Docker base configuration for Spektra WordPress development.

## Purpose

Prepared (not primary) Docker setup: WordPress + MariaDB + WP-CLI. Primary local runtime is WAMP (DR-004).

## Structure

```
docker/
├── docker-compose.yml   # WP 6.7 (PHP 8.3) + MariaDB 11.4 + WP-CLI
├── .env.example         # DB credentials + WP_PORT template
└── README.md
```

## Services

| Service | Image | Port | Szerep |
|---|---|---|---|
| `wordpress` | `wordpress:6.7-php8.3-apache` | `${WP_PORT:-8080}:80` | WP runtime |
| `mariadb` | `mariadb:11.4` | — | Database |
| `wpcli` | `wordpress:cli-php8.3` | — | CLI (profiles: cli) |

## Usage

```sh
cd sp-infra/docker
cp .env.example .env     # fill in credentials
docker compose up -d
docker compose run --rm wpcli core install ...
```

## Status

Prepared, not primary. WAMP is the reference local environment.
