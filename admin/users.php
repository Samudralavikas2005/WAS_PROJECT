<?php
$pageTitle = 'Users & Security Logs — Admin SecureCart';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

require_admin();

$pdo = getDBConnection();

// Fetch registered users
$usersStmt = $pdo->prepare("SELECT id, name, email, role, created_at FROM users ORDER BY id ASC");
$usersStmt->execute();
$users = $usersStmt->fetchAll();

// Fetch security login attempt logs
$logStmt = $pdo->prepare("SELECT id, email, failed_attempts, last_attempt, locked_until FROM login_attempts ORDER BY last_attempt DESC");
$logStmt->execute();
$logs = $logStmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="mb-4">
    <span class="security-hero-badge">🛡️ Security & Authentication Audit</span>
    <h2 class="text-white font-weight-bold mb-0">Registered Users & Lockout Logs</h2>
</div>

<!-- Registered Users Table -->
<div class="card card-glass p-4 mb-5">
    <h5 class="text-white font-weight-bold mb-3">1. Registered Accounts</h5>
    <div class="table-responsive">
        <table class="table table-custom mb-0">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Full Name</th>
                    <th>Email Address</th>
                    <th>Role</th>
                    <th>Account Created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td class="font-monospace text-muted">#<?= (int)$u['id'] ?></td>
                        <td><strong class="text-white"><?= e($u['name']) ?></strong></td>
                        <td class="font-monospace text-light"><?= e($u['email']) ?></td>
                        <td>
                            <span class="badge <?= $u['role'] === 'admin' ? 'bg-warning text-dark' : 'bg-info-subtle text-info border border-info' ?>">
                                <?= e(ucfirst($u['role'])) ?>
                            </span>
                        </td>
                        <td class="small text-muted"><?= e($u['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Login Attempts / Brute-Force Audit Log -->
<div class="card card-glass p-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="text-white font-weight-bold mb-0">2. Brute-Force Rate Limiting Audit Logs</h5>
        <span class="security-badge">Max 5 Attempts / 15 Min Lockout</span>
    </div>
    <div class="table-responsive">
        <table class="table table-custom mb-0">
            <thead>
                <tr>
                    <th>Email Targeted</th>
                    <th class="text-center">Failed Attempt Count</th>
                    <th>Last Attempt Timestamp</th>
                    <th>Lockout Expiry Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">No failed login records found. All accounts operating cleanly.</td></tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): 
                        $isLocked = !empty($log['locked_until']) && strtotime($log['locked_until']) > time();
                    ?>
                        <tr>
                            <td class="font-monospace text-light"><?= e($log['email']) ?></td>
                            <td class="text-center font-monospace">
                                <span class="badge <?= (int)$log['failed_attempts'] >= 5 ? 'bg-danger' : 'bg-secondary' ?>">
                                    <?= (int)$log['failed_attempts'] ?> / 5
                                </span>
                            </td>
                            <td class="small text-muted"><?= e($log['last_attempt']) ?></td>
                            <td>
                                <?php if ($isLocked): ?>
                                    <span class="badge bg-danger text-white border border-danger">
                                        🔒 Locked Until <?= e($log['locked_until']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-success-subtle text-success border border-success">
                                        ✓ Active / Unlocked
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
