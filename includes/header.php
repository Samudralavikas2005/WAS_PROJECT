<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/auth.php';

// Apply HTTP security headers globally
set_security_headers();

$currentUser = current_user();
$cartCount = get_cart_count();
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'SecureCart — Secure Online Shopping') ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Custom Security Theme CSS -->
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= APP_URL ?>/index.php">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="#38bdf8" class="bi bi-shield-lock-fill" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M8 0c-.69 0-1.843.265-2.928.56-1.11.3-2.229.655-2.887.87a1.54 1.54 0 0 0-1.044 1.262c-.596 4.477.787 7.795 2.465 9.99a11.8 11.8 0 0 0 4.12 3.141c.175.086.36.13.546.13s.371-.044.546-.13a11.8 11.8 0 0 0 4.12-3.141c1.678-2.195 3.061-5.513 2.465-9.99a1.54 1.54 0 0 0-1.044-1.263c-.658-.216-1.777-.57-2.887-.87C9.843.266 8.69 0 8 0m0 5a1.5 1.5 0 0 1 1.5 1.5v.757a1.5 1.5 0 0 1 1 1.413v1.66c0 .921-.75 1.67-1.67 1.67H7.17A1.67 1.67 0 0 1 5.5 10.33v-1.66c0-.687.417-1.277 1-1.413V6.5A1.5 1.5 0 0 1 8 5m.5 3.5V6.5a.5.5 0 0 0-1 0v2z"/>
            </svg>
            <span>Secure</span>Cart
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                <li class="nav-item">
                    <a class="nav-link" href="<?= APP_URL ?>/index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= APP_URL ?>/products/index.php">Browse Products</a>
                </li>
                <?php if (is_logged_in()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= APP_URL ?>/orders/index.php">My Orders</a>
                    </li>
                <?php endif; ?>
                <?php if (is_admin()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-warning font-weight-bold" href="#" data-bs-toggle="dropdown">
                            🛡️ Admin Panel
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark">
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/admin/index.php">Dashboard Overview</a></li>
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/admin/products.php">Manage Products</a></li>
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/admin/orders.php">Customer Orders</a></li>
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/admin/users.php">Users & Security Logs</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <?php if (is_logged_in()): ?>
                    <a href="<?= APP_URL ?>/cart/index.php" class="btn btn-outline-info position-relative btn-sm px-3">
                        🛒 Cart
                        <?php if ($cartCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $cartCount ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            👤 <?= e($currentUser['name']) ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
                            <li><span class="dropdown-item-text text-muted small"><?= e($currentUser['email']) ?> (<?= e(ucfirst($currentUser['role'])) ?>)</span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= APP_URL ?>/auth/logout.php">Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?= APP_URL ?>/auth/login.php" class="btn btn-outline-light btn-sm">Login</a>
                    <a href="<?= APP_URL ?>/auth/register.php" class="btn btn-primary-custom btn-sm">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- System Flash Alerts -->
<div class="container mt-3">
    <?php if ($flash): ?>
        <div class="alert alert-custom-<?= e($flash['type']) ?> alert-dismissible fade show d-flex align-items-center justify-content-between" role="alert">
            <div>
                <strong>Security Alert:</strong> <?= e($flash['message']) ?>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
</div>

<main class="py-4">
    <div class="container">
