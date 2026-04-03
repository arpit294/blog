<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: /blog/config/login&signup/login.php');
    exit;
}

$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$isHomePage = in_array($currentPage, ['home.php', 'index.php'], true);
$isDashboardPage = $currentPage === 'dashboard.php';
$isPostsPage = in_array($currentPage, ['allpost.php', 'addblog.php', 'editpost.php'], true);
$isCategoryPage = in_array($currentPage, ['allcategory.php', 'addcategory.php', 'editcategory.php'], true);
?>

<style>
    .sidebar {
        position: fixed;
        width: 250px;
        height: 100vh;
        top: 0;
        left: 0;
        background: linear-gradient(180deg, #1e293b, #0f172a);
        color: #e2e8f0;
        padding-top: 20px;
        z-index: 1000;
        overflow-y: auto;
    }

    .sidebar a {
        display: block;
        padding: 12px 20px;
        color: #cbd5f5;
        text-decoration: none;
        border-radius: 6px;
        margin: 5px 10px;
        transition: 0.3s;
    }

    .sidebar a:hover {
        background: #3b82f6;
        color: white;
    }

    .sidebar a.active {
        background: #2563eb;
        color: white;
    }

    @media (max-width: 991px) {
        .sidebar {
            position: static;
            width: 100%;
            height: auto;
            padding-bottom: 16px;
        }
    }
</style>

<div class="sidebar">
    <h4 class="text-center">Admin Panel</h4>

    <a href="/blog/index.php" class="<?= $isHomePage ? 'active' : ''; ?>">Home</a>
    <a href="/blog/admin/dashboard.php" class="<?= $isDashboardPage ? 'active' : ''; ?>">Dashboard</a>
    <a href="/blog/admin/post/allpost.php" class="<?= $isPostsPage ? 'active' : ''; ?>">Posts</a>
    <a href="/blog/admin/category/allcategory.php" class="<?= $isCategoryPage ? 'active' : ''; ?>">Categories</a>

    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
        <a href="#">Admin Panel</a>
    <?php endif; ?>

    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="/blog/config/login&signup/leadcontroller.php?action=logout" class="text-danger">Logout</a>
    <?php else: ?>
        <a href="/blog/config/login&signup/login.php">Login</a>
    <?php endif; ?>
</div>
