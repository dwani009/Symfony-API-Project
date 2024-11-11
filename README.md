# Symfony API Project

This is a Symfony-based API project that demonstrates the implementation of CRUD operations for Customer, Product, and Cart entities. The project is designed following SOLID principles and clean architecture practices, with added functionalities such as Redis caching, RabbitMQ message queuing, and structured exception handling.

---

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Architecture and Design](#architecture-and-design)
- [Caching](#caching)
- [Message Queue with RabbitMQ](#message-queue-with-rabbitmq)
- [Exception Handling](#exception-handling)
- [Testing](#testing)
- [Contributing](#contributing)

---

## Features

- **CRUD Operations**: Basic Create, Read, Update, and Delete operations for Customer, Product, and Cart entities.
- **Caching with Redis**: Caches frequently accessed data using Redis, reducing database load and improving response times.
- **Asynchronous Messaging with RabbitMQ**: Utilizes Symfony Messenger and RabbitMQ for event-driven processing.
- **Structured API Responses**: Consistent response structure with status messages and metadata.
- **Custom Exception Handling**: Standardized error response with custom error formats.

---

## Requirements

- PHP 7.4 or later
- Composer
- Symfony 5.4
- MySQL
- Redis
- RabbitMQ

---

## Installation

1. **Clone the repository**:
    ```bash
    git clone https://github.com/yourusername/your-repo-name.git
    cd your-repo-name
    ```

2. **Install dependencies**:
    ```bash
    composer install
    ```

3. **Set up the environment variables**:
   - Copy `.env.example` to `.env` and adjust your database, Redis, and RabbitMQ configurations.

4. **Run migrations**:
    ```bash
    php bin/console doctrine:migrations:migrate
    ```

5. **Start Redis and RabbitMQ services** (if not already running).

---

## Configuration

- **Redis**: Ensure the Redis server is running and accessible at the configured URL. You can modify the Redis configuration in the `.env` file with `REDIS_URL`.
- **RabbitMQ**: Configure RabbitMQ in `.env` using `MESSENGER_TRANSPORT_DSN`.

---

## Usage

1. **Start the Symfony server**:
    ```bash
    symfony server:start
    ```

2. **Run RabbitMQ consumer**:
    ```bash
    php bin/console messenger:consume async
    ```

3. **API Endpoints**:
   - **Customer**: CRUD operations and cache clearing upon creation or update.
   - **Product**: CRUD operations with Redis caching.
   - **Cart**: Fetch, update, and manage products in the cart.

   Each endpoint is defined with structured JSON responses for success and error cases.

---

## Architecture and Design

The project is structured according to Symfony's best practices, with additional considerations for clean code and SOLID principles:

- **Service Layer**: Contains business logic for Customer, Product, and Cart entities.
- **DTOs (Data Transfer Objects)**: Used to transform entities into structured API responses.
- **Custom Exception Handling**: An exception listener handles and formats errors for API responses.

---

## Caching

- **RedisCacheService**: Provides caching functionality for `CustomerService`, `ProductService`, and `CartService`.
- Cached data has a configurable expiration (default set to 10 minutes).
- Cache keys are managed per entity, allowing easy invalidation of specific caches upon updates.

---

## Message Queue with RabbitMQ

- **Symfony Messenger**: Integrates RabbitMQ for asynchronous processing.
- **Customer Notifications**: Upon creation of a customer, a notification message is dispatched to RabbitMQ, which is consumed asynchronously.
- **Example Message**: `CustomerNotificationMessage` dispatched on `CustomerService::saveCustomer()`.

---

## Exception Handling

A global exception listener standardizes error responses. Unhandled exceptions are captured and returned with a consistent format:

```json
{
  "status": "error",
  "statusCode": 404,
  "message": "Resource not found",
  "metadata": {
    "requestId": "unique-request-id",
    "requestDuration": "xx ms"
  }
}
