<?php
require_once 'config.php';
require_once 'session.php';

require_admin();

$success = '';
$error = '';

// Handle status messages
if (isset($_GET['deleted'])) {
    $success = 'Item deleted successfully.';
} elseif (isset($_GET['user_deleted'])) {
    $success = 'User deleted successfully.';
} elseif (isset($_GET['error'])) {
    $error = 'Operation failed. Please try again.';
}

// Get statistics
$stats = [];

$stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users WHERE user_type = 'user'");
$stats['total_users'] = $stmt->fetch()['total_users'];

$stmt = $pdo->query("SELECT COUNT(*) as total_products FROM products");
$stats['total_products'] = $stmt->fetch()['total_products'];

$stmt = $pdo->query("SELECT COUNT(*) as sold_products FROM products WHERE is_sold = 1");
$stats['sold_products'] = $stmt->fetch()['sold_products'];

$stmt = $pdo->query("SELECT COUNT(*) as total_comments FROM comments");
$stats['total_comments'] = $stmt->fetch()['total_comments'];

// Get recent users
$stmt = $pdo->query("SELECT * FROM users WHERE user_type = 'user' ORDER BY created_at DESC LIMIT 10");
$recent_users = $stmt->fetchAll();

// Get recent products
$stmt = $pdo->query("
    SELECT p.*, u.username, u.full_name 
    FROM products p 
    JOIN users u ON p.user_id = u.id 
    ORDER BY p.created_at DESC 
    LIMIT 10
");
$recent_products = $stmt->fetchAll();

$page_title = 'Admin Panel';
include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Admin Panel</h1>
    <div>
        <a href="index.php" class="btn btn-outline-primary">View Site</a>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo $stats['total_users']; ?></h4>
                        <p class="mb-0">Total Users</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo $stats['total_products']; ?></h4>
                        <p class="mb-0">Total Products</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-box fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo $stats['sold_products']; ?></h4>
                        <p class="mb-0">Sold Products</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo $stats['total_comments']; ?></h4>
                        <p class="mb-0">Total Comments</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-comments fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Users -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Users</h5>
                <a href="#" class="btn btn-sm btn-outline-primary" onclick="toggleUsersList()">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo date('M j', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-danger" 
                                           onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Products -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Products</h5>
                <a href="#" class="btn btn-sm btn-outline-primary" onclick="toggleProductsList()">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Price</th>
                                <th>Seller</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_products as $product): ?>
                                <tr>
                                    <td>
                                        <a href="product.php?id=<?php echo $product['id']; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars(substr($product['title'], 0, 30)) . (strlen($product['title']) > 30 ? '...' : ''); ?>
                                        </a>
                                    </td>
                                    <td><?php echo format_price($product['price']); ?></td>
                                    <td><?php echo htmlspecialchars($product['username']); ?></td>
                                    <td>
                                        <?php if ($product['is_sold']): ?>
                                            <span class="badge bg-success">Sold</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary">Available</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-danger" 
                                           onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden sections for full lists -->
<div id="allUsersList" class="card mt-4" style="display: none;">
    <div class="card-header">
        <h5 class="mb-0">All Users</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Joined</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("SELECT * FROM users WHERE user_type = 'user' ORDER BY created_at DESC");
                    $all_users = $stmt->fetchAll();
                    foreach ($all_users as $user):
                    ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <?php if ($user['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-danger" 
                                   onclick="return confirm('Are you sure you want to delete this user and all their products?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="allProductsList" class="card mt-4" style="display: none;">
    <div class="card-header">
        <h5 class="mb-0">All Products</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Price</th>
                        <th>Category</th>
                        <th>Seller</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("
                        SELECT p.*, u.username, u.full_name 
                        FROM products p 
                        JOIN users u ON p.user_id = u.id 
                        ORDER BY p.created_at DESC
                    ");
                    $all_products = $stmt->fetchAll();
                    foreach ($all_products as $product):
                    ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td>
                                <a href="product.php?id=<?php echo $product['id']; ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($product['title']); ?>
                                </a>
                            </td>
                            <td><?php echo format_price($product['price']); ?></td>
                            <td><?php echo htmlspecialchars($product['category']); ?></td>
                            <td><?php echo htmlspecialchars($product['username']); ?></td>
                            <td>
                                <?php if ($product['is_sold']): ?>
                                    <span class="badge bg-success">Sold</span>
                                <?php else: ?>
                                    <span class="badge bg-primary">Available</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($product['created_at'])); ?></td>
                            <td>
                                <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-danger" 
                                   onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function toggleUsersList() {
    const list = document.getElementById('allUsersList');
    list.style.display = list.style.display === 'none' ? 'block' : 'none';
}

function toggleProductsList() {
    const list = document.getElementById('allProductsList');
    list.style.display = list.style.display === 'none' ? 'block' : 'none';
}
</script>

<?php include 'includes/footer.php'; ?>

