<?php
if (!isset($borrow) || !isset($items)) {
    die("No borrowing data or items provided.");
}
?>
<div class="modal fade" id="editModal<?= $borrow['borrowing_id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $borrow['borrowing_id'] ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel<?= $borrow['borrowing_id'] ?>">
                    <span class="material-icons">edit</span>Edit Borrowing
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="process/edit_borrowing_process.php" method="POST">
                <input type="hidden" name="form_token" value="<?= $_SESSION['form_token'] ?>">
                <input type="hidden" name="borrowing_id" value="<?= $borrow['borrowing_id'] ?>">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="item_id<?= $borrow['borrowing_id'] ?>" class="form-label">Item <span class="required">*</span></label>
                        <select class="form-select" id="item_id<?= $borrow['borrowing_id'] ?>" name="item_id" required>
                            <?php foreach ($items as $item): ?>
                                <option value="<?= $item['item_id'] ?>" <?= ($item['item_id'] == $borrow['item_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($item['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="borrower_name<?= $borrow['borrowing_id'] ?>" class="form-label">Borrower Name <span class="required">*</span></label>
                        <input type="text" class="form-control" id="borrower_name<?= $borrow['borrowing_id'] ?>" name="borrower_name" value="<?= htmlspecialchars($borrow['borrower_name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="borrower_contact<?= $borrow['borrowing_id'] ?>" class="form-label">Borrower Contact</label>
                        <input type="text" class="form-control" id="borrower_contact<?= $borrow['borrowing_id'] ?>" name="borrower_contact" value="<?= htmlspecialchars($borrow['borrower_contact'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="quantity<?= $borrow['borrowing_id'] ?>" class="form-label">Quantity <span class="required">*</span></label>
                        <input type="number" class="form-control" id="quantity<?= $borrow['borrowing_id'] ?>" name="quantity" value="<?= $borrow['quantity'] ?>" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="borrow_date<?= $borrow['borrowing_id'] ?>" class="form-label">Borrow Date <span class="required">*</span></label>
                        <input type="date" class="form-control" id="borrow_date<?= $borrow['borrowing_id'] ?>" name="borrow_date" value="<?= $borrow['borrow_date'] ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="expected_return_date<?= $borrow['borrowing_id'] ?>" class="form-label">Expected Return Date <span class="required">*</span></label>
                        <input type="date" class="form-control" id="expected_return_date<?= $borrow['borrowing_id'] ?>" name="expected_return_date" value="<?= $borrow['expected_return_date'] ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="condition_notes<?= $borrow['borrowing_id'] ?>" class="form-label">Condition Notes</label>
                        <textarea class="form-control" id="condition_notes<?= $borrow['borrowing_id'] ?>" name="condition_notes" rows="3"><?= htmlspecialchars($borrow['condition_notes'] ?? '') ?></textarea>
                        <div class="textarea-counter">
                            <span id="charCount<?= $borrow['borrowing_id'] ?>"><?= strlen($borrow['condition_notes'] ?? '') ?></span>/200
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-compact btn-secondary" data-bs-dismiss="modal">
                        <span class="material-icons">close</span>Cancel
                    </button>
                    <button type="submit" class="btn btn-compact btn-primary">
                        <span class="material-icons">save</span>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> 