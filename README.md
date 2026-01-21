# Oikos

Oikos is a full-stack application built with Vue.js (Frontend) and Laravel (Backend), containerized with Docker.

## Prerequisites

- [Docker](https://docs.docker.com/get-docker/)
- [Docker Compose](https://docs.docker.com/compose/install/)

## Tech Stack

- **Frontend**: Vue.js, Vite (Node 22)
- **Backend**: Laravel (PHP 8.5)
- **Database**: PostgreSQL

## Getting Started

### Development Environment

The development environment runs the services with hot-reloading enabled.

1.  **Clone the repository:**
    ```bash
    git clone <repository-url>
    cd oikos
    ```

2.  **Start the application:**
    ```bash
    docker-compose up
    ```

    - **Frontend**: Accessible at http://localhost:5173
    - **Backend**: Accessible at http://localhost:8000
    - **Database**: Port 5432

3.  **Stopping the services:**
    ```bash
    docker-compose down
    ```

### Production Environment

The production environment runs optimized builds of the frontend and backend, served via Nginx.

1.  **Start the application in production mode:**
    ```bash
    docker-compose -f docker-compose.prod.yml up --build -d
    ```

    - **Frontend**: Accessible at http://localhost:80
    - **Backend API**: Accessible at http://localhost:8000 (Served via Nginx)

2.  **Stopping the services:**
    ```bash
    docker-compose -f docker-compose.prod.yml down
    ```

## Project Structure

- `frontend/`: Vue.js application
- `backend/`: Laravel application
- `nginx/`: Nginx configuration for production backend
- `docker-compose.yml`: Development Docker Compose configuration
- `docker-compose.prod.yml`: Production Docker Compose configuration
