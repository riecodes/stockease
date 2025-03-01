<?php
session_start();
require_once '../include/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method!";
    header("Location: ../dashboard.php?section=add_borrowing");
    exit();
}

// Store POST data to preserve form values on error
$_SESSION['old'] = $_POST;

// Prevent double submissions using session token
if (!isset($_SESSION['form_token'])) {
    $_SESSION['form_token'] = bin2hex(random_bytes(32));
}
if (!isset($_POST['form_token']) || !hash_equals($_SESSION['form_token'], $_POST['form_token'])) {
    $_SESSION['error'] = "Form already submitted or invalid token!";
    header("Location: ../dashboard.php?section=add_borrowing");
    exit();
}
unset($_SESSION['form_token']);

// Sanitize inputs
$itemId = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
$borrowerName = trim(filter_input(INPUT_POST, 'borrower_name', FILTER_SANITIZE_STRING));
$borrowerContact = trim(filter_input(INPUT_POST, 'borrower_contact', FILTER_SANITIZE_STRING));
$borrowDate = trim(filter_input(INPUT_POST, 'borrow_date', FILTER_SANITIZE_STRING));
$expectedReturnDate = trim(filter_input(INPUT_POST, 'expected_return_date', FILTER_SANITIZE_STRING));
$conditionNotes = trim(filter_input(INPUT_POST, 'condition_notes', FILTER_SANITIZE_STRING));
$borrowQuantity = filter_input(INPUT_POST, 'borrow_quantity', FILTER_VALIDATE_INT);

// Validate required fields
$errors = [];
if (!$itemId) $errors[] = "Please select an item";
if (empty($borrowerName)) $errors[] = "Borrower name is required";
if (empty($borrowDate)) $errors[] = "Borrow date is required";
if (empty($expectedReturnDate)) $errors[] = "Return date is required";
if (!$borrowQuantity || $borrowQuantity < 1) $errors[] = "Valid quantity is required";

// Validate borrower name (basic pattern check)
if (!preg_match('/^[A-Za-z\s\-\']{2,50}$/', $borrowerName)) {
    $errors[] = "Invalid borrower name format";
}

// Validate contact number (must be exactly 11 digits)
$borrowerContact = preg_replace('/[^0-9]/', '', $borrowerContact);
if (strlen($borrowerContact) !== 11 || !is_numeric($borrowerContact)) {
    $errors[] = "Contact number must be 11 digits";
}

// Date validations
try {
    $today = new DateTime();
    $today->setTime(0, 0, 0);
    
    $borrowDateObj = new DateTime($borrowDate);
    $returnDateObj = new DateTime($expectedReturnDate);
    
    if ($borrowDateObj < $today) {
        $errors[] = "Borrow date cannot be in the past";
    }
    
    if ($returnDateObj < $today) {
        $errors[] = "Return date cannot be in the past";
    }
    
    if ($returnDateObj <= $borrowDateObj) {
        $errors[] = "Return date must be after borrow date";
    }
    
} catch (Exception $e) {
    $errors[] = "Invalid date format";
}

if (!empty($errors)) {
    $_SESSION['error'] = implode("<br>", $errors);
    header("Location: ../dashboard.php?section=add_borrowing");
    exit();
}

// Check available quantity for the selected item
try {
    $stmt = $conn->prepare("SELECT quantity FROM items WHERE item_id = ?");
    $stmt->bind_param("i", $itemId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$result) {
        $_SESSION['error'] = "Selected item not found.";
        header("Location: ../dashboard.php?section=add_borrowing");
        exit();
    }
    
    $availableQuantity = $result['quantity'];
    if ($borrowQuantity > $availableQuantity) {
        $_SESSION['error'] = "Not enough stock available. Only $availableQuantity left.";
        header("Location: ../dashboard.php?section=add_borrowing");
        exit();
    }
} catch (Exception $e) {
    error_log("Error checking item quantity: " . $e->getMessage());
    $_SESSION['error'] = "Error processing request. Please try again.";
    header("Location: ../dashboard.php?section=add_borrowing");
    exit();
}

// Insert record with duplicate check
try {
    $stmtCheck = $conn->prepare("SELECT borrowing_id FROM borrowings 
                               WHERE item_id = ? AND borrower_name = ? 
                               AND borrow_date = ? AND expected_return_date = ?
                               LIMIT 1");
    $stmtCheck->bind_param("isss", $itemId, $borrowerName, $borrowDate, $expectedReturnDate);
    $stmtCheck->execute();
    if ($stmtCheck->get_result()->num_rows > 0) {
        $_SESSION['error'] = "This borrowing record already exists!";
        header("Location: ../dashboard.php?section=add_borrowing");
        exit();
    }
    $stmtCheck->close();

    $stmt = $conn->prepare("INSERT INTO borrowings (item_id, borrower_name, borrower_contact, borrow_date, expected_return_date, condition_notes, quantity) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssi", $itemId, $borrowerName, $borrowerContact, $borrowDate, $expectedReturnDate, $conditionNotes, $borrowQuantity);
    $stmt->execute();
    $stmt->close();
    
    // Deduct the borrowed quantity from the item
    $stmtUpdate = $conn->prepare("UPDATE items SET quantity = quantity - ? WHERE item_id = ?");
    $stmtUpdate->bind_param("ii", $borrowQuantity, $itemId);
    $stmtUpdate->execute();
    $stmtUpdate->close();
    
    unset($_SESSION['old']); // Clear old values on success
    $_SESSION['success'] = "Borrowing record added successfully!";
} catch (Exception $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = "Error adding borrowing record. Please try again.";
} finally {
    $conn->close();
    header("Location: ../dashboard.php?section=add_borrowing");
    exit();
}
?>
