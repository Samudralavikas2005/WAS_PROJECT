# Software Requirements Specification (SRS) — SecureCart

**Project Name**: SecureCart E-Commerce Platform  
**Document Standard**: IEEE 830-1998 SRS Standard  
**Version**: 1.0.0  
**Status**: Approved & Baseline  

---

## 1. Introduction

### 1.1 Purpose
This Software Requirements Specification (SRS) document details the functional, non-functional, security, and interface requirements for **SecureCart**, a secure online shopping and checkout web application designed with defensive software engineering patterns to mitigate OWASP Top 10 web vulnerabilities.

### 1.2 Scope
SecureCart provides a full e-commerce experience including user registration, authentication, product browsing, dynamic cart management, atomic checkout, and order history, alongside an administrative management portal for inventory control and security audit logging.

### 1.3 Definitions, Acronyms, and Abbreviations
* **SRS**: Software Requirements Specification
* **OWASP**: Open Web Application Security Project
* **SQLi**: SQL Injection
* **XSS**: Cross-Site Scripting
* **CSRF**: Cross-Site Request Forgery
* **IDOR**: Insecure Direct Object Reference
* **RBAC**: Role-Based Access Control
* **PDO**: PHP Data Objects

---

## 2. Overall Description

### 2.1 Product Perspective
SecureCart operates as a 3-tier server-side web application. It connects a web browser front-end (HTML5/CSS3/JavaScript) to a server-side PHP 8+ application tier and a MySQL 8.0 relational database tier.

### 2.2 User Classes and Characteristics
1. **Unauthenticated Visitor**: Can browse product catalog, view product details, search items, and register an account.
2. **Authenticated Customer**: Can manage shopping cart items, place atomic orders, view private order receipts, and submit product reviews.
3. **Administrator**: Can manage product inventory, view global customer orders, and inspect brute-force authentication security logs.

### 2.3 Operating Environment
* **OS**: Linux (Ubuntu 22.04/24.04 LTS)
* **Backend Language**: PHP 8.0+ (PHP 8.3 CLI/FPM)
* **Database**: MySQL Server 8.0+
* **Web Server**: Apache2 / Nginx / Built-in PHP Dev Server

---

## 3. Functional Requirements

### FR-1: User Registration & Identity Management
* **FR-1.1**: The system shall allow new users to register via `/auth/register.php` with full name, email, and password.
* **FR-1.2**: The system shall validate password complexity and hash passwords using `password_hash()` (Bcrypt).
* **FR-1.3**: The system shall reject registration attempts with duplicate email addresses.

### FR-2: User Authentication & Session Security
* **FR-2.1**: The system shall authenticate users against Bcrypt hashes in the `users` table.
* **FR-2.2**: The system shall execute `session_regenerate_id(true)` immediately upon successful login.
* **FR-2.3**: Session cookies shall enforce `HttpOnly`, `SameSite=Lax`, and `Secure` (over HTTPS) flags.

### FR-3: Product Catalog & Discovery
* **FR-3.1**: The system shall dynamically display products retrieved from the `products` table on `/products/index.php`.
* **FR-3.2**: The system shall support keyword search and category filtering.
* **FR-3.3**: The system shall display detailed product pages (`/products/view.php?id=X`) with customer ratings and reviews.

### FR-4: Shopping Cart Engine
* **FR-4.1**: Authenticated customers shall be able to add products to their shopping cart.
* **FR-4.2**: Cart items shall be persisted in the MySQL `cart` table scoped to `$_SESSION['user_id']`.
* **FR-4.3**: Customers shall be able to update item quantities and remove items from the cart.

### FR-5: Transactional Checkout & Order Processing
* **FR-5.1**: Checkout processing (`/checkout/place_order.php`) shall execute inside a MySQL ACID transaction (`START TRANSACTION`).
* **FR-5.2**: Order item prices shall be calculated strictly from canonical MySQL database records, ignoring client-submitted prices.
* **FR-5.3**: Successful orders shall decrement product inventory stock (`UPDATE products SET stock_quantity = stock_quantity - :qty`) and clear the user's cart.

### FR-6: Order History & IDOR Protection
* **FR-6.1**: Customers shall view their past orders on `/orders/index.php`.
* **FR-6.2**: Viewing order details (`/orders/view.php?id=X`) shall verify server-side that `order['user_id'] === $_SESSION['user_id']`. Unowned orders shall return HTTP 403 Forbidden.

### FR-7: Administrative Control Portal
* **FR-7.1**: Access to `/admin/*` routes shall enforce server-side RBAC checking `$_SESSION['role'] === 'admin'`.
* **FR-7.2**: Administrators shall be able to add, edit, and delete products, view customer orders, and inspect brute-force audit logs (`login_attempts`).

---

## 4. Non-Functional & Security Requirements

### NFR-1: Security Defenses (OWASP Top 10 Mitigation)
* **SQL Injection**: 100% of database interactions shall use PDO prepared statements with bound parameters.
* **Cross-Site Scripting (XSS)**: All user-supplied output rendered in HTML shall be escaped using `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
* **CSRF Protection**: All state-modifying POST forms shall validate per-session cryptographic CSRF tokens (`random_bytes(32)`) using `hash_equals()`.
* **Brute-Force Lockout**: 5 consecutive failed login attempts for an email shall trigger a 15-minute account lockout (tracked in `login_attempts`).

### NFR-2: Performance & Reliability
* **Response Time**: Page response times shall be under 200ms for standard database queries.
* **Data Consistency**: Atomic transactions shall prevent race conditions during simultaneous user checkouts.
