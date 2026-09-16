<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../config/database.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_URL . '/checkout/index.php');
    exit();
}

// 1. Verify CSRF Token
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash('Invalid or expired CSRF token.', 'danger');
    header('Location: ' . APP_URL . '/checkout/index.php');
    exit();
}

$shippingName = trim($_POST['shipping_name'] ?? '');
$shippingAddress = trim($_POST['shipping_address'] ?? '');
$phone = trim($_POST['phone'] ?? '');

if (empty($shippingName) || empty($shippingAddress) || empty($phone)) {
    set_flash('All shipping details (Name, Address, Phone) are required.', 'warning');
    header('Location: ' . APP_URL . '/checkout/index.php');
    exit();
}

$user = current_user();
$pdo = getDBConnection();

try {
    // 2. BEGIN ATOMIC MYSQL TRANSACTION
    $pdo->beginTransaction();

    // 3. Retrieve user's cart items and lock product rows for update to prevent race conditions
    $stmt = $pdo->prepare("
        SELECT c.product_id, c.quantity, p.name, p.price, p.stock 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ? 
        FOR UPDATE
    ");
    $stmt->execute([$user['id']]);
    $cartItems = $stmt->fetchAll();

    if (empty($cartItems)) {
        throw new Exception('Your shopping cart is empty.');
    }

    $totalAmount = 0.00;

    // 4. Validate stock & calculate actual price on server (PRICE MANIPULATION DEFENSE)
    foreach ($cartItems as $item) {
        if ($item['stock'] < $item['quantity']) {
            throw new Exception(sprintf(
                'Insufficient stock for "%s". Requested: %d, Available: %d',
                $item['name'],
                $item['quantity'],
                $item['stock']
            ));
        }

        // Price MUST come from database row ($item['price']), never from $_POST
        $totalAmount += (float)$item['price'] * (int)$item['quantity'];
    }

    // 5. Create Order record in `orders`
    $orderStmt = $pdo->prepare("
        INSERT INTO orders (user_id, total_amount, shipping_name, shipping_address, phone, status) 
        VALUES (?, ?, ?, ?, ?, 'Processing')
    ");
    $orderStmt->execute([
        $user['id'],
        $totalAmount,
        $shippingName,
        $shippingAddress,
        $phone
    ]);
    $orderId = $pdo->lastInsertId();

    // 6. Create Order Items and update stock for each product
    $itemStmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price) 
        VALUES (?, ?, ?, ?)
    ");
    $stockStmt = $pdo->prepare("
        UPDATE products 
        SET stock = stock - ? 
        WHERE id = ?
    ");

    foreach ($cartItems as $item) {
        $itemStmt->execute([
            $orderId,
            $item['product_id'],
            $item['quantity'],
            $item['price']
        ]);

        $stockStmt->execute([
            $item['quantity'],
            $item['product_id']
        ]);
    }

    // 7. Clear user's cart
    $clearCartStmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $clearCartStmt->execute([$user['id']]);

    // 8. COMMIT TRANSACTION
    $pdo->commit();

    set_flash('Order #' . $orderId . ' placed successfully! Thank you for shopping safely with SecureCart.', 'success');
    header('Location: ' . APP_URL . '/orders/view.php?id=' . (int)$orderId);
    exit();

} catch (Exception $e) {
    // ROLLBACK TRANSACTION ON FAILURE
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    set_flash('Order Processing Failed: ' . $e->getMessage(), 'danger');
    header('Location: ' . APP_URL . '/checkout/index.php');
    exit();
}
