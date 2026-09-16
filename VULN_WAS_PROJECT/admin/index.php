<?php
$pageTitle = 'Admin Dashboard — SecureCart';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

// UNDERLYING VULNERABILITY: require_admin() does NOT block non-admin users!
require_admin();

$pdo = getDBConnection();

$productCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$orderCount = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue = $pdo->query("SELECT SUM(total_amount) FROM orders")->fetchColumn() ?? 0;
$userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$lockedCount = 0;

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <span class="security-hero-badge">🛡️ Role-Based Access Control Active</span>
        <h2 class="text-white font-weight-bold mb-0">Administrator Portal</h2>
    </div>
</div>

<!-- Admin Metrics Row -->
<div class="row gy-4 mb-5">
    <div class="col-md-3">
        <div class="card card-glass p-4 text-center">
            <span class="text-muted small mb-1">Total Products</span>
            <h3 class="text-info font-weight-bold mb-0"><?= (int)$productCount ?></h3>
            <a href="<?= APP_URL ?>/admin/products.php" class="small text-decoration-none text-muted mt-2 d-inline-block">Manage Products →</a>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-glass p-4 text-center">
            <span class="text-muted small mb-1">Total Orders</span>
            <h3 class="text-success font-weight-bold mb-0"><?= (int)$orderCount ?></h3>
            <a href="<?= APP_URL ?>/admin/orders.php" class="small text-decoration-none text-muted mt-2 d-inline-block">View Customer Orders →</a>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-glass p-4 text-center">
            <span class="text-muted small mb-1">Total Revenue</span>
            <h3 class="text-warning font-weight-bold mb-0 font-monospace">₹<?= number_format((float)$totalRevenue, 2) ?></h3>
            <span class="small text-muted mt-2 d-inline-block">Verified via DB</span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-glass p-4 text-center">
            <span class="text-muted small mb-1">Registered Users</span>
            <h3 class="text-purple font-weight-bold mb-0"><?= (int)$userCount ?></h3>
            <a href="<?= APP_URL ?>/admin/users.php" class="small text-decoration-none text-muted mt-2 d-inline-block">View Security Logs →</a>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row gy-4">
    <div class="col-md-6">
        <div class="card card-glass p-4 h-100">
            <h5 class="text-white font-weight-bold mb-3">Product Catalog Management</h5>
            <p class="text-muted small mb-4">Add, update stock quantities, or delete inventory items.</p>
            <div class="d-flex gap-2">
                <a href="<?= APP_URL ?>/admin/products.php" class="btn btn-outline-info btn-sm">View Product List</a>
                <a href="<?= APP_URL ?>/admin/add_product.php" class="btn btn-primary-custom btn-sm">+ Add New Product</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-glass p-4 h-100">
            <h5 class="text-white font-weight-bold mb-3">Brute Force & Security Auditing</h5>
            <p class="text-muted small mb-2">Monitor failed login attempts, locked IP/emails, and registered accounts.</p>
            <div class="mb-3">
                <span class="badge bg-success">
                    0 Accounts currently locked out
                </span>
            </div>
            <div>
                <a href="<?= APP_URL ?>/admin/users.php" class="btn btn-outline-light btn-sm">Inspect Security Logs</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
