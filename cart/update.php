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
    set_flash('Invalid or expired CSRF token.', 'danger');
    header('Location: ' . APP_URL . '/cart/index.php');
    exit();
}

$cartId = filter_input(INPUT_POST, 'cart_id', FILTER_VALIDATE_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

if (!$cartId || $quantity === false) {
    set_flash('Invalid cart request.', 'warning');
    header('Location: ' . APP_URL . '/cart/index.php');
    exit();
}

$user = current_user();
$pdo = getDBConnection();

if ($quantity <= 0) {
    // Delete item if quantity set to 0
    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cartId, $user['id']]);
    set_flash('Item removed from cart.', 'info');
} else {
    // Update quantity ensuring cart item belongs to logged in user
    $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$quantity, $cartId, $user['id']]);
    set_flash('Cart updated successfully.', 'success');
}

header('Location: ' . APP_URL . '/cart/index.php');
exit();
