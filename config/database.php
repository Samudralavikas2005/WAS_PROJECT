<?php
/**
 * SecureCart - Database Connection Module
 * 
 * Configures a PDO connection using strict security options.
 * Disables emulation of prepared statements to force true MySQL engine preparation.
 */

require_once __DIR__ . '/config.php';

function getDBConnection(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // Crucial for true SQLi protection
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log error internally in production; never expose DB credentials/traces to client
            error_log('Database Connection Error: ' . $e->getMessage());
            
            if (defined('APP_ENV') && APP_ENV === 'development') {
                die('Database Connection Error. Please verify configuration.');
            } else {
                header('HTTP/1.1 500 Internal Server Error');
                require_once __DIR__ . '/../errors/500.php';
                exit();
            }
        }
    }

    return $pdo;
}
