<?php
$pageTitle = 'Shopping Cart — SecureCart';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

require_login();

$user = current_user();
$pdo = getDBConnection();

$cartItems = $pdo->query("
    SELECT c.id as cart_id, c.quantity, p.id as product_id, p.name, p.price, p.stock, p.image_url 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = " . $user['id']
)->fetchAll();

$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="mb-4">
    <span class="security-badge mb-2">🛡️ Database Price Protection Active</span>
    <h2 class="text-white font-weight-bold">Your Shopping Cart</h2>
    <p class="text-muted small">Items stored in MySQL session context</p>
</div>

<?php if (empty($cartItems)): ?>
    <div class="card card-glass p-5 text-center my-4">
        <h4 class="text-muted mb-3">Your cart is currently empty.</h4>
        <div>
            <a href="<?= APP_URL ?>/products/index.php" class="btn btn-primary-custom px-4">Browse Products</a>
        </div>
    </div>
<?php else: ?>
    <div class="row gy-4 mb-5">
        <div class="col-lg-8">
            <div class="card card-glass p-4">
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Price (MySQL Verified)</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Subtotal</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cartItems as $item): 
                                $itemSubtotal = $item['price'] * $item['quantity'];
                            ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="<?= APP_URL ?>/<?= $item['image_url'] ?>" alt="" width="40" height="40" class="rounded bg-dark p-1">
                                            <div>
                                                <strong class="text-white d-block"><?= $item['name'] ?></strong>
                                                <small class="text-muted">ID: #<?= (int)$item['product_id'] ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center font-monospace">₹<?= number_format($item['price'], 2) ?></td>
                                    <td class="text-center">
                                        <input type="number" name="quantity" class="form-control form-control-custom form-control-sm text-center" value="<?= (int)$item['quantity'] ?>" min="1" style="width: 70px;">
                                    </td>
                                    <td class="text-end font-monospace text-info font-weight-bold">₹<?= number_format($itemSubtotal, 2) ?></td>
                                    <td class="text-end">
                                        <a href="<?= APP_URL ?>/cart/index.php" class="btn btn-sm btn-outline-danger">Remove</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-glass p-4">
                <h5 class="text-white font-weight-bold mb-3">Order Summary</h5>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Items Subtotal:</span>
                    <span class="text-white font-monospace">₹<?= number_format($subtotal, 2) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Shipping Fee:</span>
                    <span class="text-success font-monospace">FREE</span>
                </div>
                <hr class="border-secondary opacity-25 my-3">
                <div class="d-flex justify-content-between mb-4">
                    <strong class="text-white fs-5">Total Amount:</strong>
                    <strong class="text-info fs-5 font-monospace">₹<?= number_format($subtotal, 2) ?></strong>
                </div>

                <a href="<?= APP_URL ?>/checkout/index.php" class="btn btn-success-custom w-100 btn-lg fs-6">
                    Proceed to Checkout →
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
