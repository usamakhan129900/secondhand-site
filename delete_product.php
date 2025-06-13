<?php
require_once 'config.php';
require_once 'session.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get product ID from GET or POST
$product_id = 0;
if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
} elseif (isset($_POST['id'])) {
    $product_id = intval($_POST['id']);
}

if (!$product_id) {
    redirect('dashboard.php?error=invalid_id');
}

// Check if user owns this product or is admin
$stmt = $pdo->prepare("SELECT user_id FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    redirect('dashboard.php?error=product_not_found');
}

if (!is_logged_in() || ($_SESSION['user_id'] != $product['user_id'] && !is_admin())) {
    redirect('dashboard.php?error=permission_denied');
}

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Delete related comments first
    $stmt = $pdo->prepare("DELETE FROM comments WHERE product_id = ?");
    $stmt->execute([$product_id]);
    
    // Delete related messages
    $stmt = $pdo->prepare("DELETE FROM messages WHERE product_id = ?");
    $stmt->execute([$product_id]);
    
    // Delete the product
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    if ($stmt->execute([$product_id])) {
        $pdo->commit();
        if (is_admin()) {
            redirect('admin.php?deleted=1');
        } else {
            redirect('dashboard.php?deleted=1');
        }
    } else {
        $pdo->rollback();
        redirect('dashboard.php?error=delete_failed');
    }
} catch (Exception $e) {
    $pdo->rollback();
    error_log("Delete product error: " . $e->getMessage());
    redirect('dashboard.php?error=delete_failed');
}
?>

