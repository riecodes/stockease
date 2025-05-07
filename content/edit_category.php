<?php
if (!isset($_GET['id'])) {
    header("Location: dashboard.php?section=manage_categories");
    exit();
}

$categoryId = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

// Fetch category details
try {
    $stmt = $conn->prepare("SELECT * FROM categories WHERE category_id = ?");
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $category = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$category) {
        $_SESSION['error'] = "Category not found";
        header("Location: dashboard.php?section=manage_categories");
        exit();
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = "Error loading category";
    header("Location: dashboard.php?section=manage_categories");
    exit();
}

// Fetch categories
$categories = [];
try {
    $stmt = $conn->prepare("SELECT category_id, name FROM categories");
    $stmt->execute();
    $categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching categories: " . $e->getMessage());
    $_SESSION['error'] = "Error loading categories";
}
?>

<head>    
    <title>Edit Category - CVSU</title>    
    <link rel="stylesheet" href="css/add.css">
</head>
<body>
    <div class="container-fluid cvsu-container">
        <div class="cvsu-header">
            <h2><i class="fas fa-edit me-2"></i>Edit Category</h2>
            <p>Update category details below</p>
        </div>

        <!-- Display Success/Error Messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <span class="required">* </span><?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form action="process/edit_category.php" method="POST">
            <input type="hidden" name="category_id" value="<?= $category['category_id'] ?>">

            <div class="mb-4">
                <label class="form-label">Category Name <span class="required">*<span></label>
                <input type="text" name="categoryName" class="form-control" value="<?= htmlspecialchars($category['name']) ?>" required>
            </div>

            <div class="mb-4">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4" maxlength="200"><?= htmlspecialchars($category['description']) ?></textarea>
                <div class="textarea-counter">
                    <span id="charCount">0</span>/200
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn cvsu-btn-primary">
                    <i class="fas fa-save me-2"></i>Update Category
                </button>
                <a href="dashboard.php?section=manage_categories" class="btn btn-secondary">
                    <i class="fas fa-xmark me-2"></i>Cancel
                </a>
            </div>
        </form>
    </div>


    <!-- Character Counter Script -->
    <script>
        const textarea = document.querySelector('textarea[name="description"]');
        const charCount = document.getElementById('charCount');
        
        textarea.addEventListener('input', () => {
            charCount.textContent = textarea.value.length;
        });
    </script>
</body>
</html>
