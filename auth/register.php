<?php
$pageTitle = 'Register — SecureCart';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

if (is_logged_in()) {
    header('Location: ' . APP_URL . '/index.php');
    exit();
}

$errors = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF Token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid or expired CSRF token. Please refresh and try again.';
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // 2. Server-side Input Validation
    if (empty($name)) {
        $errors[] = 'Full name is required.';
    } elseif (strlen($name) > 100) {
        $errors[] = 'Name cannot exceed 100 characters.';
    }

    if (empty($email)) {
        $errors[] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please provide a valid email address format.';
    }

    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter, one lowercase letter, and one digit.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Password confirmation does not match.';
    }

    // 3. Check for existing email in MySQL database using Prepared Statements
    if (empty($errors)) {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with this email address already exists.';
        }
    }

    // 4. Create new user if no validation errors
    if (empty($errors)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $role = 'customer'; // Default role

        $insertStmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
        if ($insertStmt->execute([$name, $email, $passwordHash, $role])) {
            set_flash('Registration successful! You may now log in with your credentials.', 'success');
            header('Location: ' . APP_URL . '/auth/login.php');
            exit();
        } else {
            $errors[] = 'An error occurred during account creation. Please try again.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center my-5">
    <div class="col-md-6 col-lg-5">
        <div class="card card-glass p-4">
            <div class="text-center mb-4">
                <span class="security-hero-badge">🔐 Secure Registration</span>
                <h3 class="font-weight-bold text-white mb-1">Create Account</h3>
                <p class="text-muted small">Join SecureCart with protected credentials</p>
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

            <form action="<?= APP_URL ?>/auth/register.php" method="POST" class="needs-validation">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" class="form-control form-control-custom" id="name" name="name" value="<?= e($name) ?>" required placeholder="e.g. John Doe">
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control form-control-custom" id="email" name="email" value="<?= e($email) ?>" required placeholder="user@example.com">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control form-control-custom" id="password" name="password" required placeholder="At least 8 chars (1 upper, 1 lower, 1 digit)">
                </div>

                <div class="mb-4">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control form-control-custom" id="confirm_password" name="confirm_password" required placeholder="Re-enter your password">
                </div>

                <button type="submit" class="btn btn-primary-custom w-100 mb-3">
                    Register Securely
                </button>

                <div class="text-center small text-muted">
                    Already have an account? <a href="<?= APP_URL ?>/auth/login.php" class="text-info text-decoration-none font-weight-bold">Log in here</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
