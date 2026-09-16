<?php
$pageTitle = '400 Bad Request — SecureCart';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center my-5 py-5 text-center">
    <div class="col-md-6">
        <div class="card card-glass p-5">
            <h1 class="display-1 font-weight-extrabold text-warning mb-2">400</h1>
            <h3 class="text-white font-weight-bold mb-3">Bad Request</h3>
            <p class="text-muted mb-4">The server could not understand the request due to invalid syntax or corrupted parameters.</p>
            <div>
                <a href="<?= APP_URL ?>/index.php" class="btn btn-primary-custom px-4">Return to Homepage</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
