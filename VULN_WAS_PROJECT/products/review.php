<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

require_login();

// VULNERABLE: No CSRF Token verification!
$productId = $_POST['product_id'] ?? 1;
$rating = $_POST['rating'] ?? 5;
$reviewText = $_POST['review_text'] ?? '';

$user = current_user();
$pdo = getDBConnection();

// Store raw text in database so payloads with single quotes don't break SQL syntax
$stmt = $pdo->prepare("INSERT INTO reviews (user_id, product_id, rating, review_text) VALUES (?, ?, ?, ?)");
$stmt->execute([$user['id'], $productId, $rating, $reviewText]);

set_flash('Review submitted!', 'info');
header('Location: ' . APP_URL . '/products/view.php?id=' . $productId);
exit();
