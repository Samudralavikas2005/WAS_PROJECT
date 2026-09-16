<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

require_login();

$shippingName = $_POST['shipping_name'] ?? 'User';
$shippingAddress = $_POST['shipping_address'] ?? 'Address';
$phone = $_POST['phone'] ?? '123456';

// =========================================================================
// CRITICAL VULNERABILITY: PRICE MANIPULATION / PARAMETER TAMPERING
// Directly accepts $totalAmount from $_POST['total_amount'] sent by browser!
// If an attacker changes total_amount=1.00 in Burp Suite, the order is created for ₹1!
// =========================================================================
$totalAmount = $_POST['total_amount'] ?? 0.00;

$user = current_user();
$pdo = getDBConnection();

$pdo->query("
    INSERT INTO orders (user_id, total_amount, shipping_name, shipping_address, phone, status) 
    VALUES ({$user['id']}, $totalAmount, '$shippingName', '$shippingAddress', '$phone', 'Processing')
");
$orderId = $pdo->lastInsertId();

// Clear cart
$pdo->query("DELETE FROM cart WHERE user_id = " . $user['id']);

set_flash("Order #$orderId placed using client total ₹" . number_format($totalAmount, 2) . "!", "warning");
header("Location: " . APP_URL . "/orders/view.php?id=" . $orderId);
exit();
