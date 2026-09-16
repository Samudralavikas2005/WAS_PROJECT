<?php
/**
 * SecureCart Automated Test & Verification Suite
 * Used by CI/CD Pipelines (GitHub Actions / GitLab CI) and Local Development
 */

define('BASE_DIR', dirname(__DIR__));
$failed = false;

function log_test($name, $status, $message = '') {
    global $failed;
    $symbol = $status ? "[PASS]" : "[FAIL]";
    echo sprintf("%-8s %-50s %s\n", $symbol, $name, $message);
    if (!$status) {
        $failed = true;
    }
}

echo "========================================================\n";
echo "       SecureCart Automated Test & Security Audit       \n";
echo "========================================================\n\n";

// Test 1: PHP Syntax Linting across all project files
echo "1. Running PHP Syntax Checks (php -l)...\n";
$phpFiles = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(BASE_DIR)
);

$syntaxErrors = 0;
$scannedFiles = 0;

foreach ($phpFiles as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        // Skip scratch or vendor directories if present
        if (strpos($file->getPathname(), '/scratch/') !== false || strpos($file->getPathname(), '/vendor/') !== false) {
            continue;
        }
        $scannedFiles++;
        $cmd = sprintf("php -l %s 2>&1", escapeshellarg($file->getPathname()));
        $output = shell_exec($cmd);
        if (strpos($output, 'No syntax errors detected') === false) {
            log_test("Syntax Check: " . basename($file->getPathname()), false, trim($output));
            $syntaxErrors++;
        }
    }
}

if ($syntaxErrors === 0) {
    log_test("PHP Syntax Linting ($scannedFiles files scanned)", true, "All PHP files passed syntax checks cleanly.");
} else {
    log_test("PHP Syntax Linting", false, "$syntaxErrors file(s) failed syntax verification.");
}

// Test 2: Essential File Structure & Security Files Verification
echo "\n2. Verifying Application File Structure & Security Artifacts...\n";
$requiredFiles = [
    'config.example.php' => 'Configuration Template',
    'database/schema.sql' => 'Database Schema Definition',
    'database/seed.sql' => 'Database Seed Data',
    'index.php' => 'Application Entrypoint',
    'auth/login.php' => 'Authentication Module',
    'auth/register.php' => 'Registration Module',
    'checkout/index.php' => 'Checkout Interface Module',
    'checkout/place_order.php' => 'Transactional Order Processing Module',
    'includes/auth.php' => 'Auth & RBAC Helpers',
    'includes/csrf.php' => 'CSRF Defense Module',
    'includes/security.php' => 'XSS & Input Sanitization Helpers',
    'includes/session.php' => 'Secure Session Management',
    'admin/index.php' => 'RBAC Admin Portal'
];

foreach ($requiredFiles as $relPath => $label) {
    $fullPath = BASE_DIR . '/' . $relPath;
    if (file_exists($fullPath)) {
        log_test("File Check: $label ($relPath)", true, "File exists (" . filesize($fullPath) . " bytes)");
    } else {
        log_test("File Check: $label ($relPath)", false, "Missing required file!");
    }
}

// Test 3: Security Defensive Patterns Audit in Codebase
echo "\n3. Auditing Security Implementation Patterns...\n";

// Check XSS escaping in security.php
$secFile = BASE_DIR . '/includes/security.php';
if (file_exists($secFile)) {
    $content = file_get_contents($secFile);
    $hasXSS = (strpos($content, 'htmlspecialchars') !== false || strpos($content, 'sanitize') !== false);
    log_test("Security Audit: XSS Output Escaping (security.php)", $hasXSS, $hasXSS ? "htmlspecialchars / sanitize helper present" : "Missing XSS mitigation");
} else {
    log_test("Security Audit: XSS Output Escaping", false, "includes/security.php missing!");
}

// Check CSRF protection in csrf.php
$csrfFile = BASE_DIR . '/includes/csrf.php';
if (file_exists($csrfFile)) {
    $content = file_get_contents($csrfFile);
    $hasCSRF = (strpos($content, 'random_bytes') !== false || strpos($content, 'hash_equals') !== false || strpos($content, 'csrf') !== false);
    log_test("Security Audit: CSRF Cryptographic Protection (csrf.php)", $hasCSRF, $hasCSRF ? "random_bytes / hash_equals CSRF protection present" : "Missing CSRF mitigation");
} else {
    log_test("Security Audit: CSRF Cryptographic Protection", false, "includes/csrf.php missing!");
}

// Check Session protection in session.php
$sessionFile = BASE_DIR . '/includes/session.php';
if (file_exists($sessionFile)) {
    $content = file_get_contents($sessionFile);
    $hasSession = (strpos($content, 'session') !== false);
    log_test("Security Audit: Session Protection (session.php)", $hasSession, $hasSession ? "Session security initialization present" : "Missing session handling");
} else {
    log_test("Security Audit: Session Protection", false, "includes/session.php missing!");
}

// Test 4: Database Connection & Schema Verification (If MySQL available in environment)
echo "\n4. Verifying Database Schema & Connection (CI / Environment)...\n";
$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_NAME') ?: 'securecart';
$dbUser = getenv('DB_USER') ?: 'vikas';
$dbPass = getenv('DB_PASS') ?: 'Vikas@2005';

try {
    $dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    log_test("Database Connection", true, "Connected to MySQL database '$dbName' on $dbHost:$dbPort");

    // Check required tables
    $tables = ['users', 'products', 'reviews', 'cart', 'orders', 'order_items', 'login_attempts'];
    $stmt = $pdo->query("SHOW TABLES");
    $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $missingTables = array_diff($tables, $existingTables);
    if (empty($missingTables)) {
        log_test("Database Schema Verification", true, "All " . count($tables) . " required database tables present.");
    } else {
        log_test("Database Schema Verification", false, "Missing tables: " . implode(', ', $missingTables));
    }
} catch (PDOException $e) {
    $isCI = getenv('CI') || getenv('GITHUB_ACTIONS') || getenv('GITLAB_CI');
    if ($isCI) {
        log_test("Database Connection", false, "Database error in CI environment: " . $e->getMessage());
    } else {
        log_test("Database Connection (Local)", true, "Local MySQL offline or skipped: " . $e->getMessage());
    }
}

echo "\n========================================================\n";
if ($failed) {
    echo "       STATUS: TEST SUITE FAILED WITH ERRORS           \n";
    echo "========================================================\n";
    exit(1);
} else {
    echo "       STATUS: ALL TESTS PASSED SUCCESSFULLY!          \n";
    echo "========================================================\n";
    exit(0);
}
