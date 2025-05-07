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
$item_id = filter_input(INPUT_POST, 'item_id', FILTER_SANITIZE_NUMBER_INT);
$borrower_name = filter_input(INPUT_POST, 'borrower_name', FILTER_SANITIZE_STRING);
$borrower_contact = filter_input(INPUT_POST, 'borrower_contact', FILTER_SANITIZE_STRING);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT);
$borrow_date = filter_input(INPUT_POST, 'borrow_date', FILTER_SANITIZE_STRING);
$expected_return_date = filter_input(INPUT_POST, 'expected_return_date', FILTER_SANITIZE_STRING);
$condition_notes = filter_input(INPUT_POST, 'condition_notes', FILTER_SANITIZE_STRING);

// Validate required fields
if (!$item_id || !$borrower_name || !$quantity || !$borrow_date || !$expected_return_date) {
    $_SESSION['error'] = "Please fill in all required fields.";
    header('Location: ../dashboard.php?section=manage_borrowings');
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Check if item exists and has enough quantity
    $stmt = $conn->prepare("SELECT quantity FROM items WHERE item_id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    $stmt->close();

    if (!$item) {
        throw new Exception("Selected item does not exist.");
    }

    if ($item['quantity'] < $quantity) {
        throw new Exception("Not enough items available for borrowing.");
    }

    // Insert borrowing record
    $stmt = $conn->prepare("
        INSERT INTO borrowings (
            item_id, borrower_name, borrower_contact, quantity,
            borrow_date, expected_return_date, condition_notes, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'borrowed')
    ");
    $stmt->bind_param(
        "ississs",
        $item_id,
        $borrower_name,
        $borrower_contact,
        $quantity,
        $borrow_date,
        $expected_return_date,
        $condition_notes
    );
    $stmt->execute();
    $stmt->close();

    // Update item quantity
    $stmt = $conn->prepare("
        UPDATE items 
        SET quantity = quantity - ? 
        WHERE item_id = ?
    ");
    $stmt->bind_param("ii", $quantity, $item_id);
    $stmt->execute();
    $stmt->close();

    // Commit transaction
    $conn->commit();

    $_SESSION['success'] = "Borrowing record added successfully.";
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    error_log("Error adding borrowing: " . $e->getMessage());
    $_SESSION['error'] = "Error adding borrowing record: " . $e->getMessage();
} finally {
    // Close connection
    $conn->close();
}

header('Location: ../dashboard.php?section=manage_borrowings');
exit(); 