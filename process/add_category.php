<?php
session_start();
require_once '../include/db.php'; // Adjust path as needed

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method!";
    header("Location: ../dashboard.php?section=add_category");
    exit();
}

// Sanitize inputs
$categoryName = trim(filter_input(INPUT_POST, 'categoryName', FILTER_SANITIZE_STRING));
$categoryDesc = trim(filter_input(INPUT_POST, 'categoryDesc', FILTER_SANITIZE_STRING));

// Validate inputs
if (empty($categoryName)) {
    $_SESSION['error'] = "Category name is required!";
    header("Location: ../dashboard.php?section=add_category");
    exit();
}

try {
    // Check if category already exists
    $stmt = $conn->prepare("SELECT category_id FROM categories WHERE name = ?");
    $stmt->bind_param("s", $categoryName);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        $_SESSION['error'] = "Category already exists!";
        header("Location: ../dashboard.php?section=add_category");
        exit();
    }

    // Insert new category
    $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $categoryName, $categoryDesc);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Category added successfully!";
    } else {
        throw new Exception("Database error: " . $stmt->error);
    }
    
} catch (Exception $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = "Error adding category. Please try again.";
} finally {
    $stmt->close();
    $conn->close();
    header("Location: ../dashboard.php?section=add_category");
    exit();
}
?>