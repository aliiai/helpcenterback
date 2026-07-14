## Contents

```text
┌────────────────────────────────────────────────────────────────────────────┐
│                             Table of Contents                              │
├──────────────────────────┬─────────────────────────────────────────────────┤
│ Section                  │ Description                                     │
├──────────────────────────┼─────────────────────────────────────────────────┤
│ Overview                 │ Platform overview and key features              │
├──────────────────────────┼─────────────────────────────────────────────────┤
│ Local Setup              │ Requirements and steps to run it locally        │
├──────────────────────────┼─────────────────────────────────────────────────┤
│ API Structure            │ Request flow, modules, and endpoints            │
├──────────────────────────┼─────────────────────────────────────────────────┤
│ Design Decisions         │ Architecture and key technical choices          │
├──────────────────────────┼─────────────────────────────────────────────────┤
│ Libraries & Technologies │ Tools and packages used per layer               │
└──────────────────────────┴─────────────────────────────────────────────────┘
```
================================================================================

# Mini Helpdesk

> A simplified support ticketing backend where standard users open tickets and admins manage the full queue.

Built on **Laravel 13 / PHP 8.3** with **Laravel Sanctum** for token-based REST authentication. Users create tickets and reply on their own threads; admins can view, reply to, and update the status of every ticket.


## Key Features

- **Role-based access** with `user` and `admin` roles on the same users table.
- **Tickets** with title, description, and status (`open`, `in_progress`, `closed`).
- **Threaded replies** from either the ticket owner or an admin.
- **Image attachments** on tickets and replies (up to 5 images each, stored on the public disk).
- **Token-based REST API** secured with Sanctum personal access tokens.
- **Pagination & status filtering** on the ticket list.
- **Docker Compose** for a one-command local environment (app + MySQL).
- **Feature tests** covering ticket creation (auth, validation, and role rules).

================================================================================

## Local Setup

Get a local instance running in a few steps. The flow below shows the order; each step is detailed in its own card underneath.

### Requirements

```text
╔══════════════════════════════════════════════════════════╗
║                       Requirements                       ║
╠══════════════════════════════════════════════════════════╣
║ • PHP 8.3                   • Composer                   ║
║ • MySQL 8+                  • Docker (optional)          ║
╚══════════════════════════════════════════════════════════╝
```

### Setup Flow

```text
┌────────────┐   ┌──────────────┐   ┌───────────────┐   ┌────────────┐
│ Clone Repo │──►│ Install Deps │──►│ Configure Env │──►│  Database  │──┐
└────────────┘   └──────────────┘   └───────────────┘   └────────────┘  │
┌────────────┐   ┌──────────┐                                           │
│ Run Server │◄──│ Migrations│◄─────────────────────────────────────────┘
└────────────┘   └──────────┘
```

### 📦 Step 1 · Clone & Install

```text
┌──────────────────────────────────────────────────────────┐
│  Step 1 · Clone & Install                                │
└──────────────────────────────────────────────────────────┘
```

Clone the repository and install the PHP dependencies with Composer.

```bash
git clone <repository-url> help-center
cd help-center
composer install
```

### ⚙️ Step 2 · Configure Environment

```text
┌──────────────────────────────────────────────────────────┐
│  Step 2 · Configure Environment                          │
└──────────────────────────────────────────────────────────┘
```

Copy `.env.example` to `.env`, generate the app key, then set your **MySQL** credentials (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).

```bash
cp .env.example .env
php artisan key:generate
```

Default credentials in `.env.example` (match Docker Compose):

| Setting | Value |
|---------|-------|
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | `127.0.0.1` |
| `DB_PORT` | `3306` |
| `DB_DATABASE` | `helpdesk` |
| `DB_USERNAME` | `helpdesk` |
| `DB_PASSWORD` | `secret` |

### 🗄 Step 3 · Database & Migrations

```text
┌──────────────────────────────────────────────────────────┐
│  Step 3 · Database & Migrations                          │
└──────────────────────────────────────────────────────────┘
```

Create a MySQL database named `helpdesk`, then run migrations and seed demo data. Create the storage symlink so uploaded images are publicly reachable.

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS helpdesk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE USER IF NOT EXISTS 'helpdesk'@'%' IDENTIFIED BY 'secret'; GRANT ALL ON helpdesk.* TO 'helpdesk'@'%'; FLUSH PRIVILEGES;"
php artisan migrate --seed
php artisan storage:link
```

> If you already have a local MySQL user, you can skip the SQL create step and only adjust `.env` to match your credentials.

### Seeded accounts

| Role  | Email             | Password |
|-------|-------------------|----------|
| Admin | admin@example.com | password |
| User  | ali@gmail.com     | password |
| Users | user1@example.com … user10@example.com | password |

تشغيل البيانات التجريبية العربية فقط:

```bash
php artisan db:seed --class=ArabicHelpdeskSeeder
```

### 🚀 Step 4 · Run the Server

```text
┌──────────────────────────────────────────────────────────┐
│  Step 4 · Run the Server                                 │
└──────────────────────────────────────────────────────────┘
```

Start the local development server; the API is served at `http://127.0.0.1:8000`.

```bash
php artisan serve
```

### 🐳 Alternative · Docker Compose

```text
┌──────────────────────────────────────────────────────────┐
│  Alternative · Docker Compose                            │
└──────────────────────────────────────────────────────────┘
```

The Docker stack runs the API and MySQL with a dedicated entrypoint that:

1. waits for MySQL to be healthy  
2. creates `.env` if missing  
3. generates `APP_KEY` **only when empty**  
4. installs Composer deps if needed  
5. links storage and runs migrations  
6. starts the HTTP server  

**Seed is optional** and does not run on every restart.

#### First-time setup (with demo data)

```bash
APP_SEED=true docker compose up --build -d
```

#### Regular start (no re-seed)

```bash
docker compose up -d
```

#### Useful commands

```bash
# Follow app logs
docker compose logs -f app

# Run artisan inside the container
docker compose exec app php artisan route:list

# Seed manually later
docker compose exec app php artisan db:seed --force

# Run tests against the MySQL testing database
docker compose exec app php artisan test --compact

# Stop everything
docker compose down
```

- API: `http://localhost:8000`
- MySQL: `localhost:3306`  
  database/user/password: `helpdesk` / `helpdesk` / `secret` (override via `.env`)

> Tip: bind-mounts keep your code in sync, while `vendor` lives in a named Docker volume so host/container dependency trees do not fight each other.

### 🧪 Step 5 · Run Tests

```text
┌──────────────────────────────────────────────────────────┐
│  Step 5 · Run Tests                                      │
└──────────────────────────────────────────────────────────┘
```

Run the feature tests (or the full suite) to verify the installation. PHPUnit uses a separate MySQL database: `helpdesk_testing` (see `phpunit.xml` for credentials).

```bash
# Create the testing database (adjust credentials to match your MySQL root/user)
php -r "new PDO('mysql:host=127.0.0.1', 'root', '')->exec('CREATE DATABASE IF NOT EXISTS helpdesk_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');"

php artisan test --compact --filter=TicketCreationTest
php artisan test --compact
```

If your MySQL user/password differ, update the `DB_*` values inside [`phpunit.xml`](phpunit.xml).

> **Getting started with the API:** All routes live under `/api`. Call `POST /api/register` or `POST /api/login` to obtain a token, then send it as `Authorization: Bearer <token>`. Import [api-endpoints.json](api-endpoints.json) into Postman/Apidog to try every endpoint.

================================================================================

## API Structure

All routes start with the `/api` prefix. There are only two public routes (register and login); everything else is protected by Sanctum. After authentication, Policies enforce ownership and admin privileges before the request reaches the controllers. The diagram below shows the request flow from top to bottom.

### Request Flow

```text
                  ┌───────────────────────────┐
                  │        API Client         │
                  └─────────────┬─────────────┘
                                │  HTTPS · JSON · Bearer Token
                                ▼
                  ┌───────────────────────────┐
                  │       Prefix: /api        │
                  └─────────────┬─────────────┘
                                │
                                ├───────────────►  PUBLIC  (no auth)
                                │                    • POST /register
                                │                    • POST /login
                                ▼
                  ┌───────────────────────────┐
                  │   Middleware Guard        │
                  │   auth:sanctum            │
                  └─────────────┬─────────────┘
                                ▼
                  ┌───────────────────────────┐
                  │   Policies                │
                  │   TicketPolicy            │
                  │   (owner / admin rules)   │
                  │   404 on unauthorized     │
                  │   ticket access           │
                  └─────────────┬─────────────┘
                                ▼
                  ┌───────────────────────────┐
                  │   Application Modules     │
                  └─────────────┬─────────────┘
                                ▼
 ┌──────────────────────────────────────────────────────────────────┐
 │  auth (logout · user)                                            │
 │  tickets (list · create · show · update status)                  │
 │  replies (store on ticket)                                       │
 └──────────────────────────────────────────────────────────────────┘
```

### Modules & Endpoints

| Module | Key Endpoints | Description |
| --- | --- | --- |
| **Auth** | `POST /register` · `POST /login` · `GET /user` · `POST /logout` | Register a standard user, log in, and manage the token |
| **users** | `GET/POST /users` · `GET/PATCH/DELETE /users/{id}` | Admin-only user management (list, create, update, delete) |
| **notifications** | `GET /notifications` · `POST /notifications/read-all` · `POST /notifications/{id}/read` | Last 5 notifications + unread count for the bell icon |
| **tickets** | `GET/POST /tickets` · `GET/PATCH /tickets/{id}` | List (paginated + `?status=`), create (optional images), show, update status |
| **replies** | `POST /tickets/{id}/replies` | Add a reply (optional images) |

### Image Uploads

- Create ticket / add reply with images using `multipart/form-data` (not JSON).
- Field name: `images[]` (array), optional, max **5** files.
- Allowed types: `jpg`, `jpeg`, `png`, `webp`, `gif` — max **5MB** each.
- Responses include an `attachments` array with `id`, `url`, `original_name`, `mime_type`, `size`.
- Ensure `php artisan storage:link` has been run so `url` is reachable.

### Request Flow Explained

1. **API Client:** The client sends the request in JSON (or `multipart/form-data` when uploading images), attaching the auth token in the `Authorization: Bearer <token>` header (except for register and login).
2. **Prefix `/api`:** The unified entry point for all API routes.
3. **PUBLIC (no auth):** Only two routes are publicly available: `POST /register` to create a standard user, and `POST /login` to obtain a token.
4. **Middleware Guard:** All other routes pass through `auth:sanctum` (token verification).
5. **Policies:** After authentication, `TicketPolicy` decides whether the user may create, view, reply, or update a ticket. Unauthorized viewing of another user's ticket returns `404`.
6. **Application Modules:** After clearing the previous layers, the request reaches auth, tickets, or replies.

### Business Rules

- Standard users can create tickets and only view/reply to their own tickets.
- Admins can view and reply to all tickets and update ticket status.
- Admins cannot create tickets.

### Example Login

```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"user@example.com\",\"password\":\"password\"}"
```

The full route definitions live in [routes/api.php](routes/api.php).

================================================================================

## Design Decisions

- **Thin controllers:** Controllers stay focused on HTTP concerns; validation lives in Form Requests and authorization in Policies.
- **Role column (no permission package):** A simple `role` enum (`user` | `admin`) on `users` keeps the take-home scope clear without an external ACL package.
- **Ownership via TicketPolicy:** Viewing and replying use the same ownership/admin check; failed ticket access prefers `404` over `403` to avoid leaking ticket existence.
- **Ticket status enum:** Status values are constrained to `open`, `in_progress`, and `closed`, with admins updating status via `PATCH /tickets/{id}`.
- **Authentication:** Sanctum personal access tokens for the SPA/API client; consistent JSON via API Resources.
- **Image attachments:** Tickets and replies accept optional image uploads via a polymorphic `attachments` table. Files are stored on the `public` disk with generated UUID filenames; original names are kept only as metadata.
- **List ergonomics:** Ticket index is paginated (15 per page), filterable by `status`, ordered newest first, and eager-loads relations to avoid N+1 queries.
- **Local DX:** MySQL is the only supported database. Docker Compose uses a production-style entrypoint (wait for DB → migrate → serve) with optional one-shot seeding via `APP_SEED=true`.

================================================================================

## Libraries & Technologies

The main libraries and technologies used in the project, grouped by layer:

```text
┌────────────────────────────────────────────────────────────────────────────┐
│                          Core Technologies (Backend)                       │
├──────────────────────┬───────────────┬─────────────────────────────────────┤
│ Technology / Library │ Version       │ Purpose                             │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ PHP                  │ ^8.3          │ Core programming language           │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ Laravel Framework    │ ^13.8         │ Core framework                      │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ MySQL                │ 8+            │ Primary application database        │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ Eloquent ORM         │ (in Laravel)  │ Database access and relationships   │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ Laravel Sanctum      │ ^4.0          │ API authentication via tokens       │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ Laravel Tinker       │ ^3.0          │ REPL for testing and debugging code │
└──────────────────────┴───────────────┴─────────────────────────────────────┘
```

```text
┌────────────────────────────────────────────────────────────────────────────┐
│                            Dev Tools                                       │
├──────────────────────┬───────────────┬─────────────────────────────────────┤
│ Technology / Library │ Version       │ Purpose                             │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ PHPUnit              │ ^12.5         │ Testing framework (Feature & Unit)  │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ Laravel Pint         │ ^1.27         │ Code style formatter                │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ Laravel Pail         │ ^1.2          │ Tailing logs during development     │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ Laravel Boost        │ ^2.4          │ Helper tooling for Laravel dev      │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ FakerPHP             │ ^1.23         │ Fake data for factories and tests   │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ Mockery              │ ^1.6          │ Mocking objects in tests            │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ Nuno Collision       │ ^8.6          │ Clear error reporting in terminal   │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ Docker Compose       │ —             │ App + MySQL local stack with entrypoint │
└──────────────────────┴───────────────┴─────────────────────────────────────┘
```
