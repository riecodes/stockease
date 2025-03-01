<?php
// Fetch items for the dropdown
$items = [];
try {
    $stmt = $conn->prepare("SELECT item_id, name FROM items");
    $stmt->execute();
    $result = $stmt->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching items: " . $e->getMessage());
    $_SESSION['error'] = "Error loading items. Please try again.";
}

// Generate CSRF token if not set
if (!isset($_SESSION['form_token'])) {
    $_SESSION['form_token'] = bin2hex(random_bytes(32));
}

// Retrieve old input values
$old = isset($_SESSION['old']) ? $_SESSION['old'] : [];
?>
<head>
    <title>Add Borrowing - CVSU</title>
    <link rel="stylesheet" href="css/add.css">    
</head>
<body>
    <!-- Main Container -->
    <div class="container-fluid cvsu-container">
        <div class="cvsu-header">
            <h2><i class="fas fa-hand-holding me-2"></i>Add New Borrowing</h2>
            <p>Record a new borrowing entry in the inventory system</p>
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

        <form action="process/add_borrowing.php" method="POST">
            <!-- CSRF Token Hidden Field -->
            <input type="hidden" name="form_token" value="<?= $_SESSION['form_token'] ?>">
            
            <!-- Item Dropdown -->
            <div class="mb-4">
                <label for="item_id" class="form-label">Item <span class="required">*<span></label>
                <select name="item_id" id="item_id" class="form-control" required>
                    <option value="">Select an item</option>
                    <?php foreach ($items as $item): ?>
                        <option value="<?= $item['item_id'] ?>" <?= (isset($old['item_id']) && $old['item_id'] == $item['item_id']) ? 'selected' : '' ?>><?= htmlspecialchars($item['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Quantity to Borrow -->
            <div class="mb-4">
                <label for="borrow_quantity" class="form-label">Quantity to Borrow <span class="required">*<span></label>
                <input type="number" name="borrow_quantity" id="borrow_quantity" class="form-control" min="1" placeholder="e.g., 1" required value="<?= isset($old['borrow_quantity']) ? htmlspecialchars($old['borrow_quantity']) : '1' ?>">
            </div>

            <!-- Borrower Name -->
            <div class="mb-4">
                <label for="borrower_name" class="form-label">Borrower Name <span class="required">*<span></label>
                <input type="text" name="borrower_name" id="borrower_name" class="form-control" placeholder="e.g., John Doe" required value="<?= isset($old['borrower_name']) ? htmlspecialchars($old['borrower_name']) : '' ?>">
            </div>

            <!-- Borrower Contact -->
            <div class="mb-4">
                <label for="borrower_contact" class="form-label">Borrower Contact <span class="required">*</span></label>
                <input type="text" name="borrower_contact" id="borrower_contact" class="form-control" placeholder="e.g., 09171234567" required value="<?= isset($old['borrower_contact']) ? htmlspecialchars($old['borrower_contact']) : '' ?>">
            </div>

            <!-- Borrow Date -->
            <div class="mb-4">
                <label for="borrow_date" class="form-label">Borrow Date <span class="required">*<span></label>
                <input type="date" name="borrow_date" id="borrow_date" min="<?= date('Y-m-d') ?>" class="form-control" required value="<?= isset($old['borrow_date']) ? htmlspecialchars($old['borrow_date']) : '' ?>">
            </div>

            <!-- Expected Return Date -->
            <div class="mb-4">
                <label for="expected_return_date" class="form-label">Expected Return Date <span class="required">*<span></label>
                <input type="date" name="expected_return_date" id="expected_return_date" min="<?= date('Y-m-d') ?>" class="form-control" required value="<?= isset($old['expected_return_date']) ? htmlspecialchars($old['expected_return_date']) : '' ?>">
            </div>

            <!-- Condition Notes -->
            <div class="mb-4">
                <label for="condition_notes" class="form-label">Condition Notes</label>
                <textarea name="condition_notes" id="condition_notes" class="form-control" rows="4" placeholder="Enter any notes (optional)" maxlength="200"><?= isset($old['condition_notes']) ? htmlspecialchars($old['condition_notes']) : '' ?></textarea>
                <div class="textarea-counter">
                    <span id="charCount">0</span>/200
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn cvsu-btn-primary">
                Add Borrowing <i class="fas fa-plus-circle"></i>
            </button>
        </form>
    </div>

    <!-- Character Counter Script for Condition Notes -->
    <script>
        const textarea = document.getElementById('condition_notes');
        const charCount = document.getElementById('charCount');

        textarea.addEventListener('input', () => {
            charCount.textContent = textarea.value.length;
        });

        // Prevent double submissions
        document.querySelector('form').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type=\"submit\"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class=\"fas fa-spinner fa-spin\"></i> Processing...';
        });
    </script>
</body>
</html>
