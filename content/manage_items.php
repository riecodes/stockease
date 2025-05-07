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

<link rel="stylesheet" href="content/css/manage.css">

<div class="container cvsu-container">
    <div class="cvsu-header">
        <h2><span class="material-icons">inventory_2</span>Manage Items</h2>
        <p>View and manage inventory items</p>
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

    <div class="cvsu-content-container">
        <div class="card cvsu-card">
            <div class="card-body">
                <div class="d-flex justify-content-end mb-4">
                    <a href="dashboard.php?section=add_item" class="btn btn-compact btn-primary">
                        <span class="material-icons">add_circle</span>Add New Item
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
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
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <span class="material-icons">report_problem</span> No items found
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['name']) ?></td>
                                        <td><?= htmlspecialchars($item['category_name'] ?? 'Uncategorized') ?></td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td><?= htmlspecialchars($item['description'] ?? 'No description') ?></td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-compact btn-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $item['item_id'] ?>">
                                                    <span class="material-icons">edit</span>
                                                </button>
                                                <button type="button" class="btn btn-compact btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $item['item_id'] ?>">
                                                    <span class="material-icons">delete</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal<?= $item['item_id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $item['item_id'] ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editModalLabel<?= $item['item_id'] ?>">
                                                        <span class="material-icons">edit</span>Edit Item
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="process/edit_item.php" method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                                                        <div class="mb-4">
                                                            <label for="name<?= $item['item_id'] ?>" class="form-label">Item Name <span class="required">*</span></label>
                                                            <input type="text" class="form-control" id="name<?= $item['item_id'] ?>" name="name" value="<?= htmlspecialchars($item['name']) ?>" required>
                                                        </div>
                                                        <div class="mb-4">
                                                            <label for="category<?= $item['item_id'] ?>" class="form-label">Category <span class="required">*</span></label>
                                                            <select class="form-select" id="category<?= $item['item_id'] ?>" name="category_id" required>
                                                                <option value="">Select Category</option>
                                                                <?php foreach ($categories as $category): ?>
                                                                    <option value="<?= $category['category_id'] ?>" <?= $category['category_id'] == $item['category_id'] ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars($category['name']) ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="mb-4">
                                                            <label for="quantity<?= $item['item_id'] ?>" class="form-label">Quantity <span class="required">*</span></label>
                                                            <input type="number" class="form-control" id="quantity<?= $item['item_id'] ?>" name="quantity" value="<?= $item['quantity'] ?>" min="0" required>
                                                        </div>
                                                        <div class="mb-4">
                                                            <label for="description<?= $item['item_id'] ?>" class="form-label">Description</label>
                                                            <textarea class="form-control" id="description<?= $item['item_id'] ?>" name="description" rows="3"><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
                                                            <div class="textarea-counter">
                                                                <span id="charCount<?= $item['item_id'] ?>"><?= strlen($item['description'] ?? '') ?></span>/200
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-compact btn-secondary" data-bs-dismiss="modal">
                                                            <span class="material-icons">close</span>Cancel
                                                        </button>
                                                        <button type="submit" class="btn btn-compact btn-primary">
                                                            <span class="material-icons">save</span>Save Changes
                                                        </button>
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
                                                    <h5 class="modal-title" id="deleteModalLabel<?= $item['item_id'] ?>">
                                                        <span class="material-icons">delete</span>Delete Item
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Are you sure you want to delete the item "<?= htmlspecialchars($item['name']) ?>"?</p>
                                                    <div class="alert alert-warning">
                                                        <span class="material-icons">warning</span>
                                                        This item currently has <?= $item['quantity'] ?> units in stock. Deleting this item will permanently remove all stock information.
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-compact btn-secondary" data-bs-dismiss="modal">
                                                        <span class="material-icons">close</span>Cancel
                                                    </button>
                                                    <form action="process/delete_item.php" method="POST" class="d-inline">
                                                        <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                                                        <button type="submit" class="btn btn-compact btn-danger">
                                                            <span class="material-icons">delete</span>Delete
                                                        </button>
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
        </div>
    </div>
</div>

<script>
    // Character counter for description textareas
    document.querySelectorAll('textarea[id^="description"]').forEach(textarea => {
        const itemId = textarea.id.replace('description', '');
        const charCount = document.getElementById('charCount' + itemId);
        
        if (charCount) {
            textarea.addEventListener('input', () => {
                charCount.textContent = textarea.value.length;
            });
        }
    });
</script>

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