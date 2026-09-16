<?php
$pageTitle = 'My Orders — SecureCart';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

require_login();

$user = current_user();
$pdo = getDBConnection();

// Fetch orders exclusively belonging to the currently logged in user
$stmt = $pdo->prepare("SELECT id, total_amount, status, created_at FROM orders WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <span class="security-badge mb-2">🛡️ IDOR Protected User Scope</span>
        <h2 class="text-white font-weight-bold mb-0">My Order History</h2>
    </div>
    <a href="<?= APP_URL ?>/products/index.php" class="btn btn-outline-info btn-sm">Shop More Products</a>
</div>

<?php if (empty($orders)): ?>
    <div class="card card-glass p-5 text-center my-4">
        <h4 class="text-muted mb-3">You haven't placed any orders yet.</h4>
        <div>
            <a href="<?= APP_URL ?>/products/index.php" class="btn btn-primary-custom px-4">Start Shopping</a>
        </div>
    </div>
<?php else: ?>
    <div class="card card-glass p-4 mb-5">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date Placed</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>
                                <strong class="text-white">#<?= (int)$order['id'] ?></strong>
                            </td>
                            <td class="small text-muted"><?= e($order['created_at']) ?></td>
                            <td class="font-monospace text-info font-weight-bold">₹<?= number_format($order['total_amount'], 2) ?></td>
                            <td>
                                <span class="badge bg-info-subtle text-info border border-info">
                                    <?= e($order['status']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="<?= APP_URL ?>/orders/view.php?id=<?= (int)$order['id'] ?>" class="btn btn-outline-light btn-sm">
                                    View Receipt →
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
