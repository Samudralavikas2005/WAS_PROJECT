    </div>
</main>

<!-- Footer -->
<footer>
    <div class="container">
        <div class="row gy-4">
            <div class="col-md-5">
                <h5 class="text-white font-weight-bold mb-3 d-flex align-items-center gap-2">
                    🔒 SecureCart Platform
                </h5>
                <p class="small text-muted mb-3">
                    A Web Application Security mini-project demonstrating secure PHP & MySQL development patterns. Protection mechanisms against SQLi, XSS, CSRF, IDOR, Session Fixation, and Price Tampering.
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <span class="security-badge">PDO Prepared Statements</span>
                    <span class="security-badge">XSS Escaped</span>
                    <span class="security-badge">CSRF Tokens</span>
                    <span class="security-badge">HttpOnly Sessions</span>
                    <span class="security-badge">Atomic Transactions</span>
                </div>
            </div>
            <div class="col-md-3 ms-auto">
                <h6 class="text-white font-weight-bold mb-3">Quick Navigation</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?= APP_URL ?>/index.php" class="text-muted text-decoration-none">Home</a></li>
                    <li class="mb-2"><a href="<?= APP_URL ?>/products/index.php" class="text-muted text-decoration-none">Browse Catalog</a></li>
                    <li class="mb-2"><a href="<?= APP_URL ?>/cart/index.php" class="text-muted text-decoration-none">View Cart</a></li>
                    <li class="mb-2"><a href="<?= APP_URL ?>/orders/index.php" class="text-muted text-decoration-none">Order Receipts</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h6 class="text-white font-weight-bold mb-3">Security Auditing</h6>
                <p class="small text-muted mb-2">
                    Registered users and administrators are subjected to strict role-based access control (RBAC).
                </p>
                <span class="badge bg-dark text-info border border-info">PHP 8.3 & MySQL 8.0 Environment</span>
            </div>
        </div>
        <hr class="my-4 border-secondary opacity-25">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center small">
            <div>&copy; <?= date('Y') ?> SecureCart. Designed for Web Application Security Mini-Project.</div>
            <div class="mt-2 mt-sm-0">Built with PHP, MySQL & PDO.</div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JavaScript -->
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body>
</html>
