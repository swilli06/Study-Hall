Study Hall (Dockerized LAMP)
----------------------------

Quick start
-----------
1) Copy `.env` (root) from `app/.env` if needed and set secrets:
   - DB_HOST=db
   - DB_NAME=studyhall
   - DB_USER=studyhall
   - DB_PASS=change_me
   - MARIADB_ROOT_PASSWORD=supersecret
   - MARIADB_DATABASE=studyhall
   - MARIADB_USER=studyhall
   - MARIADB_PASSWORD=change_me
   - API_KEY=...

2) Bring everything up:
   docker compose up --build -d

3) Visit:
   - Web:     http://localhost:8080
   - Adminer: http://localhost:8081 (server: db, db: studyhall, user/pass: from .env)

Database
--------
- Schema/seed auto-run on first DB start from `sql/00_schema.sql` and `sql/10_seed.sql`.
- To reset clean: `docker compose down -v && docker compose up --build -d`.
- To re-run scripts without dropping data:
  docker compose exec -T db mariadb -u root -p"$MARIADB_ROOT_PASSWORD" < sql/00_schema.sql
  docker compose exec -T db mariadb -u root -p"$MARIADB_ROOT_PASSWORD" studyhall < sql/10_seed.sql
- Seed creates admin user `admin@studyhall.local` (password is the bcrypt in seed file).

File uploads
------------
- Banners for boards are stored in `app/public/uploads`. Ensure this directory exists and is writable by the web user. From host:
  mkdir -p app/public/uploads && chmod -R 777 app/public app/public/uploads
  (Adjust permissions/ownership as needed for production.)

Project layout
--------------
- app/public          : public web root (index.php, assets, etc.)
- app/controllers     : controllers (BoardController, PostController, ...)
- app/models          : data models
- app/views           : PHP views/templates
- sql/00_schema.sql   : DB schema
- sql/10_seed.sql     : seed data
- docker-compose.yml  : web/db/adminer services
- Dockerfile          : Apache + PHP 8 on openSUSE
- docker/apache/*.conf: vhost
- docker/php/php.ini  : PHP configuration
- docker/entrypoint.sh: container startup
- docs/OVERVIEW.md    : light developer notes (layout, features, dev tips)
