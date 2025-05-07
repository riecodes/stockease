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
                    <span class="material-icons">edit</span>Edit Category
                </h5>
                <a href="dashboard.php?section=manage_categories" class="btn-close" aria-label="Close"></a>
            </div>
            <form action="process/edit_category_process.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="category_id" value="<?= $currentItem['category_id'] ?>">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Category Name <span class="required">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($currentItem['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($currentItem['description'] ?? '') ?></textarea>
                        <div class="textarea-counter">
                            <span id="charCount"><?= strlen($currentItem['description'] ?? '') ?></span>/500 characters
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <a href="dashboard.php?section=manage_categories" class="btn btn-compact btn-secondary">
                        <span class="material-icons">close</span>Cancel
                    </a>
                    <button type="submit" class="btn btn-compact btn-primary">
                        <span class="material-icons">save</span>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> 