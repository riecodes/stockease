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
    <link rel="stylesheet" href="content/css/manage.css">
</head>
<body>
    <!-- Main Content -->
    <div class="cvsu-container">
        <div class="d-flex justify-content-between align-items-center mb-4 cvsu-header">
            <h2><span class="material-icons me-2">swap_horiz</span>Manage Borrowings</h2>
            <a href="dashboard.php?section=add_borrowing" class="btn cvsu-btn-primary">
                <span class="material-icons me-2">add_circle</span>Add New Borrowing
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

        <div class="cvsu-content-container">
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
                                    <span class="material-icons me-2">report_problem</span> No records found.
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
                                            <span class="material-icons">visibility</span>
                                        </button>
                                        <?php if ($borrow['status'] !== 'returned'): ?>
                                            <button class="btn btn-sm btn-primary edit-borrowing"
                                                data-id="<?= $borrow['borrowing_id'] ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editBorrowingModal">
                                                <span class="material-icons">edit</span>
                                            </button>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $borrow['borrowing_id'] ?>">
                                            <span class="material-icons">delete</span>
                                        </button>
                                    </td>
                                </tr>

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
                                                    <span class="material-icons me-2">warning</span>
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
    </div>

    <!-- View Borrowing Modal -->
    <div class="modal fade" id="borrowingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header cvsu-bg-green text-white">
                    <h5 class="modal-title">Borrowing Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="borrowingDetails">
                    <!-- Details loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Borrowing Modal -->
    <div class="modal fade" id="editBorrowingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header cvsu-bg-green text-white">
                    <h5 class="modal-title">Edit Borrowing</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="editBorrowingDetails">
                    <!-- Form loaded via AJAX -->
                </div>
            </div>
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

        // Edit Borrowing Details
        document.querySelectorAll('.edit-borrowing').forEach(button => {
            button.addEventListener('click', function() {
                const borrowingId = this.dataset.id;
                fetch(`process/edit_borrowing_form.php?id=${borrowingId}`)
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('editBorrowingDetails').innerHTML = data;
                    });
            });
        });
    </script>
</body>
</html>