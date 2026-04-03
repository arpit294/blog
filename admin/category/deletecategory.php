<?php
session_start();
require_once __DIR__ . '/../../config/category/categoryCrud.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /blog/config/login&signup/login.php');
    exit;
}

$categoryObj = new category();
$deleteId = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);

if ($deleteId <= 0) {
    header('Location: /blog/admin/category/allcategory.php');
    exit;
}

$deletingCategory = $categoryObj->getById($deleteId);

$categoryName = $deletingCategory['name'];
$message = '';
$messageType = '';



header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $categoryObj->delete($deleteId);
    
    if (stripos($result, 'successfully') !== false) {
        echo json_encode([
            'success' => true,
            'message' => $result
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $result
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
