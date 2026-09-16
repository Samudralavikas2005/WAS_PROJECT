<?php
$pageTitle = 'SecureCart — Home & Security Highlights';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

$pdo = getDBConnection();
$stmt = $pdo->query("SELECT id, name, description, price, stock, category, image_url FROM products ORDER BY id ASC LIMIT 3");
$featuredProducts = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Banner -->
<div class="row align-items-center my-5 py-4">
    <div class="col-lg-7">
        <span class="security-hero-badge">🔒 Web Application Security Mini-Project</span>
        <h1 class="display-4 font-weight-extrabold text-white mb-3">
            Shop Safely.<br><span class="text-info">Checkout Securely.</span>
        </h1>
        <p class="lead text-description mb-4">
            SecureCart is a demonstration e-commerce web application engineered to protect against OWASP Top 10 vulnerabilities, including SQL Injection, XSS, CSRF, IDOR, Session Fixation, and Price Manipulation.
        </p>
        <div class="d-flex flex-wrap gap-3">
            <a href="<?= APP_URL ?>/products/index.php" class="btn btn-primary-custom btn-lg px-4">
                Browse Products →
            </a>
            <?php if (!is_logged_in()): ?>
                <a href="<?= APP_URL ?>/auth/register.php" class="btn btn-secondary-custom btn-lg px-4">
                    Create Account
                </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-5 mt-4 mt-lg-0 text-center">
        <div class="card card-glass p-4 border-info">
            <h5 class="text-white font-weight-bold mb-3">System Defense Status</h5>
            <div class="d-flex flex-column gap-3 text-start">
                <div class="p-3 bg-dark rounded d-flex align-items-center gap-3">
                    <span class="fs-3">🔐</span>
                    <div>
                        <strong class="text-white d-block">PDO Prepared Statements</strong>
                        <small class="text-muted">100% Parameterized queries across all DB layers</small>
                    </div>
                </div>
                <div class="p-3 bg-dark rounded d-flex align-items-center gap-3">
                    <span class="fs-3">🛡️</span>
                    <div>
                        <strong class="text-white d-block">CSRF & XSS Escaping</strong>
                        <small class="text-muted">Crypto-random tokens & output sanitization</small>
                    </div>
                </div>
                <div class="p-3 bg-dark rounded d-flex align-items-center gap-3">
                    <span class="fs-3">⚡</span>
                    <div>
                        <strong class="text-white d-block">Atomic Transactions</strong>
                        <small class="text-muted">Database price validation on checkout</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Security Highlights Grid -->
<div class="row my-5 gy-4">
    <div class="col-md-3">
        <div class="card card-glass p-4 text-center h-100">
            <div class="fs-1 mb-2">🔐</div>
            <h5 class="text-white font-weight-bold">Secure Login</h5>
            <p class="small text-muted mb-0">Bcrypt password hashes & database-backed brute force rate-limiting.</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-glass p-4 text-center h-100">
            <div class="fs-1 mb-2">🛡️</div>
            <h5 class="text-white font-weight-bold">Protected Checkout</h5>
            <p class="small text-muted mb-0">Atomic MySQL transactions preventing cart price tampering attacks.</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-glass p-4 text-center h-100">
            <div class="fs-1 mb-2">🔒</div>
            <h5 class="text-white font-weight-bold">Secure Sessions</h5>
            <p class="small text-muted mb-0">HttpOnly & SameSite flags with automatic session ID regeneration.</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-glass p-4 text-center h-100">
            <div class="fs-1 mb-2">✓</div>
            <h5 class="text-white font-weight-bold">IDOR Prevention</h5>
            <p class="small text-muted mb-0">Strict server-side authorization checks for user order receipts.</p>
        </div>
    </div>
</div>

<!-- Featured Products Section -->
<div class="my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="text-white font-weight-bold mb-1">Featured Security Products</h3>
            <p class="text-muted small mb-0">High-grade privacy hardware and encryption tools</p>
        </div>
        <a href="<?= APP_URL ?>/products/index.php" class="btn btn-outline-info btn-sm">View All Products →</a>
    </div>

    <div class="row gy-4">
        <?php foreach ($featuredProducts as $product): ?>
            <div class="col-md-4">
                <div class="card card-glass product-card">
                    <div class="product-img-wrapper">
                        <img src="<?= APP_URL ?>/<?= $product['image_url'] ?>" alt="<?= $product['name'] ?>" class="product-img">
                    </div>
                    <div class="card-body d-flex flex-column justify-content-between p-4">
                        <div>
                            <span class="badge bg-dark text-info border border-info mb-2"><?= $product['category'] ?></span>
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
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
