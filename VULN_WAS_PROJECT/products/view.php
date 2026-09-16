<?php
$pageTitle = 'Product Details — SecureCart';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$productId = $_GET['id'] ?? 1;
$pdo = getDBConnection();

$product = $pdo->query("SELECT * FROM products WHERE id = " . (int)$productId)->fetch();

if (!$product) {
    die("Product not found");
}

$reviews = $pdo->query("SELECT r.rating, r.review_text, r.created_at, u.name as reviewer_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = " . (int)$productId . " ORDER BY r.id DESC")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="mb-4">
    <a href="<?= APP_URL ?>/products/index.php" class="text-muted text-decoration-none small">← Back to Catalog</a>
</div>

<div class="row gy-4 mb-5">
    <!-- Product Image & Basic Info -->
    <div class="col-md-5">
        <div class="card card-glass p-5 text-center">
            <img src="<?= APP_URL ?>/<?= $product['image_url'] ?>" alt="<?= $product['name'] ?>" class="img-fluid style-product-img mb-3" style="max-height: 250px;">
        </div>
    </div>

    <!-- Product Purchase Box -->
    <div class="col-md-7">
        <div class="card card-glass p-4 h-100 d-flex flex-column justify-content-between">
            <div>
                <span class="badge bg-dark text-info border border-info mb-2"><?= $product['category'] ?></span>
                <h2 class="text-white font-weight-bold mb-3"><?= $product['name'] ?></h2>
                <div class="product-price mb-3">₹<?= number_format($product['price'], 2) ?></div>
                <p class="text-muted lead fs-6 mb-4"><?= $product['description'] ?></p>

                <div class="mb-4">
                    <span class="text-muted d-block small mb-1">Stock Availability:</span>
                    <?php if ($product['stock'] > 0): ?>
                        <span class="badge bg-success-subtle text-success border border-success px-3 py-2">
                            ✓ In Stock (<?= (int)$product['stock'] ?> units available)
                        </span>
                    <?php else: ?>
                        <span class="badge bg-danger-subtle text-danger border border-danger px-3 py-2">
                            ❌ Out of Stock
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($product['stock'] > 0): ?>
                <form action="<?= APP_URL ?>/cart/add.php" method="POST" class="mt-4 pt-3 border-top border-secondary">
                    <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">

                    <div class="row align-items-center gy-3">
                        <div class="col-auto">
                            <label for="quantity" class="form-label mb-0 me-2">Quantity:</label>
                        </div>
                        <div class="col-auto">
                            <input type="number" id="quantity" name="quantity" class="form-control form-control-custom form-control-sm text-center" value="1" min="1" max="<?= (int)$product['stock'] ?>" style="width: 90px;" required>
                        </div>
                        <div class="col">
                            <?php if (is_logged_in()): ?>
                                <button type="submit" class="btn btn-primary-custom w-100">
                                    🛒 Add to Cart
                                </button>
                            <?php else: ?>
                                <a href="<?= APP_URL ?>/auth/login.php" class="btn btn-outline-warning w-100">
                                    Log in to Purchase
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Customer Reviews Section (XSS UNPROTECTED IN VULNERABLE SITE) -->
<div class="card card-glass p-4 my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <span class="security-badge mb-2">🛡️ Output Escaped (XSS Safe)</span>
            <h4 class="text-white font-weight-bold mb-0">Customer Reviews & Ratings</h4>
        </div>
    </div>

    <!-- Review Submission Form for Logged-In Users -->
    <?php if (is_logged_in()): ?>
        <div class="bg-dark p-4 rounded border border-secondary mb-4">
            <h6 class="text-white font-weight-bold mb-3">Write a Review</h6>
            <form action="<?= APP_URL ?>/products/review.php" method="POST">
                <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">

                <div class="mb-3">
                    <label for="rating" class="form-label">Rating</label>
                    <select name="rating" id="rating" class="form-select form-select-custom form-select-sm" style="max-width: 200px;" required>
                        <option value="5">⭐⭐⭐⭐⭐ (5/5)</option>
                        <option value="4">⭐⭐⭐⭐ (4/5)</option>
                        <option value="3">⭐⭐⭐ (3/5)</option>
                        <option value="2">⭐⭐ (2/5)</option>
                        <option value="1">⭐ (1/5)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="review_text" class="form-label">Your Review (Supports XSS testing payloads)</label>
                    <textarea name="review_text" id="review_text" rows="3" class="form-control form-control-custom" placeholder="Share your experience... (e.g. try submitting &lt;script&gt;alert('XSS')&lt;/script&gt;)" required></textarea>
                </div>

                <button type="submit" class="btn btn-sm btn-info">Submit Review</button>
            </form>
        </div>
    <?php endif; ?>

    <!-- Display Reviews list -->
    <?php if (empty($reviews)): ?>
        <p class="text-muted mb-0">No reviews yet for this product.</p>
    <?php else: ?>
        <div class="d-flex flex-column gap-3">
            <?php foreach ($reviews as $rev): ?>
                <div class="p-3 rounded bg-dark border border-secondary">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong class="text-white"><?= $rev['reviewer_name'] ?></strong>
                        <span class="text-warning small"><?= str_repeat('⭐', (int)$rev['rating']) ?></span>
                    </div>
                    <!-- UNDERLYING VULNERABILITY: Raw unescaped output causes XSS alert to execute! -->
                    <p class="text-light mb-1 small"><?= $rev['review_text'] ?></p>
                    <small class="text-muted font-monospace" style="font-size: 0.75rem;"><?= $rev['created_at'] ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
