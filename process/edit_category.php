<?php
session_start();
require_once '../include/db.php';

if (!isset($_SESSION['admin_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../index.php");
    exit();
}

$categoryId = filter_input(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT);
$categoryName = trim(filter_input(INPUT_POST, 'categoryName', FILTER_SANITIZE_STRING));
$description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING));

// Validation
if (empty($categoryName)) {
    $_SESSION['error'] = "Please fill all required fields";
    header("Location: ../dashboard.php?section=edit_category&id=$categoryId");
    exit();
}

try {
    $stmt = $conn->prepare("
        UPDATE categories SET             
            name = ?,
            description = ?
        WHERE category_id = ?
    ");
    $stmt->bind_param("ssi",$categoryName, $description, $categoryId);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Category updated successfully!";
    } else {
        throw new Exception("Update failed: " . $stmt->error);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = "Error updating category. Please try again.";
} finally {
    $stmt->close();
    $conn->close();
    header("Location: ../dashboard.php?section=manage_categories");
    exit();
}
