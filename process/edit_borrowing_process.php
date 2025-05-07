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
$item_id = filter_input(INPUT_POST, 'item_id', FILTER_SANITIZE_NUMBER_INT);
$borrower_name = filter_input(INPUT_POST, 'borrower_name', FILTER_SANITIZE_STRING);
$borrower_contact = filter_input(INPUT_POST, 'borrower_contact', FILTER_SANITIZE_STRING);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT);
$borrow_date = filter_input(INPUT_POST, 'borrow_date', FILTER_SANITIZE_STRING);
$expected_return_date = filter_input(INPUT_POST, 'expected_return_date', FILTER_SANITIZE_STRING);
$condition_notes = filter_input(INPUT_POST, 'condition_notes', FILTER_SANITIZE_STRING);

// Validate required fields
if (!$borrowing_id || !$item_id || !$borrower_name || !$quantity || !$borrow_date || !$expected_return_date) {
    $_SESSION['error'] = "Please fill in all required fields.";
    header('Location: ../dashboard.php?section=manage_borrowings');
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Get current borrowing details
    $stmt = $conn->prepare("
        SELECT item_id, quantity, status 
        FROM borrowings 
        WHERE borrowing_id = ?
    ");
    $stmt->bind_param("i", $borrowing_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $current_borrowing = $result->fetch_assoc();
    $stmt->close();

    if (!$current_borrowing) {
        throw new Exception("Borrowing record not found.");
    }

    if ($current_borrowing['status'] === 'returned') {
        throw new Exception("Cannot edit a returned borrowing record.");
    }

    // Check if new item exists and has enough quantity
    if ($current_borrowing['item_id'] !== $item_id || $current_borrowing['quantity'] !== $quantity) {
        $stmt = $conn->prepare("SELECT quantity FROM items WHERE item_id = ?");
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();
        $stmt->close();

        if (!$item) {
            throw new Exception("Selected item does not exist.");
        }

        // Calculate available quantity
        $available_quantity = $item['quantity'];
        if ($current_borrowing['item_id'] === $item_id) {
            $available_quantity += $current_borrowing['quantity'];
        }

        if ($available_quantity < $quantity) {
            throw new Exception("Not enough items available for borrowing.");
        }
    }

    // Update borrowing record
    $stmt = $conn->prepare("
        UPDATE borrowings 
        SET item_id = ?, borrower_name = ?, borrower_contact = ?, 
            quantity = ?, borrow_date = ?, expected_return_date = ?, 
            condition_notes = ?
        WHERE borrowing_id = ?
    ");
    $stmt->bind_param(
        "ississsi",
        $item_id,
        $borrower_name,
        $borrower_contact,
        $quantity,
        $borrow_date,
        $expected_return_date,
        $condition_notes,
        $borrowing_id
    );
    $stmt->execute();
    $stmt->close();

    // Update item quantities if needed
    if ($current_borrowing['item_id'] !== $item_id || $current_borrowing['quantity'] !== $quantity) {
        // Return quantity to old item
        if ($current_borrowing['item_id'] !== $item_id) {
            $stmt = $conn->prepare("
                UPDATE items 
                SET quantity = quantity + ? 
                WHERE item_id = ?
            ");
            $stmt->bind_param("ii", $current_borrowing['quantity'], $current_borrowing['item_id']);
            $stmt->execute();
            $stmt->close();
        }

        // Deduct quantity from new item
        $stmt = $conn->prepare("
            UPDATE items 
            SET quantity = quantity - ? 
            WHERE item_id = ?
        ");
        $stmt->bind_param("ii", $quantity, $item_id);
        $stmt->execute();
        $stmt->close();
    }

    // Commit transaction
    $conn->commit();

    $_SESSION['success'] = "Borrowing record updated successfully.";
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    error_log("Error updating borrowing: " . $e->getMessage());
    $_SESSION['error'] = "Error updating borrowing record: " . $e->getMessage();
} finally {
    // Close connection
    $conn->close();
}

header('Location: ../dashboard.php?section=manage_borrowings');
exit(); 