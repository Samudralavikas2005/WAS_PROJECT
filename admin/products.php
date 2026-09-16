<?php
$pageTitle = 'Manage Products — Admin SecureCart';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../config/database.php';

require_admin();

$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT id, name, category, price, stock, created_at FROM products ORDER BY id DESC");
$stmt->execute();
$products = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="text-white font-weight-bold mb-0">Product Inventory</h2>
        <p class="text-muted small">Manage items in the MySQL catalog</p>
    </div>
    <a href="<?= APP_URL ?>/admin/add_product.php" class="btn btn-primary-custom btn-sm">+ Add New Product</a>
</div>

<div class="card card-glass p-4 mb-5">
    <div class="table-responsive">
        <table class="table table-custom mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th class="text-center">Price</th>
                    <th class="text-center">Stock</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td class="font-monospace text-muted">#<?= (int)$p['id'] ?></td>
                        <td><strong class="text-white"><?= e($p['name']) ?></strong></td>
                        <td><span class="badge bg-dark text-info border border-info"><?= e($p['category']) ?></span></td>
                        <td class="text-center font-monospace text-info">₹<?= number_format($p['price'], 2) ?></td>
                        <td class="text-center">
                            <span class="badge <?= $p['stock'] > 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>">
                                <?= (int)$p['stock'] ?> units
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="<?= APP_URL ?>/admin/edit_product.php?id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-outline-warning me-2">Edit</a>
                            
                            <form action="<?= APP_URL ?>/admin/delete_product.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
