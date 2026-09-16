<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';

// Unset all session variables
$_SESSION = array();

// Destroy session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy session on server
session_destroy();

// Start fresh session for flash message
session_start();
set_flash('You have been securely logged out.', 'info');

header('Location: ' . APP_URL . '/auth/login.php');
exit();
