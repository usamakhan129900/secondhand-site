<?php
require_once 'config.php';
require_once 'session.php';

// Handle search redirect from form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $search = sanitize_input($_POST['search']);
    $category = sanitize_input($_POST['category']);
    $min_price = floatval($_POST['min_price']);
    $max_price = floatval($_POST['max_price']);
    
    $params = [];
    if (!empty($search)) $params['search'] = $search;
    if (!empty($category)) $params['category'] = $category;
    if ($min_price > 0) $params['min_price'] = $min_price;
    if ($max_price > 0) $params['max_price'] = $max_price;
    
    $query_string = http_build_query($params);
    redirect('index.php' . ($query_string ? '?' . $query_string : ''));
}

// If accessed directly, redirect to home
redirect('index.php');
?>

