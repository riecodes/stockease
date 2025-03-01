<?php
session_start();
require_once '../include/db.php';

if (!isset($_SESSION['admin_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../index.php");
    exit();
}

$itemId = filter_input(INPUT_POST, 'item_id', FILTER_SANITIZE_NUMBER_INT);
$itemName = trim(filter_input(INPUT_POST, 'itemName', FILTER_SANITIZE_STRING));
$category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_NUMBER_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT);
$description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING));

// Validation
if (empty($itemName) || empty($category) || empty($quantity)) {
    $_SESSION['error'] = "Please fill all required fields";
    header("Location: ../dashboard.php?section=edit_item&id=$itemId");
    exit();
}

try {
    $stmt = $conn->prepare("
        UPDATE items SET
            name = ?,
            category_id = ?,
            quantity = ?,
            description = ?
        WHERE item_id = ?
    ");
    $stmt->bind_param("siisi", $itemName, $category, $quantity, $description, $itemId);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Item updated successfully!";
    } else {
        throw new Exception("Update failed: " . $stmt->error);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = "Error updating item. Please try again.";
} finally {
    $stmt->close();
    $conn->close();
    header("Location: ../dashboard.php?section=manage_item");
    exit();
}
