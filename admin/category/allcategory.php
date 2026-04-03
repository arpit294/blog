<?php
session_start();
require_once __DIR__ . '/../../config/category/categoryCrud.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /blog/config/login&signup/login.php');
    exit;
}

$message = $_SESSION['category_message'] ?? '';
$messageType = $_SESSION['category_message_type'] ?? '';

unset($_SESSION['category_message'], $_SESSION['category_message_type']);


if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    require_once __DIR__ . '/../../config/category/categoryCrud.php';

    header('Content-Type: application/json');

    $categoryObj = new category();

    $status = $_GET['status'] ?? '';

    $categories = $categoryObj->getAll();

    if (!empty($status)) {
        foreach ($categories as $key => $cat) {
            if ($cat['status'] != $status) {
                unset($categories[$key]);
            }
        }
    } else {
        $categories = $categoryObj->getAll();
    }

    $categories = array_values($categories);

    echo json_encode([
        "data" => $categories,
    ]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Categories</title>
    <?php include __DIR__ . '/../../include/header.php'; ?>
    <link href="https://cdn.datatables.net/2.3.7/css/dataTables.bootstrap5.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #e0e7ff, #f8fafc);
        }

        .page-wrap {
            padding: 48px 24px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-wrap.with-panel {
            margin-left: 250px !important;
            max-width: calc(100% - 270px) !important;
        }

        @media (max-width: 991px) {
            .page-wrap.with-panel {
                margin-left: 0 !important;
                max-width: 100% !important;
            }
        }

        .page-header {
            background: linear-gradient(135deg, #1e293b, #0f172a);
            color: #fff;
            padding: 28px 32px;
            border-radius: 20px 20px 0 0;
        }

        .page-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .page-header .btn-light {
            background: #fff;
            border: none;
            padding: 10px 24px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .page-header .btn-light:hover {
            background: #f1f5f9;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .table-card {
            background: #fff;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.12);
            overflow: hidden;
        }

        .table-card-header {
            background: #f8fafc;
            padding: 20px 24px;
            border-bottom: 1px solid #e2e8f0;
        }
    
        .table-card-header h4 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 2px;
        }

        .filter-wrapper .form-select {
            border-radius: 10px;
            padding: 8px 36px 8px 12px;
            border: 1px solid #cbd5e1;
            font-size: 0.85rem;
            min-width: 140px;
        }

        .filter-wrapper .form-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.15);
        }

        .category-table {
            margin-bottom: 0;
        }

        .category-table thead th {
            background: #1e293b;
            color: #fff;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 14px 16px;
            border: none;
        }

        .category-table thead th:first-child {
            border-radius: 0;
        }

        .category-table tbody td {
            padding: 14px 16px;
            vertical-align: middle;
            font-size: 0.9rem;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
        }

        .category-table tbody tr:hover {
            background: #f8fafc;
        }

        .category-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Status badges */
        .badge {
            padding: 6px 14px;
            border-radius: 999px;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: capitalize;
        }

        .badge-launch {
            background: #dcfce7;
            color: #166534;
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-unpublished {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Action buttons */
        .btn-action {
            width: 34px;
            height: 34px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: none;
            transition: all 0.2s ease;
            font-size: 0.9rem;
        }

        .btn-edit {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .btn-edit:hover {
            background: #2563eb;
            color: #fff;
        }

        .btn-delete {
            background: #fee2e2;
            color: #dc2626;
        }

        .btn-delete:hover {
            background: #dc2626;
            color: #fff;
        }

        .btn-launch {
            background: #dcfce7;
            color: #166534;
        }

        .btn-launch:hover {
            background: #166534;
            color: #fff;
        }

        /* DataTables customization */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 16px;
        }

        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            padding: 6px 10px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 8px !important;
            margin: 0 2px;
            border: 1px solid #e2e8f0 !important;
            background: #fff !important;
            color: #334155 !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: linear-gradient(135deg, #1e293b, #3b82f6) !important;
            color: #fff !important;
            border-color: transparent !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #f1f5f9 !important;
            color: #0f172a !important;
        }

        /* Toast */
        .toast {
            border-radius: 12px;
        }

        /* Modal customization */
        .modal-content {
            border-radius: 16px;
            border: none;
            overflow: hidden;
            z-index: 99999 !important;
            position: relative;
        }

        .modal {
            z-index: 99999 !important;
            position: fixed !important;
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, -50%) !important;
        }

        .modal-backdrop {
            z-index: 99998 !important;
        }

        /* Ensure admin panel doesn't interfere */
        .admin-panel {
            z-index: 1000 !important;
        }

        .modal-header {
            background: linear-gradient(135deg, #1e293b, #0f172a);
            color: #fff;
            border-radius: 16px 16px 0 0;
        }

        .modal-header .btn-close {
            filter: invert(1);
        }

        .modal-body {
            padding: 24px;
        }

        .modal-body .form-label {
            font-weight: 600;
            color: #334155;
        }

        .modal-body .form-control,
        .modal-body .form-select {
            border-radius: 10px;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
        }

        .modal-body .form-control:focus,
        .modal-body .form-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.15);
        }

        .modal-body .btn-primary {
            background: linear-gradient(135deg, #1e293b, #3b82f6);
            border: none;
            padding: 10px 24px;
            border-radius: 10px;
            font-weight: 600;
        }

        .modal-body .btn-primary:hover {
            background: linear-gradient(135deg, #3b82f6, #6366f1);
        }

        /* Empty state */
        .dataTables_empty {
            text-align: center;
            padding: 40px !important;
            color: #94a3b8;
            font-size: 0.95rem;
        }
    </style>
</head>

<body class="category-all-page">
    <?php include __DIR__ . '/../adminpanel.php'; ?>
    <!-- this for toaster  -->
    <?php if ($message !== ''): ?>
        <div class="toast-container position-fixed top-0 end-0 p-3">
            <div
                id="authToast"
                class="toast text-bg-<?= $messageType === 'success' ? 'success' : 'danger'; ?> border-0"
                role="alert"
                aria-live="assertive"
                aria-atomic="true"
                data-bs-delay="1000">
                <div class="d-flex">
                    <div class="toast-body"><?= htmlspecialchars($message); ?></div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <!-- modal  -->
    <div class="modal fade" id="myModal">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title text-dark">Categories</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body" id="modalContent"></div>

            </div>
        </div>
    </div>
    <form method="get">
<div class="page-wrap with-panel">
            <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 class="mb-2">All Categories</h1>
                    <p class="mb-0 text-white-50">Manage every category from one page.</p>
                </div>
                <button
                    type="button"
                    class="btn btn-light text-dark"
                    id="addBtn">
                    Add Category
                </button>
            </div>

            <div class="table-card">
                <div class="table-card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h4 class="mb-1">Category List</h4>
                        <p class="text-muted mb-0">All categories are now shown in table format.</p>
                    </div>
                    <div class="filter-wrapper">
                        <!-- <select id="statusFilter" class="custom-dropdown"> -->
                        <div class="position-relative ">
                            <select name="status" id="statusFilter" class="form-select" style="padding-right: 42px;" <?php if (isset($_GET['status'])) echo 'value="' . htmlspecialchars($_GET['status']) . '"'; ?>>
                                <option value="">Status</option>
                                <option value="launch" <?php if (isset($_GET['status']) && $_GET['status'] == 'launch') echo 'selected'; ?>>Launch</option>
                                <option value="pending" <?php if (isset($_GET['status']) && $_GET['status'] == 'pending') echo 'selected'; ?>>Pending</option>
                            </select>
                            <button type="button" class="btn-clear-filter btn btn-sm btn-outline-secondary position-absolute" style="right: 8px; top: 50%; transform: translateY(-50%); display: none;">✖️</button>
                        </div>
                        
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table category-table" id="categoryDataTable">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Name</th>
                                <th scope="col">Slug</th>
                                <th scope="col">Status</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody"></tbody>

                    </table>
                </div>
            </div>
        </div>
    </form>
    <?php include __DIR__ . '/../../include/footer.php'; ?>
    <script src="https://cdn.datatables.net/2.3.7/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.3.7/js/dataTables.bootstrap5.js"></script>
</body>

</html>