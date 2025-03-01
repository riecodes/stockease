<?php
session_start();
require_once '../include/db.php'; // Adjust path as needed

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method!";
    header("Location: ../dashboard.php?section=add_item");
    exit();
}

// Sanitize inputs
$itemName = trim(filter_input(INPUT_POST, 'itemName', FILTER_SANITIZE_STRING));
$categoryId = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_NUMBER_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT);
$description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING));

// Validate inputs
if (empty($itemName) || empty($categoryId) || empty($quantity)) {
    $_SESSION['error'] = "All required fields must be filled!";
    header("Location: ../dashboard.php?section=add_item");
    exit();
}

try {
    // Insert new item
    $stmt = $conn->prepare("INSERT INTO items (name, category_id, quantity, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siis", $itemName, $categoryId, $quantity, $description);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Item added successfully!";
    } else {
        throw new Exception("Database error: " . $stmt->error);
    }
    
} catch (Exception $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = "Error adding item. Please try again.";
} finally {
    $stmt->close();
    $conn->close();
    header("Location: ../dashboard.php?section=add_item");
    exit();
}