<?php
session_start();
require_once '../include/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../index.php");
    exit();
}

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Invalid request!";
    header("Location: ../dashboard.php?section=manage_item");
    exit();
}

$itemId = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

try {
    $stmt = $conn->prepare("DELETE FROM items WHERE item_id = ?");
    $stmt->bind_param("i", $itemId);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Item deleted successfully!";
    } else {
        throw new Exception("Delete failed: " . $stmt->error);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = "Error deleting item. Please try again.";
} finally {
    $stmt->close();
    $conn->close();
    header("Location: ../dashboard.php?section=manage_item");
    exit();
}
