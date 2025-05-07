<?php
if (!isset($borrow)) {
    die("No borrowing data provided.");
}
?>
<div class="modal fade" id="viewModal<?= $borrow['borrowing_id'] ?>" tabindex="-1" aria-labelledby="viewModalLabel<?= $borrow['borrowing_id'] ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewModalLabel<?= $borrow['borrowing_id'] ?>">
                    <span class="material-icons">visibility</span>View Borrowing Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <label class="form-label fw-bold">Borrower Name</label>
                    <p class="mb-0"><?= htmlspecialchars($borrow['borrower_name']) ?></p>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Item</label>
                    <p class="mb-0"><?= htmlspecialchars($borrow['item_name'] ?? 'Unknown') ?></p>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Quantity</label>
                    <p class="mb-0"><?= $borrow['quantity'] ?> unit(s)</p>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Borrow Date</label>
                    <p class="mb-0"><?= date('M d, Y', strtotime($borrow['borrow_date'])) ?></p>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Expected Return Date</label>
                    <p class="mb-0"><?= date('M d, Y', strtotime($borrow['expected_return_date'])) ?></p>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Status</label>
                    <p class="mb-0">
                        <?php if ($borrow['status'] === 'returned'): ?>
                            <span class="badge bg-success">Returned</span>
                        <?php else: ?>
                            <span class="badge bg-warning">Borrowed</span>
                        <?php endif; ?>
                    </p>
                </div>
                <?php if ($borrow['actual_return_date']): ?>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Actual Return Date</label>
                        <p class="mb-0"><?= date('M d, Y', strtotime($borrow['actual_return_date'])) ?></p>
                    </div>
                <?php endif; ?>
                <?php if ($borrow['condition_notes']): ?>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Condition Notes</label>
                        <p class="mb-0"><?= nl2br(htmlspecialchars($borrow['condition_notes'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-compact btn-secondary" data-bs-dismiss="modal">
                    <span class="material-icons">close</span>Close
                </button>
            </div>
        </div>
    </div>
</div> 