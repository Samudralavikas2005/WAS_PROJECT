<?php
/**
 * Vulnerable Session Handler (Insecure - No HttpOnly, No Fixation Protection)
 */
require_once __DIR__ . '/../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '0');
    session_start();
}

if (!function_exists('e')) {
    function e(?string $str): string {
        return $str ?? '';
    }
}
