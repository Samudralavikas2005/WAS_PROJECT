<?php
$pageTitle = 'Add Product — Admin SecureCart';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../config/database.php';

require_admin();

$errors = [];
$name = '';
$description = '';
$price = '';
$stock = '';
$category = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid or expired CSRF token.';
    }

    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
    $category = trim($_POST['category'] ?? '');

    if (empty($name)) $errors[] = 'Product name is required.';
    if (empty($description)) $errors[] = 'Description is required.';
    if ($price === false || $price <= 0) $errors[] = 'Price must be a positive number.';
    if ($stock === false || $stock < 0) $errors[] = 'Stock must be a non-negative integer.';
    if (empty($category)) $errors[] = 'Category is required.';

    if (empty($errors)) {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            INSERT INTO products (name, description, price, stock, category, image_url) 
            VALUES (?, ?, ?, ?, ?, 'assets/images/placeholder.svg')
        ");
        if ($stmt->execute([$name, $description, $price, $stock, $category])) {
            set_flash('Product "' . e($name) . '" added successfully.', 'success');
            header('Location: ' . APP_URL . '/admin/products.php');
            exit();
        } else {
            $errors[] = 'Failed to insert product into database.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center my-4">
    <div class="col-md-7">
        <div class="card card-glass p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="text-white font-weight-bold mb-0">Add New Product</h3>
                <a href="<?= APP_URL ?>/admin/products.php" class="btn btn-outline-secondary btn-sm">← Back</a>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-custom-danger mb-4">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $err): ?>
                            <li><?= e($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="<?= APP_URL ?>/admin/add_product.php" method="POST">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="name" class="form-label">Product Name</label>
                    <input type="text" id="name" name="name" class="form-control form-control-custom" value="<?= e($name) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="category" class="form-label">Category</label>
                    <input type="text" id="category" name="category" class="form-control form-control-custom" value="<?= e($category) ?>" required placeholder="e.g. Hardware Security, Networking">
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="price" class="form-label">Price (₹)</label>
                        <input type="number" step="0.01" id="price" name="price" class="form-control form-control-custom" value="<?= e($price) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="stock" class="form-label">Stock Quantity</label>
                        <input type="number" id="stock" name="stock" class="form-control form-control-custom" value="<?= e($stock) ?>" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" rows="4" class="form-control form-control-custom" required><?= e($description) ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary-custom w-100">
                    Save Product to MySQL
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
