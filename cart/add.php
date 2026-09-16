<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../config/database.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_URL . '/products/index.php');
    exit();
}

// 1. CSRF Verification
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash('Invalid or expired CSRF token.', 'danger');
    header('Location: ' . APP_URL . '/products/index.php');
    exit();
}

$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

if (!$productId || !$quantity || $quantity < 1) {
    set_flash('Invalid product or quantity specified.', 'warning');
    header('Location: ' . APP_URL . '/products/index.php');
    exit();
}

$user = current_user();
$pdo = getDBConnection();

// 2. Fetch actual product stock from MySQL
$stmt = $pdo->prepare("SELECT id, name, stock FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    set_flash('Product not found.', 'danger');
    header('Location: ' . APP_URL . '/products/index.php');
    exit();
}

if ($quantity > $product['stock']) {
    set_flash('Requested quantity exceeds available stock (' . (int)$product['stock'] . ' available).', 'warning');
    header('Location: ' . APP_URL . '/products/view.php?id=' . (int)$productId);
    exit();
}

// 3. Add to Cart via Prepared Statement
$cartStmt = $pdo->prepare("
    INSERT INTO cart (user_id, product_id, quantity) 
    VALUES (?, ?, ?) 
    ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)
");
if ($cartStmt->execute([$user['id'], $productId, $quantity])) {
    set_flash('Added ' . (int)$quantity . ' × "' . e($product['name']) . '" to your cart!', 'success');
} else {
    set_flash('Failed to update cart. Please try again.', 'danger');
}

header('Location: ' . APP_URL . '/cart/index.php');
exit();
