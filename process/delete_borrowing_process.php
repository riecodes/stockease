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

    // If the item hasn't been returned, update the item quantity
    if ($borrowing['status'] !== 'returned') {
        $stmt = $conn->prepare("
            UPDATE items 
            SET quantity = quantity + ? 
            WHERE item_id = ?
        ");
        $stmt->bind_param("ii", $borrowing['quantity'], $borrowing['item_id']);
        $stmt->execute();
        $stmt->close();
    }

    // Delete the borrowing record
    $stmt = $conn->prepare("DELETE FROM borrowings WHERE borrowing_id = ?");
    $stmt->bind_param("i", $borrowing_id);
    $stmt->execute();
    $stmt->close();

    // Commit transaction
    $conn->commit();

    $_SESSION['success'] = "Borrowing record deleted successfully.";
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    error_log("Error deleting borrowing: " . $e->getMessage());
    $_SESSION['error'] = "Error deleting borrowing record: " . $e->getMessage();
} finally {
    // Close connection
    $conn->close();
}

header('Location: ../dashboard.php?section=manage_borrowings');
exit(); 