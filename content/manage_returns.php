<?php
$borrowings = [];
try {
    $stmt = $conn->prepare("
        SELECT b.*, i.name AS item_name, i.quantity AS item_quantity 
        FROM borrowings b
        LEFT JOIN items i ON b.item_id = i.item_id
        WHERE b.status != 'returned'
        ORDER BY b.created_at DESC
    ");
    $stmt->execute();
    $borrowings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching borrowings for returns: " . $e->getMessage());
    $_SESSION['error'] = "Error loading borrowing records. Please try again.";
}
?>

<head>
    <link rel="stylesheet" href="content/css/manage.css">
</head>
<body>
    <div class="cvsu-container">
        <div class="d-flex justify-content-between align-items-center mb-4 cvsu-header">
            <h2><span class="material-icons me-2">assignment_return</span>Manage Returns</h2>
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
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($borrowings)): ?>
                        <!-- No records found -->
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <span class="material-icons me-2">report_problem</span> No records found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <!-- Display records -->
                        <?php foreach ($borrowings as $borrow): ?>
                            <tr>
                                <td><?= htmlspecialchars($borrow['borrower_name']) ?></td>
                                <td><?= htmlspecialchars($borrow['item_name'] ?? 'Unknown') ?></td>
                                <td><?= $borrow['quantity'] ?></td>
                                <td><?= date('M d, Y', strtotime($borrow['borrow_date'])) ?></td>
                                <td><?= date('M d, Y', strtotime($borrow['expected_return_date'])) ?></td>
                                <td><?= ucfirst($borrow['status']) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary view-borrowing"
                                        data-id="<?= $borrow['borrowing_id'] ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#borrowingModal">
                                        <span class="material-icons">visibility</span>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success mark-returned"
                                        data-id="<?= $borrow['borrowing_id'] ?>">
                                        <span class="material-icons me-2">check_circle</span>Mark Returned
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
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

        // Mark as Returned
        document.querySelectorAll('.mark-returned').forEach(button => {
            button.addEventListener('click', function() {
                const borrowingId = this.dataset.id;
                if (confirm('Are you sure you want to mark this borrowing as returned?')) {
                    window.location.href = `process/return_borrowing.php?id=${borrowingId}`;
                }
            });
        });
    </script>
</body>
</html>