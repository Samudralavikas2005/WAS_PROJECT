<?php
/**
 * SecureCart - Security Utilities & Output Escaping
 */

require_once __DIR__ . '/session.php';

/**
 * Safely escapes output strings to prevent Cross-Site Scripting (XSS).
 */
function e(?string $value): string {
    if ($value === null) {
        return '';
    }
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Sets standard HTTP Security Headers across the application.
 */
function set_security_headers(): void {
    if (headers_sent()) {
        return;
    }

    // Prevent MIME-type sniffing
    header('X-Content-Type-Options: nosniff');

    // Prevent Clickjacking framing attacks
    header('X-Frame-Options: DENY');

    // Strict Referrer Policy
    header('Referrer-Policy: strict-origin-when-cross-origin');

    // Enable browser XSS filter
    header('X-XSS-Protection: 1; mode=block');

    // Content Security Policy
    $csp = "default-src 'self'; " .
           "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
           "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; " .
           "font-src 'self' https://fonts.gstatic.com; " .
           "img-src 'self' data:;";
    header('Content-Security-Policy: ' . $csp);
}

/**
 * Sets a flash notification message for the next request.
 */
function set_flash(?string $message, string $type = 'info'): void {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

/**
 * Retrieves and clears the active flash message.
 */
function get_flash(): ?array {
    if (isset($_SESSION['flash_message'])) {
        $flash = [
            'message' => $_SESSION['flash_message'],
            'type'    => $_SESSION['flash_type'] ?? 'info'
        ];
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return $flash;
    }
    return null;
}
