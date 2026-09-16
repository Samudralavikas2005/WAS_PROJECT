<?php
$pageTitle = 'Register — Vulnerable SecureCart';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if (is_logged_in()) {
    header('Location: ' . APP_URL . '/index.php');
    exit();
}

$errors = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $pdo = getDBConnection();
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->query("INSERT INTO users (name, email, password_hash, role) VALUES ('$name', '$email', '$passwordHash', 'customer')");
    if ($stmt) {
        set_flash('Registered successfully!', 'success');
        header('Location: ' . APP_URL . '/auth/login.php');
        exit();
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center my-5">
    <div class="col-md-5">
        <div class="card card-glass p-4">
            <h3 class="text-white font-weight-bold mb-3">Vulnerable Registration</h3>
            <form action="<?= APP_URL ?>/auth/register.php" method="POST">
                <div class="mb-3">
                    <label class="form-label text-light">Full Name</label>
                    <input type="text" name="name" class="form-control form-control-custom" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-light">Email Address</label>
                    <input type="email" name="email" class="form-control form-control-custom" required>
                </div>
                <div class="mb-4">
                    <label class="form-label text-light">Password</label>
                    <input type="password" name="password" class="form-control form-control-custom" required>
                </div>
                <button type="submit" class="btn btn-primary-custom w-100">Register</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
