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
                    <span class="material-icons">delete</span>Delete Item
                </h5>
                <a href="dashboard.php?section=manage_items" class="btn-close" aria-label="Close"></a>
            </div>
            <form action="process/delete_item_process.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="item_id" value="<?= $currentItem['item_id'] ?>">
                
                <div class="modal-body">
                    <p>Are you sure you want to delete the item "<strong><?= htmlspecialchars($currentItem['name']) ?></strong>"?</p>
                    <?php if ($currentItem['quantity'] > 0): ?>
                        <div class="alert alert-warning">
                            <span class="material-icons">warning</span>
                            This item still has <?= $currentItem['quantity'] ?> unit(s) in stock.
                        </div>
                    <?php endif; ?>
                    <?php if (isset($currentItem['borrowed_count']) && $currentItem['borrowed_count'] > 0): ?>
                        <div class="alert alert-danger">
                            <span class="material-icons">error</span>
                            This item has <?= $currentItem['borrowed_count'] ?> active borrowing(s). Please ensure all items are returned before deletion.
                        </div>
                    <?php endif; ?>
                    <p class="text-danger mb-0">This action cannot be undone.</p>
                </div>
                
                <div class="modal-footer">
                    <a href="dashboard.php?section=manage_items" class="btn btn-compact btn-secondary">
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