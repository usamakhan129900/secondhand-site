<?php
require_once 'config.php';
require_once 'session.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$username = isset($_GET['username']) ? sanitize_input($_GET['username']) : '';

if (empty($username)) {
    echo json_encode(['error' => 'Username required']);
    exit;
}

$stmt = $pdo->prepare("SELECT id, username, full_name FROM users WHERE username = ? AND is_active = 1");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user) {
    echo json_encode([
        'user_id' => $user['id'],
        'username' => $user['username'],
        'full_name' => $user['full_name']
    ]);
} else {
    echo json_encode(['error' => 'User not found']);
}
?>

