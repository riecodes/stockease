<?php
session_start();
require_once '../include/db.php';

if (!isset($_SESSION['admin_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../index.php");
    exit();
}

// Verify CSRF token
if (!isset($_POST['form_token']) || $_POST['form_token'] !== $_SESSION['form_token']) {
    $_SESSION['error'] = "Invalid form submission!";
    header("Location: ../dashboard.php?section=manage_items");
    exit();
}

$itemId = filter_input(INPUT_POST, 'item_id', FILTER_SANITIZE_NUMBER_INT);

if (!$itemId) {
    $_SESSION['error'] = "Invalid item ID!";
    header("Location: ../dashboard.php?section=manage_items");
    exit();
}

try {
    // Check if item has any active borrowings
    $checkStmt = $conn->prepare("SELECT COUNT(*) as active_borrowings FROM borrowings WHERE item_id = ? AND status = 'borrowed'");
    $checkStmt->bind_param("i", $itemId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['active_borrowings'] > 0) {
        $_SESSION['error'] = "Cannot delete item with active borrowings!";
        header("Location: ../dashboard.php?section=manage_items");
        exit();
    }
    $checkStmt->close();

    // Delete the item
    $stmt = $conn->prepare("DELETE FROM items WHERE item_id = ?");
    $stmt->bind_param("i", $itemId);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Item deleted successfully!";
    } else {
        throw new Exception("Database error: " . $stmt->error);
    }
    
} catch (Exception $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = "Error deleting item. Please try again.";
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
    header("Location: ../dashboard.php?section=manage_items");
    exit();
}
