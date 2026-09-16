<?php
$pageTitle = '500 Internal Error — SecureCart';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center my-5 py-5 text-center">
    <div class="col-md-6">
        <div class="card card-glass p-5">
            <h1 class="display-1 font-weight-extrabold text-danger mb-2">500</h1>
            <h3 class="text-white font-weight-bold mb-3">Internal Server Error</h3>
            <p class="text-muted mb-3">An unexpected server error occurred. For security reasons, sensitive stack traces and database queries are masked.</p>
            <div class="bg-dark p-3 rounded border border-secondary text-start small text-muted mb-4">
                <strong>🛡️ Secure Error Handling:</strong> Error logs have been saved securely on the server host. Sensitive internal parameters are never leaked to client browsers.
            </div>
            <div>
                <a href="<?= APP_URL ?>/index.php" class="btn btn-primary-custom px-4">Return to Homepage</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
