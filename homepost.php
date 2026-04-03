<?php
session_start();
require_once __DIR__ . '/config/blog/blogcrud.php';
require_once __DIR__ . '/config/category/categoryCrud.php';

$postObj = new Post();
$categoryObj = new category();

// Get all published posts
$posts = $postObj->getAll();

// Filter only published posts for public view
$publishedPosts = array_filter($posts, function ($post) {
    return strtolower($post['status']) === 'published';
});

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? 'Guest';

// Get categories for filter
$categories = $categoryObj->getAll();

// Handle category filter
$selectedCategory = $_GET['category'] ?? '';
if (!empty($selectedCategory)) {
    $publishedPosts = array_filter($publishedPosts, function ($post) use ($selectedCategory) {
        return $post['category'] === $selectedCategory;
    });
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Posts | Tech Blog</title>
    <?php include __DIR__ . '/include/header.php'; ?>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .post-card {
            height: 100%;
            border: none;
            border-radius: 20px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .post-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.15);
        }

        .post-card-img {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }

        .post-card-body {
            padding: 20px;
            display: flex;
            flex-direction: column;
            height: calc(100% - 200px);
        }

        .post-card-category {
            display: inline-block;
            margin-bottom: 10px;
            padding: 4px 12px;
            border-radius: 999px;
            background: linear-gradient(135deg, #1d4ed8, #3b82f6);
            color: #fff;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            width: fit-content;
        }

        .post-card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 10px;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .post-card-title a {
            color: inherit;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .post-card-title a:hover {
            color: #1d4ed8;
        }

        .post-card-excerpt {
            color: #64748b;
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 16px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex-grow: 1;
        }

        .post-card-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 16px;
            border-top: 1px solid #e2e8f0;
            font-size: 0.8rem;
            color: #94a3b8;
        }

        .post-card-author {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .post-card-author-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1d4ed8, #f97316);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 0.8rem;
        }

        .post-card-date {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .read-more-btn {
            display: inline-block;
            margin-top: 12px;
            padding: 8px 20px;
            border-radius: 999px;
            background: linear-gradient(135deg, #0f172a, #1d4ed8);
            color: #fff;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s ease;
            width: fit-content;
        }

        .read-more-btn:hover {
            background: linear-gradient(135deg, #1d4ed8, #3b82f6);
            color: #fff;
            transform: translateX(4px);
        }

        .home-btn {
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

        .home-btn:hover {
            background: linear-gradient(135deg, #1d4ed8, #3b82f6);
            color: #fff;
            transform: translateX(-4px);
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 10px;
        }

        .page-header p {
            color: #64748b;
            font-size: 1.1rem;
        }

        .filter-section {
            margin-bottom: 30px;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .filter-btn {
            padding: 8px 20px;
            border-radius: 999px;
            border: 2px solid #e2e8f0;
            background: #fff;
            color: #64748b;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: linear-gradient(135deg, #0f172a, #1d4ed8);
            border-color: transparent;
            color: #fff;
        }

        .no-posts {
            text-align: center;
            padding: 60px 20px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
        }

        .no-posts h3 {
            color: #0f172a;
            margin-bottom: 10px;
        }

        .no-posts p {
            color: #64748b;
        }

        .posts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
        }

        @media (max-width: 768px) {
            .posts-grid {
                grid-template-columns: 1fr;
            }

            .page-header h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>

<body>


<div class="page-wrap">
        <a href="index.php" class="home-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            Home
        </a>
        <div class="page-header">
            <h1>All Blog Posts</h1>
            <p>Explore our latest articles and insights</p>
        </div>

        <?php if (!empty($publishedPosts)): ?>
            <div class="posts-grid">
                <?php foreach ($publishedPosts as $post): ?>
                    <div class="card post-card">
                        <?php if (!empty($post['image'])): ?>
                            <img src="<?= htmlspecialchars($post['image']); ?>"
                                class="post-card-img"
                                alt="<?= htmlspecialchars($post['title']); ?>"
                                onerror="this.src='https://via.placeholder.com/400x200?text=No+Image'">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/400x200?text=No+Image"
                                class="post-card-img"
                                alt="<?= htmlspecialchars($post['title']); ?>">
                        <?php endif; ?>
                        <div class="card-body post-card-body">
                            <span class="post-card-category"><?= htmlspecialchars($post['category']); ?></span>
                            <h5 class="post-card-title">
                                <a href="singlepost.php?slug=<?= urlencode($post['slug']); ?>">
                                    <?= htmlspecialchars($post['title']); ?>
                                </a>
                            </h5>
                            <p class="post-card-excerpt">
                                <?= htmlspecialchars(substr($post['sort des'] ?? $post['content'], 0, 150)); ?>...
                            </p>
                            <a href="singlepost.php?slug=<?= urlencode($post['slug']); ?>" class="read-more-btn">
                                Read More →
                            </a>
                            <div class="post-card-meta">
                                <div class="post-card-author">
                                    <div class="post-card-author-avatar">
                                        <?= strtoupper(substr($post['createdby'] ?? 'A', 0, 1)); ?>
                                    </div>
                                    <span><?= htmlspecialchars($post['createdby'] ?? 'Admin'); ?></span>
                                </div>
                                <div class="post-card-date">
                                    <?php
                                    $date = isset($post['posted']) ? date('M d, Y', strtotime($post['posted'])) : 'N/A';
                                    echo $date;
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-posts">
                <h3>No Posts Found</h3>
                <p>There are no published posts available at the moment.</p>
                <a href="index.php" class="btn btn-primary mt-3" style="border-radius: 999px;">
                    Go to Home
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/include/footer.php'; ?>
</body>

</html>