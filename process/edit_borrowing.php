<?php
session_start();
require_once '../include/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method!";
    header("Location: ../dashboard.php?section=manage_borrowings");
    exit();
}

// Retrieve and sanitize inputs
$borrowingId = filter_input(INPUT_POST, 'borrowing_id', FILTER_VALIDATE_INT);
$newItemId = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
$newBorrowerName = trim(filter_input(INPUT_POST, 'borrower_name', FILTER_SANITIZE_STRING));
$newBorrowerContact = trim(filter_input(INPUT_POST, 'borrower_contact', FILTER_SANITIZE_STRING));
$newBorrowDate = trim(filter_input(INPUT_POST, 'borrow_date', FILTER_SANITIZE_STRING));
$newExpectedReturnDate = trim(filter_input(INPUT_POST, 'expected_return_date', FILTER_SANITIZE_STRING));
$newConditionNotes = trim(filter_input(INPUT_POST, 'condition_notes', FILTER_SANITIZE_STRING));
$newQuantity = filter_input(INPUT_POST, 'borrow_quantity', FILTER_VALIDATE_INT);

// Validate required fields
if (!$borrowingId || !$newItemId || empty($newBorrowerName) || empty($newBorrowDate) || empty($newExpectedReturnDate) || !$newQuantity) {
    $_SESSION['error'] = "Please fill in all required fields!";
    header("Location: ../dashboard.php?section=edit_borrowing&id=" . $borrowingId);
    exit();
}

try {
    // Fetch current borrowing record
    $stmt = $conn->prepare("SELECT * FROM borrowings WHERE borrowing_id = ?");
    $stmt->bind_param("i", $borrowingId);
    $stmt->execute();
    $currentBorrowing = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$currentBorrowing) {
        $_SESSION['error'] = "Borrowing record not found.";
        header("Location: ../dashboard.php?section=manage_borrowings");
        exit();
    }

    $oldItemId = $currentBorrowing['item_id'];
    $oldQuantity = (int)$currentBorrowing['quantity'];

    // Begin transaction
    $conn->begin_transaction();

    // If the item is changed
    if ($newItemId !== $oldItemId) {
        // 1. Add back the old quantity to the original item.
        $stmtUpdateOld = $conn->prepare("UPDATE items SET quantity = quantity + ? WHERE item_id = ?");
        $stmtUpdateOld->bind_param("ii", $oldQuantity, $oldItemId);
        $stmtUpdateOld->execute();
        $stmtUpdateOld->close();

        // 2. Check available quantity of the new item.
        $stmtCheck = $conn->prepare("SELECT quantity FROM items WHERE item_id = ?");
        $stmtCheck->bind_param("i", $newItemId);
        $stmtCheck->execute();
        $newItemData = $stmtCheck->get_result()->fetch_assoc();
        $stmtCheck->close();

        if (!$newItemData) {
            throw new Exception("New item not found.");
        }

        $availableNewQuantity = $newItemData['quantity'];
        if ($newQuantity > $availableNewQuantity) {
            throw new Exception("Not enough stock available for the new item. Only $availableNewQuantity left.");
        }

        // 3. Deduct the new borrow quantity from the new item.
        $stmtUpdateNew = $conn->prepare("UPDATE items SET quantity = quantity - ? WHERE item_id = ?");
        $stmtUpdateNew->bind_param("ii", $newQuantity, $newItemId);
        $stmtUpdateNew->execute();
        $stmtUpdateNew->close();

    } else {
        // Same item: Adjust quantity difference
        $quantityDiff = $newQuantity - $oldQuantity; // positive if more is borrowed, negative if less.
        if ($quantityDiff > 0) {
            // Need to deduct additional quantity from the item.
            // Check available quantity first.
            $stmtCheck = $conn->prepare("SELECT quantity FROM items WHERE item_id = ?");
            $stmtCheck->bind_param("i", $oldItemId);
            $stmtCheck->execute();
            $itemData = $stmtCheck->get_result()->fetch_assoc();
            $stmtCheck->close();

            if (!$itemData) {
                throw new Exception("Item not found.");
            }
            $availableQuantity = $itemData['quantity'];
            if ($quantityDiff > $availableQuantity) {
                throw new Exception("Not enough stock available. Only $availableQuantity additional items available.");
            }

            $stmtUpdate = $conn->prepare("UPDATE items SET quantity = quantity - ? WHERE item_id = ?");
            $stmtUpdate->bind_param("ii", $quantityDiff, $oldItemId);
            $stmtUpdate->execute();
            $stmtUpdate->close();
        } elseif ($quantityDiff < 0) {
            // Return extra items back to stock.
            $absDiff = abs($quantityDiff);
            $stmtUpdate = $conn->prepare("UPDATE items SET quantity = quantity + ? WHERE item_id = ?");
            $stmtUpdate->bind_param("ii", $absDiff, $oldItemId);
            $stmtUpdate->execute();
            $stmtUpdate->close();
        }
        // If zero, no adjustment needed.
    }

    // Now update the borrowing record.
    $stmtUpdateBorrowing = $conn->prepare("UPDATE borrowings SET item_id = ?, borrower_name = ?, borrower_contact = ?, borrow_date = ?, expected_return_date = ?, condition_notes = ?, quantity = ? WHERE borrowing_id = ?");
    $stmtUpdateBorrowing->bind_param("issssssi", $newItemId, $newBorrowerName, $newBorrowerContact, $newBorrowDate, $newExpectedReturnDate, $newConditionNotes, $newQuantity, $borrowingId);
    if (!$stmtUpdateBorrowing->execute()) {
        throw new Exception("Failed to update borrowing record: " . $stmtUpdateBorrowing->error);
    }
    $stmtUpdateBorrowing->close();

    // Commit the transaction.
    $conn->commit();

    $_SESSION['success'] = "Borrowing record updated successfully!";
} catch (Exception $e) {
    $conn->rollback();
    error_log($e->getMessage());
    $_SESSION['error'] = "Error updating borrowing record: " . $e->getMessage();
}

$conn->close();
header("Location: ../dashboard.php?section=manage_borrowings");
exit();
