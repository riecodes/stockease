<?php
session_start();
require_once '../include/db.php'; // Adjust path as needed

if (!isset($_SESSION['admin_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../index.php");
    exit();
}

// Verify CSRF token
if (!isset($_POST['form_token']) || $_POST['form_token'] !== $_SESSION['form_token']) {
    $_SESSION['error'] = "Invalid form submission!";
    header("Location: ../dashboard.php?section=add_category");
    exit();
}

// Sanitize inputs
$name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
$description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING));

// Store old input for form repopulation
$_SESSION['old'] = [
    'name' => $name,
    'description' => $description
];

// Validation
if (empty($name)) {
    $_SESSION['error'] = "Category name is required!";
    header("Location: ../dashboard.php?section=add_category");
    exit();
}

try {
    // Check if category name already exists
    $checkStmt = $conn->prepare("SELECT category_id FROM categories WHERE name = ?");
    $checkStmt->bind_param("s", $name);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "A category with this name already exists!";
        header("Location: ../dashboard.php?section=add_category");
        exit();
    }
    $checkStmt->close();

    // Insert new category
    $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $description);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Category added successfully!";
        // Clear old input after successful submission
        unset($_SESSION['old']);
    } else {
        throw new Exception("Database error: " . $stmt->error);
    }
    
} catch (Exception $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = "Error adding category. Please try again.";
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
    header("Location: ../dashboard.php?section=manage_categories");
    exit();
}
?>