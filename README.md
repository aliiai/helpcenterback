## Contents

```text
┌────────────────────────────────────────────────────────────────────────────┐
│                             Table of Contents                              │
├──────────────────────────┬─────────────────────────────────────────────────┤
│ Section                  │ Description                                     │
├──────────────────────────┼─────────────────────────────────────────────────┤
│ Overview                 │ Full-stack product overview and key features    │
├──────────────────────────┼─────────────────────────────────────────────────┤
│ Repository Layout        │ Backend + frontend folder map                   │
├──────────────────────────┼─────────────────────────────────────────────────┤
│ Local Setup              │ Run MySQL API and React SPA together            │
├──────────────────────────┼─────────────────────────────────────────────────┤
│ Backend · API            │ Endpoints, auth flow, and business rules        │
├──────────────────────────┼─────────────────────────────────────────────────┤
│ Frontend · SPA           │ Routes, modules, and API consumption            │
├──────────────────────────┼─────────────────────────────────────────────────┤
│ Design Decisions         │ Architecture choices across both layers         │
├──────────────────────────┼─────────────────────────────────────────────────┤
│ Libraries & Technologies │ Stack by layer                                  │
├──────────────────────────┼─────────────────────────────────────────────────┤
│ Submission               │ Public GitHub checklist                         │
└──────────────────────────┴─────────────────────────────────────────────────┘
```
================================================================================

# Mini Helpdesk · كلير ديسك (ClearDesk)

> A full-stack support ticketing system: **Laravel API** + **React SPA**.  
> Standard users open and follow tickets; admins manage the full queue.

| Layer | Stack |
| --- | --- |
| **Backend** | Laravel 13 · PHP 8.3 · MySQL 8+ · Sanctum Bearer tokens |
| **Frontend** | React 19 · TypeScript · Vite · Tailwind · Arabic RTL |

================================================================================

## Key Features

### Backend
- **Role-based API** — `user` and `admin` with Policies and Form Requests.
- **Tickets & replies** — ownership rules, status workflow (`open` → `in_progress` → `closed`).
- **Image attachments** — up to 5 images per ticket/reply (`multipart/form-data`).
- **Notifications** — last-5 bell feed + unread count (tickets + new registrations).
- **Admin user CRUD** — list / create / update / delete.
- **Pagination + status filter** on ticket index.
- **Docker Compose** — app + MySQL with a senior-style entrypoint.
- **Feature tests** — ticket creation, images, users, notifications.

### Frontend
- **Auth screens** — login / register with Sanctum token persistence.
- **Global session** — `AuthContext` restores the user via `GET /api/user`.
- **Role-aware routing** — users → `/dashboard`; admins → `/admin`.
- **User dashboard** — filters, create ticket, threaded chat + images.
- **Admin console** — inbox, status updates, user management.
- **Loading & error UX** — empty/error states, Laravel 422 field errors, retries.

================================================================================

## Repository Layout

```text
help-center/                 ← monorepo root (public GitHub)
├── app/ … routes/ …         ← Laravel API (this folder is the backend root)
├── docker-compose.yml
├── api-endpoints.json       ← Postman/Apidog collection
├── README.md                ← you are here (backend + frontend)
└── frontend/                ← React + Vite SPA
    ├── package.json
    ├── src/
    └── .env.example
```

> If you have not copied the SPA yet, place the React project under [`frontend/`](frontend/) before following the frontend steps below.

================================================================================

## Local Setup

Run the API first, then the SPA. Both are required for a working product.

### Requirements

```text
╔══════════════════════════════════════════════════════════╗
║                       Requirements                       ║
╠══════════════════════════════════════════════════════════╣
║ • PHP 8.3                   • Composer                   ║
║ • MySQL 8+                  • Docker (optional)          ║
║ • Node.js 20+ (LTS)         • npm 10+                    ║
╚══════════════════════════════════════════════════════════╝
```

### Setup Flow

```text
┌────────────┐   ┌─────────────┐   ┌────────────┐   ┌─────────────┐
│ Clone Repo │──►│ Backend API │──►│  Migrate   │──►│ Frontend SPA│
└────────────┘   │ composer +  │   │  + seed    │   │ npm + Vite  │
                 │ .env + MySQL│   │  + serve   │   └─────────────┘
                 └─────────────┘   └────────────┘
```

---

### Backend · Step 1 · Install

```bash
git clone <repository-url> help-center
cd help-center
composer install
```

### Backend · Step 2 · Environment

```bash
cp .env.example .env
php artisan key:generate
```

Default MySQL settings in `.env.example` (match Docker Compose):

| Setting | Value |
|---------|-------|
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | `127.0.0.1` |
| `DB_PORT` | `3306` |
| `DB_DATABASE` | `helpdesk` |
| `DB_USERNAME` | `helpdesk` |
| `DB_PASSWORD` | `secret` |

Also set:

```env
APP_URL=http://127.0.0.1:8000
```

so attachment URLs resolve correctly for the SPA.

### Backend · Step 3 · Database

Create the MySQL database, then migrate, seed, and link storage:

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS helpdesk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE USER IF NOT EXISTS 'helpdesk'@'%' IDENTIFIED BY 'secret'; GRANT ALL ON helpdesk.* TO 'helpdesk'@'%'; FLUSH PRIVILEGES;"
php artisan migrate --seed
php artisan storage:link
```

> If you already have MySQL credentials, skip the SQL create step and only adjust `.env`.

Re-run Arabic demo data only:

```bash
php artisan db:seed --class=ArabicHelpdeskSeeder
```

### Seeded accounts

| Role  | Email             | Password |
|-------|-------------------|----------|
| Admin | admin@example.com | password |
| User  | ali@gmail.com     | password |
| Users | user1@example.com … user10@example.com | password |

### Backend · Step 4 · Serve the API

```bash
php artisan serve
```

- API: `http://127.0.0.1:8000`
- Health: `http://127.0.0.1:8000/up`

Import [api-endpoints.json](api-endpoints.json) into Postman/Apidog if you want to explore the API manually.

### Backend · Alternative · Docker Compose

Entrypoint waits for MySQL, creates `.env` if missing, generates `APP_KEY` only when empty, migrates, then serves. **Seed is optional.**

```bash
# First time with demo data
APP_SEED=true docker compose up --build -d

# Later starts (no re-seed)
docker compose up -d

docker compose logs -f app
docker compose exec app php artisan db:seed --force
docker compose down
```

- API: `http://localhost:8000`
- MySQL: `localhost:3306` (`helpdesk` / `helpdesk` / `secret`)

### Backend · Tests

PHPUnit uses MySQL database `helpdesk_testing` (see [`phpunit.xml`](phpunit.xml)).

```bash
php artisan test --compact --filter=TicketCreationTest
php artisan test --compact
```

---

### Frontend · Step 5 · Install

```bash
cd frontend
npm install
```

### Frontend · Step 6 · Environment

```bash
cp .env.example .env
```

| Variable | Default | Purpose |
| --- | --- | --- |
| `VITE_API_URL` | `/api` | API base. Relative `/api` uses the Vite proxy to Laravel. |
| `VITE_BACKEND_URL` | `http://127.0.0.1:8000` | Laravel origin for `/storage` image URLs. |

Optional (call Laravel directly, no proxy):

```env
VITE_API_URL=http://127.0.0.1:8000/api
VITE_BACKEND_URL=http://127.0.0.1:8000
```

### Frontend · Step 7 · Run the SPA

Keep `php artisan serve` running, then:

```bash
cd frontend
npm run dev
```

| Surface | URL |
| --- | --- |
| SPA | `http://localhost:5173` |
| Proxied API | `http://localhost:5173/api` → `http://127.0.0.1:8000/api` |
| Proxied storage | `http://localhost:5173/storage` → `http://127.0.0.1:8000/storage` |

### Frontend · Production build (optional)

```bash
cd frontend
npm run build
npm run preview
```

Set `VITE_API_URL` and `VITE_BACKEND_URL` to real origins before building.

================================================================================

## Backend · API Structure

All routes live under `/api`. Public: `register` + `login`. Everything else requires `Authorization: Bearer <token>`.

### Request Flow

```text
                  ┌───────────────────────────┐
                  │   React SPA / API Client  │
                  └─────────────┬─────────────┘
                                │  JSON · multipart · Bearer
                                ▼
                  ┌───────────────────────────┐
                  │       Prefix: /api        │
                  └─────────────┬─────────────┘
                                ├──── PUBLIC: /register · /login
                                ▼
                  ┌───────────────────────────┐
                  │   auth:sanctum            │
                  └─────────────┬─────────────┘
                                ▼
                  ┌───────────────────────────┐
                  │   Policies + Form Requests│
                  └─────────────┬─────────────┘
                                ▼
                  auth · users · tickets · replies · notifications
```

### Modules & Endpoints

| Module | Key Endpoints | Description |
| --- | --- | --- |
| **Auth** | `POST /register` · `POST /login` · `GET /user` · `POST /logout` | Register, login, session, logout |
| **users** | `GET/POST /users` · `GET/PATCH/DELETE /users/{id}` | Admin-only user management |
| **notifications** | `GET /notifications` · `POST …/read-all` · `POST …/{id}/read` | Last 5 + unread count (bell) |
| **tickets** | `GET/POST /tickets` · `GET/PATCH /tickets/{id}` | List, create, show, update status |
| **replies** | `POST /tickets/{id}/replies` | Add reply (+ optional images) |

### Image uploads (API)

- Use `multipart/form-data` with field `images[]` (max 5).
- Types: `jpg`, `jpeg`, `png`, `webp`, `gif` — max **5MB** each.
- Responses include `attachments[]` with `url` (requires `php artisan storage:link`).

### Business rules

- Users create tickets and only view/reply on their own.
- Admins view/reply on all tickets and update status.
- Admins cannot create tickets.
- Unauthorized ticket access returns **404** (not 403) where applicable.

Full routes: [`routes/api.php`](routes/api.php).

================================================================================

## Frontend · SPA Structure

### Request Flow

```text
                  ┌───────────────────────────┐
                  │   Browser · Vite :5173    │
                  └─────────────┬─────────────┘
                                │  Bearer token
                                ▼
                  ┌───────────────────────────┐
                  │   Vite proxy (dev)        │
                  │   /api · /storage → :8000 │
                  └─────────────┬─────────────┘
                                ▼
                  ┌───────────────────────────┐
                  │   Laravel REST API        │
                  └───────────────────────────┘
```

### Modules

```text
frontend/src/
├── api/           # HTTP client, auth, tickets, users, media helpers
├── components/    # Shared UI (modals, lists, feedback, image picker)
├── context/       # AuthProvider — session + login/register/logout
├── pages/         # Route screens (landing, auth, user, admin)
├── types/         # Shared TypeScript models
├── App.tsx        # Route tree + auth guards
└── index.css      # Design tokens + Tailwind
```

### Routes

| Path | Access | Description |
| --- | --- | --- |
| `/` | Guest | Marketing landing |
| `/auth` | Guest | Login / register |
| `/dashboard` | User | Ticket list, filters, create + chat |
| `/tickets/:id` | Authenticated | Full-page thread + reply + attachments |
| `/admin` | Admin | Stats overview |
| `/admin/tickets` | Admin | All tickets + status updates |
| `/admin/users` | Admin | User CRUD |

### API consumption (SPA)

| Module | Frontend entry | Backend endpoints |
| --- | --- | --- |
| **Auth** | `frontend/src/api/auth.ts` | `/register` · `/login` · `/user` · `/logout` |
| **Tickets** | `frontend/src/api/tickets.ts` | `/tickets` · `/tickets/{id}` · `/replies` |
| **Users** | `frontend/src/api/users.ts` | `/users` (admin) |
| **Media** | `frontend/src/api/media.ts` | Resolves `/storage/...` for `<img>` |

### Image uploads (client)

- Prefer `FormData` + `images[]`; do **not** set `Content-Type` manually.
- Max 5 images; same types/size rules as the API.
- Relative `/storage` paths are rewritten using `VITE_BACKEND_URL`.

### Auth flow (SPA)

1. Guest submits login/register on `/auth`.
2. API returns `{ token, user }` → stored and hydrated into `AuthContext`.
3. On reload, `GET /api/user` validates the token; failure clears the session.
4. Route guards send users to `/dashboard` or `/admin` by role.
5. Every request sends `Authorization: Bearer <token>` and `Accept: application/json`.

================================================================================

## Design Decisions

### Backend
- Thin controllers; validation in Form Requests; authorization in Policies.
- Simple `role` enum instead of an external ACL package.
- Polymorphic `attachments` with UUID filenames on the `public` disk.
- Database notifications for the bell (tickets + registrations).
- MySQL-only; Docker entrypoint migrates without forced re-seed.

### Frontend
- SPA (Vite + React Router) fitted to Sanctum token auth.
- Context for session only; list/thread state stays local to pages.
- Thin API modules with shared `ApiError` / Laravel 422 parsing.
- Role-split shells matching API business rules.
- Arabic RTL first (`lang="ar"` / `dir="rtl"`).
- Dev proxy for `/api` and `/storage` to reduce CORS friction.

================================================================================

## Libraries & Technologies

### Backend

```text
┌────────────────────────────────────────────────────────────────────────────┐
│                          Core Technologies (Backend)                       │
├──────────────────────┬───────────────┬─────────────────────────────────────┤
│ Technology / Library │ Version       │ Purpose                             │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ PHP                  │ ^8.3          │ Runtime                             │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ Laravel Framework    │ ^13.8         │ Application framework               │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ MySQL                │ 8+            │ Primary database                    │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ Laravel Sanctum      │ ^4.0          │ API token authentication            │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ PHPUnit              │ ^12.5         │ Feature / unit tests                │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ Docker Compose       │ —             │ App + MySQL local stack             │
└──────────────────────┴───────────────┴─────────────────────────────────────┘
```

### Frontend

```text
┌────────────────────────────────────────────────────────────────────────────┐
│                         Core Technologies (Frontend)                       │
├──────────────────────┬───────────────┬─────────────────────────────────────┤
│ Technology / Library │ Version       │ Purpose                             │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ React                │ ^19.2         │ UI library                          │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ TypeScript           │ ~6.0          │ Static typing                       │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ Vite                 │ ^8.1          │ Dev server + production build       │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ React Router DOM     │ ^7.18         │ Client routing & guards             │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ Tailwind CSS         │ ^4.3          │ Styling                             │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ Motion               │ ^12.42        │ UI motion                           │
└──────────────────────┴───────────────┴─────────────────────────────────────┘
```

### Useful commands

| Area | Command | Description |
| --- | --- | --- |
| API | `php artisan serve` | Backend on `:8000` |
| API | `php artisan test --compact` | Run PHPUnit |
| API | `APP_SEED=true docker compose up --build -d` | Docker first boot |
| SPA | `cd frontend && npm run dev` | Vite on `:5173` |
| SPA | `cd frontend && npm run build` | Production bundle |
| SPA | `cd frontend && npm run lint` | ESLint |

================================================================================

## Smoke test (after setup)

1. Open `http://localhost:5173` with the API running on `:8000`.
2. Login as `ali@gmail.com` / `password` → create a ticket with an image → reply.
3. Login as `admin@example.com` / `password` → open the ticket → change status → check users page.
4. Confirm the notifications bell shows recent activity.

================================================================================

## Submission

Push this **monorepo** to a **public** GitHub repository. Do **not** include any company name in the repository name, README, or source.

Suggested remote name: `mini-helpdesk` (or similar).

```bash
git init
git add .
git status   # confirm: no .env, node_modules, vendor
git commit -m "Submit Mini Helpdesk full-stack app"
git branch -M main
git remote add origin https://github.com/<your-username>/mini-helpdesk.git
git push -u origin main
```

Ensure [`frontend/`](frontend/) is included in the same repository so reviewers can clone once and run both layers from this README.
