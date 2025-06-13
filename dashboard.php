<?php
require_once 'config.php';
require_once 'session.php';

require_login();

$user = get_user_info();

// Handle status messages
$success = '';
$error = '';

if (isset($_GET['deleted'])) {
    $success = 'Product deleted successfully.';
} elseif (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'delete_failed':
            $error = 'Failed to delete product. Please try again.';
            break;
        default:
            $error = 'An error occurred. Please try again.';
            break;
    }
}

// Get user's products
$stmt = $pdo->prepare("SELECT * FROM products WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$user_products = $stmt->fetchAll();

$page_title = 'Dashboard';
include 'includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-header">
                <h5>User Profile</h5>
            </div>
            <div class="card-body">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($user['full_name']); ?></p>
                <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Member since:</strong> <?php echo date('M Y', strtotime($user['created_at'])); ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-9">
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>My Products</h2>
            <a href="upload.php" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Add New Product
            </a>
        </div>
        
        <?php if (empty($user_products)): ?>
            <div class="alert alert-info">
                <h5>No products yet!</h5>
                <p>You haven't listed any products for sale. <a href="upload.php">Click here to add your first product</a>.</p>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($user_products as $product): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <?php if ($product['image_path']): ?>
                                <img src="<?php echo UPLOAD_DIR . $product['image_path']; ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['title']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?></p>
                                <p class="card-text">
                                    <strong><?php echo format_price($product['price']); ?></strong>
                                    <?php if ($product['is_sold']): ?>
                                        <span class="badge bg-success ms-2">SOLD</span>
                                    <?php endif; ?>
                                </p>
                                <small class="text-muted">Listed <?php echo time_ago($product['created_at']); ?></small>
                            </div>
                            <div class="card-footer">
                                <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        onclick="showDeleteConfirm(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['title']); ?>')">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete "<span id="deleteProductTitle"></span>"?</p>
                <p class="text-danger"><strong>This action cannot be undone.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="delete_product.php" style="display: inline;" id="deleteForm">
                    <input type="hidden" name="id" id="deleteProductId">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>Delete Product
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showDeleteConfirm(productId, productTitle) {
    document.getElementById('deleteProductId').value = productId;
    document.getElementById('deleteProductTitle').textContent = productTitle;
    var modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    modal.show();
}
</script>

<?php include 'includes/footer.php'; ?>

