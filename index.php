<?php
require_once 'config.php';
require_once 'session.php';

// Get search parameters
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$category = isset($_GET['category']) ? sanitize_input($_GET['category']) : '';
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 0;

// Build query
$where_conditions = ["is_sold = 0"];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(title LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($category)) {
    $where_conditions[] = "category = ?";
    $params[] = $category;
}

if ($min_price > 0) {
    $where_conditions[] = "price >= ?";
    $params[] = $min_price;
}

if ($max_price > 0) {
    $where_conditions[] = "price <= ?";
    $params[] = $max_price;
}

$where_clause = implode(" AND ", $where_conditions);

$stmt = $pdo->prepare("
    SELECT p.*, u.username, u.full_name 
    FROM products p 
    JOIN users u ON p.user_id = u.id 
    WHERE $where_clause 
    ORDER BY p.created_at DESC
");
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories for filter
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

$page_title = 'Home';
include 'includes/header.php';
?>

<!-- Search Form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" class="form-control" name="search" placeholder="Search products..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-2">
                <select class="form-control" name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['name']; ?>" 
                                <?php echo ($category === $cat['name']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control" name="min_price" placeholder="Min Price (¥)" step="0.01" 
                       value="<?php echo $min_price > 0 ? $min_price : ''; ?>">
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control" name="max_price" placeholder="Max Price (¥)" step="0.01" 
                       value="<?php echo $max_price > 0 ? $max_price : ''; ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i>Search
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Welcome Message -->
<?php if (empty($search) && empty($category) && $min_price == 0 && $max_price == 0): ?>
    <div class="jumbotron bg-light p-5 rounded mb-4">
        <h1 class="display-4">Welcome to <?php echo SITE_NAME; ?></h1>
        <p class="lead">Find great deals on second-hand items or sell your own products to the community.</p>
        <?php if (!is_logged_in()): ?>
            <a class="btn btn-primary btn-lg" href="register.php" role="button">Get Started</a>
        <?php else: ?>
            <a class="btn btn-primary btn-lg" href="upload.php" role="button">Sell Something</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- Products Grid -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>
        <?php if (!empty($search) || !empty($category) || $min_price > 0 || $max_price > 0): ?>
            Search Results (<?php echo count($products); ?> items)
        <?php else: ?>
            Latest Products
        <?php endif; ?>
    </h2>
    <?php if (is_logged_in()): ?>
        <a href="upload.php" class="btn btn-success">
            <i class="fas fa-plus me-1"></i>Sell Item
        </a>
    <?php endif; ?>
</div>

<?php if (empty($products)): ?>
    <div class="alert alert-info">
        <h5>No products found</h5>
        <p>
            <?php if (!empty($search) || !empty($category) || $min_price > 0 || $max_price > 0): ?>
                Try adjusting your search criteria.
            <?php else: ?>
                Be the first to list a product! <a href="<?php echo is_logged_in() ? 'upload.php' : 'register.php'; ?>">Click here to get started</a>.
            <?php endif; ?>
        </p>
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($products as $product): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <?php if ($product['image_path']): ?>
                        <img src="<?php echo UPLOAD_DIR . $product['image_path']; ?>" class="card-img-top" style="height: 250px; object-fit: cover;">
                    <?php else: ?>
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 250px;">
                            <i class="fas fa-image fa-3x text-muted"></i>
                        </div>
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo htmlspecialchars($product['title']); ?></h5>
                        <p class="card-text flex-grow-1"><?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?></p>
                        <div class="mt-auto">
                            <p class="card-text">
                                <strong class="text-primary"><?php echo format_price($product['price']); ?></strong>
                                <?php if ($product['category']): ?>
                                    <span class="badge bg-secondary ms-2"><?php echo htmlspecialchars($product['category']); ?></span>
                                <?php endif; ?>
                            </p>
                            <p class="card-text">
                                <small class="text-muted">
                                    By <?php echo htmlspecialchars($product['full_name']); ?> • 
                                    <?php echo time_ago($product['created_at']); ?>
                                    <?php if ($product['location']): ?>
                                        • <?php echo htmlspecialchars($product['location']); ?>
                                    <?php endif; ?>
                                </small>
                            </p>
                            <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>

