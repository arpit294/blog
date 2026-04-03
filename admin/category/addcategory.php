<?php
session_start();
require_once __DIR__ . '/../../config/category/categoryCrud.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /blog/config/login&signup/login.php');
    exit;
}

$categoryObj = new category();

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['modal']) && $_GET['modal'] == 1) {
    $submitLabel = 'Add Category';
    $formAction = 'addcategory.php';
    $formId = 'addForm';
    include __DIR__ . '/partials/categoryFormModal.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $categoryObj->create($_POST);
    
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }
    
    if ($result['success']) {
        $_SESSION['category_message'] = 'Category added successfully.';
        $_SESSION['category_message_type'] = 'success';
        header('Location: /blog/admin/category/allcategory.php');
        exit;
    } else {
        $categoryError = $result['errors']['name'] ?? ($result['errors']['general'] ?? '');
        $statusError = $result['errors']['status'] ?? '';
        $generalError = $result['errors']['general'] ?? '';
        $message = $generalError ?: 'Failed to add category';
        $messageType = 'danger';
    }
}

$name = $_POST['name'] ?? '';
$status = $_POST['status'] ?? 'pending';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Category</title>
    <?php include __DIR__ . '/../../include/header.php'; ?>
</head>

<body class="category-add-page">
    <?php include __DIR__ . '/../adminpanel.php'; ?>
    <div class="page-wrap">
        <div class="card-wrap">
            <div class="card add-card">
                <div class="add-card-header d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div>
                        <h3>Add Category</h3>
                        <p>Create a new category for your blog posts.</p>
                    </div>
                    <a href="/blog/admin/category/allcategory.php" class="btn btn-light btn-sm">Back</a>
                </div>

                <div class="add-card-body">
                    <?php if (isset($_GET['modal']) && $_GET['modal'] == 1): ?>
                        <?php include __DIR__ . '/partials/categoryFormModal.php'; ?>
                    <?php else: ?>
                        <form method="POST" action="addcategory.php" id="addForm">
                            <input type="hidden" name="id" value="">
                            <div class="mb-3">
                                <label for="name" class="form-label">Category Name</label>
                                <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($name); ?>" >
                                <div class="text-danger" id="categoryError"><?= htmlspecialchars($categoryError ?? ''); ?></div>
                            </div>
                            <div class="mb-4">
                                <label for="status" class="form-label">Status</label>
                                <select id="status" name="status" class="form-select" >
                                    <option value="draft" <?= $status === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                    <option value="published" <?= $status === 'published' ? 'selected' : ''; ?> >Published</option>
                                </select>
                                <div class="text-danger" id="statusError"><?= htmlspecialchars($statusError ?? ''); ?></div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Add Category</button>
                                <a href="allcategory.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php include __DIR__ . '/../../include/footer.php'; ?>
</body>

</html>
