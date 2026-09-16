<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

require_login();

// VULNERABLE: NO CSRF TOKEN CHECK
$productId = $_POST['product_id'] ?? 1;
$quantity = $_POST['quantity'] ?? 1;
$user = current_user();

$pdo = getDBConnection();
$pdo->query("INSERT INTO cart (user_id, product_id, quantity) VALUES ({$user['id']}, $productId, $quantity)");

set_flash('Added to cart without CSRF protection.', 'info');
header('Location: ' . APP_URL . '/cart/index.php');
exit();
