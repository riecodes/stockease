<?php
session_start();
require_once '../include/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../index.php");
    exit();
}

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Invalid request!";
    header("Location: ../dashboard.php?section=manage_borrowings");
    exit();
}

$borrowingId = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

try {
    $stmt = $conn->prepare("DELETE FROM borrowings WHERE borrowing_id = ?");
    $stmt->bind_param("i", $borrowingId);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Borrowing record deleted successfully!";
    } else {
        throw new Exception("Delete failed: " . $stmt->error);
    }
    $stmt->close();
} catch (Exception $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = "Error deleting borrowing record. Please try again.";
} finally {
    $conn->close();
    header("Location: ../dashboard.php?section=manage_borrowings");
    exit();
}
?>
