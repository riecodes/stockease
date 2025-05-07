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
                    <span class="material-icons">visibility</span>View Item Details
                </h5>
                <a href="dashboard.php?section=manage_items" class="btn-close" aria-label="Close"></a>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <label class="form-label fw-bold">Item Name</label>
                    <p class="mb-0"><?= htmlspecialchars($currentItem['name']) ?></p>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Category</label>
                    <p class="mb-0"><?= htmlspecialchars($currentItem['category_name']) ?></p>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Quantity</label>
                    <p class="mb-0"><?= $currentItem['quantity'] ?> units</p>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Description</label>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($currentItem['description'] ?? 'No description')) ?></p>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Status</label>
                    <p class="mb-0">
                        <?php if ($currentItem['quantity'] > 0): ?>
                            <span class="badge bg-success">Available</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Out of Stock</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <a href="dashboard.php?section=manage_items" class="btn btn-compact btn-secondary">
                    <span class="material-icons">close</span>Close
                </a>
            </div>
        </div>
    </div>
</div> 