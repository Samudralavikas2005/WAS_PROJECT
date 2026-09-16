<?php
$pageTitle = 'Customer Orders — Admin SecureCart';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

require_admin();

$pdo = getDBConnection();
$stmt = $pdo->prepare("
    SELECT o.id, o.total_amount, o.shipping_name, o.phone, o.status, o.created_at, u.name as customer_name, u.email as customer_email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.id DESC
");
$stmt->execute();
$orders = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="text-white font-weight-bold mb-0">Customer Orders Overview</h2>
        <p class="text-muted small">All system transactions verified via MySQL</p>
    </div>
</div>

<div class="card card-glass p-4 mb-5">
    <div class="table-responsive">
        <table class="table table-custom mb-0">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Recipient / Phone</th>
                    <th>Total Amount</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No customer orders found in system.</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td><strong class="text-white">#<?= (int)$o['id'] ?></strong></td>
                            <td>
                                <strong class="text-white d-block"><?= e($o['customer_name']) ?></strong>
                                <small class="text-muted"><?= e($o['customer_email']) ?></small>
                            </td>
                            <td>
                                <span class="d-block text-light"><?= e($o['shipping_name']) ?></span>
                                <small class="text-muted"><?= e($o['phone']) ?></small>
                            </td>
                            <td class="font-monospace text-info font-weight-bold">₹<?= number_format($o['total_amount'], 2) ?></td>
                            <td class="small text-muted"><?= e($o['created_at']) ?></td>
                            <td>
                                <span class="badge bg-info-subtle text-info border border-info">
                                    <?= e($o['status']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="<?= APP_URL ?>/orders/view.php?id=<?= (int)$o['id'] ?>" class="btn btn-outline-light btn-sm">
                                    View Receipt →
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
