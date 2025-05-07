<?php
if (!isset($items)) {
    die("No items data provided.");
}
?>
<div class="modal fade" id="addBorrowingModal" tabindex="-1" aria-labelledby="addBorrowingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addBorrowingModalLabel">
                    <span class="material-icons">add_circle</span>Add New Borrowing
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="process/add_borrowing_process.php" method="POST">
                <input type="hidden" name="form_token" value="<?= $_SESSION['form_token'] ?>">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="item_id" class="form-label">Item <span class="required">*</span></label>
                        <select class="form-select" id="item_id" name="item_id" required>
                            <option value="">Select an item</option>
                            <?php foreach ($items as $item): ?>
                                <option value="<?= $item['item_id'] ?>">
                                    <?= htmlspecialchars($item['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="borrower_name" class="form-label">Borrower Name <span class="required">*</span></label>
                        <input type="text" class="form-control" id="borrower_name" name="borrower_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="borrower_contact" class="form-label">Borrower Contact</label>
                        <input type="text" class="form-control" id="borrower_contact" name="borrower_contact">
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity <span class="required">*</span></label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="borrow_date" class="form-label">Borrow Date <span class="required">*</span></label>
                        <input type="date" class="form-control" id="borrow_date" name="borrow_date" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="expected_return_date" class="form-label">Expected Return Date <span class="required">*</span></label>
                        <input type="date" class="form-control" id="expected_return_date" name="expected_return_date" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="condition_notes" class="form-label">Condition Notes</label>
                        <textarea class="form-control" id="condition_notes" name="condition_notes" rows="3"></textarea>
                        <div class="textarea-counter">
                            <span id="charCount">0</span>/200
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-compact btn-secondary" data-bs-dismiss="modal">
                        <span class="material-icons">close</span>Cancel
                    </button>
                    <button type="submit" class="btn btn-compact btn-primary">
                        <span class="material-icons">add</span>Add Borrowing
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> 