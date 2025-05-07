<?php
$borrowings = [];
try {
    $stmt = $conn->prepare("
        SELECT b.*, i.name AS item_name
        FROM borrowings b
        LEFT JOIN items i ON b.item_id = i.item_id
        ORDER BY b.created_at DESC
    ");
    $stmt->execute();
    $borrowings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching borrowings: " . $e->getMessage());
    $_SESSION['error'] = "Error loading borrowings. Please try again.";
}

// Fetch items for the edit modal dropdown
$stmt = $conn->prepare("SELECT item_id, name FROM items WHERE quantity > 0 ORDER BY name");
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<head>
    <link rel="stylesheet" href="css/manage.css">
</head>
<body>
    <div class="cvsu-container">
        <div class="d-flex justify-content-between align-items-center mb-4 cvsu-header">
            <h2><i class="fas fa-hand-holding me-2"></i>Manage Borrowings</h2>
            <a href="dashboard.php?section=add_borrowing" class="btn cvsu-btn-primary">
                <i class="fas fa-plus-circle me-2"></i>Add New Borrowing
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

        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="cvsu-bg-green text-white">
                    <tr>
                        <th>Borrower Name</th>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Borrow Date</th>
                        <th>Expected Return</th>
                        <th>Actual Return</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($borrowings)): ?>
                        <!-- No records found -->
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-exclamation-circle me-2"></i> No records found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <!-- Display records -->
                        <?php foreach ($borrowings as $borrow): ?>
                            <tr>
                                <td><?= htmlspecialchars($borrow['borrower_name']) ?></td>
                                <td><?= htmlspecialchars($borrow['item_name'] ?? 'Unknown') ?></td>
                                <td><?= htmlspecialchars($borrow['quantity'] ?? 'N/A') ?></td>
                                <td><?= date('M d, Y', strtotime($borrow['borrow_date'])) ?></td>
                                <td><?= date('M d, Y', strtotime($borrow['expected_return_date'])) ?></td>
                                <td>
                                    <?= $borrow['actual_return_date'] 
                                        ? date('M d, Y', strtotime($borrow['actual_return_date'])) 
                                        : 'Not Returned' ?>
                                </td>
                                <td><?= ucfirst($borrow['status']) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary view-borrowing"
                                        data-id="<?= $borrow['borrowing_id'] ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#borrowingModal">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if ($borrow['status'] !== 'returned'): ?>
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $borrow['borrowing_id'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $borrow['borrowing_id'] ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editModal<?= $borrow['borrowing_id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $borrow['borrowing_id'] ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editModalLabel<?= $borrow['borrowing_id'] ?>">Edit Borrowing</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="actions/borrowing_actions.php" method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="action" value="edit">
                                                <input type="hidden" name="borrowing_id" value="<?= $borrow['borrowing_id'] ?>">
                                                <div class="mb-3">
                                                    <label for="item<?= $borrow['borrowing_id'] ?>" class="form-label">Item</label>
                                                    <select class="form-select" id="item<?= $borrow['borrowing_id'] ?>" name="item_id" required>
                                                        <?php foreach ($items as $item): ?>
                                                            <option value="<?= $item['item_id'] ?>" <?= $item['item_id'] == $borrow['item_id'] ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($item['name']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="borrower<?= $borrow['borrowing_id'] ?>" class="form-label">Borrower Name</label>
                                                    <input type="text" class="form-control" id="borrower<?= $borrow['borrowing_id'] ?>" name="borrower_name" value="<?= htmlspecialchars($borrow['borrower_name']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="quantity<?= $borrow['borrowing_id'] ?>" class="form-label">Quantity</label>
                                                    <input type="number" class="form-control" id="quantity<?= $borrow['borrowing_id'] ?>" name="quantity" value="<?= $borrow['quantity'] ?>" min="1" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="borrowDate<?= $borrow['borrowing_id'] ?>" class="form-label">Borrow Date</label>
                                                    <input type="date" class="form-control" id="borrowDate<?= $borrow['borrowing_id'] ?>" name="borrow_date" value="<?= date('Y-m-d', strtotime($borrow['borrow_date'])) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="returnDate<?= $borrow['borrowing_id'] ?>" class="form-label">Expected Return Date</label>
                                                    <input type="date" class="form-control" id="returnDate<?= $borrow['borrowing_id'] ?>" name="expected_return_date" value="<?= date('Y-m-d', strtotime($borrow['expected_return_date'])) ?>" required>
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
                            <div class="modal fade" id="deleteModal<?= $borrow['borrowing_id'] ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?= $borrow['borrowing_id'] ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="deleteModalLabel<?= $borrow['borrowing_id'] ?>">Delete Borrowing</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to delete this borrowing record?</p>
                                            <div class="alert alert-warning">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                This action cannot be undone. All borrowing history will be permanently deleted.
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <form action="actions/borrowing_actions.php" method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="borrowing_id" value="<?= $borrow['borrowing_id'] ?>">
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

    <script>
        // View Borrowing Details
        document.querySelectorAll('.view-borrowing').forEach(button => {
            button.addEventListener('click', function() {
                const borrowingId = this.dataset.id;
                fetch(`process/view_borrowing.php?id=${borrowingId}`)
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('borrowingDetails').innerHTML = data;
                    });
            });
        });
    </script>
</body>
</html>