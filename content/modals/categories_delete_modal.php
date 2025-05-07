<?php
if (!isset($currentItem)) {
    return;
}
?>
<div class="modal show">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="material-icons">delete</span>Delete Category
                </h5>
                <a href="dashboard.php?section=manage_categories" class="btn-close" aria-label="Close"></a>
            </div>
            <form action="process/delete_category_process.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="category_id" value="<?= $currentItem['category_id'] ?>">
                
                <div class="modal-body">
                    <p>Are you sure you want to delete the category "<strong><?= htmlspecialchars($currentItem['name']) ?></strong>"?</p>
                    <?php if (($currentItem['item_count'] ?? 0) > 0): ?>
                        <div class="alert alert-warning">
                            <span class="material-icons">warning</span>
                            This category has <?= $currentItem['item_count'] ?> item(s) associated with it. Deleting this category will also delete all associated items.
                        </div>
                    <?php endif; ?>
                    <p class="text-danger mb-0">This action cannot be undone.</p>
                </div>
                
                <div class="modal-footer">
                    <a href="dashboard.php?section=manage_categories" class="btn btn-compact btn-secondary">
                        <span class="material-icons">close</span>Cancel
                    </a>
                    <button type="submit" class="btn btn-compact btn-danger">
                        <span class="material-icons">delete</span>Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> 