<?php
$page_title = 'Budget Management';
$css_path = '../../src/css/dashboard.css';
$extra_css = '../../src/css/finance.css';

require_once '../../includes/header.php';
require_once '../../includes/auth.php';

if (!hasRole(['Treasurer', 'Super Admin'])) {
    die('Access denied. Treasurer/Super Admin only.');
}

// Filters
$year_filter = trim($_GET['year'] ?? '');
$search_filter = trim($_GET['search'] ?? '');

// Build dynamic WHERE for events
$where = "1=1";
$params = [];
$types = '';

if ($year_filter !== '') {
    $where .= " AND YEAR(e.event_date) = ?";
    $params[] = (int)$year_filter;
    $types .= 'i';
}

if ($search_filter !== '') {
    $where .= " AND e.event_title LIKE ?";
    $params[] = '%' . $search_filter . '%';
    $types .= 's';
}

// KPI: Total Planned Budget (from event budget_est with filters)
$total_planned = 0.0;
$planned_sql = "
    SELECT COALESCE(SUM(e.budget_est), 0) AS total_planned
    FROM tbl_event e
    WHERE $where
";
$planned_stmt = $conn->prepare($planned_sql);
if ($planned_stmt) {
    if (!empty($params)) {
        $planned_stmt->bind_param($types, ...$params);
    }
    $planned_stmt->execute();
    $planned_row = $planned_stmt->get_result()->fetch_assoc();
    $total_planned = (float)($planned_row['total_planned'] ?? 0);
    $planned_stmt->close();
}

// KPI: Total Actual Expense (expense tx linked to filtered events)
$total_actual_expense = 0.0;
$actual_sql = "
    SELECT COALESCE(SUM(t.amount), 0) AS total_actual_expense
    FROM tbl_event e
    LEFT JOIN tbl_transaction t 
        ON t.event_id = e.event_id
       AND t.trans_type = 'Expense'
    WHERE $where
";
$actual_stmt = $conn->prepare($actual_sql);
if ($actual_stmt) {
    if (!empty($params)) {
        $actual_stmt->bind_param($types, ...$params);
    }
    $actual_stmt->execute();
    $actual_row = $actual_stmt->get_result()->fetch_assoc();
    $total_actual_expense = (float)($actual_row['total_actual_expense'] ?? 0);
    $actual_stmt->close();
}

$total_variance = $total_planned - $total_actual_expense;

// Budget by event table
$table_sql = "
    SELECT
        e.event_id,
        e.event_title,
        e.event_date,
        COALESCE(e.budget_est, 0) AS planned_budget,
        COALESCE(SUM(CASE WHEN t.trans_type = 'Expense' THEN t.amount ELSE 0 END), 0) AS actual_expense
    FROM tbl_event e
    LEFT JOIN tbl_transaction t ON t.event_id = e.event_id
    WHERE $where
    GROUP BY e.event_id, e.event_title, e.event_date, e.budget_est
    ORDER BY e.event_date DESC, e.event_id DESC
";
$table_stmt = $conn->prepare($table_sql);
$event_budgets = [];
if ($table_stmt) {
    if (!empty($params)) {
        $table_stmt->bind_param($types, ...$params);
    }
    $table_stmt->execute();
    $table_result = $table_stmt->get_result();
    while ($row = $table_result->fetch_assoc()) {
        $event_budgets[] = $row;
    }
    $table_stmt->close();
}
?>

<div class="finance-dashboard finance-page">
    <div class="container-xl">
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <h1 class="mb-0" style="font-size: 2.5rem; font-weight: 800; background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                    Budget Management
                </h1>
            </div>
        </div>

        <!-- Filters -->
        <div class="recent-transactions-card mb-4">
            <div class="recent-header">
                <h2 class="recent-title">Filters</h2>
            </div>
            <div class="p-3 p-md-4">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="year" class="form-label">Year</label>
                        <input type="number" min="2000" max="2100" class="form-control" id="year" name="year" value="<?php echo htmlspecialchars($year_filter); ?>" placeholder="e.g. 2026">
                    </div>
                    <div class="col-md-5">
                        <label for="search" class="form-label">Event Title</label>
                        <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($search_filter); ?>" placeholder="Search event title">
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="budget.php" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="finance-kpi-grid">
            <div class="finance-kpi-card balance-card">
                <div class="kpi-icon-finance">📌</div>
                <h3 class="kpi-title">Total Planned Budget</h3>
                <div class="kpi-number">RM <?php echo number_format($total_planned, 2); ?></div>
            </div>
            <div class="finance-kpi-card expense-card">
                <div class="kpi-icon-finance">💸</div>
                <h3 class="kpi-title">Total Actual Expense</h3>
                <div class="kpi-number">RM <?php echo number_format($total_actual_expense, 2); ?></div>
            </div>
            <div class="finance-kpi-card <?php echo $total_variance >= 0 ? 'income-card' : 'expense-card'; ?>">
                <div class="kpi-icon-finance"><?php echo $total_variance >= 0 ? '✅' : '⚠️'; ?></div>
                <h3 class="kpi-title">Budget Variance</h3>
                <div class="kpi-number">RM <?php echo number_format($total_variance, 2); ?></div>
            </div>
        </div>

        <!-- Budget by Event -->
        <div class="recent-transactions-card">
            <div class="recent-header">
                <h2 class="recent-title">Budget by Event</h2>
            </div>
            <div class="trans-table-container">
                <div class="table-responsive">
                    <table class="table table-finance">
                        <thead>
                            <tr>
                                <th>Event Date</th>
                                <th>Event Title</th>
                                <th>Planned Budget</th>
                                <th>Actual Expense</th>
                                <th>Variance</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($event_budgets)): ?>
                            <?php foreach ($event_budgets as $event): ?>
                                <?php
                                    $planned = (float)$event['planned_budget'];
                                    $actual = (float)$event['actual_expense'];
                                    $variance = $planned - $actual;

                                    if ($variance > 0) {
                                        $status = 'Under Budget';
                                        $status_class = 'text-success';
                                    } elseif ($variance < 0) {
                                        $status = 'Over Budget';
                                        $status_class = 'text-danger';
                                    } else {
                                        $status = 'On Track';
                                        $status_class = 'text-primary';
                                    }
                                ?>
                                <tr>
                                    <td><?php echo !empty($event['event_date']) ? date('M j, Y', strtotime($event['event_date'])) : '-'; ?></td>
                                    <td><?php echo htmlspecialchars($event['event_title'] ?? '-'); ?></td>
                                    <td><strong>RM <?php echo number_format($planned, 2); ?></strong></td>
                                    <td>RM <?php echo number_format($actual, 2); ?></td>
                                    <td class="<?php echo $variance >= 0 ? 'text-success' : 'text-danger'; ?>">
                                        RM <?php echo number_format($variance, 2); ?>
                                    </td>
                                    <td><span class="<?php echo $status_class; ?>"><strong><?php echo $status; ?></strong></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">No budget data found for current filters.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
