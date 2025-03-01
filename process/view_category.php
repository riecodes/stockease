<?php
require_once '../include/db.php';

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$categoryId = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

try {
    $stmt = $conn->prepare("SELECT * FROM categories WHERE category_id = ?");
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $category = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($category) {
        echo '
        <div class="row">
            <div class="col-md-6">
                <p><strong>Category Name:</strong><br>' . htmlspecialchars($category['name']) . '</p>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <p><strong>Description:</strong><br>' . nl2br(htmlspecialchars($category['description'] ?? 'No description available')) . '</p>
            </div>
        </div>';
    } else {
        echo '<p class="text-danger">Category not found</p>';
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    echo '<p class="text-danger">Error loading category details</p>';
}
