# 📚 Study Hall  
_A lightweight board & post-based discussion platform for teams, classes, and communities._

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.1+-777BB4?logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/MariaDB-10.5+-003545?logo=mariadb&logoColor=white" />
  <img src="https://img.shields.io/badge/Docker-Compose-2496ED?logo=docker&logoColor=white" />
</p>

---

## 🚀 Quick Start

1. **Create your `.env` (root)**  
   If missing, copy from `app/.env`. Update secrets accordingly:

   ```env
   DB_HOST=db
   DB_NAME=studyhall
   DB_USER=studyhall
   DB_PASS=change_me
   MARIADB_ROOT_PASSWORD=supersecret
   MARIADB_DATABASE=studyhall
   MARIADB_USER=studyhall
   MARIADB_PASSWORD=change_me
   API_KEY=...
   ```

2. **Start stack**

   ```bash
   docker compose up --build -d
   ```

3. **Access UI**

| Service   | URL                     | Notes |
|----------|-------------------------|-------|
| Web App  | http://localhost:8080   | Main application |
| Adminer  | http://localhost:8081   | DB UI — use values from `.env`, server=`db` |

---

## 🗄 Database

- Schema + seed auto-imported on first DB boot  
  (`sql/00_schema.sql` + `sql/10_seed.sql`)
- Reset database completely:

  ```bash
  docker compose down -v && docker compose up --build -d
  ```

- Re-run scripts without wiping data:

  ```bash
  docker compose exec -T db mariadb -u root -p"$MARIADB_ROOT_PASSWORD" < sql/00_schema.sql
  docker compose exec -T db mariadb -u root -p"$MARIADB_ROOT_PASSWORD" studyhall < sql/10_seed.sql
  ```

> 🧑‍💻 Default admin account: `admin@studyhall.local`  
> 🔑 Password = bcrypt hash inside seed file

---

## 🖼 File Uploads

Board banner uploads live under:

```
app/public/uploads/
```

Ensure accessible:

```bash
mkdir -p app/public/uploads && chmod -R 777 app/public app/public/uploads
# For production, use proper ownership instead of 777
```

---

## 📂 Project Structure

```
app/public            # Web root (index.php, assets, uploads, etc.)
app/controllers       # Controllers (BoardController, PostController, ...)
app/models            # Data models
app/views             # PHP view templates
sql/00_schema.sql     # Database schema
sql/10_seed.sql       # Seed/example data
docker-compose.yml    # Web/DB/Adminer services
Dockerfile            # Apache + PHP 8 on openSUSE image
docker/apache/*.conf  # Virtual host config
docker/php/php.ini    # PHP overrides
docker/entrypoint.sh  # Startup logic
docs/OVERVIEW.md      # Development notes and feature descriptions
```

---

## 🛠 Tech Stack

| Component | Tech |
|---|---|
| Backend | PHP 8 (Apache) |
| Database | MariaDB |
| Web Server | Apache inside Docker |
| Tools | Adminer, Docker Compose |
| Auth | Email + password (bcrypt) |

---


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



# AI Comment Helper API

Small internal API that lets the post page ask OpenAI for a contextual reply to a user’s question about a post. Stateless; only used from the post detail page via JavaScript.

## Endpoint
- `POST /ai/comment-response`
- Script: `app/ai/aiCommentResponse.php`
- Router (in `app/public/index.php`):
  ```php
  elseif ($uri === 'ai/comment-response') {
      if (!is_post()) { http_response_code(405); echo 'Method Not Allowed'; exit; }
      require __DIR__ . '/../ai/aiCommentResponse.php';
      exit;
  }
  ```

## Request
- JSON body:
  ```json
  {
    "event": "userChat",
    "question": "Short question about this post",
    "post": "Full text of the post"
  }
  ```
- `event` must be `"userChat"`.
- `question` and `post` are required strings.
- Header: `Content-Type: application/json`.

## Backend flow (`aiCommentResponse.php`)
- Require `POST`; otherwise 405.
- Parse JSON body; basic validation on `event`/`question`.
- Load API key: `API_KEY` env var (not sent to client).
- Trim inputs to avoid huge payloads.
- Build system/user messages and call OpenAI Chat Completions with model `gpt-5-nano` (update if your key needs a different model).
- Return the raw OpenAI JSON on success; otherwise return an error object.

## Response
- Success: raw OpenAI chat completion JSON; typical content is in `choices[0].message.content`.
- Frontend usually resolves text as:
  ```js
  const text =
    data?.choices?.[0]?.message?.content ||
    data.reply ||
    data.error ||
    'No reply.';
  ```

## Errors
- Invalid/missing JSON: `{"error": "Invalid JSON body"}`
- Bad shape (`event`/`question` missing): `{"error": "Invalid request"}`
- Missing API key: `{"error": "API key missing or unreadable secret"}`
- cURL/HTTP issues: `{"error": "cURL error: ..."}`
- OpenAI errors are passed through with the HTTP status from the API.

## Frontend usage (post detail page)
- `window.postBodyForAI` is set in the view to the current post body.
- `app/public/js/postCommentsAI.js` sends:
  ```js
  fetch('/ai/comment-response', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ event: 'userChat', question, post: window.postBodyForAI })
  })
  ```
- UI shows the response text as plain text to avoid XSS.

## Configuration notes
- Set `API_KEY` in `.env` (root) for the container; the endpoint returns an error if missing.
- Adjust `model` in `aiCommentResponse.php` if your key cannot access `gpt-5-nano`.
- Endpoint accepts `POST` only; responses should be rendered as text, not raw HTML.
