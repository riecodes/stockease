<?php
require_once 'include/db.php'; // Adjust the path as needed
// Fetch all categories
$categories = [];
try {
    $stmt = $conn->prepare("
        SELECT c.*, COUNT(i.item_id) AS item_count 
        FROM categories c
        LEFT JOIN items i ON c.category_id = i.category_id
        GROUP BY c.category_id
    ");
    $stmt->execute();
    $categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching categories: " . $e->getMessage());
    $_SESSION['error'] = "Error loading categories. Please try again.";
}
?>

<head>
    <link rel="stylesheet" href="css/manage.css">
</head>

<!-- Main Content -->
<div class="cvsu-container">
    <div class="d-flex justify-content-between align-items-center mb-4 cvsu-header">
        <h2><i class="fas fa-tags me-2"></i>Manage Categories</h2>
        <a href="dashboard.php?section=add_category" class="btn cvsu-btn-primary">
            <i class="fas fa-plus-circle me-2"></i>Add New Category
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
                    <th>Category Name</th>
                    <th>Description</th>
                    <th>Items Count</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                    <!-- No records found -->
                    <tr>
                        <td colspan="4" class="text-center py-4">
                            <i class="fas fa-exclamation-circle me-2"></i> No records found.
                        </td>
                    </tr>
                <?php else: ?>
                    <!-- Display records -->
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?= htmlspecialchars($category['name']) ?></td>
                            <td><?= htmlspecialchars($category['description'] ?? 'No description') ?></td>
                            <td><?= $category['item_count'] ?></td>
                            <td>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $category['category_id'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $category['category_id'] ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal<?= $category['category_id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $category['category_id'] ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editModalLabel<?= $category['category_id'] ?>">Edit Category</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="process/edit_category.php" method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="category_id" value="<?= $category['category_id'] ?>">
                                            <div class="mb-3">
                                                <label for="name<?= $category['category_id'] ?>" class="form-label">Category Name</label>
                                                <input type="text" class="form-control" id="name<?= $category['category_id'] ?>" name="name" value="<?= htmlspecialchars($category['name']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="description<?= $category['category_id'] ?>" class="form-label">Description</label>
                                                <textarea class="form-control" id="description<?= $category['category_id'] ?>" name="description" rows="3"><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
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
                        <div class="modal fade" id="deleteModal<?= $category['category_id'] ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?= $category['category_id'] ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="deleteModalLabel<?= $category['category_id'] ?>">Delete Category</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Are you sure you want to delete the category "<?= htmlspecialchars($category['name']) ?>"?</p>
                                        <?php if ($category['item_count'] > 0): ?>
                                            <div class="alert alert-warning">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                This category has <?= $category['item_count'] ?> item(s) associated with it. Deleting this category will also delete all associated items.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <form action="process/delete_category.php" method="POST" class="d-inline">
                                            <input type="hidden" name="category_id" value="<?= $category['category_id'] ?>">
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