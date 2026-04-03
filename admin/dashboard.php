<?php
session_start();
require_once __DIR__ . '/../config/blog/blogcrud.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /blog/config/login&signup/login.php');
    exit;
}

$isLoggedIn = isset($_SESSION['user_id']);
$postObj = new Post();
$totalPosts = $postObj->countAll();
$hasPosts = $totalPosts > 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Dashboard</title>

    <?php include __DIR__ . '/../include/header.php'; ?>

    <style>
        body {
            background: #f1f5f9;
        }

        .navbar {
            background: linear-gradient(180deg, #1e293b, #0f172a);
        }
    
        .content {
            margin-left: 250px;
            padding: 32px;
            min-height: 100vh;
        }

        .hero {
            position: relative;
            overflow: hidden;
            margin-bottom: 40px;
            background:
                radial-gradient(circle at top left, rgba(255, 255, 255, 0.22), transparent 34%),
                linear-gradient(135deg, #0f172a 0%, #1d4ed8 52%, #f97316 100%);
            color: white;
            padding: 56px 48px;
            border-radius: 24px;
            box-shadow: 0 24px 50px rgba(15, 23, 42, 0.22);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .hero::before,
        .hero::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }

        .hero::before {
            width: 220px;
            height: 220px;
            top: -90px;
            right: -40px;
        }

        .hero::after {
            width: 150px;
            height: 150px;
            bottom: -55px;
            left: 35%;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 32px;
            flex-wrap: wrap;
        }

        .hero-copy {
            max-width: 640px;
        }

        .hero-eyebrow {
            display: inline-block;
            margin-bottom: 14px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            font-size: 0.84rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .hero h1 {
            margin-bottom: 16px;
            font-size: clamp(2.4rem, 4vw, 4.2rem);
            font-weight: 800;
            line-height: 1.05;
        }

        .hero p {
            margin-bottom: 24px;
            max-width: 560px;
            font-size: 1.08rem;
            color: rgba(255, 255, 255, 0.84);
        }

        .hero-actions {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }

        .hero-actions .btn {
            border-radius: 999px;
            padding: 11px 22px;
            font-weight: 600;
        }

        .hero-stat {
            min-width: 230px;
            padding: 24px;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(8px);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.12);
        }

        .hero-stat-label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.8rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.72);
        }

        .hero-stat-value {
            display: block;
            margin-bottom: 8px;
            font-size: 2.5rem;
            font-weight: 800;
            line-height: 1;
        }

        .hero-stat small {
            color: rgba(255, 255, 255, 0.78);
        }

        .section-title {
            margin-bottom: 24px;
            font-size: 2.2rem;
            font-weight: 700;
            color: #0f172a;
        }

        .overview-card {
            border: none;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
            transition: 0.3s;
            height: 100%;
            background: #ffffff;
        }

        .overview-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 18px 35px rgba(15, 23, 42, 0.14);
        }

        .overview-card-top {
            height: 10px;
            background: linear-gradient(135deg, #0f172a, #1d4ed8);
        }

        .overview-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            background: #e0e7ff;
            color: #3730a3;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .overview-value {
            font-size: 2rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
        }

        .card {
            border: none;
            border-radius: 16px;
            transition: 0.3s;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 18px 35px rgba(15, 23, 42, 0.14);
        }

        .empty-dashboard {
            margin-bottom: 28px;
            padding: 18px 20px;
            border-radius: 16px;
            background: #fff7ed;
            border: 1px solid #fdba74;
            color: #9a3412;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
        }

        .btn-primary {
            background: #2563eb;
            border: none;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .footer {
            margin-left: 250px;
            padding: 20px;
            text-align: center;
            background: linear-gradient(180deg, #1e293b, #0f172a);
            color: white;
        }

        @media (max-width: 991px) {
            .content,
            .footer {
                margin-left: 0;
            }
        }

        @media (max-width: 768px) {
            .content {
                padding: 20px;
            }

            .hero {
                padding: 34px 24px;
                border-radius: 18px;
            }

            .hero-content {
                align-items: flex-start;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <div class="ms-auto">
                <?php if ($isLoggedIn): ?>
                    <span class="text-white me-3">
                        Welcome, <?= htmlspecialchars($_SESSION['user_name']); ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <?php include __DIR__ . '/adminpanel.php'; ?>
 <!-- main body content  -->
    <div class="content">
        <div class="hero">
            <div class="hero-content">
                <div class="hero-copy">
                    <span class="hero-eyebrow">Car Info Platform</span>
                    <h1>Turn ideas into posts people actually want to read.</h1>
                    <p>Build your space for tutorials, opinions, and updates. Guests can explore your content, and logged-in writers can keep publishing fresh Articles.</p>
                    <div class="hero-actions">
                        <a href="/blog/admin/post/allpost.php" class="btn btn-light text-dark">Explore Posts</a>
                        <?php if ($isLoggedIn): ?>
                            <a href="/blog/admin/post/addblog.php" class="btn btn-outline-light">Create New Post</a>
                        <?php else: ?>
                            <a href="/blog/config/login&signup/login.php" class="btn btn-outline-light">Login to Write</a>
                        <?php endif; ?>
                    </div>
                </div>

           
            </div>
        </div>

        <?php if (!$hasPosts): ?>
         <div class="empty-dashboard">
                <strong>No data found.</strong>
                The blog database does not have any posts yet.
            </div>
        <?php endif; ?> 

        <section>
            <h2 class="section-title">Dashboard Overview</h2>
            <div class="row g-4">
                <div class="col-md-6 col-xl-4">
                    <div class="overview-card">
                        <div class="overview-card-top"></div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="overview-badge">Posts</span>
                                <small class="text-muted">launch and pending</small>
                            </div>
                            <div class="overview-value mb-3"><?= $totalPosts; ?></div>
                            <p class="text-muted mb-0">Total blog posts currently available in your project.</p>
                        </div>
                    </div>

                    
                </div>

                <div class="col-md-6 col-xl-4">
                    <div class="overview-card">
                        <div class="overview-card-top"></div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="overview-badge">Access</span>
                                <small class="text-muted"><?= $isLoggedIn ? 'Admin' : 'Guest'; ?></small>
                            </div>
                            <div class="overview-value mb-3"><?= $isLoggedIn ? 'Editor' : 'Viewer'; ?></div>
                            <p class="text-muted mb-0">Your current dashboard access level based on login status.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-4">
                    <div class="overview-card">
                        <div class="overview-card-top"></div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="overview-badge">Next Step</span>
                                <small class="text-muted">Quick Action</small>
                            </div>
                            <div class="overview-value mb-3"><?= $isLoggedIn ? 'Create' : 'Login'; ?></div>
                            <p class="text-muted mb-3">
                                <?= $isLoggedIn ? 'Start writing a fresh post or manage your current content.' : 'Sign in to create posts and manage categories.'; ?>
                            </p>
                            <a href="<?= $isLoggedIn ? '/blog/admin/post/addblog.php' : '/blog/config/login&signup/login.php'; ?>" class="btn btn-primary">
                                <?= $isLoggedIn ? 'Create Post' : 'Login Now'; ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    
    <?php include __DIR__ . '/../include/footer.php'; ?>
</body>

</html>
