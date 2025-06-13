<?php
require_once 'config.php';
require_once 'session.php';

require_login();

$error = '';
$success = '';

// Get categories for dropdown
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize_input($_POST['title']);
    $description = sanitize_input($_POST['description']);
    $price = floatval($_POST['price']);
    $category = sanitize_input($_POST['category']);
    $condition_status = sanitize_input($_POST['condition_status']);
    $location = sanitize_input($_POST['location']);
    
    // Validation
    if (empty($title) || empty($description) || $price <= 0) {
        $error = 'Please fill in all required fields with valid values.';
    } else {
        // Handle image upload
        $image_filename = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image_filename = upload_image($_FILES['image']);
            if (!$image_filename) {
                $error = 'Failed to upload image. Please check file type and size.';
            }
        }
        
        if (!$error) {
            $stmt = $pdo->prepare("INSERT INTO products (user_id, title, description, price, category, condition_status, image_path, location) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$_SESSION['user_id'], $title, $description, $price, $category, $condition_status, $image_filename, $location])) {
                $success = 'Product uploaded successfully!';
                // Clear form data
                $_POST = array();
            } else {
                $error = 'Failed to upload product. Please try again.';
            }
        }
    }
}

$page_title = 'Upload Product';
include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Upload New Product</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                        <a href="dashboard.php" class="alert-link">View your products</a>
                    </div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label">Product Title *</label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="4" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="price" class="form-label">Price (¥) *</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0.01" 
                                       value="<?php echo isset($_POST['price']) ? $_POST['price'] : ''; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-control" id="category" name="category">
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['name']; ?>" 
                                                <?php echo (isset($_POST['category']) && $_POST['category'] === $cat['name']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="condition_status" class="form-label">Condition</label>
                                <select class="form-control" id="condition_status" name="condition_status">
                                    <option value="excellent" <?php echo (isset($_POST['condition_status']) && $_POST['condition_status'] === 'excellent') ? 'selected' : ''; ?>>Excellent</option>
                                    <option value="good" <?php echo (isset($_POST['condition_status']) && $_POST['condition_status'] === 'good') ? 'selected' : ''; ?>>Good</option>
                                    <option value="fair" <?php echo (isset($_POST['condition_status']) && $_POST['condition_status'] === 'fair') ? 'selected' : ''; ?>>Fair</option>
                                    <option value="poor" <?php echo (isset($_POST['condition_status']) && $_POST['condition_status'] === 'poor') ? 'selected' : ''; ?>>Poor</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location" 
                                       value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">Product Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <div class="form-text">Maximum file size: 5MB. Supported formats: JPG, PNG, GIF</div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Upload Product</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

