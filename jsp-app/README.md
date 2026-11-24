# myBook — Quiz 2

Library management website with a JSP frontend and Laravel backend.

Farrel Satria Mukti (5025251138) & Fazli Irham Ramadhan Abdillah (5025251178).

## Run locally

Requirements: PHP 8.4+ with `pdo_pgsql`, Composer, JDK 17+, Maven, and Docker. From the project root:

```bash
docker compose up -d db
bash jsp-app/start.sh
```

Open **http://localhost:8080**. Migrations run automatically; demo data is added if the database is empty. Press Ctrl+C to stop the app.

## PostgreSQL with Docker

Start Docker Desktop first. Run these commands from the project root, where `compose.yaml` is located:

```bash
docker compose up -d db
docker compose exec db pg_isready -U postgres -d mybook
```

Wait for `accepting connections`, then run `bash jsp-app/start.sh`.

For pgAdmin, connect to host `127.0.0.1`, port `5432`, database `mybook`, username `postgres`, password `postgres`.

To open the database in a terminal:

```bash
docker compose exec db psql -U postgres -d mybook
```

Use `\dt` to list tables and `\q` to exit. Stop PostgreSQL with `docker compose stop db`; data remains in the Docker volume. To use another PostgreSQL server, set `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` before starting the app.

## Demo accounts

| Role | Email | Password |
|---|---|---|
| Admin | admin@mybook.test | AdminDemo123! |
| Librarian | librarian@mybook.test | Librarian123! |
| Member | member1@mybook.test | MemberDemo123! |

## Features

CRUD for books, categories, authors, publishers, members, librarians, reservations, and loans. Includes a dashboard, reports, favorites, reviews, and ratings.
