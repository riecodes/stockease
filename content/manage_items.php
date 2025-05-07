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
                    <button type="button" class="btn btn-compact btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                        <span class="material-icons">add_circle</span>Add New Item
                    </button>
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
                                        <td title="<?= htmlspecialchars($item['description'] ?? 'No description') ?>"><?= htmlspecialchars($item['description'] ?? 'No description') ?></td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-compact btn-info" data-bs-toggle="modal" data-bs-target="#viewModal<?= $item['item_id'] ?>">
                                                    <span class="material-icons">visibility</span>
                                                </button>
                                                <button type="button" class="btn btn-compact btn-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $item['item_id'] ?>">
                                                    <span class="material-icons">edit</span>
                                                </button>
                                                <button type="button" class="btn btn-compact btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $item['item_id'] ?>">
                                                    <span class="material-icons">delete</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- View Modal -->
                                    <div class="modal fade" id="viewModal<?= $item['item_id'] ?>" tabindex="-1" aria-labelledby="viewModalLabel<?= $item['item_id'] ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="viewModalLabel<?= $item['item_id'] ?>">
                                                        <span class="material-icons">visibility</span>View Item Details
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-4">
                                                        <label class="form-label fw-bold">Item Name</label>
                                                        <p class="mb-0"><?= htmlspecialchars($item['name']) ?></p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <label class="form-label fw-bold">Category</label>
                                                        <p class="mb-0"><?= htmlspecialchars($item['category_name']) ?></p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <label class="form-label fw-bold">Quantity</label>
                                                        <p class="mb-0"><?= $item['quantity'] ?> units</p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <label class="form-label fw-bold">Description</label>
                                                        <p class="mb-0"><?= nl2br(htmlspecialchars($item['description'] ?? 'No description')) ?></p>
                                                    </div>
                                                    <div class="mb-4">
                                                        <label class="form-label fw-bold">Status</label>
                                                        <p class="mb-0">
                                                            <?php if ($item['quantity'] > 0): ?>
                                                                <span class="badge bg-success">Available</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-danger">Out of Stock</span>
                                                            <?php endif; ?>
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-compact btn-secondary" data-bs-dismiss="modal">
                                                        <span class="material-icons">close</span>Close
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

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
                                                <form action="process/edit_item_process.php" method="POST">
                                                    <input type="hidden" name="form_token" value="<?= $_SESSION['form_token'] ?>">
                                                    <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                                                    
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label for="name<?= $item['item_id'] ?>" class="form-label">Item Name <span class="required">*</span></label>
                                                            <input type="text" class="form-control" id="name<?= $item['item_id'] ?>" name="name" value="<?= htmlspecialchars($item['name']) ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="category_id<?= $item['item_id'] ?>" class="form-label">Category <span class="required">*</span></label>
                                                            <select class="form-select" id="category_id<?= $item['item_id'] ?>" name="category_id" required>
                                                                <?php foreach ($categories as $category): ?>
                                                                    <option value="<?= $category['category_id'] ?>" <?= ($category['category_id'] == $item['category_id']) ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars($category['name']) ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="quantity<?= $item['item_id'] ?>" class="form-label">Quantity <span class="required">*</span></label>
                                                            <input type="number" class="form-control" id="quantity<?= $item['item_id'] ?>" name="quantity" value="<?= $item['quantity'] ?>" min="0" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="description<?= $item['item_id'] ?>" class="form-label">Description</label>
                                                            <textarea class="form-control" id="description<?= $item['item_id'] ?>" name="description" rows="3"><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
                                                            <div class="textarea-counter">
                                                                <span id="charCount<?= $item['item_id'] ?>"><?= strlen($item['description'] ?? '') ?></span>/500 characters
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
                                                    <p>Are you sure you want to delete the item "<strong><?= htmlspecialchars($item['name']) ?></strong>"?</p>
                                                    <?php if ($item['quantity'] > 0): ?>
                                                        <div class="alert alert-warning">
                                                            <span class="material-icons">warning</span>
                                                            This item still has <?= $item['quantity'] ?> unit(s) in stock.
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (isset($item['borrowed_count']) && $item['borrowed_count'] > 0): ?>
                                                        <div class="alert alert-danger">
                                                            <span class="material-icons">error</span>
                                                            This item has <?= $item['borrowed_count'] ?> active borrowing(s). Please ensure all items are returned before deletion.
                                                        </div>
                                                    <?php endif; ?>
                                                    <p class="text-danger mb-0">This action cannot be undone.</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-compact btn-secondary" data-bs-dismiss="modal">
                                                        <span class="material-icons">close</span>Cancel
                                                    </button>
                                                    <form action="process/delete_item_process.php" method="POST" class="d-inline">
                                                        <input type="hidden" name="form_token" value="<?= $_SESSION['form_token'] ?>">
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

<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addItemModalLabel">
                    <span class="material-icons">add_circle</span>Add New Item
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="process/add_item_process.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="form_token" value="<?= $_SESSION['form_token'] ?>">
                    <div class="mb-4">
                        <label for="name" class="form-label">Item Name <span class="required">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-4">
                        <label for="category_id" class="form-label">Category <span class="required">*</span></label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['category_id'] ?>">
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="quantity" class="form-label">Quantity <span class="required">*</span></label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="0" required>
                    </div>
                    <div class="mb-4">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        <div class="textarea-counter">
                            <span id="charCount">0</span>/200
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-compact btn-secondary" data-bs-dismiss="modal">
                        <span class="material-icons">close</span>Cancel
                    </button>
                    <button type="submit" class="btn btn-compact btn-primary">
                        <span class="material-icons">save</span>Save Item
                    </button>
                </div>
            </form>
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

    // Character counter for description textarea
    document.getElementById('description').addEventListener('input', function() {
        const maxLength = 200;
        const currentLength = this.value.length;
        document.getElementById('charCount').textContent = currentLength;
        if (currentLength > maxLength) {
            this.value = this.value.substring(0, maxLength);
            document.getElementById('charCount').textContent = maxLength;
        }
    });
</script>