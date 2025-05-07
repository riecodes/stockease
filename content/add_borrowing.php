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
    <link rel="stylesheet" href="css/add.css">
</head>

<div class="cvsu-container">
    <div class="d-flex justify-content-between align-items-center mb-4 cvsu-header">
        <h2><i class="fas fa-hand-holding me-2"></i>Add New Borrowing</h2>
        <a href="dashboard.php?section=manage_borrowings" class="btn cvsu-btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Borrowings
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

    <div class="card cvsu-card">
        <div class="card-body">
            <form action="process/add_borrowing.php" method="POST">
                <input type="hidden" name="form_token" value="<?= $_SESSION['form_token'] ?>">
                
                <div class="mb-4">
                    <label for="item_id" class="form-label">Item <span class="required">*</span></label>
                    <select class="form-control" id="item_id" name="item_id" required>
                        <option value="">Select an item</option>
                        <?php foreach ($items as $item): ?>
                            <option value="<?= $item['item_id'] ?>" 
                                <?= (isset($old['item_id']) && $old['item_id'] == $item['item_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($item['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="borrower_name" class="form-label">Borrower Name <span class="required">*</span></label>
                    <input type="text" class="form-control" id="borrower_name" name="borrower_name" required 
                           value="<?= isset($old['borrower_name']) ? htmlspecialchars($old['borrower_name']) : '' ?>">
                </div>

                <div class="mb-4">
                    <label for="borrower_contact" class="form-label">Borrower Contact <span class="required">*</span></label>
                    <input type="text" class="form-control" id="borrower_contact" name="borrower_contact" required 
                           value="<?= isset($old['borrower_contact']) ? htmlspecialchars($old['borrower_contact']) : '' ?>">
                </div>

                <div class="mb-4">
                    <label for="borrow_quantity" class="form-label">Quantity to Borrow <span class="required">*</span></label>
                    <input type="number" class="form-control" id="borrow_quantity" name="borrow_quantity" min="1" required 
                           value="<?= isset($old['borrow_quantity']) ? htmlspecialchars($old['borrow_quantity']) : '1' ?>">
                </div>

                <div class="mb-4">
                    <label for="borrow_date" class="form-label">Borrow Date <span class="required">*</span></label>
                    <input type="date" class="form-control" id="borrow_date" name="borrow_date" required 
                           value="<?= isset($old['borrow_date']) ? htmlspecialchars($old['borrow_date']) : date('Y-m-d') ?>">
                </div>

                <div class="mb-4">
                    <label for="expected_return_date" class="form-label">Expected Return Date <span class="required">*</span></label>
                    <input type="date" class="form-control" id="expected_return_date" name="expected_return_date" required 
                           value="<?= isset($old['expected_return_date']) ? htmlspecialchars($old['expected_return_date']) : date('Y-m-d', strtotime('+7 days')) ?>">
                </div>

                <div class="mb-4">
                    <label for="condition_notes" class="form-label">Condition Notes</label>
                    <textarea class="form-control" id="condition_notes" name="condition_notes" rows="3"><?= isset($old['condition_notes']) ? htmlspecialchars($old['condition_notes']) : '' ?></textarea>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn cvsu-btn-primary">
                        <i class="fas fa-save me-2"></i>Save Borrowing
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
