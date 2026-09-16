# SecureCart — Secure Online Shopping & Checkout Web Application

**SecureCart** is a clean, modern e-commerce web application built using **PHP 8+**, **MySQL**, **HTML5**, **CSS3**, and minimal **JavaScript**. 

Designed specifically as a **Web Application Security Mini-Project**, SecureCart demonstrates how common web vulnerabilities listed in the OWASP Top 10 can be systematically prevented through secure server-side coding practices and defensive database transactions.

---

## 1. Core Features & Architecture

### E-Commerce Capabilities
* **User Authentication**: Secure user registration, password strength validation, and login.
* **Product Catalog**: Dynamic catalog browsing, product search, category filtering, and product details.
* **Product Reviews**: Customer rating and review submission section.
* **Shopping Cart**: Real-time item additions, quantity updates, and subtotal calculations stored in MySQL.
* **Transactional Checkout**: Atomic order creation with stock decrementing and receipt generation.
* **Order History**: Scoped customer order receipts viewable in "My Orders".
* **Admin Management Portal**: Administrative control panel to manage product inventory, view customer orders, and inspect brute-force security logs.

### Primary Security Defenses
* **SQL Injection (SQLi)**: 100% PDO Prepared Statements with parameterized inputs across all database queries.
* **Cross-Site Scripting (XSS)**: Output escaping using `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` and Content Security Policy (CSP) headers.
* **Cross-Site Request Forgery (CSRF)**: Cryptographically secure per-session CSRF tokens (`bin2hex(random_bytes(32))`) validated on all POST forms.
* **Insecure Direct Object Reference (IDOR)**: Server-side ownership verification (`order['user_id'] === $_SESSION['user_id']`) returning HTTP 403 Forbidden.
* **Brute-Force Protection**: Database-tracked failed attempts (`login_attempts` table) triggering a 15-minute lockout after 5 consecutive failures.
* **Checkout Price Manipulation Protection**: Client-supplied item prices completely ignored; item prices and order totals calculated exclusively from trusted MySQL database values inside an atomic transaction.
* **Session Fixation Defense**: Immediate `session_regenerate_id(true)` execution upon successful authentication.
* **Session Hijacking Defense**: Cookie attributes set to `HttpOnly`, `SameSite=Lax`, `Secure` (over HTTPS), with inactivity timeouts and complete session invalidation upon logout.
* **Role-Based Access Control (RBAC)**: Protected `/admin/*` routes enforced server-side.

---

## 2. Technology Stack & Requirements

* **Operating System**: Linux (Ubuntu 22.04/24.04 recommended)
* **Backend Language**: PHP 8.0+ (PHP 8.3 CLI / FPM)
* **Database**: MySQL Server 8.0+
* **Frontend**: HTML5, CSS3 (Glassmorphism dark theme + Bootstrap 5), Minimal JavaScript
* **Web Server**: Built-in PHP Development Server (or Apache2 / Nginx)

---

## 3. Database Setup (Linux Terminal Commands)

Follow these steps to set up the MySQL database on Linux:

### Step 1: Install & Start MySQL Server
```bash
sudo apt update
sudo apt install -y mysql-server
sudo systemctl start mysql
sudo systemctl enable mysql
```

### Step 2: Create the Database & User
Run MySQL terminal CLI as root or with your local database administrator user:
```bash
mysql -u root -p
```

Inside the MySQL prompt, create the database and grant privileges:
```sql
CREATE DATABASE securecart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'vikas'@'localhost' IDENTIFIED BY 'Vikas@2005';
GRANT ALL PRIVILEGES ON securecart.* TO 'vikas'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 3: Import Schema and Seed Data
Import the database schema and sample data using the Linux terminal:
```bash
mysql -u vikas -pVikas@2005 securecart < database/schema.sql
mysql -u vikas -pVikas@2005 securecart < database/seed.sql
```

---

## 4. Running the Application Locally

1. Navigate to the project directory:
   ```bash
   cd /home/vikas/WAS_PROJECT
   ```

2. Copy the configuration template and verify database credentials:
   ```bash
   cp config.example.php config/config.php
   ```
   *(Note: `config/config.php` is pre-configured with local credentials and excluded from Git).*

3. Start PHP's built-in development server:
   ```bash
   php -S localhost:8000
   ```

4. Open your browser and navigate to:
   ```text
   http://localhost:8000
   ```

---

## 5. Sample Demonstration Accounts

| Role | Email Address | Password | Capabilities |
| :--- | :--- | :--- | :--- |
| **Customer** | `user@securecart.com` | `User@123456` | Browse catalog, add to cart, checkout, write reviews, view own orders |
| **Admin** | `admin@securecart.com` | `Admin@123456` | Access `/admin/*`, manage products, view all orders, inspect security logs |

---

## 6. Step-by-Step Security Demonstrations (Viva & Defense Guide)

This section provides explicit instructions on how to demonstrate each security control during a mini-project presentation.

### Demonstration 1: SQL Injection Prevention
* **Attack Mechanism**: Attacker inputs SQL control characters like `' OR '1'='1` in the login or search form.
* **Demonstration Procedure**:
  1. Go to `http://localhost:8000/auth/login.php`.
  2. In the Email field, enter: `' OR '1'='1`
  3. Enter any password and submit.
* **Expected Result**: Authentication fails with "Invalid email or password."
* **Security Explanation**: In `auth/login.php`, PDO uses parameterized queries (`SELECT ... WHERE email = ?`). The SQL engine treats `' OR '1'='1` strictly as a literal string value rather than executable code.

### Demonstration 2: Cross-Site Scripting (XSS) Prevention
* **Attack Mechanism**: Attacker submits malicious JavaScript payload `<script>alert('XSS')</script>` in product reviews.
* **Demonstration Procedure**:
  1. Log in as a customer (`user@securecart.com` / `User@123456`).
  2. Open any product page (e.g. `http://localhost:8000/products/view.php?id=1`).
  3. In the Review text box, type: `<script>alert('XSS Payload Executed!')</script>` and submit.
* **Expected Result**: The page reloads and displays the literal text `<script>alert('XSS Payload Executed!')</script>` under reviews. No JavaScript popup alert triggers.
* **Security Explanation**: In `products/view.php`, all user output is filtered using `htmlspecialchars($review_text, ENT_QUOTES, 'UTF-8')`, rendering special HTML characters (`<`, `>`, `"`, `'`) as safe entity references (`&lt;`, `&gt;`).

### Demonstration 3: Cross-Site Request Forgery (CSRF) Protection
* **Attack Mechanism**: Attacker hosts a malicious external form to submit unauthorized state-changing POST requests (e.g., adding items or placing orders) without user consent.
* **Demonstration Procedure**:
  1. Open Chrome/Firefox Developer Tools (F12) -> Elements tab on `auth/login.php` or `cart/index.php`.
  2. Notice the hidden input tag: `<input type="hidden" name="csrf_token" value="...">`.
  3. Edit the `value` attribute to `invalid_token_123` or delete the hidden field.
  4. Submit the form.
* **Expected Result**: The server rejects the submission and displays: `"Invalid or expired CSRF token."`
* **Security Explanation**: In `includes/csrf.php`, every session generates a 256-bit cryptographically secure token (`bin2hex(random_bytes(32))`). Incoming POST requests are validated using `hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])`.

### Demonstration 4: Insecure Direct Object Reference (IDOR) Protection
* **Attack Mechanism**: Attacker logged in as User A attempts to view User B's order receipt by changing the URL parameter `/orders/view.php?id=101` to `?id=102`.
* **Demonstration Procedure**:
  1. Log in as `user@securecart.com` and place an order (e.g. Order #1).
  2. Log out and log in as `alice@securecart.com`.
  3. Attempt to access Order #1 directly by visiting `http://localhost:8000/orders/view.php?id=1`.
* **Expected Result**: Access is blocked and the custom `403 Forbidden` error page is displayed.
* **Security Explanation**: In `orders/view.php`, the server checks ownership: `if ($order['user_id'] !== $_SESSION['user_id'] && !is_admin())`. Access is denied on the server side regardless of URL parameters.

### Demonstration 5: Brute-Force Rate Limiting & Account Lockout
* **Attack Mechanism**: Attacker attempts automated password guessing attacks against a user email.
* **Demonstration Procedure**:
  1. Navigate to `http://localhost:8000/auth/login.php`.
  2. Enter email `user@securecart.com` and an incorrect password 5 consecutive times.
* **Expected Result**: On the 5th attempt, the application locks the account and displays: `"Too many failed attempts. Account is temporarily locked. Please try again in 15 minute(s)."`
* **Security Explanation**: In `includes/auth.php`, failed logins increment the `failed_attempts` counter in the `login_attempts` table. Upon reaching 5 failures, `locked_until` is populated with a 15-minute expiry timestamp.

### Demonstration 6: Checkout Price Manipulation Defense
* **Attack Mechanism**: Attacker intercepts checkout POST requests to alter product price fields from `₹2499` to `₹1`.
* **Demonstration Procedure**:
  1. Add an item to cart and proceed to Checkout (`http://localhost:8000/checkout/index.php`).
  2. Open Developer Tools -> Console / Network tab, or inspect form fields. Notice there are **no price fields** sent in the form POST data.
  3. Even if an attacker injects a `price=1.00` parameter into the request payload, observe the order summary upon placement.
* **Expected Result**: The order is placed using the actual price fetched from MySQL (`₹2499`).
* **Security Explanation**: In `checkout/place_order.php`, the server initiates an atomic MySQL transaction (`$pdo->beginTransaction()`), queries the database directly (`SELECT price FROM products WHERE id = ? FOR UPDATE`), and calculates order total strictly on the backend.

### Demonstration 7: Session Fixation Defense
* **Attack Mechanism**: Attacker forces a victim to use a pre-determined session ID prior to authentication.
* **Demonstration Procedure**:
  1. Open Browser Dev Tools -> Application / Storage -> Cookies before logging in.
  2. Note the initial value of the `PHPSESSID` cookie.
  3. Log in as a valid user.
  4. Inspect the `PHPSESSID` cookie value again.
* **Expected Result**: The `PHPSESSID` cookie value changes immediately after login.
* **Security Explanation**: In `auth/login.php`, `session_regenerate_id(true)` is invoked upon successful password verification, deleting the old unauthenticated session identifier.

### Demonstration 8: Session Cookie Security & Hijacking Protection
* **Demonstration Procedure**:
  1. Open Dev Tools -> Application -> Cookies.
  2. Observe cookie flags for `PHPSESSID`:
     * `HttpOnly`: Checked (prevents JavaScript `document.cookie` theft).
     * `SameSite`: Set to `Lax` (mitigates cross-site request forgery).
  3. Open JavaScript Console and run: `console.log(document.cookie);`.
* **Expected Result**: `document.cookie` returns an empty string or does not contain `PHPSESSID`.
* **Security Explanation**: `includes/session.php` configures strict session cookie options via `session_set_cookie_params()`.

### Demonstration 9: Unauthorized Admin Route Access (RBAC)
* **Demonstration Procedure**:
  1. Log in as standard customer `user@securecart.com`.
  2. Type the admin URL directly: `http://localhost:8000/admin/index.php` or `/admin/products.php`.
* **Expected Result**: Server returns HTTP `403 Forbidden`.
* **Security Explanation**: Every file under `/admin/*` includes `require_admin()`, which checks `$_SESSION['role'] === 'admin'` and rejects non-admin users.

---

## 7. Project Structure

```text
WAS_PROJECT/
├── config/
│   ├── config.php            # Main DB & application configuration (git-ignored)
│   └── database.php          # PDO singleton connection with secure flags
├── includes/
│   ├── session.php           # Secure session handler & cookie configuration
│   ├── auth.php              # Auth functions & brute-force rate limiter
│   ├── csrf.php              # Crypto CSRF token generator & validator
│   ├── security.php          # Output escaping (e()) & Security Headers
│   ├── header.php            # Glassmorphism Top Navigation Header
│   └── footer.php            # Footer with security badge overview
├── auth/
│   ├── register.php          # Account registration form & server validation
│   ├── login.php             # Login form & lockout protection
│   └── logout.php            # Secure logout & session invalidation
├── products/
│   ├── index.php             # Product catalog grid & category filters
│   ├── view.php              # Product details & XSS-safe review listing
│   └── review.php            # Review submission processor
├── cart/
│   ├── index.php             # Shopping Cart view
│   ├── add.php               # Add to cart handler
│   ├── update.php            # Update item quantity
│   └── remove.php            # Remove item from cart
├── checkout/
│   ├── index.php             # Checkout form
│   └── place_order.php       # Atomic MySQL transaction order processor
├── orders/
│   ├── index.php             # My Orders list
│   └── view.php              # IDOR-protected Order receipt
├── admin/
│   ├── index.php             # Admin Dashboard overview
│   ├── products.php          # Product catalog management
│   ├── add_product.php       # Add product form
│   ├── edit_product.php      # Edit product form
│   ├── delete_product.php    # Delete product handler
│   ├── orders.php            # View customer orders
│   └── users.php             # View registered users & brute-force audit logs
├── errors/
│   ├── 400.php               # Bad Request
│   ├── 403.php               # Forbidden Access
│   ├── 404.php               # Page Not Found
│   └── 500.php               # Internal Server Error
├── assets/
│   ├── css/
│   │   └── style.css         # Custom Glassmorphism dark theme CSS
│   ├── js/
│   │   └── main.js           # Micro-interaction JavaScript
│   └── images/               # Product SVG icons
├── database/
│   ├── schema.sql            # Table definitions with foreign keys
│   └── seed.sql              # Pre-populated products & hashed demo accounts
├── index.php                 # Landing page with security highlights
├── config.example.php        # Configuration template
├── .gitignore                # Exclude secret config files
└── README.md                 # Complete documentation & viva defense guide
```

---

## 8. Summary of Web Application Security Principles Demonstrated

1. **Defense in Depth**: Security controls applied at HTTP header, application code, session engine, and database transaction layers.
2. **Principle of Least Privilege**: Customer accounts cannot modify administrative tables or view peer receipts.
3. **Never Trust Client Input**: All product prices, user IDs, and cart subtotals validated server-side.
4. **Fail Securely**: Unhandled errors present generic user messages without leaking SQL syntax or stack traces.
