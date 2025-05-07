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

if (!$categoryId) {
    $_SESSION['error'] = "Invalid category ID!";
    header("Location: ../dashboard.php?section=manage_categories");
    exit();
}

try {
    // Delete all items that belong to this category
    $stmtItems = $conn->prepare("DELETE FROM items WHERE category_id = ?");
    $stmtItems->bind_param("i", $categoryId);
    if (!$stmtItems->execute()) {
        throw new Exception("Error deleting items: " . $stmtItems->error);
    }
    $stmtItems->close();

    // Delete the category itself
    $stmtCat = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
    $stmtCat->bind_param("i", $categoryId);
    if ($stmtCat->execute()) {
        $_SESSION['success'] = "Category and associated items deleted successfully!";
    } else {
        throw new Exception("Delete failed: " . $stmtCat->error);
    }
    $stmtCat->close();
} catch (Exception $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = "Error deleting category. Please try again.";
} finally {
    $conn->close();
    header("Location: ../dashboard.php?section=manage_categories");
    exit();
}
?>
