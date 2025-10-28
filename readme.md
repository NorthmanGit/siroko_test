
# Siroko Test API

API REST desarrollada en **Symfony 7**, estructurada siguiendo los principios de **Domain-Driven Design (DDD)**, **Arquitectura Hexagonal** y **CQRS (Command Query Responsibility Segregation)**.  
El proyecto está completamente dockerizado e integra persistencia mediante **PostgreSQL** y almacenamiento temporal en **Redis**.

---

## Getting Started

### 1. Clonar el repositorio

```bash
git clone <repo-url>
cd siroko_test
````

### 2. Levantar el entorno Docker

```bash
sudo docker-compose up --build -d
```

### 3. Instalar dependencias

Entrar al contenedor PHP y ejecutar Composer:

```bash
sudo docker exec -it siroko_test_app_1 bash
composer install
```

### 4. Ejecutar migraciones (base de datos PostgreSQL)

Para generar y aplicar las migraciones necesarias para el modelo de datos (entidades `Order` y `OrderItem`):

```bash
php bin/console doctrine:migrations:migrate
```

Esto creará las tablas en la base de datos configurada en `.env`


### 5. Verificar servicios

| Servicio                 | URL / Puerto                                                     |
| ------------------------ | ---------------------------------------------------------------- |
| API                      | [http://localhost:8080](http://localhost:8080)                   |
| PostgreSQL               | 5432                                                             |
| Redis                    | 6379                                                             |
| Swagger UI (si activado) | [http://localhost:8080/api/docs](http://localhost:8080/api/docs) |

---

## Breve descripción del proyecto

El sistema implementa una **API de carrito de compras (Cart)** que permite:

* Crear un carrito (`POST /api/cart`)
* Añadir o eliminar productos (`POST /api/cart/{id}/item`, `DELETE /api/cart/{id}/item/{productId}`)
* Modificar cantidades (`PATCH /api/cart/{id}/item/{productId}`)
* Consultar el carrito (`GET /api/cart/{id}`)
* Realizar el **checkout** (`POST /api/cart/{cartId}/checkout`)

El proceso de checkout genera una **orden (Order)** persistida en la base de datos, eliminando el carrito de Redis tras la confirmación.

---

## Modelado del dominio

El dominio se ha separado en **entidades** y **repositorios**, siguiendo una **arquitectura hexagonal**.

### Entidades principales

* **Cart** → Representa el carrito temporal (almacenado en Redis).
* **CartItem** → Producto agregado al carrito.
* **Order** → Representa una orden confirmada, persistida en PostgreSQL.
* **OrderItem** → Ítem de una orden.

### Repositorios

* `CartRepositoryInterface` → Implementado por `RedisCartRepository`.
* `OrderRepositoryInterface` → Implementado por `DoctrineOrderRepository`.

### Patrón CQRS

* **Commands** → Mutan el estado del sistema (crear carrito, añadir producto, checkout...).
* **Queries** → Consultan información (recuperar el carrito).
* **Handlers** → Ejecutan la lógica de cada comando o consulta.

---

##  Tecnología utilizada

| Componente                  | Descripción                                              |
| --------------------------- | -------------------------------------------------------- |
| **Symfony 7**               | Framework principal del API                              |
| **Doctrine ORM**            | Persistencia en PostgreSQL                               |
| **Redis**                   | Almacenamiento temporal de carritos                      |
| **Messenger**               | Implementación CQRS y bus de comandos/consultas          |
| **PHP 8.2 (FPM)**           | Lenguaje base                                            |
| **PostgreSQL**              | Base de datos relacional                                 |
| **Docker & Docker Compose** | Entorno de desarrollo aislado                            |
| **NelmioApiDocBundle**      | Generación automática de documentación OpenAPI / Swagger |

---

## OpenAPI / Swagger Specification

La documentación del API se genera automáticamente con **NelmioApiDocBundle**.

### JSON Specification

## Swagger de test

1. Abre tu navegador en [http://localhost:8080/api/docs](http://localhost:8080/api/docs)
2. Explora los endpoints:

   * `POST /api/cart`
   * `POST /api/cart/{id}/item`
   * `GET /api/cart/{id}`
   * `PATCH /api/cart/{id}/item/{productId}`
   * `DELETE /api/cart/{id}/item/{productId}`
   * `POST /api/cart/{cartId}/checkout`
3. Puedes realizar pruebas directamente desde la interfaz Swagger.
---

## Tests

El proyecto incluye **tests funcionales** implementados con `PHPUnit` para verificar el comportamiento completo del API.

Para ejecutar los tests:

```bash
sudo docker exec -it siroko_test_app_1 bash
php bin/phpunit
```

### Tests principales

* `CartApiTest` → Cubre creación, actualización y borrado del carrito.
* `CheckoutHandlerTest` → Cubre el proceso de checkout y persistencia de ordenes.

Los tests validan la comunicación entre Redis, Doctrine y los handlers CQRS.


---

## Arquitectura general

```text
┌────────────────────┐
│  Presentation/API  │   ← Symfony Controllers
└────────┬───────────┘
         │
         ▼
┌────────────────────┐
│ Application Layer  │   ← Commands / Queries / Handlers (CQRS)
└────────┬───────────┘
         │
         ▼
┌────────────────────┐
│   Domain Layer     │   ← Entities + Repositories (DDD)
└────────┬───────────┘
         │
         ▼
┌────────────────────┐
│ Infrastructure     │   ← Doctrine, Redis, Repositories
└────────────────────┘
```
