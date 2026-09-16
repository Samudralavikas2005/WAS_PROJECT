<?php
/**
 * SecureCart - Authentication & Authorization Control Module
 * 
 * Manages user access, role checks, and brute-force lockout mechanisms.
 */

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../config/database.php';

/**
 * Checks if a user is currently authenticated.
 */
function is_logged_in(): bool {
    return !empty($_SESSION['user_id']);
}

/**
 * Checks if the authenticated user has the 'admin' role.
 */
function is_admin(): bool {
    return is_logged_in() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Enforces login authentication; redirects to login page if unauthorized.
 */
function require_login(): void {
    if (!is_logged_in()) {
        $_SESSION['flash_message'] = 'Please log in to access this page.';
        $_SESSION['flash_type'] = 'warning';
        header('Location: ' . APP_URL . '/auth/login.php');
        exit();
    }
}

/**
 * Enforces Admin role access; returns 403 Forbidden if user is a normal customer.
 */
function require_admin(): void {
    require_login();
    if (!is_admin()) {
        header('HTTP/1.1 403 Forbidden');
        require_once __DIR__ . '/../errors/403.php';
        exit();
    }
}

/**
 * Returns current user metadata array from session.
 */
function current_user(): ?array {
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['user_name'] ?? 'User',
        'email' => $_SESSION['user_email'] ?? '',
        'role'  => $_SESSION['role'] ?? 'customer'
    ];
}

/**
 * Checks whether an account email is currently locked due to brute-force attempts.
 */
function check_login_lockout(string $email): array {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT failed_attempts, last_attempt, locked_until FROM login_attempts WHERE email = ?");
    $stmt->execute([$email]);
    $record = $stmt->fetch();

    if (!$record) {
        return ['locked' => false, 'attempts' => 0];
    }

    if (!empty($record['locked_until'])) {
        $lockedUntil = strtotime($record['locked_until']);
        if ($lockedUntil > time()) {
            $remaining = $lockedUntil - time();
            return [
                'locked'    => true,
                'remaining' => ceil($remaining / 60) // Remaining time in minutes
            ];
        } else {
            // Lockout period has elapsed; reset tracking
            $resetStmt = $pdo->prepare("UPDATE login_attempts SET failed_attempts = 0, locked_until = NULL WHERE email = ?");
            $resetStmt->execute([$email]);
            return ['locked' => false, 'attempts' => 0];
        }
    }

    return ['locked' => false, 'attempts' => (int)$record['failed_attempts']];
}

/**
 * Records a failed login attempt for the given email and enforces temporary lockout if limit exceeded.
 */
function record_failed_login(string $email): void {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT failed_attempts FROM login_attempts WHERE email = ?");
    $stmt->execute([$email]);
    $record = $stmt->fetch();

    if ($record) {
        $attempts = (int)$record['failed_attempts'] + 1;
        if ($attempts >= MAX_LOGIN_ATTEMPTS) {
            $lockedUntil = date('Y-m-d H:i:s', time() + LOCKOUT_TIME_SECONDS);
            $update = $pdo->prepare("UPDATE login_attempts SET failed_attempts = ?, locked_until = ? WHERE email = ?");
            $update->execute([$attempts, $lockedUntil, $email]);
        } else {
            $update = $pdo->prepare("UPDATE login_attempts SET failed_attempts = ? WHERE email = ?");
            $update->execute([$attempts, $email]);
        }
    } else {
        $insert = $pdo->prepare("INSERT INTO login_attempts (email, failed_attempts) VALUES (?, 1)");
        $insert->execute([$email]);
    }
}

/**
 * Resets failed login attempt counters upon successful authentication.
 */
function reset_failed_logins(string $email): void {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE email = ?");
    $stmt->execute([$email]);
}

/**
 * Helper to fetch total items in the user's cart for the navigation badge.
 */
function get_cart_count(): int {
    if (!is_logged_in()) {
        return 0;
    }
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $res = $stmt->fetch();
    return (int)($res['total'] ?? 0);
}
