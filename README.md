# 🛍️ E-Commerce API (Laravel & Sanctum)

An extensive, production-ready E-Commerce API built with the Laravel framework, covering the full purchasing lifecycle, secure payments, and a robust role-based administration system.

## ✨ Key Features

This API adheres to **RESTful API** principles and demonstrates strong proficiency in modern Laravel development practices:

* **Secure Authentication:** Utilizes **Laravel Sanctum** for token-based authentication (API Tokens).
* **Role-Based Access Control (RBAC):** Implements **Spatie/laravel-permission** to restrict critical endpoints to the `admin` role.
* **Secure Checkout:** Integrates **Stripe** via **Laravel Cashier** for creating secure payment sessions.
* **Transactional Integrity:** Guarantees data consistency during the checkout process using **Database Transactions** (e.g., atomically creating orders and deducting stock).
* **Clean Code Standards:** Enforces standardized data output across all endpoints using **Laravel API Resources**.
* **Full CRUD:** Complete management APIs for **Products** and **Categories**, and status management for **Orders**.

---

## 🛠️ Installation & Setup

### Prerequisites

* PHP (8.2+)
* Composer
* Laravel (10+)
* Database (SQLite recommended for local testing)

### Installation Steps

1.  **Clone the Repository:**
    ```bash
    git clone [Your Repository URL]
    cd ecommerce-api
    ```

2.  **Install Dependencies:**
    ```bash
    composer install
    ```

3.  **Configure Environment:**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    * **Stripe Configuration:** Update your Stripe secret keys and publishable keys in the `.env` file (required for the Checkout functionality).

4.  **Database Setup:**
    ```bash
    php artisan migrate
    php artisan db:seed 
    ```
    *(The Seeder automatically creates `admin` and `customer` roles and assigns the `admin` role to a default user for testing: **admin@example.com** / Password: **12345678**)*

---

## 🔑 Authentication & Roles

| Role | Access Level | Protected Endpoints |
| :--- | :--- | :--- |
| **`customer`** | Standard user. | Cart management, Checkout, Personal Order History. |
| **`admin`** | Store administrator. | All `customer` access + all endpoints under `/api/admin/*`. |

### Security Gate:

All administrator endpoints are protected by a dual middleware gate: `['auth:sanctum', 'role:admin']`. Access is denied with a `403 Forbidden` error if the user is authenticated but not an administrator.

---

## 🌐 API Endpoints Guide

| Category | Endpoint | Method | Description | Security |
| :--- | :--- | :--- | :--- | :--- |
| **Auth** | `/api/register` | `POST` | Creates a new user account. | Public |
| | `/api/login` | `POST` | Generates a new Sanctum API Token. | Public |
| | `/api/logout` | `POST` | Revokes the current user's Token. | Token Required |
| **Products** | `/api/products` | `GET` | Lists products (supports `search`, `category`, `min_price`, `max_price` query params). | Public |
| | `/api/products/{slug}` | `GET` | Displays detailed product information. | Public |
| **Cart** | `/api/cart/add` | `POST` | Adds or updates product quantity in the cart. | Token Required |
| | `/api/cart` | `GET` | Views current cart contents. | Token Required |
| **Orders** | `/api/checkout` | `POST` | **Critical:** Creates order, deducts stock, and initiates Stripe checkout session. | Token Required |
| | `/api/orders` | `GET` | Views the currently authenticated user's order history. | Token Required |
| **Admin** | `/api/admin/products` | `POST/PUT/DELETE` | Full CRUD operations for products. | `admin` Role |
| | `/api/admin/orders` | `GET` | Views **all** orders placed in the store. | `admin` Role |
| | `/api/admin/orders/{order}/status` | `PUT` | Updates an order's status (`shipped`, `delivered`, etc.). | `admin` Role |
| | `/api/admin/categories` | `POST/PUT/DELETE` | Full CRUD operations for categories. | `admin` Role |
