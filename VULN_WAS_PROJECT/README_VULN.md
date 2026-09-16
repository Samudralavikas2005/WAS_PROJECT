# Vulnerable SecureCart — Comparative Demonstration Web Application (PORT 8001)

This project folder (`/home/vikas/WAS_PROJECT/VULN_WAS_PROJECT/`) contains an intentionally vulnerable version of **SecureCart** running on **Port 8001**. 

It is designed to be run alongside the secure version on **Port 8000** to provide a direct **"Before vs After"** comparative demonstration for your Web Application Security viva presentation.

---

## 1. Quick Launch Commands

### Database Setup
```bash
mysql -u vikas -pVikas@2005 < VULN_WAS_PROJECT/database/vuln_schema.sql
mysql -u vikas -pVikas@2005 < VULN_WAS_PROJECT/database/vuln_seed.sql
```

### Launch Servers Side-by-Side

1. **Start Vulnerable Server (Port 8001)**:
   ```bash
   php -S 127.0.0.1:8001 -t /home/vikas/WAS_PROJECT/VULN_WAS_PROJECT
   ```
2. **Start Secure Server (Port 8000)**:
   ```bash
   php -S 127.0.0.1:8000 -t /home/vikas/WAS_PROJECT
   ```

---

## 2. Side-by-Side Viva Demonstration Matrix

| Vulnerability Category | Vulnerable Server (Port 8001) | Secure Server (Port 8000) | Comparative Presentation Test |
| :--- | :--- | :--- | :--- |
| **SQL Injection (SQLi)** | Concatenated query: `SELECT * FROM users WHERE email = '$email'` | PDO Parameterized query (`SELECT ... WHERE email = ?`) | Enter `' OR '1'='1` on login. On **8001**: Auth bypassed. On **8000**: Rejected with generic error. |
| **Cross-Site Scripting (XSS)** | Raw unescaped output: `echo $review['review_text']` | Escaped output using `htmlspecialchars()` | Submit `<script>alert('XSS')</script>` in reviews. On **8001**: JavaScript alert box triggers. On **8000**: Safe text. |
| **CSRF Protection** | No CSRF tokens generated or validated | Cryptographic 256-bit CSRF token on all POSTs | Send POST without token. On **8001**: Request processed. On **8000**: Blocked. |
| **IDOR Protection** | Query `SELECT * FROM orders WHERE id = $_GET['id']` without check | Server ownership check `$order['user_id'] === $_SESSION['user_id']` | Visit `/orders/view.php?id=1001` as User 2. On **8001**: Displays User 1's order. On **8000**: HTTP 403 Forbidden. |
| **Price Tampering** | Form contains `<input type="hidden" name="total_amount">` | Total calculated strictly from MySQL DB | Change `total_amount=1.00` in Burp. On **8001**: Item bought for ₹1. On **8000**: Charged full price ₹2499. |
| **Brute-Force Protection** | Infinite login attempts allowed | `login_attempts` table locks account after 5 tries | Submit 5 wrong passwords. On **8001**: No lockout. On **8000**: 15-minute temporary lockout. |
| **Broken Access Control (RBAC)** | Unprotected `/admin/*` routes accessible to any user | Enforces `require_admin()` server-side | Visit `/admin/index.php` as customer. On **8001**: Admin dashboard opens. On **8000**: HTTP 403 Forbidden. |
| **User Enumeration** | Error *"Email address does not exist in database"* | Uniform generic error *"Invalid email or password"* | Try non-existent email. On **8001**: Reveals email missing. On **8000**: Generic error. |
| **Session Fixation / Cookies** | Session ID retained post-login; `HttpOnly` flag off | `session_regenerate_id(true)` & `HttpOnly` cookie | Check `document.cookie` in JS console. On **8001**: `PHPSESSID` visible to JS. On **8000**: Hidden from JS. |

---

## 3. Sample Accounts for Testing

| Role | Email Address | Password |
| :--- | :--- | :--- |
| **Customer** | `user@securecart.com` | `User@123456` |
| **Admin** | `admin@securecart.com` | `Admin@123456` |
