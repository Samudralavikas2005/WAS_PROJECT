<?php
$pageTitle = 'Login — SecureCart';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

if (is_logged_in()) {
    header('Location: ' . APP_URL . '/index.php');
    exit();
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF Token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid or expired CSRF token. Please try again.';
    }

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $errors[] = 'Both email and password are required.';
    } else {
        // 2. Check Brute-Force Lockout Status
        $lockoutState = check_login_lockout($email);
        if ($lockoutState['locked']) {
            $errors[] = sprintf(
                'Too many failed attempts. Account is temporarily locked. Please try again in %d minute(s).',
                $lockoutState['remaining']
            );
        }
    }

    if (empty($errors)) {
        $pdo = getDBConnection();
        // 3. PDO Prepared Statement for SQL Injection immunity
        $stmt = $pdo->prepare("SELECT id, name, email, password_hash, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // 4. Verify password with password_verify()
        if ($user && password_verify($password, $user['password_hash'])) {
            // Success: Reset failed attempt count
            reset_failed_logins($email);

            // 5. Session Fixation Protection: Regenerate session ID
            regenerate_session_id();

            // Store user session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            set_flash('Welcome back, ' . e($user['name']) . '!', 'success');

            if ($user['role'] === 'admin') {
                header('Location: ' . APP_URL . '/admin/index.php');
            } else {
                header('Location: ' . APP_URL . '/index.php');
            }
            exit();
        } else {
            // Failed login: Record failure for rate limiting
            record_failed_login($email);

            // Generic error message to prevent User Enumeration vulnerabilities
            $errors[] = 'Invalid email or password.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center my-5">
    <div class="col-md-5">
        <div class="card card-glass p-4">
            <div class="text-center mb-4">
                <span class="security-hero-badge">🛡️ Brute-Force Protected</span>
                <h3 class="font-weight-bold text-white mb-1">Account Login</h3>
                <p class="text-muted small">Sign in to your SecureCart account</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-custom-danger mb-4">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $error): ?>
                            <li><?= e($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="<?= APP_URL ?>/auth/login.php" method="POST">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control form-control-custom" id="email" name="email" value="<?= e($email) ?>" required placeholder="user@example.com">
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control form-control-custom" id="password" name="password" required placeholder="Enter your password">
                </div>

                <button type="submit" class="btn btn-primary-custom w-100 mb-3">
                    Authenticate
                </button>

                <div class="text-center small text-muted">
                    Don't have an account? <a href="<?= APP_URL ?>/auth/register.php" class="text-info text-decoration-none font-weight-bold">Register here</a>
                </div>
            </form>

            <hr class="border-secondary my-4 opacity-25">

            <div class="bg-dark p-3 rounded border border-secondary text-muted small">
                <strong class="text-light d-block mb-1">🔑 Demonstration Credentials:</strong>
                <div>Customer: <code>user@securecart.com</code> / <code>User@123456</code></div>
                <div>Admin: <code>admin@securecart.com</code> / <code>Admin@123456</code></div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
