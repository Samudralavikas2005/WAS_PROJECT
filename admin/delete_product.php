<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../config/database.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_URL . '/admin/products.php');
    exit();
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash('Invalid or expired CSRF token.', 'danger');
    header('Location: ' . APP_URL . '/admin/products.php');
    exit();
}

$productId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if ($productId) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    set_flash('Product #' . $productId . ' deleted successfully.', 'info');
}

header('Location: ' . APP_URL . '/admin/products.php');
exit();
