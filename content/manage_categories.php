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
                                <button class="btn btn-sm btn-outline-primary view-category"
                                    data-id="<?= $category['category_id'] ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#categoryModal">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <a href="dashboard.php?section=edit_category&id=<?= $category['category_id'] ?>"
                                    class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger delete-category"
                                    data-id="<?= $category['category_id'] ?>">
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
    // Manage Categories Details
    document.querySelectorAll('.view-category').forEach(button => {
        button.addEventListener('click', function() {
            const categoryId = this.dataset.id;
            fetch(`process/view_category.php?id=${categoryId}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('categoryDetails').innerHTML = data;
                });
        });
    });

    // Delete Category
    document.querySelectorAll('.delete-category').forEach(button => {
        button.addEventListener('click', function() {
            const categoryId = this.dataset.id;
            if (confirm('Are you sure you want to delete this category?')) {
                window.location.href = `process/delete_category.php?id=${categoryId}`;
            }
        });
    });
</script>