---

# Symfony Docker Setup

This project is a basic Docker environment for running a Symfony application using:

* **PHP 8.2 (FPM)**
* **PostgreSQL**
* **Redis**
* **Docker Compose**

## 🚀 Getting Started

### 1. Clone the repository

```bash
git clone <repo-url>
cd siroko_test
```

### 2. Build and start the containers

Make sure Docker and Docker Compose are installed and running.

```bash
sudo docker-compose up --build -d
```

### 3. Check container status

```bash
sudo docker-compose ps
```

You should see something like:

```
Name                    Command                  State           Ports
----------------------------------------------------------------------------
siroko_test_app_1       docker-php-entrypoint    Up              0.0.0.0:8080->80/tcp
siroko_test_db_1        docker-entrypoint.sh     Exit 1
siroko_test_redis_1     docker-entrypoint.sh     Exit 1
```

> ⚠️ If `db` or `redis` are exiting, check the logs:
>
> ```bash
> docker-compose logs db
> docker-compose logs redis
> ```

## 🛠 Installing Symfony Dependencies

The PHP container includes Composer, but dependencies are **not installed automatically**. You must do it manually.

### 1. Enter the app container

```bash
sudo docker exec -it siroko_test_app_1 bash
```

### 2. Install dependencies

Inside the container:

```bash
composer install
```

Or if needed:

```bash
composer update
```

> This will generate the `vendor/` directory and install Symfony dependencies.

## 📁 Project Structure (after composer install)

```bash
.
├── Dockerfile               # PHP 8.2 + Symfony + extensions
├── docker-compose.yml       # Defines app, db, redis services
├── composer.json            # Project dependencies (Symfony Flex)
├── composer.lock            # Locked versions of dependencies
├── .env                     # Symfony environment variables
├── vendor/                  # Created after composer install/update
└── ...
```

## ⚙️ Default Ports

| Service | Port                    |
| ------- | ----------------------- |
| App     | `http://localhost:8080` |
| DB      | `5432`                  |
| Redis   | `6379`                  |

## ✅ Notes

* The Dockerfile installs PHP extensions for PostgreSQL and Redis.
* Composer is copied from the official Composer image.
* Symfony source code is mounted into the container at `/var/www/html`.
* The `vendor/` folder is ignored in Git and is only created after installing dependencies inside the container.

---
