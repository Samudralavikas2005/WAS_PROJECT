<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../config/database.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_URL . '/cart/index.php');
    exit();
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash('Invalid CSRF token.', 'danger');
    header('Location: ' . APP_URL . '/cart/index.php');
    exit();
}

$cartId = filter_input(INPUT_POST, 'cart_id', FILTER_VALIDATE_INT);
if (!$cartId) {
    header('Location: ' . APP_URL . '/cart/index.php');
    exit();
}

$user = current_user();
$pdo = getDBConnection();

$stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
$stmt->execute([$cartId, $user['id']]);

set_flash('Item removed from cart.', 'info');
header('Location: ' . APP_URL . '/cart/index.php');
exit();
