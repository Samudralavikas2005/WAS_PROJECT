<?php
/**
 * SecureCart - Configuration Template Example
 */

$scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1:8000';

define('APP_NAME', 'SecureCart');
define('APP_URL', $scheme . '://' . $host);
define('APP_ENV', 'development');

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'securecart');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('DB_CHARSET', 'utf8mb4');

define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME_SECONDS', 900);
define('SESSION_LIFETIME', 1800);
