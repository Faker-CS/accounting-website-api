# Accountant API

This is the backend API for the Accountant application, a platform for managing accounting tasks, documents, clients, and communication between accountants and their clients.

## About The Project

This API provides a comprehensive set of endpoints to support the Accountant web application. It includes features like:

*   **User Authentication:** Secure user login and registration using JWT.
*   **Company Management:** CRUD operations for companies and their associated files.
*   **Role-Based Access Control:** Differentiated permissions for different user roles (e.g., `entreprise`, `comptable`).
*   **Document Handling:** Upload, download, and manage various types of documents.
*   **Form Processing:** Submission and management of different service forms (e.g., SARL registration).
*   **Real-time Chat:** One-on-one and group chat functionality with file attachments and read receipts.
*   **Task Management:** Create, update, and manage tasks and sub-tasks.
*   **Notifications:** In-app notifications for important events.
*   **Calendar:** Manage events and schedules.

## Built With

*   [Laravel](https://laravel.com/) (v12.0)
*   [PHP](https://www.php.net/) (v8.2)
*   [Tymon JWT-Auth](https://jwt-auth.readthedocs.io/en/develop/) for authentication
*   [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission/v6/introduction) for roles and permissions
*   [Pusher](https://pusher.com/) for real-time functionalities

## Getting Started

To get a local copy up and running, follow these simple steps.

### Prerequisites

Make sure you have the following installed on your development machine:
*   PHP >= 8.2
*   [Composer](https://getcomposer.org/)
*   Node.js & NPM

### Installation

1.  **Clone the repository**
    ```sh
    git clone <your-repo-url>
    cd accountant-api-app
    ```

2.  **Install PHP dependencies**
    ```sh
    composer install
    ```

3.  **Install NPM dependencies**
    ```sh
    npm install
    ```

4.  **Create your environment file**
    ```sh
    cp .env.example .env
    ```

5.  **Generate an application key**
    ```sh
    php artisan key:generate
    ```

6.  **Configure your database**
    Open the `.env` file and set your database connection details (DB_DATABASE, DB_USERNAME, DB_PASSWORD).

7.  **Run database migrations and seeders**
    The seeders will populate the database with initial data, like user roles and services.
    ```sh
    php artisan migrate --seed
    ```

8.  **Set up JWT secret**
    ```sh
    php artisan jwt:secret
    ```

## Running the Application

You can run the development server, queue worker, and other necessary processes using the custom `dev` script.

```sh
composer run dev
```

Alternatively, you can run the Laravel development server by itself:
```sh
php artisan serve
```
The API will be available at `http://localhost:8000`.

## Running Tests

To run the automated test suite for this project, use the following command:

```sh
php artisan test
```

## API Endpoints

The API is protected by JWT authentication. You need to obtain a token from the `/api/login` endpoint and include it in the `Authorization` header of your requests as a Bearer token.

Here is a summary of the available API resources:

*   **Authentication**: `POST /api/login`, `POST /api/logout`, `GET /api/user`
*   **Companies**: `GET|POST|PUT|DELETE /api/companies/{id}`
*   **Company Files**: `GET|POST|DELETE /api/companies/{company}/files`
*   **Aide Comptables**: `GET|POST|PUT|DELETE /api/aideComptable/{id}`
*   **Chat**: `/api/conversations`, `/api/messages`
*   **User Profile**: `GET|PUT /api/profile`
*   **Documents**: `GET|POST|DELETE /api/documents`
*   **Forms**: `GET|POST|PATCH|DELETE /api/forms/{id}`
*   **Services**: `GET /api/services`
*   **Notifications**: `GET|PATCH|DELETE /api/notifications`
*   **Calendar**: `GET|POST|PUT|DELETE /api/calendar/{id}`
*   **Tasks**: `GET|POST|PUT|DELETE /api/tasks/{id}`

For detailed information on all endpoints and their parameters, please refer to the `routes/api.php` file.
