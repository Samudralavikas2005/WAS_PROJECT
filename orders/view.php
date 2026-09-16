<?php
$pageTitle = 'Order Receipt — SecureCart';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

require_login();

$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$orderId) {
    header('Location: ' . APP_URL . '/orders/index.php');
    exit();
}

$currentUser = current_user();
$pdo = getDBConnection();

// Fetch Order Information
$stmt = $pdo->prepare("SELECT id, user_id, total_amount, shipping_name, shipping_address, phone, status, created_at FROM orders WHERE id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    header('HTTP/1.1 404 Not Found');
    require_once __DIR__ . '/../errors/404.php';
    exit();
}

// =========================================================================
// CRITICAL IDOR (INSECURE DIRECT OBJECT REFERENCE) SECURITY CHECK
// Server-side ownership verification: User can ONLY view their OWN order
// =========================================================================
if ((int)$order['user_id'] !== (int)$currentUser['id'] && !is_admin()) {
    header('HTTP/1.1 403 Forbidden');
    require_once __DIR__ . '/../errors/403.php';
    exit();
}

// Fetch Order Items joined with products
$itemsStmt = $pdo->prepare("
    SELECT oi.quantity, oi.price, p.name, p.category, p.image_url 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$itemsStmt->execute([$orderId]);
$orderItems = $itemsStmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="mb-4">
    <a href="<?= APP_URL ?>/orders/index.php" class="text-muted text-decoration-none small">← Back to My Orders</a>
</div>

<div class="card card-glass p-4 mb-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2 border-bottom border-secondary pb-3">
        <div>
            <span class="security-badge mb-2">🛡️ Ownership Validated (IDOR Protected)</span>
            <h3 class="text-white font-weight-bold mb-1">Order Confirmation #<?= (int)$order['id'] ?></h3>
            <span class="text-muted small">Placed on: <?= e($order['created_at']) ?></span>
        </div>
        <div>
            <span class="badge bg-success-subtle text-success border border-success px-3 py-2 fs-6">
                Status: <?= e($order['status']) ?>
            </span>
        </div>
    </div>

    <div class="row gy-4 mb-4">
        <!-- Shipping Details -->
        <div class="col-md-6">
            <div class="bg-dark p-3 rounded border border-secondary h-100">
                <h6 class="text-white font-weight-bold mb-2">Shipping Information</h6>
                <div class="small text-muted mb-1"><strong class="text-light">Name:</strong> <?= e($order['shipping_name']) ?></div>
                <div class="small text-muted mb-1"><strong class="text-light">Phone:</strong> <?= e($order['phone']) ?></div>
                <div class="small text-muted"><strong class="text-light">Address:</strong> <?= e($order['shipping_address']) ?></div>
            </div>
        </div>

        <!-- Security Audit Badge -->
        <div class="col-md-6">
            <div class="bg-dark p-3 rounded border border-info h-100">
                <h6 class="text-info font-weight-bold mb-2">🛡️ Security Verification Log</h6>
                <p class="small text-muted mb-0">
                    Access to Order #<?= (int)$order['id'] ?> was granted after server-side session identity check <code>($_SESSION['user_id'] === order.user_id)</code>. Attempts to change the URL query parameter to view unauthorized orders are blocked with HTTP 403.
                </p>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <h5 class="text-white font-weight-bold mb-3">Purchased Items</h5>
    <div class="table-responsive">
        <table class="table table-custom mb-0">
            <thead>
                <tr>
                    <th>Product</th>
                    <th class="text-center">Category</th>
                    <th class="text-center">Price Paid</th>
                    <th class="text-center">Quantity</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orderItems as $item): 
                    $subtotal = $item['price'] * $item['quantity'];
                ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <img src="<?= APP_URL ?>/<?= e($item['image_url']) ?>" alt="" width="35" height="35" class="rounded bg-dark p-1">
                                <strong class="text-white"><?= e($item['name']) ?></strong>
                            </div>
                        </td>
                        <td class="text-center small"><span class="badge bg-dark text-info border border-info"><?= e($item['category']) ?></span></td>
                        <td class="text-center font-monospace">₹<?= number_format($item['price'], 2) ?></td>
                        <td class="text-center font-monospace"><?= (int)$item['quantity'] ?></td>
                        <td class="text-end font-monospace text-info font-weight-bold">₹<?= number_format($subtotal, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <hr class="border-secondary opacity-25 my-4">

    <div class="d-flex justify-content-between align-items-center fs-5">
        <strong class="text-white">Total Amount Paid:</strong>
        <strong class="text-success font-monospace fs-4">₹<?= number_format($order['total_amount'], 2) ?></strong>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
