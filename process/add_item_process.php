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

// Sanitize inputs
$name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
$categoryId = filter_input(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT);
$description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING));

// Validation
if (empty($name) || empty($categoryId) || $quantity === false) {
    $_SESSION['error'] = "Please fill all required fields!";
    header("Location: ../dashboard.php?section=manage_items");
    exit();
}

try {
    // Check if item name already exists
    $checkStmt = $conn->prepare("SELECT item_id FROM items WHERE name = ?");
    $checkStmt->bind_param("s", $name);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "An item with this name already exists!";
        header("Location: ../dashboard.php?section=manage_items");
        exit();
    }
    $checkStmt->close();

    // Insert new item
    $stmt = $conn->prepare("INSERT INTO items (name, category_id, quantity, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siis", $name, $categoryId, $quantity, $description);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Item added successfully!";
    } else {
        throw new Exception("Database error: " . $stmt->error);
    }
    
} catch (Exception $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = "Error adding item. Please try again.";
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
    header("Location: ../dashboard.php?section=manage_items");
    exit();
}