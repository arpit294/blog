<?php
session_start();
require_once __DIR__ . '/config/blog/blogcrud.php';
require_once __DIR__ . '/config/category/categoryCrud.php';

$postObj = new Post();
$categoryObj = new category();

// Get slug from URL
$slug = $_GET['slug'] ?? '';

// Find post by slug
$allPosts = $postObj->getAll();
$post = null;

foreach ($allPosts as $p) {
    if ($p['slug'] === $slug) {
        $post = $p;
        break;
    }
}

// If post not found, redirect
if (!$post) {
    header("Location: homepost.php");
    exit();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? 'Guest';

// Get related posts (same category, excluding current post)
$relatedPosts = array_filter($allPosts, function($p) use ($post) {
    return $p['category'] === $post['category'] 
        && $p['slug'] !== $post['slug'] 
        && strtolower($p['status']) === 'published';
});
$relatedPosts = array_slice($relatedPosts, 0, 3);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']); ?> | Tech Blog</title>
    <meta name="description" content="<?= htmlspecialchars($post['sort des'] ?? substr($post['content'], 0, 160)); ?>">
    <?php include __DIR__ . '/include/header.php'; ?>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .single-post-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .post-card {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
        }

        .post-card-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }

        .post-card-body {
            padding: 40px;
        }

        .post-category-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 999px;
            background: linear-gradient(135deg, #1d4ed8, #3b82f6);
            color: #fff;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 16px;
        }

        .post-title {
            font-size: 2rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 20px;
            line-height: 1.3;
        }

        .post-meta {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 16px 0;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .post-meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            font-size: 0.9rem;
        }

        .post-author {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .post-author-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1d4ed8, #f97316);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 1rem;
        }

        .post-short-description {
            font-size: 1.15rem;
            color: #64748b;
            font-style: italic;
            margin-bottom: 24px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 12px;
            border-left: 4px solid #3b82f6;
            line-height: 1.7;
        }

        .post-content {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #334155;
        }

        .post-content h2 {
            font-size: 1.6rem;
            font-weight: 700;
            color: #0f172a;
            margin: 28px 0 16px;
        }

        .post-content h3 {
            font-size: 1.3rem;
            font-weight: 600;
            color: #0f172a;
            margin: 22px 0 12px;
        }

        .post-content p {
            margin-bottom: 20px;
        }

        .post-content ul, .post-content ol {
            margin-bottom: 20px;
            padding-left: 20px;
        }

        .post-content li {
            margin-bottom: 8px;
        }

        .post-content blockquote {
            border-left: 4px solid #3b82f6;
            padding-left: 20px;
            margin: 24px 0;
            font-style: italic;
            color: #64748b;
        }

        .post-content img {
            max-width: 100%;
            border-radius: 10px;
            margin: 16px 0;
        }

        .post-content pre {
            background: #1e293b;
            color: #e2e8f0;
            padding: 16px;
            border-radius: 10px;
            overflow-x: auto;
            margin: 16px 0;
        }

        .post-content code {
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.9em;
        }

        .post-content pre code {
            background: transparent;
            padding: 0;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 999px;
            background: linear-gradient(135deg, #0f172a, #1d4ed8);
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            margin-bottom: 24px;
        }

        .back-btn:hover {
            background: linear-gradient(135deg, #1d4ed8, #3b82f6);
            color: #fff;
            transform: translateX(-4px);
        }

        /* Related Posts Section */
        .related-posts-section {
            margin-top: 40px;
        }

        .related-posts-section h3 {
            font-size: 1.4rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 20px;
        }

        .related-posts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .related-post-card {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
            text-decoration: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .related-post-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.12);
        }

        .related-post-img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        .related-post-body {
            padding: 16px;
        }

        .related-post-body h5 {
            font-size: 0.95rem;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .related-post-body span {
            font-size: 0.8rem;
            color: #94a3b8;
        }

        @media (max-width: 768px) {
            .single-post-container {
                padding: 20px 16px;
            }

            .post-card-image {
                height: 250px;
            }

            .post-card-body {
                padding: 24px;
            }

            .post-title {
                font-size: 1.5rem;
            }

            .post-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .related-posts-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php if ($isLoggedIn): ?>
        <?php include __DIR__ . '/admin/adminpanel.php'; ?>
    <?php endif; ?>

    <div class="page-wrap <?= $isLoggedIn ? 'with-panel' : ''; ?>">
        <div class="single-post-container">
            <!-- Back Button -->
            <a href="homepost.php" class="back-btn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Back to Posts
            </a>

            <!-- Single Post Card -->
            <div class="post-card">
                <!-- Featured Image -->
                <?php if (!empty($post['image'])): ?>
                    <img src="<?= htmlspecialchars($post['image']); ?>" 
                         class="post-card-image" 
                         alt="<?= htmlspecialchars($post['title']); ?>"
                         onerror="this.src='https://via.placeholder.com/900x400?text=No+Image'">
                <?php else: ?>
                    <img src="https://via.placeholder.com/900x400?text=No+Image" 
                         class="post-card-image" 
                         alt="<?= htmlspecialchars($post['title']); ?>">
                <?php endif; ?>

                <div class="post-card-body">
                    <!-- Category -->
                    <span class="post-category-badge"><?= htmlspecialchars($post['category']); ?></span>

                    <!-- Title -->
                    <h1 class="post-title"><?= htmlspecialchars($post['title']); ?></h1>

                    <!-- Meta Information -->
                    <div class="post-meta">
                        <div class="post-meta-item post-author">
                            <div class="post-author-avatar">
                                <?= strtoupper(substr($post['createdby'] ?? 'A', 0, 1)); ?>
                            </div>
                            <span><?= htmlspecialchars($post['createdby'] ?? 'Admin'); ?></span>
                        </div>
                        <div class="post-meta-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            <span><?= date('F d, Y', strtotime($post['posted'])); ?></span>
                        </div>
                        <div class="post-meta-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            <span><?= date('h:i A', strtotime($post['posted'])); ?></span>
                        </div>
                        <div class="post-meta-item">
                            <span class="badge <?= $post['status'] === 'Published' ? 'bg-success' : 'bg-warning'; ?>">
                                <?= htmlspecialchars(ucfirst($post['status'])); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Short Description -->
                    <?php if (!empty($post['sort des'])): ?>
                        <div class="post-short-description">
                            <?= nl2br(htmlspecialchars($post['sort des'])); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Full Content -->
                    <div class="post-content">
                        <?= nl2br(htmlspecialchars($post['content'])); ?>
                    </div>
                </div>
            </div>

            <!-- Related Posts -->
            <?php if (!empty($relatedPosts)): ?>
                <div class="related-posts-section">
                    <h3>Related Posts</h3>
                    <div class="related-posts-grid">
                        <?php foreach ($relatedPosts as $related): ?>
                            <a href="singlepost.php?slug=<?= urlencode($related['slug']); ?>" class="related-post-card">
                                <?php if (!empty($related['image'])): ?>
                                    <img src="<?= htmlspecialchars($related['image']); ?>" 
                                         class="related-post-img" 
                                         alt="<?= htmlspecialchars($related['title']); ?>"
                                         onerror="this.src='https://via.placeholder.com/250x150?text=No+Image'">
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/250x150?text=No+Image" 
                                         class="related-post-img" 
                                         alt="<?= htmlspecialchars($related['title']); ?>">
                                <?php endif; ?>
                                <div class="related-post-body">
                                    <h5><?= htmlspecialchars($related['title']); ?></h5>
                                    <span><?= date('M d, Y', strtotime($related['posted'])); ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include __DIR__ . '/include/footer.php'; ?>
</body>

</html>