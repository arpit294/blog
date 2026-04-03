<?php
$formAction = $formAction ?? 'addcategory.php';
$submitLabel = $submitLabel ?? 'Add Category';
$name = $name ?? '';
$status = $status ?? 'pending';
$categoryId = (int) ($categoryId ?? 0);
$formId = $formId ?? 'addForm';
?>

<form action="<?= htmlspecialchars($formAction); ?>" method="POST" id="<?= htmlspecialchars($formId); ?>">
    <?php if ($categoryId > 0): ?>
        <input type="hidden" name="id" value="<?= $categoryId; ?>">
    <?php endif; ?>

    <div class="mb-3">
        <label for="modalCategoryName" class="form-label">Category Name</label>
        <input
            type="text"
            id="modalCategoryName"
            name="name"
            class="form-control"
            placeholder="Enter category name"
            value="<?= htmlspecialchars($name); ?>">
        <span class="text-danger" id="categoryError"></span>
    </div>

    <div class="mb-4">
        <label for="modalCategoryStatus" class="form-label">Status</label>
        <select id="modalCategoryStatus" name="status" class="form-select">
            <option value="pending" <?= $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="launch" <?= $status === 'launch' ? 'selected' : ''; ?>>Launch</option>
        </select>
        <span class="text-danger" id="statusError"></span>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary"><?= htmlspecialchars($submitLabel); ?></button>
    </div>
</form>
