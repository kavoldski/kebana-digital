<?php
$page_title = 'Finance Dashboard';
$css_path = '../../src/css/dashboard.css';
$extra_css = '../../src/css/finance.css';

require_once '../../includes/header.php';

$fund_balance = 0;
$total_income = 0;
$total_expense = 0;

// Simple totals (add dashboard_helper.php logic later)
if (isset($conn)) {
    $result = $conn->query("SELECT SUM(CASE WHEN trans_type = 'Income' THEN amount ELSE 0 END) as income, SUM(CASE WHEN trans_type = 'Expense' THEN amount ELSE 0 END) as expense FROM tbl_transaction");
    if ($row = $result->fetch_assoc()) {
        $total_income = (float)$row['income'];
        $total_expense = (float)$row['expense'];
        $fund_balance = $total_income - $total_expense;
    }
}
?>
<div class="finance-dashboard">
    <div class="container-xl">
        <div class="row mb-5">
            <div class="col-12">
                <h1 class="mb-5" style="font-size: 2.5rem; font-weight: 800; background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Finance Dashboard</h1>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="finance-kpi-grid">
            <div class="finance-kpi-card balance-card">
                <div class="kpi-icon-finance">💰</div>
                <h3 class="kpi-title">Fund Balance</h3>
                <div class="kpi-number">RM <?php echo number_format($fund_balance, 2); ?></div>
            </div>
            <div class="finance-kpi-card income-card">
                <div class="kpi-icon-finance">📈</div>
                <h3 class="kpi-title">Total Income</h3>
                <div class="kpi-number">RM <?php echo number_format($total_income, 2); ?></div>
            </div>
            <div class="finance-kpi-card expense-card">
                <div class="kpi-icon-finance">📉</div>
                <h3 class="kpi-title">Total Expenses</h3>
                <div class="kpi-number">RM <?php echo number_format($total_expense, 2); ?></div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="recent-transactions-card">
            <div class="recent-header">
                <h2 class="recent-title">Recent Transactions</h2>
                <a href="transactions/list.php" class="btn-view-all">View All Transactions</a>
            </div>
            <div class="trans-table-container">
                <div class="table-responsive">
                    <table class="table table-finance">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Amount</th>
                                <th>Recorded By</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $recent_stmt = $conn->prepare("SELECT trans_date, trans_type, category, amount, recorded_by FROM tbl_transaction ORDER BY created_at DESC LIMIT 10");
                        if ($recent_stmt) {
                            $recent_stmt->execute();
                            $recent_result = $recent_stmt->get_result();
                            if ($recent_result->num_rows > 0) {
                                while ($row = $recent_result->fetch_assoc()) {
                                    $type_class = $row['trans_type'] == 'Income' ? 'text-success' : 'text-danger';
                                    echo "<tr>
                                        <td>" . date('M j', strtotime($row['trans_date'])) . "</td>
                                        <td><span class='badge {$type_class}'>" . $row['trans_type'] . "</span></td>
                                        <td>" . htmlspecialchars($row['category']) . "</td>
                                        <td><strong>RM " . number_format($row['amount'], 2) . "</strong></td>
                                        <td>ID " . htmlspecialchars($row['recorded_by']) . "</td>
                                    </tr>";
                                }
                            } else {
                                echo '<tr><td colspan="5" class="text-center text-muted">No transactions yet. <a href="transactions/create.php">Add first transaction</a></td></tr>';
                            }
                            $recent_stmt->close();
                        } else {
                            echo '<tr><td colspan="5" class="text-center text-muted">Error loading transactions (check if tbl_transaction exists)</td></tr>';
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>


