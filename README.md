# 🚀 TeamFlow — Project Management API

![PHP](https://img.shields.io/badge/PHP-8.4-8892BF?style=for-the-badge&logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

**TeamFlow** is a full-featured project management REST API built with **Laravel 12**. It enables teams to collaborate on projects, manage tasks, track progress, and communicate through comments — a powerful Jira/Asana-style backend API.

---

## 📋 Table of Contents

- [Overview](#-overview)
- [Tech Stack](#-tech-stack)
- [Features](#-features)
  - [Authentication](#-authentication)
  - [Roles & Permissions](#-roles--permissions)
  - [Projects](#-projects)
  - [Tasks](#-tasks)
  - [Labels](#-labels)
  - [Comments](#-comments)
  - [Attachments](#-attachments)
  - [Activity Logs](#-activity-logs)
  - [Notifications](#-notifications)
  - [Caching](#-caching)
  - [Queue System](#-queue-system)
  - [Security](#-security)
- [API Endpoints](#-api-endpoints)
- [Getting Started](#-getting-started)
  - [Requirements](#requirements)
  - [Installation](#installation)
  - [Environment Variables](#-environment-variables)
- [Default Credentials](#-default-credentials)
- [Request Requirements](#-request-requirements)
- [Built With](#-built-with)
- [Contributing](#-contributing)
- [License](#-license)

---

## 🧭 Overview

TeamFlow provides a robust backend REST API for managing the full lifecycle of projects and tasks within a team. It features JWT-based authentication, a granular role and permission system, Redis-powered caching and queues, file uploads, threaded comments, real-time notifications, and comprehensive activity logging — all built on Laravel 12.

---

## 🛠 Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 |
| Language | PHP 8.4 |
| Database | PostgreSQL |
| Cache & Queue | Redis |
| Authentication | JWT (`tymon/jwt-auth`) |
| Authorization | Spatie Laravel Permission |
| Redis Client | Predis |
| Web Server | Nginx + PHP-FPM |
| Deployment | Railway |

---

## ✨ Features

### 🔐 Authentication

- User **registration** and **login** with JWT tokens
- **Logout** with token invalidation
- **Token refresh** for seamless sessions
- **Get authenticated user** profile (`/me`)
- All API routes are protected except `/login` and `/register`

---

### 👥 Roles & Permissions

TeamFlow uses a **permission-based** authorization model (not just role-based), powered by Spatie Laravel Permission.

**4 Roles:**
| Role | Description |
|---|---|
| `admin` | Full system access |
| `manager` | Manage projects and teams |
| `team_leader` | Lead tasks and team members |
| `member` | Participate in assigned projects and tasks |

**Available Permissions:**

| Permission | Description |
|---|---|
| `create projects` | Create new projects |
| `edit projects` | Update existing projects |
| `delete projects` | Soft delete or archive projects |
| `manage members` | Add/remove/update project members |
| `create tasks` | Create tasks within a project |
| `edit tasks` | Edit all task fields |
| `assign tasks` | Assign tasks to team members |
| `update task status` | Change the status of a task |
| `add comments` | Post comments on projects/tasks |
| `upload attachments` | Attach files to projects/tasks |
| `view reports` | View project statistics and reports |

> **Role Hierarchy Enforcement:** Managers cannot assign admin-level roles; higher roles cannot be overridden by lower ones.

---

### 📁 Projects

- Full **CRUD** with **soft deletes**
- **Archive**, **restore**, and **force delete** operations
- **Project member management** — add, remove, update member roles
- **Project statistics** — total tasks, in-progress, done, overdue counts, and member count
- **Filtering** by status, owner, or member
- **Pagination** on all list endpoints
- **Response caching** via Redis for blazing-fast repeated reads

---

### ✅ Tasks

- Full **CRUD** with **soft deletes**, restore, and force delete
- **Kanban-style statuses:** `todo` → `in_progress` → `in_review` → `done`
- **Priority levels:** `low`, `medium`, `high`, `critical`
- **Task assignment** with role hierarchy validation
- **Field-level permissions:** Members can only update task status; managers and above can edit all fields
- **Label attachment** (many-to-many relationship)
- **Advanced filtering:**
  - By status, priority, assignee
  - By due date: exact date, date range, overdue, today, this week

---

### 🏷 Labels

- **Per-project labels** with hex color validation (e.g., `#FF5733`)
- Attach or detach **multiple labels** to tasks in a single request
- **Cross-project label protection** — labels cannot be applied to tasks in other projects

> When a project is created, a **ProjectCreated** event automatically fires a `CreateDefaultLabels` listener that seeds five default labels: **Bug**, **Feature**, **Urgent**, **Design**, and **Performance**.

---

### 💬 Comments (Polymorphic)

- Comment on both **projects** and **tasks** using the same polymorphic system
- **Threaded replies** via self-referential parent/child relationships
- **Soft deletes** to preserve thread integrity

---

### 📎 Attachments (Polymorphic)

- File uploads on both **projects** and **tasks**
- **Dual validation** — mime type and file extension are both verified
- **Path traversal attack prevention**
- Organized storage directory structure
- **Supported file types:**
  - Images: `jpg`, `jpeg`, `png`, `gif`, `webp`, `svg`
  - Video: `mp4`, `mov`, `avi`
  - Audio: `mp3`, `wav`
  - Documents: `pdf`, `doc`, `docx`, `xls`, `xlsx`, `ppt`, `pptx`
  - Archives: `zip`, `rar`

> File uploads **must** use `multipart/form-data`.

---

### 📜 Activity Logs

- Automatic logging powered by **Eloquent Observers**
- **Polymorphic** subject (what changed) and causer (who changed it)
- Tracked events:
  - Project: created, updated, deleted, restored
  - Task: created, status changed, reassigned, deleted

---

### 🔔 Notifications

- **Database notifications** — stored and fetchable via API
- **Email notifications** via Gmail SMTP
- **Queued via Redis** for non-blocking, background delivery
- **Trigger events:**
  - Task assigned to a user → `TaskAssigned` → `NotifyTaskAssignee`
  - User added to a project
- API actions: mark one as read, mark all as read, delete a notification

---

### ⚡ Caching

- Redis caching for **project stats** and **project list** responses
- **Automatic cache invalidation** on project and task updates
- **Cache tags** for efficient group invalidation
- Measured performance improvement: **12ms → 0.18ms** on cached endpoints

---

### ⚙️ Queue System

- **Redis** as the queue driver
- All notification jobs are queued for **background processing**
- **Failed job handling** with retries

Start the queue worker with:
```bash
php artisan queue:work
```

---

### 🛡 Security

- **Security middleware** applied to every request, setting headers:
  - `X-Frame-Options`
  - `X-Content-Type-Options`
  - `Content-Security-Policy`
  - `Strict-Transport-Security` (HTTPS/production only)
  - `Referrer-Policy`
  - `Permissions-Policy`
- **Secure file uploads** with path traversal prevention
- **Role hierarchy enforcement** on all assignment operations
- **Policy-based authorization** on every endpoint
- **Soft deletes** to prevent accidental data loss

---

## 📡 API Endpoints

All endpoints require:
- `Authorization: Bearer {token}` header *(except `/login` and `/register`)*
- `Accept: application/json` header

| Group | Method | Endpoint |
|---|---|---|
| **Auth** | `POST` | `/api/v1/register` |
| | `POST` | `/api/v1/login` |
| | `POST` | `/api/v1/logout` |
| | `POST` | `/api/v1/refresh` |
| | `GET` | `/api/v1/me` |
| **Projects** | `GET` / `POST` | `/api/v1/projects` |
| | `GET` / `PUT` / `DELETE` | `/api/v1/projects/{id}` |
| **Project Stats** | `GET` | `/api/v1/projects/{id}/stats` |
| **Project Members** | `GET` / `POST` | `/api/v1/projects/{id}/members` |
| | `PATCH` / `DELETE` | `/api/v1/projects/{id}/members/{user}` |
| **Tasks** | `GET` / `POST` | `/api/v1/projects/{id}/tasks` |
| | `GET` / `PUT` / `DELETE` | `/api/v1/projects/{id}/tasks/{task}` |
| **Labels** | `GET` / `POST` | `/api/v1/projects/{id}/labels` |
| | `PATCH` / `DELETE` | `/api/v1/projects/{id}/labels/{label}` |
| **Label ↔ Task** | `POST` / `DELETE` | `/api/v1/projects/{id}/tasks/{task}/labels` |
| **Comments** | `GET` / `POST` | `/api/v1/projects/{id}/comments` |
| | `GET` / `POST` | `/api/v1/projects/{id}/tasks/{task}/comments` |
| **Attachments** | `GET` / `POST` | `/api/v1/projects/{id}/attachments` |
| | `GET` / `POST` | `/api/v1/projects/{id}/tasks/{task}/attachments` |
| **Notifications** | `GET` | `/api/v1/notifications` |
| | `POST` | `/api/v1/notifications/{id}/read` |
| | `POST` | `/api/v1/notifications/read-all` |
| **Profile** | `GET` / `POST` | `/api/v1/profile` |
| | `DELETE` | `/api/v1/profile/avatar` |
| **User Role** | `PATCH` | `/api/v1/users/{user}/assign-role` |
| **Health** | `GET` | `/health-check` |

---

## 🚀 Getting Started

### Requirements

- PHP **8.4+**
- **PostgreSQL**
- **Redis**
- **Composer**

---

### Installation

**1. Clone the repository**
```bash
git clone https://github.com/your-username/team-flow.git
cd team-flow
```

**2. Install dependencies**
```bash
composer install
```

**3. Set up environment**
```bash
cp .env.example .env
```

**4. Generate application key and JWT secret**
```bash
php artisan key:generate
php artisan jwt:secret
```

**5. Configure your `.env` file**

Update the database, Redis, mail, and JWT values. See [Environment Variables](#-environment-variables) below.

**6. Run database migrations**
```bash
php artisan migrate
```

**7. Seed the database**
```bash
php artisan db:seed
```

**8. Start the queue worker**
```bash
php artisan queue:work
```

**9. Start the development server**
```bash
php artisan serve
```

The API will be available at `http://localhost:8000`.

---

### 🔧 Environment Variables

Configure the following variables in your `.env` file. **Never commit real values to version control.**

| Variable | Description |
|---|---|
| `APP_KEY` | Laravel application encryption key |
| `APP_ENV` | Application environment (`local`, `production`) |
| `APP_DEBUG` | Enable debug mode (`true` / `false`) |
| `APP_URL` | Base URL of the application |
| `DB_CONNECTION` | Database driver (use `pgsql` for PostgreSQL) |
| `DB_HOST` | Database host address |
| `DB_PORT` | Database port (PostgreSQL default: `5432`) |
| `DB_DATABASE` | Database name |
| `DB_USERNAME` | Database username |
| `DB_PASSWORD` | Database password |
| `CACHE_STORE` | Cache driver (use `redis`) |
| `QUEUE_CONNECTION` | Queue driver (use `redis`) |
| `REDIS_HOST` | Redis server host |
| `REDIS_PORT` | Redis server port |
| `REDIS_PASSWORD` | Redis authentication password |
| `REDIS_CLIENT` | Redis client library (use `predis`) |
| `MAIL_MAILER` | Mail driver (e.g., `smtp`) |
| `MAIL_HOST` | SMTP host (e.g., Gmail SMTP) |
| `MAIL_PORT` | SMTP port |
| `MAIL_USERNAME` | SMTP username / email address |
| `MAIL_PASSWORD` | SMTP password or app password |
| `MAIL_FROM_ADDRESS` | The "from" email address for outgoing mail |
| `JWT_SECRET` | Secret key for signing JWT tokens |

---

## 🔑 Default Credentials

After running `php artisan db:seed`, the following accounts are available:

| Role | Email | Password |
|---|---|---|
| Admin | `admin@teamflow.com` | `password` |
| Manager | `manager@teamflow.com` | `password` |
| Team Leader | `teamleader@teamflow.com` | `password` |
| Member | `member@teamflow.com` | `password` |

> ⚠️ **Change all default passwords before using in any production or staging environment.**

---

## 📌 Request Requirements

| Requirement | Details |
|---|---|
| Auth header | `Authorization: Bearer {token}` (all routes except login & register) |
| Accept header | `Accept: application/json` (all routes) |
| JSON body | `Content-Type: application/json` |
| File uploads | `Content-Type: multipart/form-data` |

---

## 🏗 Built With

- [Laravel 12](https://laravel.com) — PHP web application framework
- [tymon/jwt-auth](https://github.com/tymondesigns/jwt-auth) — JWT authentication for Laravel
- [spatie/laravel-permission](https://github.com/spatie/laravel-permission) — Role and permission management
- [predis/predis](https://github.com/predis/predis) — PHP Redis client
- [PostgreSQL](https://www.postgresql.org) — Relational database
- [Redis](https://redis.io) — In-memory data store for caching and queues
- [Railway](https://railway.app) — Application deployment platform

---

## 📄 License

This project is licensed under the **MIT License**. See the [LICENSE](LICENSE) file for details.

---

<p align="center">Built with ❤️ using Laravel 12</p>
