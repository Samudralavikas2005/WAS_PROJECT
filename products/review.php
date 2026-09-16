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

// 1. Verify CSRF Token
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash('Invalid or expired CSRF token.', 'danger');
    header('Location: ' . APP_URL . '/products/index.php');
    exit();
}

$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
$reviewText = trim($_POST['review_text'] ?? '');

if (!$productId || !$rating || $rating < 1 || $rating > 5 || empty($reviewText)) {
    set_flash('Please fill in all review fields correctly.', 'warning');
    header('Location: ' . APP_URL . '/products/view.php?id=' . (int)$productId);
    exit();
}

$user = current_user();
$pdo = getDBConnection();

// Prepared statement insert prevents SQL injection
$stmt = $pdo->prepare("INSERT INTO reviews (user_id, product_id, rating, review_text) VALUES (?, ?, ?, ?)");
if ($stmt->execute([$user['id'], $productId, $rating, $reviewText])) {
    set_flash('Thank you! Your review has been published securely.', 'success');
} else {
    set_flash('Failed to post review. Please try again.', 'danger');
}

header('Location: ' . APP_URL . '/products/view.php?id=' . (int)$productId);
exit();
