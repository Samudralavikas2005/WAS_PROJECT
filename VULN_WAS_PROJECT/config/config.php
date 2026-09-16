<?php
/**
 * Vulnerable SecureCart - Global Configuration File (PORT 8001)
 */

$scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1:8001';

define('APP_NAME', 'Vulnerable SecureCart (PORT 8001)');
define('APP_URL', $scheme . '://' . $host);
define('APP_ENV', 'vulnerable_demo');

// Database Configuration - Connects to vuln_securecart
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'vuln_securecart');
define('DB_USER', 'vikas');
define('DB_PASS', 'Vikas@2005');
define('DB_CHARSET', 'utf8mb4');
