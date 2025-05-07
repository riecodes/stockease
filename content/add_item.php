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

// Generate CSRF token if not set
if (!isset($_SESSION['form_token'])) {
    $_SESSION['form_token'] = bin2hex(random_bytes(32));
}

// Retrieve old input values
$old = isset($_SESSION['old']) ? $_SESSION['old'] : [];
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
    <div class="cvsu-container">
        <div class="d-flex justify-content-between align-items-center mb-4 cvsu-header">
            <h2><i class="fas fa-box me-2"></i>Add New Item</h2>
            <a href="dashboard.php?section=manage_items" class="btn cvsu-btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Items
            </a>
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

        <div class="card cvsu-card">
            <div class="card-body">
                <form action="process/add_item.php" method="POST">
                    <input type="hidden" name="form_token" value="<?= $_SESSION['form_token'] ?>">
                    
                    <div class="mb-4">
                        <label for="name" class="form-label">Item Name <span class="required">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required 
                               value="<?= isset($old['name']) ? htmlspecialchars($old['name']) : '' ?>">
                    </div>

                    <div class="mb-4">
                        <label for="category_id" class="form-label">Category <span class="required">*</span></label>
                        <select class="form-control" id="category_id" name="category_id" required>
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['category_id'] ?>" 
                                    <?= (isset($old['category_id']) && $old['category_id'] == $category['category_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="quantity" class="form-label">Quantity <span class="required">*</span></label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="0" required 
                               value="<?= isset($old['quantity']) ? htmlspecialchars($old['quantity']) : '0' ?>">
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= isset($old['description']) ? htmlspecialchars($old['description']) : '' ?></textarea>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn cvsu-btn-primary">
                            <i class="fas fa-save me-2"></i>Save Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>