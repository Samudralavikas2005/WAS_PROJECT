<?php
/**
 * SecureCart - CSRF Protection Module
 * 
 * Provides cryptographically secure CSRF token generation and verification.
 */

require_once __DIR__ . '/session.php';

/**
 * Generates or retrieves the active CSRF token for the session.
 */
function get_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validates the submitted CSRF token using hash_equals to prevent timing attacks.
 */
function verify_csrf_token(?string $token): bool {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Renders an HTML hidden input tag containing the active CSRF token.
 */
function csrf_field(): string {
    $token = get_csrf_token();
    return sprintf('<input type="hidden" name="csrf_token" value="%s">', htmlspecialchars($token, ENT_QUOTES, 'UTF-8'));
}
