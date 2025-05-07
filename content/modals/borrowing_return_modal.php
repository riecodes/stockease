<?php
if (!isset($borrow)) {
    die("No borrowing data provided.");
}
?>
<div class="modal fade" id="returnModal<?= $borrow['borrowing_id'] ?>" tabindex="-1" aria-labelledby="returnModalLabel<?= $borrow['borrowing_id'] ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="returnModalLabel<?= $borrow['borrowing_id'] ?>">
                    <span class="material-icons">assignment_return</span>Mark as Returned
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="process/return_borrowing_process.php" method="POST">
                <input type="hidden" name="form_token" value="<?= $_SESSION['form_token'] ?>">
                <input type="hidden" name="borrowing_id" value="<?= $borrow['borrowing_id'] ?>">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="return_date<?= $borrow['borrowing_id'] ?>" class="form-label">Return Date <span class="required">*</span></label>
                        <input type="date" class="form-control" id="return_date<?= $borrow['borrowing_id'] ?>" name="return_date" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="return_condition_notes<?= $borrow['borrowing_id'] ?>" class="form-label">Return Condition Notes</label>
                        <textarea class="form-control" id="return_condition_notes<?= $borrow['borrowing_id'] ?>" name="return_condition_notes" rows="3"></textarea>
                        <div class="textarea-counter">
                            <span id="charCount<?= $borrow['borrowing_id'] ?>">0</span>/200
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-compact btn-secondary" data-bs-dismiss="modal">
                        <span class="material-icons">close</span>Cancel
                    </button>
                    <button type="submit" class="btn btn-compact btn-success">
                        <span class="material-icons">check</span>Mark as Returned
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> 