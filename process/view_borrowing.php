<?php
require_once '../include/db.php';

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$borrowingId = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

try {
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

    if ($borrowing) {
        echo '
        <div class="row">
            <div class="col-md-6">
                <p><strong>Borrower Name:</strong><br>' . htmlspecialchars($borrowing['borrower_name']) . '</p>
                <p><strong>Item:</strong><br>' . htmlspecialchars($borrowing['item_name'] ?? 'Unknown') . '</p>
            </div>
            <div class="col-md-6">
                <p><strong>Borrow Date:</strong><br>' . date('M d, Y', strtotime($borrowing['borrow_date'])) . '</p>
                <p><strong>Expected Return Date:</strong><br>' . date('M d, Y', strtotime($borrowing['expected_return_date'])) . '</p>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <p><strong>Status:</strong><br><span class="badge bg-' . ($borrowing['status'] == 'returned' ? 'success' : 'warning') . '">' . ucfirst($borrowing['status']) . '</span></p>';
        if ($borrowing['actual_return_date']) {
            echo '<p><strong>Actual Return Date:</strong><br>' . date('M d, Y', strtotime($borrowing['actual_return_date'])) . '</p>';
        }
        echo '  <p><strong>Condition Notes:</strong><br>' . nl2br(htmlspecialchars($borrowing['condition_notes'] ?? 'No notes')) . '</p>
            </div>
        </div>';
    } else {
        echo '<p class="text-danger"><span class="material-icons me-2">error</span>Borrowing record not found</p>';
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    echo '<p class="text-danger"><span class="material-icons me-2">error</span>Error loading borrowing details</p>';
}
