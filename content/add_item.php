<?php
// Fetch categories for dropdown
$categories = [];
try {
    $stmt = $conn->prepare("SELECT category_id, name FROM categories");
    $stmt->execute();
    $result = $stmt->get_result();
    $categories = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching categories: " . $e->getMessage());
    $_SESSION['error'] = "Error loading categories. Please try again.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Item - CVSU</title>
    <link rel="stylesheet" href="css/add.css">
</head>

<body>
    <!-- Main Container -->
    <div class="container-fluid cvsu-container">
        <div class="cvsu-header">
            <h2><i class="fas fa-box me-2"></i>Add New Item</h2>
            <p>Add a new item to the inventory system</p>
        </div>

        <!-- Display Success/Error Messages -->
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

        <form action="process/add_item.php" method="POST">
            <!-- Item Name -->
            <div class="mb-4">
                <label for="itemName" class="form-label">Item Name <span class="required">*<span></label>
                <input type="text" name="itemName" id="itemName" class="form-control" placeholder="e.g., Spoon" required>
            </div>

            <!-- Category Dropdown -->
            <div class="mb-4">
                <label for="category" class="form-label">Category <span class="required">*<span></label>
                <select name="category" id="category" class="form-control" required>
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['category_id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Quantity -->
            <div class="mb-4">
                <label for="quantity" class="form-label">Quantity <span class="required">*<span></label>
                <input type="number" name="quantity" id="quantity" class="form-control" min="1" value="1" required>
            </div>

            <!-- Description -->
            <div class="mb-4">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" class="form-control" rows="4" placeholder="Describe the item (e.g., silver, long, stainless)" maxlength="200"></textarea>
                <div class="textarea-counter">
                    <span id="charCount">0</span>/200
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn cvsu-btn-primary">
                Add Item <i class="fas fa-plus-circle"></i>
            </button>
        </form>
    </div>

    <!-- Character Counter Script -->
    <script>
        const textarea = document.getElementById('description');
        const charCount = document.getElementById('charCount');

        textarea.addEventListener('input', () => {
            charCount.textContent = textarea.value.length;
        });
    </script>
</body>

</html>