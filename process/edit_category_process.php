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
    header("Location: ../dashboard.php?section=manage_categories");
    exit();
}

$categoryId = filter_input(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT);
$name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
$description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING));

// Validation
if (empty($name)) {
    $_SESSION['error'] = "Category name is required!";
    header("Location: ../dashboard.php?section=manage_categories");
    exit();
}

try {
    // Check if category name already exists (excluding current category)
    $checkStmt = $conn->prepare("SELECT category_id FROM categories WHERE name = ? AND category_id != ?");
    $checkStmt->bind_param("si", $name, $categoryId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "A category with this name already exists!";
        header("Location: ../dashboard.php?section=manage_categories");
        exit();
    }
    $checkStmt->close();

    // Update category
    $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ? WHERE category_id = ?");
    $stmt->bind_param("ssi", $name, $description, $categoryId);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Category updated successfully!";
    } else {
        throw new Exception("Update failed: " . $stmt->error);
    }
    
} catch (Exception $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = "Error updating category. Please try again.";
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
    header("Location: ../dashboard.php?section=manage_categories");
    exit();
}
