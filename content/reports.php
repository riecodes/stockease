<?php

$startDate = $_GET['start_date'] ?? date('Y-m-01', strtotime('-1 month'));
$endDate = $_GET['end_date'] ?? date('Y-m-t');
$reportData = [];

try {
    // Borrowing Trends Report
    $stmt = $conn->prepare("
        SELECT DATE_FORMAT(borrow_date, '%Y-%m') AS month, 
               COUNT(*) AS total_borrowings,
               SUM(quantity) AS total_items
        FROM borrowings
        WHERE borrow_date BETWEEN ? AND ?
        GROUP BY DATE_FORMAT(borrow_date, '%Y-%m')
        ORDER BY borrow_date
    ");
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $reportData['borrowing_trends'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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
    error_log("Report generation error: " . $e->getMessage());
    $_SESSION['error'] = "Error generating reports. Please try again.";
}
?>

<head>
    <link rel="stylesheet" href="css/reports.css">
</head>

<body>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 cvsu-header">
            <h2><i class="fas fa-chart-bar me-2"></i>Reports</h2>
            <form method="GET" class="report-filters">
                <div class="input-group">
                    <input type="date" class="form-control" name="start_date"
                        value="<?= htmlspecialchars($startDate) ?>">
                    <span class="input-group-text">to</span>
                    <input type="date" class="form-control" name="end_date"
                        value="<?= htmlspecialchars($endDate) ?>">
                    <button type="submit" class="btn cvsu-btn-primary">
                        <i class="fas fa-filter"></i> Apply
                    </button>
                </div>
            </form>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Borrowing Trends Chart -->
        <div class="card cvsu-card mb-4">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-chart-line me-2"></i>Borrowing Trends</h5>
                <canvas id="borrowingTrendsChart"></canvas>
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

    <script>
        // Borrowing Trends Chart
        const trendsData = <?= json_encode($reportData['borrowing_trends']) ?>;
        new Chart(document.getElementById('borrowingTrendsChart'), {
            type: 'line',
            data: {
                labels: trendsData.map(d => d.month),
                datasets: [{
                    label: 'Total Borrowings',
                    data: trendsData.map(d => d.total_borrowings),
                    borderColor: '#007f00',
                    tension: 0.4
                }, {
                    label: 'Items Borrowed',
                    data: trendsData.map(d => d.total_items),
                    borderColor: '#2c3e50',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
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