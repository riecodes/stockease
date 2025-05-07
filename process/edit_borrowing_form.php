<?php
require_once '../include/db.php';

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$borrowingId = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

try {
    // Fetch borrowing details
    $stmt = $conn->prepare("
        SELECT b.*, i.name AS item_name 
        FROM borrowings b
        LEFT JOIN items i ON b.item_id = i.item_id
        WHERE b.borrowing_id = ?
    ");
    $stmt->bind_param("i", $borrowingId);
    $stmt->execute();
    $borrowing = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Fetch items for dropdown
    $stmt = $conn->prepare("SELECT item_id, name FROM items WHERE quantity > 0 OR item_id = ? ORDER BY name");
    $stmt->bind_param("i", $borrowing['item_id']);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if ($borrowing) {
        ?>
        <form action="process/edit_borrowing.php" method="POST" id="editBorrowingForm">
            <input type="hidden" name="borrowing_id" value="<?= $borrowing['borrowing_id'] ?>">
            
            <div class="mb-3">
                <label for="item_id" class="form-label">Item</label>
                <select class="form-select" id="item_id" name="item_id" required>
                    <?php foreach ($items as $item): ?>
                        <option value="<?= $item['item_id'] ?>" <?= $item['item_id'] == $borrowing['item_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($item['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="borrower_name" class="form-label">Borrower Name</label>
                <input type="text" class="form-control" id="borrower_name" name="borrower_name" value="<?= htmlspecialchars($borrowing['borrower_name']) ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="borrower_contact" class="form-label">Borrower Contact</label>
                <input type="text" class="form-control" id="borrower_contact" name="borrower_contact" value="<?= htmlspecialchars($borrowing['borrower_contact'] ?? '') ?>">
            </div>
            
            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" class="form-control" id="quantity" name="quantity" value="<?= $borrowing['quantity'] ?>" min="1" required>
            </div>
            
            <div class="mb-3">
                <label for="borrow_date" class="form-label">Borrow Date</label>
                <input type="date" class="form-control" id="borrow_date" name="borrow_date" value="<?= date('Y-m-d', strtotime($borrowing['borrow_date'])) ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="expected_return_date" class="form-label">Expected Return Date</label>
                <input type="date" class="form-control" id="expected_return_date" name="expected_return_date" value="<?= date('Y-m-d', strtotime($borrowing['expected_return_date'])) ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="condition_notes" class="form-label">Condition Notes</label>
                <textarea class="form-control" id="condition_notes" name="condition_notes" rows="3"><?= htmlspecialchars($borrowing['condition_notes'] ?? '') ?></textarea>
            </div>
            
            <div class="text-end">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
        <?php
    } else {
        echo '<p class="text-danger"><span class="material-icons me-2">error</span>Borrowing record not found</p>';
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    echo '<p class="text-danger"><span class="material-icons me-2">error</span>Error loading borrowing details: ' . $e->getMessage() . '</p>';
}
?> 