    <?php
    session_start();
    require_once __DIR__ . '/../../config/category/categoryCrud.php';

    if (!isset($_SESSION['user_id'])) {
        header('Location: /blog/config/login&signup/login.php');
        exit;
    }

    $categoryObj = new category();
    $editId = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);

    if ($editId <= 0) {
        header('Location: /blog/admin/category/allcategory.php');
        exit;
    }

    $editingCategory = $categoryObj->getById($editId);

    if (!$editingCategory) {
        header('Location: /blog/admin/category/allcategory.php');
        exit;
    }

    $name = $editingCategory['name'];
    $status = $editingCategory['status'] ?? 'pending';
    $categoryId = $editId;
    $formId = 'editCategoryForm';
    $formAction = 'editcategory.php?id=' . $editId;

    if (isset($_GET['modal']) && $_GET['modal'] == 1) {
        $submitLabel = 'Update Category';
        include __DIR__ . '/partials/categoryFormModal.php';
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $result = $categoryObj->update($editId, $_POST);
        
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        }
        
        $messageType = $result['success'] ? 'success' : 'danger';
        $message = $result['success'] ? 'Category updated successfully.' : ($result['errors']['general'] ?? 'Update failed');

        if ($messageType !== 'success') {
            $name = trim($_POST['name'] ?? '');
            $status = $_POST['status'] ?? 'pending';
        }

        if ($messageType === 'success') {
            $_SESSION['category_message'] = $message;
            $_SESSION['category_message_type'] = $messageType;
            header('Location: /blog/admin/category/allcategory.php');
            exit;
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Edit Category</title>
        <?php include __DIR__ . '/../../include/header.php'; ?>
    </head>

    <body class="category-edit-page">
        <?php include __DIR__ . '/../adminpanel.php'; ?>







        <div class="page-wrap">
            <div class="card-wrap">
                <div class="card edit-card">
                    <div class="edit-card-header d-flex justify-content-between align-items-start flex-wrap gap-3">
                        <div>
                            <h3>Edit Category</h3>
                            <p>Update the category name and save your changes.</p>
                        </div>
                        <a href="/blog/admin/category/allcategory.php" class="btn btn-light btn-sm">Back</a>
                    </div>

                    <div class="edit-card-body">


                        <?php if ($message !== ''): ?>
                            <div class="alert alert-<?= $messageType; ?>">
                                <?= htmlspecialchars($message); ?>
                            </div>
                        <?php endif; ?>

                        <?php $fieldErrors = $result['errors'] ?? []; ?>
                        <form method="POST" action="editcategory.php?id=<?= $editId; ?>" id="editCategoryForm">
                            <input type="hidden" name="id" value="<?= $editId; ?>">

                            <div class="mb-3">
                                <label for="name" class="form-label">Category Name</label>
                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    class="form-control"
                                    placeholder="Enter category name"
                                    value="<?= htmlspecialchars($name); ?>">
                                <div class="text-danger" id="categoryError"><?= htmlspecialchars($fieldErrors['name'] ?? ($fieldErrors['general'] ?? '')); ?></div>
                            </div>

                            <div class="mb-4">
                                <label for="status" class="form-label">Status</label>
                                <select id="status" name="status" class="form-select">
                                    <option value="draft" <?= $status === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                    <option value="published" <?= $status === 'published' ? 'selected' : ''; ?>>Published</option>
                                </select>
                                <div class="text-danger" id="statusError"><?= htmlspecialchars($fieldErrors['status'] ?? ''); ?></div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Update Category</button>
                                <a href="/blog/admin/category/allcategory.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>




        <?php include __DIR__ . '/../../include/footer.php'; ?>
    </body>

    </html>