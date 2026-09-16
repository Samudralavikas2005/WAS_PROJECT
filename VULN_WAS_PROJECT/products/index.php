<?php
$pageTitle = 'Product Catalog — SecureCart';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$category = trim($_GET['category'] ?? '');
$search = trim($_GET['search'] ?? '');

$pdo = getDBConnection();

$catStmt = $pdo->query("SELECT DISTINCT category FROM products ORDER BY category ASC");
$categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);

// UNDERLYING VULNERABILITY: String concatenation for search SQLi
$query = "SELECT id, name, description, price, stock, category, image_url FROM products WHERE 1=1";
if (!empty($category)) {
    $query .= " AND category = '$category'";
}
if (!empty($search)) {
    $query .= " AND (name LIKE '%$search%' OR description LIKE '%$search%')";
}
$query .= " ORDER BY id ASC";

try {
    $products = $pdo->query($query)->fetchAll();
} catch (Exception $e) {
    $products = [];
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h2 class="text-white font-weight-bold mb-1">Product Catalog</h2>
        <p class="text-muted small mb-0">Browse our inventory of secure hardware & privacy tools</p>
    </div>

    <!-- Search Form -->
    <form action="<?= APP_URL ?>/products/index.php" method="GET" class="d-flex gap-2">
        <?php if (!empty($category)): ?>
            <input type="hidden" name="category" value="<?= $category ?>">
        <?php endif; ?>
        <input type="text" name="search" class="form-control form-control-custom form-control-sm" placeholder="Search products..." value="<?= $search ?>">
        <button type="submit" class="btn btn-outline-info btn-sm">Search</button>
        <?php if (!empty($search) || !empty($category)): ?>
            <a href="<?= APP_URL ?>/products/index.php" class="btn btn-outline-secondary btn-sm">Clear</a>
        <?php endif; ?>
    </form>
</div>

<!-- Category Filter Pills -->
<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="<?= APP_URL ?>/products/index.php<?= !empty($search) ? '?search='.urlencode($search) : '' ?>" 
       class="btn btn-sm <?= empty($category) ? 'btn-info' : 'btn-outline-secondary' ?>">
        All Categories
    </a>
    <?php foreach ($categories as $cat): ?>
        <a href="<?= APP_URL ?>/products/index.php?category=<?= urlencode($cat) ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>" 
           class="btn btn-sm <?= $category === $cat ? 'btn-info' : 'btn-outline-secondary' ?>">
            <?= $cat ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- Products Grid -->
<div class="row gy-4 mb-5">
    <?php if (empty($products)): ?>
        <div class="col-12 text-center py-5 card card-glass">
            <h4 class="text-muted">No products found matching your search criteria.</h4>
            <a href="<?= APP_URL ?>/products/index.php" class="btn btn-outline-info btn-sm mt-3">Reset Filters</a>
        </div>
    <?php else: ?>
        <?php foreach ($products as $product): ?>
            <div class="col-md-4">
                <div class="card card-glass product-card">
                    <div class="product-img-wrapper">
                        <img src="<?= APP_URL ?>/<?= $product['image_url'] ?>" alt="<?= $product['name'] ?>" class="product-img">
                    </div>
                    <div class="card-body d-flex flex-column justify-content-between p-4">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-dark text-info border border-info"><?= $product['category'] ?></span>
                                <span class="badge <?= $product['stock'] > 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>">
                                    <?= $product['stock'] > 0 ? 'In Stock (' . (int)$product['stock'] . ')' : 'Out of Stock' ?>
                                </span>
                            </div>
                            <h5 class="card-title text-white font-weight-bold mb-2"><?= $product['name'] ?></h5>
                            <p class="card-text text-description mb-3"><?= mb_strimwidth($product['description'], 0, 95, '...') ?></p>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top border-secondary">
                            <span class="product-price">₹<?= number_format($product['price'], 2) ?></span>
                            <a href="<?= APP_URL ?>/products/view.php?id=<?= (int)$product['id'] ?>" class="btn btn-outline-light btn-sm">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
