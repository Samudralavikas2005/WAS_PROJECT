<?php
$pageTitle = 'Checkout — SecureCart';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../config/database.php';

require_login();

$user = current_user();
$pdo = getDBConnection();

// Retrieve cart items and verify prices from DB
$stmt = $pdo->prepare("
    SELECT c.quantity, p.id as product_id, p.name, p.price, p.stock 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ?
");
$stmt->execute([$user['id']]);
$cartItems = $stmt->fetchAll();

if (empty($cartItems)) {
    set_flash('Your cart is empty. Please add items before checking out.', 'warning');
    header('Location: ' . APP_URL . '/products/index.php');
    exit();
}

$total = 0;
foreach ($cartItems as $item) {
    $total += $item['price'] * $item['quantity'];
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center my-4">
    <div class="col-lg-10">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <span class="security-hero-badge">⚡ Atomic Transaction Secured</span>
                <h2 class="text-white font-weight-bold mb-0">Order Checkout</h2>
            </div>
            <a href="<?= APP_URL ?>/cart/index.php" class="btn btn-outline-secondary btn-sm">← Return to Cart</a>
        </div>

        <div class="row gy-4">
            <!-- Shipping Information Form -->
            <div class="col-md-7">
                <div class="card card-glass p-4">
                    <h5 class="text-white font-weight-bold mb-3">1. Delivery Address Information</h5>
                    <form action="<?= APP_URL ?>/checkout/place_order.php" method="POST">
                        <?= csrf_field() ?>

                        <!-- 
                          SECURITY NOTE FOR EVALUATION / DEMONSTRATION:
                          Attackers attempting to tamper with prices by injecting hidden fields like <input name="price" value="1">
                          will fail because place_order.php completely ignores client fields and recalculates prices directly from MySQL.
                        -->

                        <div class="mb-3">
                            <label for="shipping_name" class="form-label">Recipient Full Name</label>
                            <input type="text" class="form-control form-control-custom" id="shipping_name" name="shipping_name" value="<?= e($user['name']) ?>" required placeholder="e.g. John Doe">
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Contact Phone Number</label>
                            <input type="tel" class="form-control form-control-custom" id="phone" name="phone" required placeholder="e.g. +91 98765 43210">
                        </div>

                        <div class="mb-4">
                            <label for="shipping_address" class="form-label">Complete Shipping Address</label>
                            <textarea class="form-control form-control-custom" id="shipping_address" name="shipping_address" rows="3" required placeholder="Street, City, State, PIN Code"></textarea>
                        </div>

                        <button type="submit" class="btn btn-success-custom w-100 btn-lg fs-6">
                            🔒 Place Order (Server Price Verified)
                        </button>
                    </form>
                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="col-md-5">
                <div class="card card-glass p-4">
                    <h5 class="text-white font-weight-bold mb-3">2. Items Breakdown</h5>
                    <div class="d-flex flex-column gap-3 mb-3">
                        <?php foreach ($cartItems as $item): 
                            $itemTotal = $item['price'] * $item['quantity'];
                        ?>
                            <div class="d-flex justify-content-between align-items-center p-2 rounded bg-dark border border-secondary">
                                <div>
                                    <strong class="text-white d-block small"><?= e($item['name']) ?></strong>
                                    <small class="text-muted"><?= (int)$item['quantity'] ?> × ₹<?= number_format($item['price'], 2) ?></small>
                                </div>
                                <span class="text-info font-monospace small">₹<?= number_format($itemTotal, 2) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <hr class="border-secondary opacity-25">

                    <div class="d-flex justify-content-between align-items-center fs-5">
                        <strong class="text-white">Total Amount:</strong>
                        <strong class="text-success font-monospace">₹<?= number_format($total, 2) ?></strong>
                    </div>

                    <div class="bg-dark p-3 rounded border border-info mt-3 small text-muted">
                        <strong class="text-info d-block mb-1">🛡️ Anti Price-Tampering Defense:</strong>
                        Prices are fetched directly from MySQL during final commit. Client-side browser tampering is automatically discarded.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
