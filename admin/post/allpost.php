<?php
session_start();
require_once __DIR__ . '/../../config/blog/blogcrud.php';

$isLoggedIn = isset($_SESSION['user_id']);

$postObj = new Post();

$message = '';
$messageType = '';
//for delete 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id']) && isset($_SESSION['user_id'])) {
    $message = $postObj->delete($_POST['delete_id']);
    $messageType = (stripos($message, 'successfully') !== false) ? 'success' : 'danger';
}

$posts = $postObj->getAll();



// DataTables AJAX response (returns JSON with posts).
if (isset($_GET['ajax']) && (string) $_GET['ajax'] === '1') {
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';

    if ($status !== '') {
        $posts = array_values(array_filter($posts, function ($post) use ($status) {
            return isset($post['status']) && strcasecmp((string) $post['status'], (string) $status) === 0;
        }));
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['data' => $posts]);
    exit;
}
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Posts</title>
    <?php include __DIR__ . '/../../include/header.php'; ?>
    <link href="https://cdn.datatables.net/2.3.7/css/dataTables.bootstrap5.css" rel="stylesheet">
    <style>
        body {
            background: #f1f5f9;
        }

        .page-wrap {
            min-height: 100vh;
            padding: 32px;
        }

        .page-wrap.with-panel {
            margin-left: 250px;
        }

        .page-header {
            background: linear-gradient(135deg, #0f172a, #1d4ed8);
            color: white;
            border-radius: 24px;
            padding: 36px;
            margin-bottom: 32px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.16);
        }

        .post-card {
            border: none;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
            transition: 0.3s;
            height: 100%;
        }

        .post-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 18px 35px rgba(15, 23, 42, 0.14);
        }

        .post-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            background: linear-gradient(135deg, #dbeafe, #e2e8f0);
        }

        .meta-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            background: #e0e7ff;
            color: #3730a3;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .empty-state {
            background: white;
            border-radius: 20px;
            padding: 48px 24px;
            text-align: center;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
        }

        @media (max-width: 991px) {
            .page-wrap,
            .page-wrap.with-panel {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>
    <?php if ($isLoggedIn): ?>
        <?php include __DIR__ . '/../adminpanel.php'; ?>
    <?php endif; ?>

    <div class="page-wrap <?= $isLoggedIn ? 'with-panel' : ''; ?>">
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="mb-2">All Blog Posts</h1>
                <p class="mb-0 text-white-50">Browse every post stored in your database in one place.</p>
            </div>
            <?php if ($isLoggedIn): ?>
                <a href="/blog/admin/post/addblog.php" class="btn btn-light text-dark">Add Post</a>
            <?php else: ?>
                <a href="/blog/config/login&signup/login.php" class="btn btn-light text-dark">Login for Admin Access</a>
            <?php endif; ?>
        </div>

        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 2000;">
            <?php if ($message !== ''): ?>
                <div id="postToast" class="toast align-items-center text-bg-<?= $messageType === 'success' ? 'success' : 'danger'; ?> border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="2500">
                    <div class="d-flex">
                        <div class="toast-body">
                            <?= htmlspecialchars($message); ?>
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if (empty($posts)): ?>
            <div class="empty-state">
                <h3 class="mb-3">No posts found</h3>
                <p class="text-muted mb-4">Create your first blog post to see it here.</p>
                <?php if ($isLoggedIn): ?>
                    <a href="/blog/admin/post/addblog.php" class="btn btn-primary">Create Post</a>
                <?php endif; ?>
            </div>
        <?php else: ?>

            <!-- for status filter -->
            <div class="d-flex align-items-center gap-2 mb-3">
                <label for="statusFilter" class="mb-0 text-secondary" style="font-weight: 600;">Status:</label>
                <div class="position-relative" style="width: 200px;">
                    <select id="statusFilter" class="form-select" style="padding-right: 42px;">
                        <option value="">All</option>
                        <option value="Published">Published</option>
                        <option value="Unpublished">Unpublished</option>
                    </select>
                    <button type="button" class="btn-clear-post-filter btn btn-sm btn-outline-secondary position-absolute" style="right: 8px; top: 50%; transform: translateY(-50%); display: none;">✖️</button>
                </div>
            </div>

            <div id="postsGrid" class="row g-4" style="display: none;">
                <?php foreach ($posts as $post): ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="card post-card">
                            <?php if (!empty($post['image'])): ?>
                                <img src="/blog/assets/posts/<?= htmlspecialchars(basename($post['image'])); ?>" alt="<?= htmlspecialchars($post['title']); ?>" class="post-image">
                            <?php else: ?>
                                <div class="post-image d-flex align-items-center justify-content-center text-muted fw-bold">No Image</div>
                            <?php endif; ?>

                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="meta-badge"><?= htmlspecialchars($post['category']); ?></span>
                                    <small class="text-muted"><?= htmlspecialchars($post['status']); ?></small>
                                </div>

                                <h4 class="card-title mb-3"><?= htmlspecialchars($post['title']); ?></h4>
                                <p class="card-text text-muted mb-3"><?= htmlspecialchars($post['sort des']); ?></p>

                                <div class="small text-secondary mb-2">By: <?= htmlspecialchars($post['createdby']); ?></div>
                                <div class="small text-secondary mb-3">Posted: <?= htmlspecialchars($post['posted']); ?></div>

                                <?php if ($isLoggedIn): ?>
                                    <div class="d-flex gap-2">
                                        <a href="/blog/admin/post/editpost.php?id=<?= (int) $post['id']; ?>" class="btn btn-primary btn-sm">Edit Post</a>

                                        <form method="POST" class="delete-post-form">
                                            <input type="hidden" name="delete_id" value="<?= (int) $post['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Delete Post</button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div id="postsTable" class="table-responsive" style="display:block; margin-top: 20px;">
                <table id="postsDataTable" class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Posted</th>
                            <?php if ($isLoggedIn): ?>
                                <th>Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $index => $post): ?>
                            <tr>
                                <td><?= $index + 1; ?></td>
                                <td>
                                    <?php if (!empty($post['image'])): ?>
                                        <img src="/blog/assets/posts/<?= htmlspecialchars(basename($post['image'])); ?>" alt="<?= htmlspecialchars($post['title']); ?>" style="width: 80px; height: 50px; object-fit: cover; border-radius: 8px;" />
                                    <?php else: ?>
                                        <span class="text-muted">No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($post['title']); ?></td>
                                <td><?= htmlspecialchars($post['category']); ?></td>
                                <td><?= htmlspecialchars($post['status']); ?></td>
                                <td><?= htmlspecialchars($post['createdby']); ?></td>
                                <td><?= htmlspecialchars($post['posted']); ?></td>
                                <?php if ($isLoggedIn): ?>
                                    <td>
                                        <a href="/blog/admin/post/editpost.php?id=<?= (int) $post['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                        <form method="POST" class="d-inline delete-post-form" style="display:inline-block;">
                                            <input type="hidden" name="delete_id" value="<?= (int) $post['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/../../include/footer.php'; ?>
</body>

</html>
