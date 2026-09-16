<?php
$pageTitle = '404 Page Not Found — SecureCart';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center my-5 py-5 text-center">
    <div class="col-md-6">
        <div class="card card-glass p-5">
            <h1 class="display-1 font-weight-extrabold text-info mb-2">404</h1>
            <h3 class="text-white font-weight-bold mb-3">Page Not Found</h3>
            <p class="text-muted mb-4">The page or product record you were looking for does not exist or has been removed.</p>
            <div>
                <a href="<?= APP_URL ?>/products/index.php" class="btn btn-primary-custom px-4">Browse Products</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
