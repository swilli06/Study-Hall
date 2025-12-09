# Study Hall – Developer Overview

Lightweight notes to help new contributors understand the current layout and runtime expectations.

## Stack and Services
- Docker Compose runs `web` (Apache/PHP 8 on openSUSE), `db` (MariaDB), and `adminer`.
- Root `.env` is consumed by `docker-compose.yml` for DB creds and `API_KEY`.
- DB schema/seed auto-run on first start from `sql/00_schema.sql` and `sql/10_seed.sql`.

## Application Structure
- Entry: `app/public/index.php` is a simple front controller that wires controllers/models and routes by `$_SERVER['REQUEST_URI']`.
- MVC-ish layout:
  - Controllers: `app/controllers/*Controller.php`
  - Models: `app/models/*.php`
  - Views: `app/views/*.php` (include shared header/footer)
- Assets are separated from PHP views for modularity:
  - CSS in `app/public/css/`, JS in `app/public/js/`
  - Views reference these assets via `<link>`/`<script>` tags instead of inline code.
- Uploads: board banners save to `app/public/uploads`. The controller auto-creates the folder at runtime but ensure it is writable inside the container.

## Features (current fork)
- Board banner uploads with runtime directory/writability checks.
- AI comment assistance endpoint (`app/ai/aiCommentResponse.php`) plus JS/CSS to request responses using `API_KEY`.
- Messaging/UI refinements across dashboard/profile/header/chat with modular JS/CSS.

## Data and Credentials
- Default DB name/user/password come from `.env`; Adminer is available at http://localhost:8081 (server: `db`, db: `studyhall`, user/pass from `.env`).
- Seed user: `admin@studyhall.local` (password hash in `sql/10_seed.sql`).
- `API_KEY` is required for the AI endpoint; without it, the endpoint returns an error.

## Development Notes
- Bring up services: `docker compose up --build -d`.
- Reset DB cleanly: `docker compose down -v && docker compose up --build -d`.
- Re-run schema/seed without dropping data:
  - `docker compose exec -T db mariadb -u root -p"$MARIADB_ROOT_PASSWORD" < sql/00_schema.sql`
  - `docker compose exec -T db mariadb -u root -p"$MARIADB_ROOT_PASSWORD" studyhall < sql/10_seed.sql`
- If uploads fail, check ownership/permissions on `app/public/uploads` from inside the container.
