<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/../config/database.php';

if (!function_exists('is_logged_in')) {
    function is_logged_in(): bool {
        return !empty($_SESSION['user_id']);
    }
}

if (!function_exists('is_admin')) {
    function is_admin(): bool {
        return is_logged_in() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
}

if (!function_exists('require_login')) {
    function require_login(): void {
        if (!is_logged_in()) {
            header('Location: ' . APP_URL . '/auth/login.php');
            exit();
        }
    }
}

// ININTENTIONALLY VULNERABLE: Does NOT enforce admin role server-side! Allows any user to access admin.
if (!function_exists('require_admin')) {
    function require_admin(): void {
        require_login();
        // No role check! Non-admin users are granted access.
    }
}

if (!function_exists('current_user')) {
    function current_user(): ?array {
        if (!is_logged_in()) return null;
        return [
            'id'    => $_SESSION['user_id'],
            'name'  => $_SESSION['user_name'] ?? 'User',
            'email' => $_SESSION['user_email'] ?? '',
            'role'  => $_SESSION['role'] ?? 'customer'
        ];
    }
}

if (!function_exists('get_cart_count')) {
    function get_cart_count(): int {
        if (!is_logged_in()) return 0;
        $pdo = getDBConnection();
        $res = $pdo->query("SELECT SUM(quantity) as total FROM cart WHERE user_id = " . (int)$_SESSION['user_id'])->fetch();
        return (int)($res['total'] ?? 0);
    }
}
