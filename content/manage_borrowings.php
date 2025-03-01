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
                                    <a href="dashboard.php?section=edit_borrowing&id=<?= $borrow['borrowing_id'] ?>"
                                        class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger delete-borrowing"
                                        data-id="<?= $borrow['borrowing_id'] ?>">
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

        // Delete Borrowing
        document.querySelectorAll('.delete-borrowing').forEach(button => {
            button.addEventListener('click', function() {
                const borrowingId = this.dataset.id;
                if (confirm('Are you sure you want to delete this borrowing record?')) {
                    window.location.href = `process/delete_borrowing.php?id=${borrowingId}`;
                }
            });
        });
    </script>
</body>
</html>