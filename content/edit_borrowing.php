<?php
if (!isset($_GET['id'])) {
    header("Location: dashboard.php?section=manage_borrowings");
    exit();
}

$borrowingId = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

// Fetch borrowing details
try {
    $stmt = $conn->prepare("SELECT * FROM borrowings WHERE borrowing_id = ?");
    $stmt->bind_param("i", $borrowingId);
    $stmt->execute();
    $borrowing = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$borrowing) {
        $_SESSION['error'] = "Borrowing record not found";
        header("Location: dashboard.php?section=manage_borrowings");
        exit();
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = "Error loading borrowing record";
    header("Location: dashboard.php?section=manage_borrowings");
    exit();
}

// Fetch items for dropdown
$items = [];
try {
    $stmt = $conn->prepare("SELECT item_id, name FROM items");
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching items: " . $e->getMessage());
    $_SESSION['error'] = "Error loading items";
}
?>

<head>
    <title>Edit Borrowing - CVSU</title>
    <link rel="stylesheet" href="css/add.css">
</head>
<body>
    <div class="container-fluid cvsu-container">
        <div class="cvsu-header">
            <h2><i class="fas fa-edit me-2"></i>Edit Borrowing</h2>
            <p>Update borrowing details below</p>
        </div>

        <!-- Display Success/Error Messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <span class="required">* </span><?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form action="process/edit_borrowing.php" method="POST">
            <input type="hidden" name="borrowing_id" value="<?= $borrowing['borrowing_id'] ?>">

            <!-- Item Dropdown -->
            <div class="mb-4">
                <label class="form-label">Item <span class="required">*<span></label>
                <select name="item_id" class="form-control" required>
                    <option value="">Select Item</option>
                    <?php foreach ($items as $item): ?>
                        <option value="<?= $item['item_id'] ?>" <?= $item['item_id'] == $borrowing['item_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($item['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Borrower Name -->
            <div class="mb-4">
                <label class="form-label">Borrower Name <span class="required">*<span></label>
                <input type="text" name="borrower_name" class="form-control" value="<?= htmlspecialchars($borrowing['borrower_name']) ?>" required>
            </div>

            <!-- Borrower Contact -->
            <div class="mb-4">
                <label class="form-label">Borrower Contact</label>
                <input type="text" name="borrower_contact" class="form-control" value="<?= htmlspecialchars($borrowing['borrower_contact']) ?>">
            </div>

            <!-- Borrow Date -->
            <div class="mb-4">
                <label class="form-label">Borrow Date <span class="required">*<span></label>
                <input type="date" name="borrow_date" class="form-control" value="<?= $borrowing['borrow_date'] ?>" required>
            </div>

            <!-- Expected Return Date -->
            <div class="mb-4">
                <label class="form-label">Expected Return Date <span class="required">*<span></label>
                <input type="date" name="expected_return_date" class="form-control" value="<?= $borrowing['expected_return_date'] ?>" required>
            </div>
            
            <!-- Borrow Quantity (if applicable) -->
            <div class="mb-4">
                <label class="form-label">Quantity Borrowed <span class="required">*<span></label>
                <input type="number" name="borrow_quantity" class="form-control" value="<?= isset($borrowing['quantity']) ? $borrowing['quantity'] : 1 ?>" min="1" required>
            </div>

            <!-- Condition Notes -->
            <div class="mb-4">
                <label class="form-label">Condition Notes</label>
                <textarea name="condition_notes" class="form-control" rows="4" maxlength="200"><?= htmlspecialchars($borrowing['condition_notes']) ?></textarea>
                <div class="textarea-counter">
                    <span id="charCount">0</span>/200
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn cvsu-btn-primary">
                    <i class="fas fa-save me-2"></i>Update Borrowing
                </button>
                <a href="dashboard.php?section=manage_borrowings" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
            </div>
        </form>
    </div>

    <!-- Character Counter Script for Condition Notes -->
    <script>
        const textarea = document.querySelector('textarea[name=\"condition_notes\"]');
        const charCount = document.getElementById('charCount');
        // Initialize counter with current text length
        charCount.textContent = textarea.value.length;
        
        textarea.addEventListener('input', () => {
            charCount.textContent = textarea.value.length;
        });
    </script>
</body>
</html>
