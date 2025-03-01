<?php
// Fetch all items with category names
$items = [];
try {
    $stmt = $conn->prepare("
        SELECT i.*, c.name AS category_name 
        FROM items i
        LEFT JOIN categories c ON i.category_id = c.category_id
        ORDER BY i.created_at DESC
    ");
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching items: " . $e->getMessage());
    $_SESSION['error'] = "Error loading items. Please try again.";
}
?>
<head>
    <link rel="stylesheet" href="css/manage.css">
</head>
<div class="cvsu-container">
    <div class="d-flex justify-content-between align-items-center mb-4 cvsu-header">
        <h2><i class="fas fa-boxes me-2"></i>Manage Items</h2>
        <a href="dashboard.php?section=add_item" class="btn cvsu-btn-primary">
            <i class="fas fa-plus-circle me-2"></i>Add New Item
        </a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <span class="required">* </span><?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <thead class="cvsu-bg-green text-white">
                <tr>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Last Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                    <!-- No records found -->
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <i class="fas fa-exclamation-circle me-2"></i> No records found.
                        </td>
                    </tr>
                <?php else: ?>
                    <!-- Display records -->
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td><?= htmlspecialchars($item['category_name'] ?? 'Uncategorized') ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td><?= date('M d, Y h:i A', strtotime($item['updated_at'])) ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary view-item"
                                    data-id="<?= $item['item_id'] ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#itemModal">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <a href="dashboard.php?section=edit_item&id=<?= $item['item_id'] ?>"
                                    class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger delete-item"
                                    data-id="<?= $item['item_id'] ?>">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // View Item Details
    document.querySelectorAll('.view-item').forEach(button => {
        button.addEventListener('click', function() {
            const itemId = this.dataset.id;
            fetch(`process/manage_item.php?id=${itemId}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('itemDetails').innerHTML = data;
                });
        });
    });

    // Delete Item
    document.querySelectorAll('.delete-item').forEach(button => {
        button.addEventListener('click', function() {
            const itemId = this.dataset.id;
            if (confirm('Are you sure you want to delete this item?')) {
                window.location.href = `process/delete_item.php?id=${itemId}`;
            }
        });
    });
</script>