<?php
require_once 'config.php';
require_once 'session.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle message actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['send_message'])) {
        $receiver_id = intval($_POST['receiver_id']);
        $product_id = !empty($_POST['product_id']) ? intval($_POST['product_id']) : null;
        $subject = sanitize_input($_POST['subject']);
        $message = sanitize_input($_POST['message']);
        
        if (empty($subject) || empty($message)) {
            $error = 'Subject and message are required.';
        } elseif ($receiver_id === $user_id) {
            $error = 'You cannot send a message to yourself.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, product_id, subject, message) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$user_id, $receiver_id, $product_id, $subject, $message])) {
                $success = 'Message sent successfully!';
            } else {
                $error = 'Failed to send message. Please try again.';
            }
        }
    } elseif (isset($_POST['mark_read'])) {
        $message_id = intval($_POST['message_id']);
        $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id = ? AND receiver_id = ?");
        $stmt->execute([$message_id, $user_id]);
    } elseif (isset($_POST['delete_message'])) {
        $message_id = intval($_POST['message_id']);
        $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ? AND (sender_id = ? OR receiver_id = ?)");
        $stmt->execute([$message_id, $user_id, $user_id]);
    }
}

// Get filter parameters
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Build query based on filter
$where_conditions = [];
$params = [];

switch ($filter) {
    case 'received':
        $where_conditions[] = "m.receiver_id = ?";
        $params[] = $user_id;
        break;
    case 'sent':
        $where_conditions[] = "m.sender_id = ?";
        $params[] = $user_id;
        break;
    case 'unread':
        $where_conditions[] = "m.receiver_id = ? AND m.is_read = 0";
        $params[] = $user_id;
        break;
    default: // all
        $where_conditions[] = "(m.sender_id = ? OR m.receiver_id = ?)";
        $params[] = $user_id;
        $params[] = $user_id;
        break;
}

$where_clause = implode(" AND ", $where_conditions);

// Get messages
$stmt = $pdo->prepare("
    SELECT m.*, 
           sender.username as sender_username, sender.full_name as sender_name,
           receiver.username as receiver_username, receiver.full_name as receiver_name,
           p.title as product_title
    FROM messages m
    JOIN users sender ON m.sender_id = sender.id
    JOIN users receiver ON m.receiver_id = receiver.id
    LEFT JOIN products p ON m.product_id = p.id
    WHERE $where_clause
    ORDER BY m.created_at DESC
    LIMIT ? OFFSET ?
");
$params[] = $per_page;
$params[] = $offset;
$stmt->execute($params);
$messages = $stmt->fetchAll();

// Get total count for pagination
$count_params = array_slice($params, 0, -2); // Remove limit and offset
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total
    FROM messages m
    WHERE $where_clause
");
$stmt->execute($count_params);
$total_messages = $stmt->fetch()['total'];
$total_pages = ceil($total_messages / $per_page);

// Get unread count
$stmt = $pdo->prepare("SELECT COUNT(*) as unread FROM messages WHERE receiver_id = ? AND is_read = 0");
$stmt->execute([$user_id]);
$unread_count = $stmt->fetch()['unread'];

$page_title = 'Messages';
include 'includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Message Filters</h5>
            </div>
            <div class="list-group list-group-flush">
                <a href="messages.php?filter=all" class="list-group-item list-group-item-action <?php echo $filter === 'all' ? 'active' : ''; ?>">
                    <i class="fas fa-inbox me-2"></i>All Messages
                    <span class="badge bg-secondary float-end"><?php echo $total_messages; ?></span>
                </a>
                <a href="messages.php?filter=received" class="list-group-item list-group-item-action <?php echo $filter === 'received' ? 'active' : ''; ?>">
                    <i class="fas fa-download me-2"></i>Received
                </a>
                <a href="messages.php?filter=sent" class="list-group-item list-group-item-action <?php echo $filter === 'sent' ? 'active' : ''; ?>">
                    <i class="fas fa-upload me-2"></i>Sent
                </a>
                <a href="messages.php?filter=unread" class="list-group-item list-group-item-action <?php echo $filter === 'unread' ? 'active' : ''; ?>">
                    <i class="fas fa-envelope me-2"></i>Unread
                    <?php if ($unread_count > 0): ?>
                        <span class="badge bg-danger float-end"><?php echo $unread_count; ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-body text-center">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#composeModal">
                    <i class="fas fa-plus me-1"></i>Compose Message
                </button>
            </div>
        </div>
    </div>
    
    <div class="col-md-9">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <?php 
                    switch ($filter) {
                        case 'received': echo 'Received Messages'; break;
                        case 'sent': echo 'Sent Messages'; break;
                        case 'unread': echo 'Unread Messages'; break;
                        default: echo 'All Messages'; break;
                    }
                    ?>
                </h5>
                <?php if ($unread_count > 0): ?>
                    <span class="badge bg-danger"><?php echo $unread_count; ?> unread</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (empty($messages)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-envelope fa-3x text-muted mb-3"></i>
                        <h5>No messages found</h5>
                        <p class="text-muted">
                            <?php if ($filter === 'unread'): ?>
                                You have no unread messages.
                            <?php else: ?>
                                Start a conversation by sending a message to a seller.
                            <?php endif; ?>
                        </p>
                    </div>
                <?php else: ?>
                    <?php foreach ($messages as $message): ?>
                        <div class="border-bottom pb-3 mb-3 <?php echo (!$message['is_read'] && $message['receiver_id'] == $user_id) ? 'bg-light p-3 rounded' : ''; ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <?php if ($message['sender_id'] == $user_id): ?>
                                            <strong>To: <?php echo htmlspecialchars($message['receiver_name']); ?></strong>
                                            <span class="badge bg-primary ms-2">Sent</span>
                                        <?php else: ?>
                                            <strong>From: <?php echo htmlspecialchars($message['sender_name']); ?></strong>
                                            <?php if (!$message['is_read']): ?>
                                                <span class="badge bg-danger ms-2">New</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <small class="text-muted ms-auto"><?php echo time_ago($message['created_at']); ?></small>
                                    </div>
                                    
                                    <h6 class="mb-2"><?php echo htmlspecialchars($message['subject']); ?></h6>
                                    
                                    <?php if ($message['product_title']): ?>
                                        <p class="mb-2">
                                            <small class="text-muted">
                                                <i class="fas fa-box me-1"></i>Regarding: 
                                                <a href="product.php?id=<?php echo $message['product_id']; ?>" class="text-decoration-none">
                                                    <?php echo htmlspecialchars($message['product_title']); ?>
                                                </a>
                                            </small>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <p class="mb-2"><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                                </div>
                                
                                <div class="dropdown ms-3">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu">
                                        <?php if ($message['receiver_id'] == $user_id && !$message['is_read']): ?>
                                            <li>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                                    <button type="submit" name="mark_read" class="dropdown-item">
                                                        <i class="fas fa-check me-2"></i>Mark as Read
                                                    </button>
                                                </form>
                                            </li>
                                        <?php endif; ?>
                                        <li>
                                            <button type="button" class="dropdown-item" onclick="replyToMessage(<?php echo $message['id']; ?>, '<?php echo htmlspecialchars($message['sender_id'] == $user_id ? $message['receiver_name'] : $message['sender_name']); ?>', <?php echo $message['sender_id'] == $user_id ? $message['receiver_id'] : $message['sender_id']; ?>, '<?php echo htmlspecialchars($message['subject']); ?>')">
                                                <i class="fas fa-reply me-2"></i>Reply
                                            </button>
                                        </li>
                                        <li>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this message?')">
                                                <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                                <button type="submit" name="delete_message" class="dropdown-item text-danger">
                                                    <i class="fas fa-trash me-2"></i>Delete
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Messages pagination">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="messages.php?filter=<?php echo $filter; ?>&page=<?php echo $page - 1; ?>">Previous</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="messages.php?filter=<?php echo $filter; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="messages.php?filter=<?php echo $filter; ?>&page=<?php echo $page + 1; ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Compose Message Modal -->
<div class="modal fade" id="composeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Compose Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="receiver_username" class="form-label">To (Username) *</label>
                        <input type="text" class="form-control" id="receiver_username" name="receiver_username" required>
                        <input type="hidden" id="receiver_id" name="receiver_id">
                        <small class="text-muted">Start typing to search for users</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject *</label>
                        <input type="text" class="form-control" id="subject" name="subject" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="message" class="form-label">Message *</label>
                        <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                    </div>
                    
                    <input type="hidden" id="product_id" name="product_id">
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
function replyToMessage(messageId, senderName, senderId, originalSubject) {
    document.getElementById('receiver_username').value = senderName;
    document.getElementById('receiver_id').value = senderId;
    document.getElementById('subject').value = 'Re: ' + originalSubject;
    document.getElementById('message').value = '';
    document.getElementById('product_id').value = '';
    
    var modal = new bootstrap.Modal(document.getElementById('composeModal'));
    modal.show();
}

// Simple user search functionality
document.getElementById('receiver_username').addEventListener('input', function() {
    var username = this.value;
    if (username.length > 2) {
        // In a real implementation, you would make an AJAX call to search users
        // For now, we'll just clear the receiver_id when typing
        document.getElementById('receiver_id').value = '';
    }
});

// Set receiver_id when a valid username is entered
document.getElementById('receiver_username').addEventListener('blur', function() {
    var username = this.value;
    if (username.length > 0) {
        // Show loading indicator
        this.style.borderColor = '#ffc107';
        
        fetch('search_user.php?username=' + encodeURIComponent(username))
            .then(response => response.json())
            .then(data => {
                if (data.user_id) {
                    document.getElementById('receiver_id').value = data.user_id;
                    this.style.borderColor = '#28a745'; // Green for success
                } else {
                    alert('User "' + username + '" not found. Please check the username.');
                    this.value = '';
                    this.style.borderColor = '#dc3545'; // Red for error
                    document.getElementById('receiver_id').value = '';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error checking username. Please try again.');
                this.style.borderColor = '#dc3545'; // Red for error
                document.getElementById('receiver_id').value = '';
            });
    }
});

// Validate form before submission
document.querySelector('#composeModal form').addEventListener('submit', function(e) {
    var receiverId = document.getElementById('receiver_id').value;
    var username = document.getElementById('receiver_username').value;
    
    if (!receiverId && username) {
        e.preventDefault();
        alert('Please wait for username validation to complete, then try again.');
        return false;
    }
    
    if (!receiverId) {
        e.preventDefault();
        alert('Please enter a valid username.');
        return false;
    }
    
    return true;
});
</script>

<?php include 'includes/footer.php'; ?>

