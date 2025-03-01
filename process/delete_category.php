<?php
session_start();
require_once '../include/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../index.php");
    exit();
}

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Invalid request!";
    header("Location: ../dashboard.php?section=manage_categories");
    exit();
}

$categoryId = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

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
