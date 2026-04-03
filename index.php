<?php
session_start();
require_once __DIR__ . '/config/blog/blogcrud.php';
require_once __DIR__ . '/config/category/categoryCrud.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? 'Guest';
$toast = $_SESSION['auth_toast'] ?? null;
unset($_SESSION['auth_toast']);

$postObj = new Post();
$categoryObj = new category();

$totalPosts = $postObj->countAll();
$totalCategories = count($categoryObj->getAll());

$dashboardLink = $isLoggedIn
    ? '/blog/admin/dashboard.php'
    : '/blog/config/login&signup/login.php';
$postsLink = '/blog/homepost.php';
$categoriesLink = $isLoggedIn
    ? '/blog/admin/category/allcategory.php'
    : '/blog/config/login&signup/login.php';
$primaryLink = $isLoggedIn
    ? $dashboardLink
    : '/blog/config/login&signup/login.php';
$primaryLabel = $isLoggedIn ? 'Open Dashboard' : 'Login to Continue';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | Tech Blog</title>
    <?php include __DIR__ . '/include/header.php'; ?>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>


    <?php
    $toastType = $toast['type'] ?? 'success';
    $toastClass = match ($toastType) {
        'danger', 'error' => 'text-bg-danger',
        'warning' => 'text-bg-warning',
        'info' => 'text-bg-info',
        default => 'text-bg-success',
    };
    ?>
        <!-- for toaster -->
    <?php if ($toast && !empty($toast['message'])): ?>
        <div class="toast-container position-fixed top-0 end-0 p-3">
            <div
                id="authToast"
                class="toast <?= $toastClass; ?> border-0"
                role="alert"
                aria-live="assertive"
                aria-atomic="true"
                data-bs-delay="2500">
                <div class="d-flex">
                    <div class="toast-body"><?= htmlspecialchars($toast['message']); ?></div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    

<div class="page-wrap">
        <section class="hero">
            <div class="hero-content">
                <div class="hero-copy">
                    <span class="hero-badge"> Car Info</span>
                    <h1>Build, manage, and grow your blog from one clean workspace.</h1>
                    <p>
                        This is a simple home page for your project. You can explore posts, manage categories,
                        and jump into the admin dashboard whenever you're ready.
                    </p>
                    <div class="hero-actions">
                        <a href="<?= $primaryLink; ?>" class="btn btn-light text-dark"><?= $primaryLabel; ?></a>
                        <a href="<?= $postsLink; ?>" class="btn btn-outline-light">Browse Posts</a>
                    </div>
                </div>

                <div class="hero-panel">
                    <small>Welcome</small>
                    <strong><?= htmlspecialchars($userName); ?></strong>
                    <div>Posts: <?= $totalPosts; ?></div>
                    <div>Categories: <?= $totalCategories; ?></div>
                    <div>Status: <?= $isLoggedIn ? 'Logged In' : 'Guest'; ?></div>
                </div>
            </div>
        </section>

        <h2 class="section-title">Quick Overview</h2>
        <div class="row g-4">
            <div class="col-md-6 col-xl-4">
                <div class="card info-card">
                    <div class="top-line"></div>
                    <div class="card-body">
                        <span class="label-pill">Posts</span>
                        <div class="stat-number"><?= $totalPosts; ?></div>
                        <p class="text-muted mb-0">Total blog posts currently available in your project.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-4">
                <div class="card info-card">
                    <div class="top-line"></div>
                    <div class="card-body">
                        <span class="label-pill">Categories</span>
                        <div class="stat-number"><?= $totalCategories; ?></div>
                        <p class="text-muted mb-0">Organize your content with clear and reusable categories.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-4">
                <div class="card info-card">
                    <div class="top-line"></div>
                    <div class="card-body">
                        <span class="label-pill">Access</span>
                        <div class="stat-number"><?= $isLoggedIn ? 'Admin' : 'Guest'; ?></div>
                        <p class="text-muted mb-0">Your current role decides whether you can manage content or just explore it.</p>
                    </div>
                </div>
            </div>
        </div>

        <h2 class="section-title">Quick Links</h2>
        <div class="quick-links">
            <a href="<?= $dashboardLink; ?>" class="quick-link">
                <h5>Dashboard</h5>
                <p><?= $isLoggedIn ? 'Open the admin dashboard and see your project summary.' : 'Log in to open the admin dashboard.'; ?></p>
            </a>

            <a href="<?= $postsLink; ?>" class="quick-link">
                <h5>All Posts</h5>
                <p><?= $isLoggedIn ? 'View posts, edit content, and keep your blog updated.' : 'Browse all posts without login. Sign in only when you want to manage them.'; ?></p>
            </a>

            <a href="<?= $categoriesLink; ?>" class="quick-link">
                <h5>Categories</h5>
                <p><?= $isLoggedIn ? 'Manage blog categories and keep your content well organized.' : 'Log in to manage blog categories.'; ?></p>
            </a>
        </div>
    </div>

    <?php include __DIR__ . '/include/footer.php'; ?>
    <script>
        const authToast = document.getElementById("authToast");
        if (authToast) {
            bootstrap.Toast.getOrCreateInstance(authToast).show();
        }
    </script>
</body>

</html>
