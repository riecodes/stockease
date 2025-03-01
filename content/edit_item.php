<?php
if (!isset($_GET['id'])) {
    header("Location: dashboard.php?section=manage_item");
    exit();
}

$itemId = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

// Fetch item details
try {
    $stmt = $conn->prepare("SELECT * FROM items WHERE item_id = ?");
    $stmt->bind_param("i", $itemId);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$item) {
        $_SESSION['error'] = "Item not found";
        header("Location: dashboard.php?section=manage_item");
        exit();
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = "Error loading item";
    header("Location: dashboard.php?section=manage_item");
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
    <title>Edit Item - CVSU</title>    
    <link rel="stylesheet" href="css/add.css">
</head>
<body>
    <div class="container-fluid cvsu-container">
        <div class="cvsu-header">
            <h2><i class="fas fa-edit me-2"></i>Edit Item</h2>
            <p>Update item details below</p>
        </div>

        <form action="process/edit_item.php" method="POST">
            <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">

            <div class="mb-4">
                <label class="form-label">Item Name <span class="required">*<span></label>
                <input type="text" name="itemName" class="form-control" value="<?= htmlspecialchars($item['name']) ?>" required>
            </div>

            <div class="mb-4">
                <label class="form-label">Category <span class="required">*<span></label>
                <select name="category" class="form-control" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['category_id'] ?>" <?= $cat['category_id'] == $item['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label">Quantity <span class="required">*<span></label>
                <input type="number" name="quantity" class="form-control" value="<?= $item['quantity'] ?>" min="1" required>
            </div>

            <div class="mb-4">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4" maxlength="200"><?= htmlspecialchars($item['description']) ?></textarea>
                <div class="textarea-counter">
                    <span id="charCount">0</span>/200
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn cvsu-btn-primary">
                    <i class="fas fa-save me-2"></i>Update Item
                </button>
                <a href="dashboard.php?section=manage_item" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancel
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
