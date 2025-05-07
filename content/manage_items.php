<?php
require_once 'include/db.php'; // Adjust the path as needed
// Fetch all items with their category names
$items = [];
try {
    $stmt = $conn->prepare("
        SELECT i.*, c.name AS category_name 
        FROM items i
        LEFT JOIN categories c ON i.category_id = c.category_id
        ORDER BY i.name
    ");
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching items: " . $e->getMessage());
    $_SESSION['error'] = "Error loading items. Please try again.";
}

// Fetch categories for the edit modal dropdown
$stmt = $conn->prepare("SELECT category_id, name FROM categories ORDER BY name");
$stmt->execute();
$categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<head>
    <link rel="stylesheet" href="css/manage.css">
</head>

<!-- Main Content -->
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
                    <th>Description</th>
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
                            <td><?= htmlspecialchars($item['description'] ?? 'No description') ?></td>
                            <td>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $item['item_id'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $item['item_id'] ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal<?= $item['item_id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $item['item_id'] ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editModalLabel<?= $item['item_id'] ?>">Edit Item</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="process/edit_item.php" method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                                            <div class="mb-3">
                                                <label for="name<?= $item['item_id'] ?>" class="form-label">Item Name</label>
                                                <input type="text" class="form-control" id="name<?= $item['item_id'] ?>" name="name" value="<?= htmlspecialchars($item['name']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="category<?= $item['item_id'] ?>" class="form-label">Category</label>
                                                <select class="form-select" id="category<?= $item['item_id'] ?>" name="category_id" required>
                                                    <option value="">Select Category</option>
                                                    <?php foreach ($categories as $category): ?>
                                                        <option value="<?= $category['category_id'] ?>" <?= $category['category_id'] == $item['category_id'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($category['name']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="quantity<?= $item['item_id'] ?>" class="form-label">Quantity</label>
                                                <input type="number" class="form-control" id="quantity<?= $item['item_id'] ?>" name="quantity" value="<?= $item['quantity'] ?>" min="0" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="description<?= $item['item_id'] ?>" class="form-label">Description</label>
                                                <textarea class="form-control" id="description<?= $item['item_id'] ?>" name="description" rows="3"><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Delete Modal -->
                        <div class="modal fade" id="deleteModal<?= $item['item_id'] ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?= $item['item_id'] ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="deleteModalLabel<?= $item['item_id'] ?>">Delete Item</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Are you sure you want to delete the item "<?= htmlspecialchars($item['name']) ?>"?</p>
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            This item currently has <?= $item['quantity'] ?> units in stock. Deleting this item will permanently remove all stock information.
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <form action="process/delete_item.php" method="POST" class="d-inline">
                                            <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                                            <button type="submit" class="btn btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
/* Ensure modals appear on top of everything */
.modals-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 9999;
}

.modals-container .modal {
    pointer-events: auto;
}

.modal-backdrop {
    z-index: 9998;
}
</style>