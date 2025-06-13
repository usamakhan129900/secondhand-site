<?php
require_once 'config.php';
require_once 'session.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$comment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

if (!$comment_id || !$product_id) {
    redirect('index.php');
}

// Check if user owns this comment or is admin
$stmt = $pdo->prepare("SELECT user_id FROM comments WHERE id = ?");
$stmt->execute([$comment_id]);
$comment = $stmt->fetch();

if (!$comment) {
    redirect("product.php?id=$product_id");
}

if (!is_logged_in() || ($_SESSION['user_id'] != $comment['user_id'] && !is_admin())) {
    redirect("product.php?id=$product_id");
}

try {
    // Delete the comment
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
    if ($stmt->execute([$comment_id])) {
        redirect("product.php?id=$product_id&comment_deleted=1");
    } else {
        redirect("product.php?id=$product_id&error=delete_failed");
    }
} catch (Exception $e) {
    error_log("Delete comment error: " . $e->getMessage());
    redirect("product.php?id=$product_id&error=delete_failed");
}
?>

