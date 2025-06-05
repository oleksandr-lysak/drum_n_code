# Todo List API

A modern RESTful API for managing tasks and subtasks with unlimited nesting, built on Laravel 12, strict typing, service/repository/DTO architecture, and full OpenAPI documentation.

---

## Features
- User authentication (Laravel Sanctum)
- CRUD for tasks with unlimited nested subtasks
- Filtering by status, priority, full-text search (title, description)
- Sorting by multiple fields (priority, created_at, completed_at)
- Only the owner can modify/delete their tasks
- Cannot delete completed tasks
- Cannot mark as done if there are incomplete subtasks
- Strict typing, DTO, Enum, PSR-12 code style
- Service, repository, policy, resource layers
- Dockerized: Laravel + Nginx + MySQL
- OpenAPI (Swagger) documentation

---

## Quick Start (Docker)

1. **Copy and configure environment:**
   ```bash
   cp .env.example .env
   # Edit DB_*, APP_* if needed
   ```
2. **Build and start containers:**
   ```bash
   docker-compose up --build -d
   ```
3. **Install dependencies and migrate:**
   ```bash
   docker-compose exec app composer install
   docker-compose exec app php artisan key:generate
   docker-compose exec app php artisan migrate --seed
   ```
4. **API available at:** [http://localhost:8080](http://localhost:8080)
5. **Swagger docs:** [http://localhost:8080/api/documentation](http://localhost:8080/api/documentation)

---

## API Authentication
- Register: `POST /api/register`
- Login: `POST /api/login` (returns Bearer token)
- Use `Authorization: Bearer <token>` for all protected endpoints

Test users (seeded):
- user1@example.com / password
- user2@example.com / password

---

## Main Endpoints
- `GET /api/tasks` — List tasks (filters: status, priority, search, sort)
- `POST /api/tasks` — Create task
- `GET /api/tasks/{id}` — Get task with subtasks
- `PUT /api/tasks/{id}` — Update task
- `DELETE /api/tasks/{id}` — Delete task
- `POST /api/tasks/{id}/done` — Mark as done

See full request/response schemas and try requests in [Swagger UI](http://localhost:8080/api/documentation).

---

## Project Structure
- `app/Http/Controllers` — Thin controllers, only delegation
- `app/Http/Requests` — FormRequest validation (with toDTO methods)
- `app/Http/Resources` — API Resources for response formatting
- `app/DTO` — Data Transfer Objects (strict typed)
- `app/Policies` — Authorization logic
- `app/TaskService.php` — All business logic
- `app/TaskRepository.php` — All DB queries, filtering, search, sort
- `app/Models/Task.php` — Eloquent model, recursive children relation
- `app/AuthService.php` — Auth business logic

---

## Technologies & Requirements
- PHP 8.2+
- Laravel 12
- MySQL 8+
- Docker, Docker Compose
- PSR-12 code style, strict_types
- OpenAPI 3.0 (Swagger)

---

## Example: Get Tasks (with filters, search, sort)
```http
GET /api/tasks?status=todo&priority=3&search=main&sort=priority:desc,created_at:asc
Authorization: Bearer <token>
```

## Example: Create Task
```http
POST /api/tasks
Authorization: Bearer <token>
Content-Type: application/json
{
  "status": "todo",
  "priority": 3,
  "title": "New Task",
  "description": "Description"
}
```

---

## Testing
- Use [Postman collection](./TodoListAPI.postman_collection.json) or Swagger UI for quick testing.
- All business rules are covered (ownership, completed/deletion, subtasks, etc).

