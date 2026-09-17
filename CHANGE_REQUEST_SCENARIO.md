# Case Study: Sudden Client Requirement Change & Secure Feature Implementation

**Scenario Name**: Agile Client Requirement Change — Product Review & Customer Rating System  
**Objective**: Demonstrate how a sudden client feature request was received mid-development, analyzed for security threats and code smells, securely implemented with defensive controls, and validated via the automated CI/CD pipeline.

---

## 1. The Sudden Requirement Request

During Sprint 2, the product owner/client submitted an urgent feature change request:

> *"We want customers to be able to leave product reviews and 1-to-5 star ratings on any product page (`/products/view.php`), and display these reviews publicly under the product details."*

---

## 2. Risk & Code Smell Analysis

Before writing code, the team conducted a **Pre-Implementation Security & Quality Audit** to identify potential vulnerabilities and code smells that naive implementations introduce:

### Potential Security Threats Identified
1. **Stored Cross-Site Scripting (Stored XSS)**: An attacker could post malicious JavaScript in review comments (`<script>steal_cookies()</script>`), which would execute in the browsers of all users viewing that product page.
2. **Cross-Site Request Forgery (CSRF)**: An attacker could forge review submissions on behalf of authenticated users via malicious external links.
3. **Database Injection (SQLi)**: Manipulating review ratings or comments to execute arbitrary SQL commands.
4. **Unauthenticated / Spam Submissions**: Unauthenticated bots spamming review submissions.

### Code Smells Identified & Avoided
1. **Code Smell 1: Duplicated Database Connection Logic**: Creating a new `new PDO(...)` instance inside the review script instead of reusing the centralized PDO provider (`config/database.php`).
2. **Code Smell 2: Mixed Presentation and Business Logic**: Embedding SQL queries directly inside HTML template loops without separating data fetching from output rendering.
3. **Code Smell 3: Magic Numbers**: Hardcoding rating boundaries (`1` and `5`) as magic numbers throughout code instead of defining validated bounds.

---

## 3. Secure Refactored Implementation

The feature was implemented in `products/review.php` and `products/view.php` following strict defensive coding standards:

### 1. CSRF & Auth Middleware Check
```php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

require_login(); // Ensure user is authenticated

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? ''); // CSRF Defense
    ...
}
```

### 2. SQL Injection Prevention & Bound Inputs
```php
$rating = filter_var($_POST['rating'], FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 5]
]);
$comment = trim($_POST['comment'] ?? '');

if ($rating === false || empty($comment)) {
    die("Invalid input parameters.");
}

// Prepared SQL Statement
$stmt = $pdo->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (:pid, :uid, :rating, :comment)");
$stmt->execute([
    'pid' => $product_id,
    'uid' => $_SESSION['user_id'],
    'rating' => $rating,
    'comment' => $comment
]);
```

### 3. Stored XSS Mitigation (Output Escaping)
When displaying reviews in `products/view.php`:
```php
foreach ($reviews as $review): ?>
    <div class="review-box">
        <strong><?= sanitize($review['full_name']) ?></strong>
        <span>Rating: <?= (int)$review['rating'] ?>/5</span>
        <p><?= sanitize($review['comment']) ?></p>
    </div>
<?php endforeach; ?>
```
*(Where `sanitize()` executes `htmlspecialchars($str, ENT_QUOTES, 'UTF-8')`).*

---

## 4. Automated CI/CD Pipeline & Security Verification

After implementing the new review feature:

1. **Local Test Execution**:
   Running `php tests/run_tests.php` verified that all 57 project files passed syntax linting and security assertions without regression.
2. **CI/CD Pipeline Validation**:
   Executing `git push origin main` triggered GitHub Actions (`.github/workflows/ci-cd.yml`):
   - **Job 1 (Lint)**: PASSED 🟢
   - **Job 2 (MySQL DB Container Test)**: PASSED 🟢 (Seeded `reviews` schema and verified query execution).
   - **Job 3 (Security Audit)**: PASSED 🟢 (Zero hardcoded secrets or unescaped strings detected).
3. **Outcome**: The sudden client requirement was safely integrated, tested, and deployed to production without introducing security vulnerabilities or breaking existing functionality.
