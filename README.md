
# Laravel REST News Portal

RESTful API for the News Portal, built with Laravel. Provides endpoints for managing articles, comments, reactions, and user authentication and authorization.

## Features
- REST API for News Management
- Role-Based Access Control (Admin/User)
- Like/Dislike Articles and Comments
- Soft Deletes for Articles

## Requirements
- PHP >= 8.0
- Composer
- MySQL or PostgreSQL
- Docker (optional, for container)

## Related Repositories
- [News Portal Frontend](https://github.com/ramdani15/angular-news-portal): The Angular frontend for user interaction, article management, and comment functionalities.

## Setup

1. Clone the repository:
   ```bash
   git clone https://github.com/ramdani15/laravel-rest-news-portal.git
   cd laravel-rest-news-portal
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Set up environment variables:
   Copy the `.env.example` file to `.env` and update the database credentials:
   ```plaintext
   DB_CONNECTION=pqsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=your_db_name
   DB_USERNAME=your_user
   DB_PASSWORD=your_password
   ```

4. Generate application key:
   ```bash
   php artisan key:generate
   ```

5. Run migrations:
   ```bash
   php artisan migrate
   ```

6. Seed initial data:
   ```bash
   php artisan db:seed
   ```

7. Start the server:
   ```bash
   php artisan serve
   ```

## Setup with Docker

1. Clone the repository:
   ```bash
   git clone https://github.com/ramdani15/laravel-rest-news-portal.git
   cd laravel-rest-news-portal
   ```

2. Run app and db container:
   ```bash
   make build
   ```
   or 
   ```bash
   docker compose up -d --build
   ```

3. Install dependencies:
   ```bash
   docker composer exec app composer install
   ```

4. Set up docker environment variables:
   Copy the `/docker/.env.example` file to `/docker/.env` and update the postgres and pgadmin credentials

5. Set up environment variables:
   Copy the `.env.example` file to `.env` and update the database credentials:
   ```plaintext
   DB_CONNECTION=pqsql
   DB_HOST=postgres
   DB_PORT=5432
   DB_DATABASE=your_postgres_db
   DB_USERNAME=your_postgres_user
   DB_PASSWORD=your_postgres_password
   ```

6. Generate application key:
   ```bash
   docker compose exec app php artisan key:generate
   ```

7. Run migrations:
   ```bash
   docker compose exec app php artisan migrate
   ```

8. Seed initial data:
   ```bash
   docker compose exec app php artisan db:seed
   ```

9. Open http://localhost:8080

## API Endpoints
- **Documentation**:
  - `/api/v1/documentation`: Swagger
- **Auth**:
  - `POST /api/v1/auth/signup`: Register a new user
  - `POST /api/v1/auth/login`: Log in a user
  - `POST /api/v1/auth/logout`: Log out the current user
- **Dashboard**:
  - `GET /api/v1/dashboard`: List of published articles
  - `GET /api/v1/dashboard/{id}`: Detail of published article
  - `GET /api/v1/dashboard/{id}/comments`: List of published article's comment
- **Articles**:
  - `GET /api/v1/articles`: List articles
  - `POST /api/v1/articles`: Create a new article
  - `GET /api/v1/articles/{id}`: Detail of article
  - `PUT /api/v1/articles/{id}`: Update an article (admin or owner)
  - `DELETE /api/v1/articles/{id}`: Soft delete an article (admin or owner)
  - `POST /api/v1/articles/{id}/toggle-reaction`: Like or Dislike article
  - `POST /api/v1/articles/{id}/request-approval`: Request for approval (owner)
  - `POST /api/v1/articles/{id}/approve`: Approve the request approval (admin)
  - `POST /api/v1/articles/{id}/reject`: Reject the request approval (admin)
  - `POST /api/v1/articles/{id}/publish`: Publish the approved article (admin or owner)
  - `POST /api/v1/articles/{id}/unpublish`: Unpublish the published article (admin or owner)
- **Comments**:
  - `POST /api/v1/comments`: Add a comment
  - `POST /api/v1/comments/{id}/reply`: Add a reply comment
  - `POST /api/v1/comments/{id}/toggle-reaction`: Like or Dislike comment
- **Profile**:
  - `GET /api/v1/profile`: Get profile of the current user
  - `PATCH /api/v1/profile`: Update profile for the current user
