<?php
/**
 * Vulnerable Security Utilities (VULN_WAS_PROJECT)
 */

require_once __DIR__ . '/session.php';

if (!function_exists('e')) {
    function e(?string $value): string {
        return $value ?? '';
    }
}

if (!function_exists('set_security_headers')) {
    function set_security_headers(): void {
        // Insecure - No security headers set
    }
}

if (!function_exists('set_flash')) {
    function set_flash(?string $message, string $type = 'info'): void {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
}

if (!function_exists('get_flash')) {
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
}
