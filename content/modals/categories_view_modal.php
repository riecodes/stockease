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
                    <span class="material-icons">visibility</span>View Category Details
                </h5>
                <a href="dashboard.php?section=manage_categories" class="btn-close" aria-label="Close"></a>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <label class="form-label fw-bold">Category Name</label>
                    <p class="mb-0"><?= htmlspecialchars($currentItem['name']) ?></p>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Description</label>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($currentItem['description'] ?? 'No description')) ?></p>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Items Count</label>
                    <p class="mb-0"><?= $currentItem['item_count'] ?? 0 ?> items</p>
                </div>
            </div>
            <div class="modal-footer">
                <a href="dashboard.php?section=manage_categories" class="btn btn-compact btn-secondary">
                    <span class="material-icons">close</span>Close
                </a>
            </div>
        </div>
    </div>
</div> 