<?php
$pageTitle = '403 Forbidden — SecureCart Security Defense';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center my-5 py-4 text-center">
    <div class="col-md-7">
        <div class="card card-glass p-5 border-danger">
            <span class="security-hero-badge bg-danger text-white border-danger mb-3">🛡️ Access Denied by Server Authorization Policy</span>
            <h1 class="display-1 font-weight-extrabold text-danger mb-2">403</h1>
            <h3 class="text-white font-weight-bold mb-3">Forbidden Access</h3>
            <p class="text-muted lead fs-6 mb-4">
                You do not have permission to access this resource or perform this action.
            </p>

            <div class="bg-dark p-3 rounded border border-secondary text-start small text-muted mb-4">
                <strong class="text-danger d-block mb-1">🔍 Why was this request blocked?</strong>
                <ul class="mb-0 ps-3">
                    <li><strong>IDOR Protection:</strong> You attempted to view an order record belonging to another registered user without explicit ownership.</li>
                    <li><strong>Role-Based Access Control (RBAC):</strong> You attempted to access an Administrator route (<code>/admin/*</code>) with a standard customer session.</li>
                </ul>
            </div>

            <div>
                <a href="<?= APP_URL ?>/index.php" class="btn btn-primary-custom px-4">Return to Homepage</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
