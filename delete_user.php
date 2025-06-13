<?php
require_once 'config.php';
require_once 'session.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_admin();

$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$user_id) {
    redirect('admin.php');
}

// Check if user exists and is not an admin
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND user_type = 'user'");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    redirect('admin.php?error=user_not_found');
}

try {
    // Delete related data first (comments, messages, products)
    $stmt = $pdo->prepare("DELETE FROM comments WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    $stmt = $pdo->prepare("DELETE FROM messages WHERE sender_id = ? OR receiver_id = ?");
    $stmt->execute([$user_id, $user_id]);
    
    $stmt = $pdo->prepare("DELETE FROM products WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    // Delete the user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt->execute([$user_id])) {
        redirect('admin.php?user_deleted=1');
    } else {
        redirect('admin.php?error=delete_failed');
    }
} catch (Exception $e) {
    error_log("Delete user error: " . $e->getMessage());
    redirect('admin.php?error=delete_failed');
}
?>

