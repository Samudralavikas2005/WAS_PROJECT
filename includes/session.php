<?php
/**
 * SecureCart - Secure Session Handler
 * 
 * Implements session hijacking, fixation, and timeout protections.
 */

require_once __DIR__ . '/../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    // Configure secure session cookie defaults before starting session
    $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');
    
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isSecure,
        'httponly' => true,      // Prevents JavaScript document.cookie theft (XSS Protection)
        'samesite' => 'Lax'      // Mitigates Cross-Site Request Forgery
    ]);

    session_start();
}

// Inactivity Timeout Enforcement
if (isset($_SESSION['last_activity'])) {
    $lifetime = defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 1800;
    if (time() - $_SESSION['last_activity'] > $lifetime) {
        // Session expired
        session_unset();
        session_destroy();
        session_start();
        $_SESSION['flash_message'] = 'Your session has expired due to inactivity. Please log in again.';
        $_SESSION['flash_type'] = 'warning';
    }
}
$_SESSION['last_activity'] = time();

/**
 * Regenerates the session ID to prevent Session Fixation attacks.
 * Should be called upon successful authentication.
 */
function regenerate_session_id(): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}
