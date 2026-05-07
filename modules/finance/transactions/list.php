<?php
$page_title = 'Transactions';
$css_path = '../../../src/css/dashboard.css';
$extra_css = '../../../src/css/finance.css';

require_once '../../../includes/header.php';
require_once '../../../includes/auth.php';

if (!hasRole(['Treasurer', 'Super Admin'])) {
    die('Access denied. Treasurer/Super Admin only.');
}

$total_income = 0;
$total_expense = 0;

// Handle delete
if (isset($_GET['delete']) && isAdmin()) {
    $trans_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM tbl_transaction WHERE trans_id = ?");
    $stmt->bind_param("i", $trans_id);
    $stmt->execute();
    $stmt->close();
    header('Location: list.php');
    exit;
}

// Filter vars
$from_date = $_GET['from'] ?? '';
$to_date = $_GET['to'] ?? '';
$type_filter = $_GET['type'] ?? '';
$category_filter = $_GET['category'] ?? '';
$page_num = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page_num - 1) * $per_page;

// Build WHERE clause
$where = '1=1';
$filter_params = [];
$filter_types = '';
if ($from_date) {
    $where .= ' AND trans_date >= ?';
    $filter_params[] = $from_date;
    $filter_types .= 's';
}
if ($to_date) {
    $where .= ' AND trans_date <= ?';
    $filter_params[] = $to_date;
    $filter_types .= 's';
}
if ($type_filter) {
    $where .= ' AND trans_type = ?';
    $filter_params[] = $type_filter;
    $filter_types .= 's';
}
if ($category_filter) {
    $where .= ' AND category LIKE ?';
    $filter_params[] = "%$category_filter%";
    $filter_types .= 's';
}

// Get filtered transactions
$stmt = $conn->prepare("SELECT * FROM tbl_transaction WHERE $where ORDER BY trans_date DESC, trans_id DESC LIMIT ? OFFSET ?");
if (!$stmt) {
    die('DB error (prepare transactions list)');
}

$list_params = $filter_params;
$list_params[] = $per_page;
$list_params[] = $offset;
$list_types = $filter_types . 'ii';

$stmt->bind_param($list_types, ...$list_params);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();


// Get totals + count for current filters (prepared to match placeholders)
$totals_sql = "SELECT 
    SUM(CASE WHEN trans_type = 'Income' THEN amount ELSE 0 END) as total_income,
    SUM(CASE WHEN trans_type = 'Expense' THEN amount ELSE 0 END) as total_expense
    FROM tbl_transaction WHERE $where";

// Bind filter params only (no pagination params)
$types_totals = $filter_types;
$params_totals = $filter_params;

// If there are no filters, $types will be '' and params will be empty; mysqli allows this.
$totals_stmt = $conn->prepare($totals_sql);
if (!$totals_stmt) {
    die('DB error (prepare totals)');
}
if (!empty($params_totals)) {
    $totals_stmt->bind_param($types_totals, ...$params_totals);
}
$totals_stmt->execute();
$totals_result = $totals_stmt->get_result();
$totals_row = $totals_result ? $totals_result->fetch_assoc() : null;
$totals_stmt->close();

$total_income = (float)($totals_row['total_income'] ?? 0);
$total_expense = (float)($totals_row['total_expense'] ?? 0);
$balance = $total_income - $total_expense;

// Total count for pagination (also prepared)
$count_sql = "SELECT COUNT(*) as count FROM tbl_transaction WHERE $where";
$count_stmt = $conn->prepare($count_sql);
if (!$count_stmt) {
    die('DB error (prepare count)');
}
if (!empty($params_totals)) {
    $count_stmt->bind_param($types_totals, ...$params_totals);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_row = $count_result ? $count_result->fetch_assoc() : null;
$count_stmt->close();

$total_count = (int)($count_row['count'] ?? 0);
$total_pages = max(1, (int)ceil($total_count / $per_page));
?>


<div class="container-xl py-5 transactions-list-page">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 style="font-size: 2.5rem; font-weight: 800; background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                    Transactions
                </h1>
                <a href="create.php" class="btn btn-primary btn-lg" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); border: none; padding: 1rem 2rem; border-radius: 50px; font-weight: 600; font-size: 1rem;">
                    + New Transaction
                </a>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4 transactions-filter-card">
        <div class="card-body">
            <form method="GET" class="transactions-filter-form">
                <div class="filter-field">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from" value="<?php echo htmlspecialchars($from_date); ?>" class="form-control">
                </div>
                <div class="filter-field">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to" value="<?php echo htmlspecialchars($to_date); ?>" class="form-control">
                </div>
                <div class="filter-field">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="">All</option>
                        <option value="Income" <?php echo $type_filter == 'Income' ? 'selected' : ''; ?>>Income</option>
                        <option value="Expense" <?php echo $type_filter == 'Expense' ? 'selected' : ''; ?>>Expense</option>
                    </select>
                </div>
                <div class="filter-field filter-field-wide">
                    <label class="form-label">Category</label>
                    <input type="text" name="category" value="<?php echo htmlspecialchars($category_filter); ?>" placeholder="e.g. Membership, Donation" class="form-control">
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4 transactions-summary-row">
        <div class="col-md-4">
            <div class="finance-kpi-card balance-card">
                <div class="kpi-icon-finance">💰</div>
                <h3 class="kpi-title">Period Balance</h3>
                <div class="kpi-number">RM <?php echo number_format($balance, 2); ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="finance-kpi-card income-card">
                <div class="kpi-icon-finance">📈</div>
                <h3 class="kpi-title">Total Income</h3>
                <div class="kpi-number">RM <?php echo number_format($total_income, 2); ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="finance-kpi-card expense-card">
                <div class="kpi-icon-finance">📉</div>
                <h3 class="kpi-title">Total Expenses</h3>
                <div class="kpi-number">RM <?php echo number_format($total_expense, 2); ?></div>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="recent-transactions-card">
        <div class="trans-table-container">
            <div class="table-responsive">
                <?php if ($result->num_rows > 0): ?>
                    <table class="table table-finance">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Amount</th>
                                <th>Recorded</th>
                                <?php if (isAdmin()): ?>
                                    <th>Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <?php 
                                $amount_class = $row['trans_type'] == 'Income' ? 'amount-positive' : 'amount-negative';
                                ?>
                                <tr>
                                    <td><?php echo date('M j, Y', strtotime($row['trans_date'])); ?></td>
                                    <td><span class="badge badge-<?php echo strtolower($row['trans_type']); ?>">
                                        <?php echo $row['trans_type']; ?>
                                    </span></td>
                                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                                    <td><span class="<?php echo $amount_class; ?>">RM <?php echo number_format($row['amount'], 2); ?></span></td>
                                    <td>ID <?php echo $row['recorded_by']; ?></td>
                                    <?php if (isAdmin()): ?>
                                        <td>
                                            <a href="edit.php?id=<?php echo $row['trans_id']; ?>" class="btn btn-sm btn-outline-primary me-1">Edit</a>
                                            <a href="list.php?delete=<?php echo $row['trans_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this transaction?')">Delete</a>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <nav aria-label="Transaction pagination">
                        <ul class="pagination justify-content-center">
                            <?php if ($page_num > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=1&from=<?php echo urlencode($from_date); ?>&to=<?php echo urlencode($to_date); ?>&type=<?php echo urlencode($type_filter); ?>&category=<?php echo urlencode($category_filter); ?>">First</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page_num - 1; ?>&from=<?php echo urlencode($from_date); ?>&to=<?php echo urlencode($to_date); ?>&type=<?php echo urlencode($type_filter); ?>&category=<?php echo urlencode($category_filter); ?>">Previous</a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = max(1, $page_num - 2); $i <= min($total_pages, $page_num + 2); $i++): ?>
                                <li class="page-item <?php echo $i == $page_num ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&from=<?php echo urlencode($from_date); ?>&to=<?php echo urlencode($to_date); ?>&type=<?php echo urlencode($type_filter); ?>&category=<?php echo urlencode($category_filter); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page_num < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page_num + 1; ?>&from=<?php echo urlencode($from_date); ?>&to=<?php echo urlencode($to_date); ?>&type=<?php echo urlencode($type_filter); ?>&category=<?php echo urlencode($category_filter); ?>">Next</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $total_pages; ?>&from=<?php echo urlencode($from_date); ?>&to=<?php echo urlencode($to_date); ?>&type=<?php echo urlencode($type_filter); ?>&category=<?php echo urlencode($category_filter); ?>">Last</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="no-data-icon">📊</div>
                        <h4 class="no-data-text">No transactions match your filters</h4>
                        <p class="text-muted mb-4">Try adjusting your date range or type filter.</p>
                        <a href="list.php" class="btn btn-primary me-2">Reset Filters</a>
                        <a href="create.php" class="btn-add-first">Add First Transaction</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../../includes/footer.php'; ?>

