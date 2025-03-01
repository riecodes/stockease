<?php
session_start();
require_once '../include/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../index.php");
    exit();
}

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Invalid request!";
    header("Location: ../dashboard.php?section=manage_borrowings");
    exit();
}

$borrowingId = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

try {
    // Fetch the borrowing record
    $stmt = $conn->prepare("SELECT * FROM borrowings WHERE borrowing_id = ?");
    $stmt->bind_param("i", $borrowingId);
    $stmt->execute();
    $result = $stmt->get_result();
    $borrowing = $result->fetch_assoc();
    $stmt->close();

    if (!$borrowing) {
        $_SESSION['error'] = "Borrowing record not found.";
        header("Location: ../dashboard.php?section=manage_borrowings");
        exit();
    }

    // Check if it's already marked as returned
    if ($borrowing['status'] === 'returned') {
        $_SESSION['error'] = "This borrowing has already been returned.";
        header("Location: ../dashboard.php?section=manage_borrowings");
        exit();
    }

    $itemId = $borrowing['item_id'];
    $quantity = $borrowing['quantity'];

    // Update borrowing record: mark as returned and set actual return date to current timestamp
    $stmtUpdate = $conn->prepare("UPDATE borrowings SET status = 'returned', actual_return_date = NOW() WHERE borrowing_id = ?");
    $stmtUpdate->bind_param("i", $borrowingId);
    if (!$stmtUpdate->execute()) {
        throw new Exception("Failed to update borrowing record: " . $stmtUpdate->error);
    }
    $stmtUpdate->close();

    // Update item: add back the quantity to available stock
    $stmtItem = $conn->prepare("UPDATE items SET quantity = quantity + ? WHERE item_id = ?");
    $stmtItem->bind_param("ii", $quantity, $itemId);
    if (!$stmtItem->execute()) {
        throw new Exception("Failed to update item quantity: " . $stmtItem->error);
    }
    $stmtItem->close();

    $_SESSION['success'] = "Borrowing record marked as returned successfully!";
} catch (Exception $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = "Error processing return. Please try again.";
} finally {
    $conn->close();
    header("Location: ../dashboard.php?section=manage_borrowings");
    exit();
}
?>
