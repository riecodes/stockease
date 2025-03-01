<?php
require_once '../include/db.php';

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$itemId = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

try {
    $stmt = $conn->prepare("
        SELECT i.*, c.name AS category_name 
        FROM items i
        LEFT JOIN categories c ON i.category_id = c.category_id
        WHERE i.item_id = ?
    ");
    $stmt->bind_param("i", $itemId);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();

    if ($item) {
        echo '
        <div class="row">
            <div class="col-md-6">
                <p><strong>Item Name:</strong><br>' . htmlspecialchars($item['name']) . '</p>
                <p><strong>Category:</strong><br>' . htmlspecialchars($item['category_name'] ?? 'N/A') . '</p>
            </div>
            <div class="col-md-6">
                <p><strong>Quantity:</strong><br>' . $item['quantity'] . '</p>
                <p><strong>Last Updated:</strong><br>' . date('M d, Y h:i A', strtotime($item['updated_at'])) . '</p>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <p><strong>Description:</strong><br>' . nl2br(htmlspecialchars($item['description'] ?? 'No description')) . '</p>
            </div>
        </div>';
    } else {
        echo '<p class="text-danger">Item not found</p>';
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    echo '<p class="text-danger">Error loading item details</p>';
}
