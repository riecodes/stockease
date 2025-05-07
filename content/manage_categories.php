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

<link rel="stylesheet" href="content/css/manage.css">

<div class="container cvsu-container">
    <div class="cvsu-header">
        <h2><span class="material-icons">category</span>Manage Categories</h2>
        <p>View and manage inventory categories</p>
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
                    <button type="button" class="btn btn-compact btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        <span class="material-icons">add_circle</span>Add New Category
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Category Name</th>
                                <th>Description</th>
                                <th>Items Count</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($categories)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <span class="material-icons">report_problem</span> No categories found
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($categories as $category): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($category['name']) ?></td>
                                        <td><?= htmlspecialchars($category['description'] ?? 'No description') ?></td>
                                        <td><?= $category['item_count'] ?></td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-compact btn-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $category['category_id'] ?>">
                                                    <span class="material-icons">edit</span>
                                                </button>
                                                <button type="button" class="btn btn-compact btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $category['category_id'] ?>">
                                                    <span class="material-icons">delete</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal<?= $category['category_id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $category['category_id'] ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editModalLabel<?= $category['category_id'] ?>">
                                                        <span class="material-icons">edit</span>Edit Category
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="process/edit_category.php" method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="category_id" value="<?= $category['category_id'] ?>">
                                                        <div class="mb-4">
                                                            <label for="name<?= $category['category_id'] ?>" class="form-label">Category Name <span class="required">*</span></label>
                                                            <input type="text" class="form-control" id="name<?= $category['category_id'] ?>" name="name" value="<?= htmlspecialchars($category['name']) ?>" required>
                                                        </div>
                                                        <div class="mb-4">
                                                            <label for="description<?= $category['category_id'] ?>" class="form-label">Description</label>
                                                            <textarea class="form-control" id="description<?= $category['category_id'] ?>" name="description" rows="3"><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
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
                                    <div class="modal fade" id="deleteModal<?= $category['category_id'] ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?= $category['category_id'] ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel<?= $category['category_id'] ?>">
                                                        <span class="material-icons">delete</span>Delete Category
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Are you sure you want to delete the category "<?= htmlspecialchars($category['name']) ?>"?</p>
                                                    <?php if ($category['item_count'] > 0): ?>
                                                        <div class="alert alert-warning">
                                                            <span class="material-icons">warning</span>
                                                            This category has <?= $category['item_count'] ?> item(s) associated with it. Deleting this category will also delete all associated items.
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-compact btn-secondary" data-bs-dismiss="modal">
                                                        <span class="material-icons">close</span>Cancel
                                                    </button>
                                                    <form action="process/delete_category.php" method="POST" class="d-inline">
                                                        <input type="hidden" name="category_id" value="<?= $category['category_id'] ?>">
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

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCategoryModalLabel">
                    <span class="material-icons">add_circle</span>Add New Category
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="process/add_category_process.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="form_token" value="<?= $_SESSION['form_token'] ?>">
                    <div class="mb-4">
                        <label for="name" class="form-label">Category Name <span class="required">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
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
                        <span class="material-icons">save</span>Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Character counter for description textarea
    const textarea = document.getElementById('description');
    const charCount = document.getElementById('charCount');

    if (textarea && charCount) {
        textarea.addEventListener('input', () => {
            charCount.textContent = textarea.value.length;
        });
    }
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