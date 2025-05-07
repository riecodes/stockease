<?php
require_once 'include/db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Generate CSRF token if not exists
if (!isset($_SESSION['form_token'])) {
    $_SESSION['form_token'] = bin2hex(random_bytes(32));
}

// Fetch all borrowings with item details
$query = "SELECT b.*, i.name as item_name, c.name as category_name 
          FROM borrowings b 
          JOIN items i ON b.item_id = i.item_id 
          JOIN categories c ON i.category_id = c.category_id 
          ORDER BY b.borrow_date DESC";
$result = $conn->query($query);

// Fetch items for dropdowns
$items_query = "SELECT i.*, c.name as category_name 
                FROM items i 
                JOIN categories c ON i.category_id = c.category_id 
                ORDER BY c.name, i.name";
$items_result = $conn->query($items_query);
$items = $items_result->fetch_all(MYSQLI_ASSOC);
?>

<link rel="stylesheet" href="content/css/manage.css">

<div class="container cvsu-container">
    <div class="cvsu-header">
        <h2><span class="material-icons">history</span>Manage Borrowings</h2>
        <p>View and manage borrowing records</p>
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

    <div class="cvsu-content-container">
        <div class="card cvsu-card">
            <div class="card-body">
                <div class="d-flex justify-content-end mb-4">
                    <button type="button" class="btn btn-compact btn-primary" data-bs-toggle="modal" data-bs-target="#addBorrowingModal">
                        <span class="material-icons">add_circle</span>Add New Borrowing
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Borrower</th>
                                <th>Item</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th>Borrow Date</th>
                                <th>Expected Return</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows === 0): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <span class="material-icons">report_problem</span> No borrowings found
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php while ($borrow = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold"><?= htmlspecialchars($borrow['borrower_name']) ?></span>
                                                <?php if (!empty($borrow['borrower_contact'])): ?>
                                                    <small class="text-muted"><?= htmlspecialchars($borrow['borrower_contact']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($borrow['item_name']) ?></td>
                                        <td><?= htmlspecialchars($borrow['category_name']) ?></td>
                                        <td><?= $borrow['quantity'] ?></td>
                                        <td><?= date('M d, Y', strtotime($borrow['borrow_date'])) ?></td>
                                        <td><?= date('M d, Y', strtotime($borrow['expected_return_date'])) ?></td>
                                        <td>
                                            <?php if ($borrow['status'] === 'returned'): ?>
                                                <span class="badge bg-success">Returned</span>
                                            <?php elseif (strtotime($borrow['expected_return_date']) < time()): 
                                                $days_overdue = ceil((time() - strtotime($borrow['expected_return_date'])) / (60 * 60 * 24));
                                            ?>
                                                <span class="badge bg-danger d-flex align-items-center justify-content-center gap-1">
                                                    <span class="material-icons" style="font-size: 16px;">warning</span>
                                                    Overdue (<?= $days_overdue ?> day<?= $days_overdue > 1 ? 's' : '' ?>)
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-primary">Active</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-compact btn-info" data-bs-toggle="modal" data-bs-target="#viewModal<?= $borrow['borrowing_id'] ?>">
                                                    <span class="material-icons">visibility</span>
                                                </button>
                                                <?php if ($borrow['status'] !== 'returned'): ?>
                                                    <button type="button" class="btn btn-compact btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $borrow['borrowing_id'] ?>">
                                                        <span class="material-icons">edit</span>
                                                    </button>
                                                    <button type="button" class="btn btn-compact btn-success" data-bs-toggle="modal" data-bs-target="#returnModal<?= $borrow['borrowing_id'] ?>">
                                                        <span class="material-icons">assignment_return</span>
                                                    </button>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-compact btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $borrow['borrowing_id'] ?>">
                                                    <span class="material-icons">delete</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include modals -->
<?php
$result->data_seek(0); // Reset result pointer
while ($borrow = $result->fetch_assoc()):
    include 'modals/borrowing_view_modal.php';
    include 'modals/borrowing_edit_modal.php';
    include 'modals/borrowing_return_modal.php';
    include 'modals/borrowing_delete_modal.php';
endwhile;
include 'modals/borrowing_add_modal.php';
?>

<script>
// Character counter for textareas
document.querySelectorAll('textarea').forEach(textarea => {
    const counter = textarea.parentElement.querySelector('.textarea-counter span');
    textarea.addEventListener('input', function() {
        const remaining = 200 - this.value.length;
        counter.textContent = this.value.length;
        if (remaining < 0) {
            this.value = this.value.substring(0, 200);
            counter.textContent = 200;
        }
    });
});
</script>