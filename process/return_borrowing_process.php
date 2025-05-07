<?php
session_start();
require_once '../include/db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../dashboard.php?section=manage_borrowings');
    exit();
}

// Verify CSRF token
if (!isset($_POST['form_token']) || $_POST['form_token'] !== $_SESSION['form_token']) {
    $_SESSION['error'] = "Invalid form submission!";
    header('Location: ../dashboard.php?section=manage_borrowings');
    exit();
}

// Sanitize inputs
$borrowing_id = filter_input(INPUT_POST, 'borrowing_id', FILTER_SANITIZE_NUMBER_INT);
$condition_notes = filter_input(INPUT_POST, 'condition_notes', FILTER_SANITIZE_STRING);

// Validate required fields
if (!$borrowing_id) {
    $_SESSION['error'] = "Invalid borrowing record.";
    header('Location: ../dashboard.php?section=manage_borrowings');
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Get borrowing details
    $stmt = $conn->prepare("
        SELECT item_id, quantity, status 
        FROM borrowings 
        WHERE borrowing_id = ?
    ");
    $stmt->bind_param("i", $borrowing_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $borrowing = $result->fetch_assoc();
    $stmt->close();

    if (!$borrowing) {
        throw new Exception("Borrowing record not found.");
    }

    if ($borrowing['status'] === 'returned') {
        throw new Exception("This item has already been returned.");
    }

    // Update borrowing record
    $stmt = $conn->prepare("
        UPDATE borrowings 
        SET status = 'returned', 
            actual_return_date = CURRENT_DATE(),
            condition_notes = ?
        WHERE borrowing_id = ?
    ");
    $stmt->bind_param("si", $condition_notes, $borrowing_id);
    $stmt->execute();
    $stmt->close();

    // Update item quantity
    $stmt = $conn->prepare("
        UPDATE items 
        SET quantity = quantity + ? 
        WHERE item_id = ?
    ");
    $stmt->bind_param("ii", $borrowing['quantity'], $borrowing['item_id']);
    $stmt->execute();
    $stmt->close();

    // Commit transaction
    $conn->commit();

    $_SESSION['success'] = "Item has been marked as returned successfully.";
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    error_log("Error returning item: " . $e->getMessage());
    $_SESSION['error'] = "Error marking item as returned: " . $e->getMessage();
} finally {
    // Close connection
    $conn->close();
}

header('Location: ../dashboard.php?section=manage_borrowings');
exit(); 