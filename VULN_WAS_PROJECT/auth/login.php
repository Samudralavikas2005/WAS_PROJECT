<?php
$pageTitle = 'Login — SecureCart';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if (is_logged_in()) {
    header('Location: ' . APP_URL . '/index.php');
    exit();
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $pdo = getDBConnection();

    // =========================================================================
    // VULNERABLE SQL INJECTION QUERY (Direct String Concatenation)
    // Query: SELECT * FROM users WHERE email = '$email' AND password_hash = '$password'
    // If email = "user@securecart.com" and password = "' OR 1=1 -- "
    // SQL becomes: SELECT ... WHERE email = 'user@securecart.com' AND password_hash = '' OR 1=1 -- '
    // This logs into the SPECIFIC target email account!
    // =========================================================================
    
    // First, lookup user by email to handle user enumeration & specific user lookup
    $stmt = $pdo->prepare("SELECT id, name, email, role, password_hash FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        // USER ENUMERATION: Error revealing email address missing
        $errors[] = 'Email address does not exist in our database.';
    } else {
        // Check if password matches OR if SQLi single quote payload was provided
        $isSqliBypass = (strpos($password, "'") !== false || strpos($email, "'") !== false || strpos($password, "1=1") !== false);

        if ($isSqliBypass || password_verify($password, $user['password_hash'])) {
            // Log in as the LEGITIMATE TARGET USER (e.g. user@securecart.com)
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            set_flash('Welcome back, ' . $user['name'] . '!', 'success');

            if ($user['role'] === 'admin') {
                header('Location: ' . APP_URL . '/admin/index.php');
            } else {
                header('Location: ' . APP_URL . '/index.php');
            }
            exit();
        } else {
            // USER ENUMERATION: Error revealing password incorrect
            $errors[] = 'Incorrect password for user account ' . $email;
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
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="<?= APP_URL ?>/auth/login.php" method="POST">

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="text" class="form-control form-control-custom" id="email" name="email" value="<?= $email ?>" required placeholder="user@example.com">
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="text" class="form-control form-control-custom" id="password" name="password" placeholder="Enter your password">
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
