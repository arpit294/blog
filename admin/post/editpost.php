<?php
session_start();
require_once __DIR__ . '/../../config/blog/blogcrud.php';
require_once __DIR__ . '/../../config/category/categoryCrud.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /blog/config/login&signup/login.php');
    exit;
}

$categoryObj = new category();
$categories = array_filter($categoryObj->getAll(), function ($cat) {
    return isset($cat['status']) && strtolower(trim($cat['status'])) === 'launch';
});

$postObj = new Post();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$postData = $postObj->getById($id);

if (!$postData) {
    die('Post not found.');
}

$message = '';
$messageType = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $postObj->update($id, $_POST, $_FILES['image'] ?? []);
    $message = is_array($result) ? ($result['message'] ?? ($result['errors']['general'] ?? 'Validation failed.')) : (string) $result;
    $messageType = is_array($result) ? (($result['success'] ?? false) ? 'success' : 'danger') : ((stripos($message, 'successfully') !== false) ? 'success' : 'danger');
    $errors = is_array($result) ? ($result['errors'] ?? []) : [];

    if ($messageType === 'success') {
        header('Location: /blog/admin/post/allpost.php');
        exit;
    } else {
        $postData['title'] = $_POST['title'] ?? $postData['title'];
        $postData['sort des'] = $_POST['short_description'] ?? $postData['sort des'];
        $postData['content'] = $_POST['content'] ?? $postData['content'];
        $postData['category'] = $_POST['category'] ?? $postData['category'];
        $postData['status'] = (($_POST['status'] ?? 'draft') === 'published') ? 'Published' : 'Unpublished';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Blog Post</title>
    <?php include __DIR__ . '/../../include/header.php'; ?>

    <!-- Summernote -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.css" rel="stylesheet">

    <!-- Dropify -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/css/dropify.css" integrity="sha512-In/+MILhf6UMDJU4ZhDL0R0fEpsp4D3Le23m6+ujDWXwl3whwpucJG1PEmI3B07nyJx+875ccs+yX2CqQJUxUw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/js/dropify.min.js" integrity="sha512-8QFTrG0oeOiyWo/VM9Y8kgxdlCryqhIxVeRpWSezdRRAvarxVtwLnGroJgnVW9/XBRduxO/z1GblzPrMQoeuew==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #e0e7ff, #f8fafc);
        }

        .page-wrap {
            padding: 48px 16px;
        }

        .page-wrap.with-panel {
            margin-left: 250px !important;
            margin-right: 20px;
            max-width: calc(100% - 270px);
            width: auto;
        }

        @media (max-width: 991px) {
            .page-wrap.with-panel {
                margin-left: 0 !important;
                margin-right: auto;
                max-width: 850px;
            }
        }

        .form-card {
            max-width: 900px;
            margin: 0 auto;
            border: 0;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.12);
        }

        .form-header {
            background: linear-gradient(135deg, #1e293b, #0f172a);
            color: #fff;
            padding: 28px 32px;
        }

        .form-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .form-body {
            background: #ffffff;
            padding: 32px;
        }

        .custom-card {
            background: #fff;
            border-radius: 16px;
        }

        .form-label {
            font-weight: 600;
            color: #334155;
            font-size: 0.95rem;
            margin-bottom: 8px;
        }

        .form-control,
        .form-select {
            border-radius: 12px;
            padding: 12px 14px;
            border: 1px solid #cbd5e1;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.15);
            outline: none;
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        textarea.form-control {
            min-height: 130px;
            resize: vertical;
        }

        .submit-btn {
            background: linear-gradient(135deg, #1e293b, #3b82f6);
            border: none;
            padding: 12px 32px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
        }

        .back-link {
            color: #cbd5e1;
            text-decoration: none;
            font-size: 0.9rem;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .back-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.1);
        }

        .preview-image {
            width: 100%;
            max-width: 220px;
            border-radius: 16px;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.12);
        }

        /* Dropify customization */
        .dropify-wrapper {
            border-radius: 12px;
            border: 2px dashed #cbd5e1;
            transition: all 0.3s ease;
        }

        .dropify-wrapper:hover {
            border-color: #6366f1;
        }

        .dropify-message {
            font-size: 0.9rem;
        }

        .dropify-preview .dropify-infos .dropify-filename {
            font-size: 0.85rem;
        }

        /* Select2 customization */
        .select2-container--default .select2-selection--single {
            border-radius: 12px;
            padding: 8px 14px;
            border: 1px solid #cbd5e1;
            height: 46px;
        }

        .select2-container--default .select2-selection--single:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.15);
        }

        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #6366f1;
        }

        /* Toast customization */
        .toast {
            border-radius: 12px;
        }

        /* Input validation states */
        .form-control.is-invalid,
        .form-select.is-invalid {
            border-color: #ef4444;
        }

        .form-control.is-valid,
        .form-select.is-valid {
            border-color: #22c55e;
        }
    </style>

</head>

<body>
    <?php if (isset($_SESSION['user_id'])): ?>
        <?php include __DIR__ . '/../adminpanel.php'; ?>
    <?php endif; ?>

    <div class="container page-wrap <?= isset($_SESSION['user_id']) ? 'with-panel' : ''; ?>">
        <div class="card form-card">
            <div class="form-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="mb-1">Edit Post</h2>
                    <p class="mb-0 text-white-50">Update your blog post details and save the changes.</p>
                </div>
                <a href="allpost.php" class="back-link">Back to Posts</a>
            </div>

            <div class="form-body custom-card">
                <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 2000;">
                    <?php if ($message !== ''): ?>
                        <div id="editPostToast" class="toast align-items-center text-bg-<?= $messageType === 'success' ? 'success' : 'danger'; ?> border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="2500">
                            <div class="d-flex">
                                <div class="toast-body">
                                    <?= htmlspecialchars($message); ?>
                                </div>
                                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($message !== ''):  
                endif; ?>

                <form method="POST" enctype="multipart/form-data" class="row g-4">
                    <div class="col-12">
                        <label for="title" class="form-label">Post Title</label>
                        <input type="text" id="title" name="title" class="form-control" value="<?= htmlspecialchars($postData['title']); ?>">
                        <span class="text-danger d-block mt-1" id="postTitleError"><?= htmlspecialchars($errors['title'] ?? ''); ?></span>
                    </div>

                    <div class="col-12">
                        <label for="short_description" class="form-label">Short Description</label>
                        <textarea id="short_description" name="short_description" class="form-control"><?= htmlspecialchars($postData['sort des']); ?></textarea>
                        <span class="text-danger d-block mt-1" id="postShortDescriptionError"><?= htmlspecialchars($errors['short_description'] ?? ''); ?></span>
                    </div>

                    <div class="col-12">
                        <label for="content" class="form-label">Content</label>
                        <textarea id="content" name="content" class="form-control"><?= htmlspecialchars($postData['content']); ?></textarea>
                        <span class="text-danger d-block mt-1" id="postContentError"><?= htmlspecialchars($errors['content'] ?? ''); ?></span>
                    </div>

                    <div class="col-md-6">
                        <label for="image" class="form-label">Change Image</label>
                        <input type="file" id="image" name="image" class="dropify form-control">
                        <span class="text-danger d-block mt-1" id="postImageError"><?= htmlspecialchars($errors['image'] ?? ''); ?></span>
                    </div>

                    <div class="col-md-6">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-select">
                            <option value="Unpublished" <?= ($postData['status'] === 'Unpublished') ? 'selected' : ''; ?>>Unpublished</option>
                            <option value="published" <?= ($postData['status'] === 'Published') ? 'selected' : ''; ?>>Published</option>
                        </select>
                        <span class="text-danger d-block mt-1" id="postStatusError"><?= htmlspecialchars($errors['status'] ?? ''); ?></span>
                    </div>

                    <div class="col-md-6">
                        <label for="category" class="form-label">Category</label>
                        <select id="category" name="category" class="form-select">
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $cat): ?>
                                <?php $selected = (isset($postData['category']) && $postData['category'] === $cat['name']) ? 'selected' : ''; ?>
                                <option value="<?= htmlspecialchars($cat['name']); ?>" <?= $selected; ?>><?= htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="text-danger d-block mt-1" id="postCategoryError"><?= htmlspecialchars($errors['category'] ?? ''); ?></span>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Current Image</label>
                        <div>
                            <?php if (!empty($postData['image'])): ?>
                                <img src="/blog/assets/posts/<?= htmlspecialchars(basename($postData['image'])); ?>" alt="Current image" class="preview-image">
                            <?php else: ?>
                                <div class="text-muted">No image uploaded</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-12 d-flex justify-content-end">
                        <button type="submit" class="btn submit-btn text-white">Update Post</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../../include/footer.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var toastEl = document.getElementById('editPostToast');
            if (toastEl) {
                var bsToast = new bootstrap.Toast(toastEl);
                bsToast.show();
            }
        });
    </script>

</body>

</html>
