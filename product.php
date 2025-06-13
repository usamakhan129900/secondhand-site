<?php
require_once 'config.php';
require_once 'session.php';

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$product_id) {
    redirect('index.php');
}

// Get product details with seller info
$stmt = $pdo->prepare("
    SELECT p.*, u.username, u.full_name, u.phone, u.email 
    FROM products p 
    JOIN users u ON p.user_id = u.id 
    WHERE p.id = ?
");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    redirect('index.php');
}

// Get comments for this product
$stmt = $pdo->prepare("
    SELECT c.*, u.full_name, u.username 
    FROM comments c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.product_id = ? 
    ORDER BY c.created_at DESC
");
$stmt->execute([$product_id]);
$comments = $stmt->fetchAll();

// Handle comment submission
$comment_error = '';
$comment_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    if (!is_logged_in()) {
        $comment_error = 'You must be logged in to post a comment.';
    } else {
        $comment = sanitize_input($_POST['comment']);
        if (empty($comment)) {
            $comment_error = 'Please enter a comment.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO comments (product_id, user_id, comment) VALUES (?, ?, ?)");
            if ($stmt->execute([$product_id, $_SESSION['user_id'], $comment])) {
                $comment_success = 'Comment posted successfully!';
                // Refresh comments
                $stmt = $pdo->prepare("
                    SELECT c.*, u.full_name, u.username 
                    FROM comments c 
                    JOIN users u ON c.user_id = u.id 
                    WHERE c.product_id = ? 
                    ORDER BY c.created_at DESC
                ");
                $stmt->execute([$product_id]);
                $comments = $stmt->fetchAll();
            } else {
                $comment_error = 'Failed to post comment. Please try again.';
            }
        }
    }
}

$page_title = $product['title'];
include 'includes/header.php';
?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <?php if ($product['image_path']): ?>
                <img src="<?php echo UPLOAD_DIR . $product['image_path']; ?>" class="card-img-top" style="max-height: 400px; object-fit: contain;">
            <?php endif; ?>
            <div class="card-body">
                <h1 class="card-title"><?php echo htmlspecialchars($product['title']); ?></h1>
                <h3 class="text-primary mb-3"><?php echo format_price($product['price']); ?></h3>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <?php if ($product['category']): ?>
                            <p><strong>Category:</strong> <?php echo htmlspecialchars($product['category']); ?></p>
                        <?php endif; ?>
                        <p><strong>Condition:</strong> 
                            <span class="badge bg-<?php 
                                echo $product['condition_status'] === 'excellent' ? 'success' : 
                                    ($product['condition_status'] === 'good' ? 'primary' : 
                                    ($product['condition_status'] === 'fair' ? 'warning' : 'danger')); 
                            ?>">
                                <?php echo ucfirst($product['condition_status']); ?>
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <?php if ($product['location']): ?>
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($product['location']); ?></p>
                        <?php endif; ?>
                        <p><strong>Listed:</strong> <?php echo time_ago($product['created_at']); ?></p>
                    </div>
                </div>
                
                <h5>Description</h5>
                <p class="card-text"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>
        </div>
        
        <!-- Comments Section -->
        <div class="card mt-4">
            <div class="card-header">
                <h5>Comments & Questions</h5>
            </div>
            <div class="card-body">
                <?php if (is_logged_in()): ?>
                    <?php if ($comment_error): ?>
                        <div class="alert alert-danger"><?php echo $comment_error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($comment_success): ?>
                        <div class="alert alert-success"><?php echo $comment_success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" class="mb-4">
                        <div class="mb-3">
                            <textarea class="form-control" name="comment" rows="3" placeholder="Ask a question or leave a comment..." required></textarea>
                        </div>
                        <button type="submit" name="add_comment" class="btn btn-primary">Post Comment</button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-info">
                        <a href="login.php">Login</a> to post comments and ask questions.
                    </div>
                <?php endif; ?>
                
                <?php if (empty($comments)): ?>
                    <p class="text-muted">No comments yet. Be the first to ask a question!</p>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="border-bottom pb-3 mb-3">
                            <div class="d-flex justify-content-between">
                                <strong><?php echo htmlspecialchars($comment['full_name']); ?></strong>
                                <small class="text-muted"><?php echo time_ago($comment['created_at']); ?></small>
                            </div>
                            <p class="mt-2 mb-0"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Seller Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($product['full_name']); ?></p>
                <p><strong>Username:</strong> <?php echo htmlspecialchars($product['username']); ?></p>
                
                <?php if (is_logged_in()): ?>
                    <hr>
                    <h6>Contact Seller</h6>
                    <p><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($product['email']); ?>"><?php echo htmlspecialchars($product['email']); ?></a></p>
                    <?php if ($product['phone']): ?>
                        <p><strong>Phone:</strong> <a href="tel:<?php echo htmlspecialchars($product['phone']); ?>"><?php echo htmlspecialchars($product['phone']); ?></a></p>
                    <?php endif; ?>
                    
                    <div class="d-grid gap-2">
                        <a href="mailto:<?php echo htmlspecialchars($product['email']); ?>?subject=Inquiry about: <?php echo urlencode($product['title']); ?>" class="btn btn-primary">
                            <i class="fas fa-envelope me-1"></i>Send Email
                        </a>
                        <?php if ($product['phone']): ?>
                            <a href="tel:<?php echo htmlspecialchars($product['phone']); ?>" class="btn btn-success">
                                <i class="fas fa-phone me-1"></i>Call Seller
                            </a>
                        <?php endif; ?>
                        <button type="button" class="btn btn-info" onclick="contactSeller(<?php echo $product['user_id']; ?>, '<?php echo htmlspecialchars($product['username']); ?>', <?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['title']); ?>')">
                            <i class="fas fa-comments me-1"></i>Send Message
                        </button>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <a href="login.php">Login</a> to view seller contact information.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (is_logged_in() && $_SESSION['user_id'] == $product['user_id']): ?>
            <div class="card mt-3">
                <div class="card-header">
                    <h5>Manage Product</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="btn btn-danger" 
                           onclick="return confirm('Are you sure you want to delete this product?')">
                            <i class="fas fa-trash me-1"></i>Delete Product
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Comments Section -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-comments me-2"></i>Comments (<?php echo count($comments); ?>)</h5>
            </div>
            <div class="card-body">
                <?php if ($comment_success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo $comment_success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($comment_error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php echo $comment_error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Add Comment Form -->
                <?php if (is_logged_in()): ?>
                    <form method="POST" class="mb-4">
                        <div class="mb-3">
                            <label for="comment" class="form-label">Add a Comment</label>
                            <textarea class="form-control" id="comment" name="comment" rows="3" placeholder="Share your thoughts about this product..." required></textarea>
                        </div>
                        <button type="submit" name="add_comment" class="btn btn-primary">
                            <i class="fas fa-comment me-1"></i>Post Comment
                        </button>
                    </form>
                    <hr>
                <?php else: ?>
                    <div class="alert alert-info">
                        <a href="login.php">Login</a> to post a comment.
                    </div>
                <?php endif; ?>

                <!-- Display Comments -->
                <?php if (empty($comments)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-comments fa-3x mb-3"></i>
                        <p>No comments yet. Be the first to comment!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment-item border-bottom pb-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="d-flex">
                                    <div class="avatar-circle me-3">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($comment['full_name']); ?></h6>
                                        <small class="text-muted">@<?php echo htmlspecialchars($comment['username']); ?> • <?php echo time_ago($comment['created_at']); ?></small>
                                        <p class="mt-2 mb-0"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                    </div>
                                </div>
                                <?php if (is_logged_in() && ($_SESSION['user_id'] == $comment['user_id'] || is_admin())): ?>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item text-danger" href="delete_comment.php?id=<?php echo $comment['id']; ?>&product_id=<?php echo $product_id; ?>" 
                                                   onclick="return confirm('Are you sure you want to delete this comment?')">
                                                    <i class="fas fa-trash me-1"></i>Delete
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Contact Seller Modal -->
<div class="modal fade" id="contactSellerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Contact Seller</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="messages.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="contact_subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="contact_subject" name="subject" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="contact_message" class="form-label">Message *</label>
                        <textarea class="form-control" id="contact_message" name="message" rows="4" placeholder="Hi, I'm interested in your product..." required></textarea>
                    </div>
                    
                    <input type="hidden" id="contact_receiver_id" name="receiver_id">
                    <input type="hidden" id="contact_product_id" name="product_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="send_message" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-1"></i>Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function contactSeller(sellerId, sellerUsername, productId, productTitle) {
    document.getElementById('contact_receiver_id').value = sellerId;
    document.getElementById('contact_product_id').value = productId;
    document.getElementById('contact_subject').value = 'Inquiry about: ' + productTitle;
    document.getElementById('contact_message').value = '';
    
    var modal = new bootstrap.Modal(document.getElementById('contactSellerModal'));
    modal.show();
}
</script>

<?php include 'includes/footer.php'; ?>

