<?php

// Fetch data for the cards and graphs
$totalItems = 0;
$totalCategories = 0;
$totalBorrowings = 0;
$itemsByCategory = [];
$borrowingsOverTime = [];
$reportData = [];

try {
    // Total Items
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM items");
    $stmt->execute();
    $totalItems = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    // Total Categories
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM categories");
    $stmt->execute();
    $totalCategories = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    // Total Borrowings
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM borrowings");
    $stmt->execute();
    $totalBorrowings = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    // Items by Category
    $stmt = $conn->prepare("
        SELECT c.name AS category_name, COUNT(i.item_id) AS item_count
        FROM categories c
        LEFT JOIN items i ON c.category_id = i.category_id
        GROUP BY c.category_id
    ");
    $stmt->execute();
    $itemsByCategory = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Borrowings Over Time (Last 6 Months)
    $stmt = $conn->prepare("
        SELECT DATE_FORMAT(borrow_date, '%b') AS month, COUNT(*) AS borrow_count
        FROM borrowings
        WHERE borrow_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY MONTH(borrow_date)
        ORDER BY MONTH(borrow_date)
    ");
    $stmt->execute();
    $borrowingsOverTime = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Item Availability Report
    $stmt = $conn->prepare("
        SELECT i.name AS item_name, 
               c.name AS category,
               i.quantity AS current_stock,
               COUNT(b.borrowing_id) AS times_borrowed
        FROM items i
        LEFT JOIN categories c ON i.category_id = c.category_id
        LEFT JOIN borrowings b ON i.item_id = b.item_id
        GROUP BY i.item_id
        ORDER BY times_borrowed DESC
    ");
    $stmt->execute();
    $reportData['item_availability'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Overdue Items Report
    $stmt = $conn->prepare("
        SELECT b.borrowing_id,
               b.borrower_name,
               i.name AS item_name,
               b.expected_return_date,
               DATEDIFF(CURDATE(), b.expected_return_date) AS days_overdue
        FROM borrowings b
        LEFT JOIN items i ON b.item_id = i.item_id
        WHERE b.status = 'borrowed'
          AND b.expected_return_date < CURDATE()
    ");
    $stmt->execute();
    $reportData['overdue_items'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching data: " . $e->getMessage());
    $_SESSION['error'] = "Error loading dashboard data. Please try again.";
}
?>

<head>
    <link rel="stylesheet" href="css/home.css">
</head>
<body>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 cvsu-header">
            <h2><i class="fas fa-home me-2"></i>Dashboard</h2>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <span class="required">* </span><?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Floating Information Rectangles -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card cvsu-card">
                    <div class="card-body floating-info">
                        <h5 class="card-title"><i class="fas fa-boxes me-2"></i>Total Items</h5>
                        <p class="card-text display-4"><?= $totalItems ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card cvsu-card">
                    <div class="card-body floating-info">
                        <h5 class="card-title"><i class="fas fa-tags me-2"></i>Total Categories</h5>
                        <p class="card-text display-4"><?= $totalCategories ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card cvsu-card">
                    <div class="card-body floating-info">
                        <h5 class="card-title"><i class="fas fa-hand-holding me-2"></i>Total Borrowings</h5>
                        <p class="card-text display-4"><?= $totalBorrowings ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphs Section -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card cvsu-card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-chart-bar me-2"></i>Items by Category</h5>
                        <canvas id="itemsByCategoryChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card cvsu-card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-chart-line me-2"></i>Borrowings Over Time</h5>
                        <canvas id="borrowingsOverTimeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Item Availability Table -->
        <div class="card cvsu-card mb-4">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-box-open me-2"></i>Item Availability</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Category</th>
                                <th>Current Stock</th>
                                <th>Times Borrowed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reportData['item_availability'] as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['item_name']) ?></td>
                                    <td><?= htmlspecialchars($item['category']) ?></td>
                                    <td><?= $item['current_stock'] ?></td>
                                    <td><?= $item['times_borrowed'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Overdue Items Report -->
        <div class="card cvsu-card">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-exclamation-triangle me-2"></i>Overdue Items</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Borrower</th>
                                <th>Expected Return</th>
                                <th>Days Overdue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reportData['overdue_items'] as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['item_name']) ?></td>
                                    <td><?= htmlspecialchars($item['borrower_name']) ?></td>
                                    <td><?= date('M d, Y', strtotime($item['expected_return_date'])) ?></td>
                                    <td class="text-danger"><?= $item['days_overdue'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Script for Charts -->
    <script>
        // Items by Category Chart
        const itemsByCategoryData = <?= json_encode($itemsByCategory) ?>;
        const itemsByCategoryLabels = itemsByCategoryData.map(item => item.category_name);
        const itemsByCategoryCounts = itemsByCategoryData.map(item => item.item_count);

        const itemsByCategoryChart = new Chart(document.getElementById('itemsByCategoryChart'), {
            type: 'bar',
            data: {
                labels: itemsByCategoryLabels,
                datasets: [{
                    label: 'Number of Items',
                    data: itemsByCategoryCounts,
                    backgroundColor: [
                        'rgba(0, 127, 0, 0.6)',
                        'rgba(0, 127, 0, 0.4)',
                        'rgba(0, 127, 0, 0.2)'
                    ],
                    borderColor: [
                        'rgba(0, 127, 0, 1)',
                        'rgba(0, 127, 0, 1)',
                        'rgba(0, 127, 0, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Borrowings Over Time Chart
        const borrowingsOverTimeData = <?= json_encode($borrowingsOverTime) ?>;
        const borrowingsOverTimeLabels = borrowingsOverTimeData.map(item => item.month);
        const borrowingsOverTimeCounts = borrowingsOverTimeData.map(item => item.borrow_count);

        const borrowingsOverTimeChart = new Chart(document.getElementById('borrowingsOverTimeChart'), {
            type: 'line',
            data: {
                labels: borrowingsOverTimeLabels,
                datasets: [{
                    label: 'Borrowings',
                    data: borrowingsOverTimeCounts,
                    backgroundColor: 'rgba(0, 127, 0, 0.2)',
                    borderColor: 'rgba(0, 127, 0, 1)',
                    borderWidth: 2,
                    fill: true
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>