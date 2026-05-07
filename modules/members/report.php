<?php
/**
 * KEBANA Management System - Member Reports
 * File: modules/members/report.php
 */

$page_title = 'Member Reports';
$css_path = '../../src/css/members.css';

require_once '../../includes/header.php';
require_once '../../includes/members_helper.php';

// Report access: Secretary and Super Admin
if (!hasRole(['Secretary', 'Super Admin'])) {
    die('Access denied. Secretary/Super Admin only.');
}

// Detect optional columns for schema-safe reporting
$column_flags = [
    'created_at' => false,
    'address' => false,
];

$col_res = $conn->query("SHOW COLUMNS FROM tbl_member");
if ($col_res) {
    while ($col = $col_res->fetch_assoc()) {
        $name = $col['Field'] ?? '';
        if (array_key_exists($name, $column_flags)) {
            $column_flags[$name] = true;
        }
    }
}

// Summary cards
$total_members = 0;
$active_members = 0;
$inactive_members = 0;
$new_this_month = 0;

$summary_sql = "
    SELECT
        COUNT(*) AS total_members,
        SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) AS active_members,
        SUM(CASE WHEN status = 'Inactive' THEN 1 ELSE 0 END) AS inactive_members,
        " . ($column_flags['created_at']
            ? "SUM(CASE WHEN DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') THEN 1 ELSE 0 END)"
            : "0") . " AS new_this_month
    FROM tbl_member
";

$summary_result = $conn->query($summary_sql);
if ($summary_result && $row = $summary_result->fetch_assoc()) {
    $total_members = (int)($row['total_members'] ?? 0);
    $active_members = (int)($row['active_members'] ?? 0);
    $inactive_members = (int)($row['inactive_members'] ?? 0);
    $new_this_month = (int)($row['new_this_month'] ?? 0);
}

// Membership growth (current year) - only if created_at exists
$growth_data = [];
if ($column_flags['created_at']) {
    $growth_sql = "
        SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS total
        FROM tbl_member
        WHERE YEAR(created_at) = YEAR(CURDATE())
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY ym ASC
    ";

    $growth_result = $conn->query($growth_sql);
    if ($growth_result) {
        while ($g = $growth_result->fetch_assoc()) {
            $growth_data[] = $g;
        }
    }
}

// Data completeness
$completeness = [
    'missing_phone' => 0,
    'missing_address' => 0,
    'missing_ic' => 0,
];

$comp_sql = "
    SELECT
        SUM(CASE WHEN phone_no IS NULL OR TRIM(phone_no) = '' THEN 1 ELSE 0 END) AS missing_phone,
        " . ($column_flags['address']
            ? "SUM(CASE WHEN address IS NULL OR TRIM(address) = '' THEN 1 ELSE 0 END)"
            : "0") . " AS missing_address,
        SUM(CASE WHEN ic_number IS NULL OR TRIM(ic_number) = '' THEN 1 ELSE 0 END) AS missing_ic
    FROM tbl_member
";

$comp_result = $conn->query($comp_sql);
if ($comp_result && $c = $comp_result->fetch_assoc()) {
    $completeness['missing_phone'] = (int)($c['missing_phone'] ?? 0);
    $completeness['missing_address'] = (int)($c['missing_address'] ?? 0);
    $completeness['missing_ic'] = (int)($c['missing_ic'] ?? 0);
}

// Custom filter + CSV export
$filter_status = $_GET['status'] ?? '';
$filter_village = $_GET['village'] ?? '';
$filter_from = $_GET['from'] ?? '';
$filter_to = $_GET['to'] ?? '';

$where = "1=1";
$params = [];
$types = '';

if ($filter_status !== '') {
    $where .= " AND status = ?";
    $params[] = $filter_status;
    $types .= 's';
}
if ($filter_village !== '') {
    $where .= " AND village LIKE ?";
    $params[] = '%' . $filter_village . '%';
    $types .= 's';
}
if ($column_flags['created_at'] && $filter_from !== '') {
    $where .= " AND DATE(created_at) >= ?";
    $params[] = $filter_from;
    $types .= 's';
}
if ($column_flags['created_at'] && $filter_to !== '') {
    $where .= " AND DATE(created_at) <= ?";
    $params[] = $filter_to;
    $types .= 's';
}

$filtered_members = [];
$list_sql = "SELECT member_id, full_name, ic_number, village, phone_no, status" . ($column_flags['created_at'] ? ", created_at" : "") . " FROM tbl_member WHERE $where ORDER BY member_id DESC";
$list_stmt = $conn->prepare($list_sql);
if ($list_stmt) {
    if ($types !== '') {
        $list_stmt->bind_param($types, ...$params);
    }
    $list_stmt->execute();
    $list_result = $list_stmt->get_result();
    while ($m = $list_result->fetch_assoc()) {
        $filtered_members[] = $m;
    }
    $list_stmt->close();
}

// CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="member_report_' . date('Ymd_His') . '.csv"');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['Member ID', 'Full Name', 'IC Number', 'Village', 'Phone', 'Status', 'Created At']);
    foreach ($filtered_members as $row) {
        fputcsv($out, [
            $row['member_id'],
            $row['full_name'],
            $row['ic_number'],
            $row['village'],
            $row['phone_no'],
            $row['status'],
            $row['created_at']
        ]);
    }
    fclose($out);
    exit;
}
?>

<div class="members-container">
    <section class="page-header-section">
        <div class="container-xl">
            <div class="page-header-content">
                <div class="page-header-text">
                    <h1 class="page-title">Member Reports</h1>
                    <p class="page-subtitle">Summary, growth trends, data quality, and filtered export</p>
                </div>
                <div class="page-header-action">
                    <a href="list.php" class="btn btn-secondary">← Back to Members</a>
                </div>
            </div>
        </div>
    </section>

    <div class="main-content-area">
        <div class="container-xl">

            <section class="kpi-section">
                <div class="kpi-grid">
                    <div class="kpi-card">
                        <div class="kpi-content">
                            <p class="kpi-label">Total Members</p>
                            <h3 class="kpi-value"><?php echo number_format($total_members); ?></h3>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-content">
                            <p class="kpi-label">Active Members</p>
                            <h3 class="kpi-value"><?php echo number_format($active_members); ?></h3>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-content">
                            <p class="kpi-label">Inactive Members</p>
                            <h3 class="kpi-value"><?php echo number_format($inactive_members); ?></h3>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-content">
                            <p class="kpi-label">New This Month</p>
                            <h3 class="kpi-value"><?php echo number_format($new_this_month); ?></h3>
                        </div>
                    </div>
                </div>
            </section>

            <div class="dashboard-card mb-4">
                <div class="card-header-custom">
                    <h3 class="card-title">Membership Growth (Current Year)</h3>
                </div>
                <div class="card-body-custom">
                    <?php if (!$column_flags['created_at']): ?>
                        <p class="text-muted mb-0">Growth report requires <code>created_at</code> column in <code>tbl_member</code>.</p>
                    <?php elseif (empty($growth_data)): ?>
                        <p class="text-muted mb-0">No growth data available for this year.</p>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>New Members</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($growth_data as $g): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($g['ym']); ?></td>
                                        <td><?php echo (int)$g['total']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <div class="dashboard-card mb-4">
                <div class="card-header-custom">
                    <h3 class="card-title">Data Completeness</h3>
                </div>
                <div class="card-body-custom">
                    <ul>
                        <li>Missing Phone: <strong><?php echo $completeness['missing_phone']; ?></strong></li>
                        <li>Missing Address: <strong><?php echo $completeness['missing_address']; ?></strong></li>
                        <li>Missing IC Number: <strong><?php echo $completeness['missing_ic']; ?></strong></li>
                    </ul>
                </div>
            </div>

            <div class="dashboard-card">
                <div class="card-header-custom">
                    <h3 class="card-title">Custom Filter & Export</h3>
                </div>
                <div class="card-body-custom">
                    <form method="GET" class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="">All</option>
                                <option value="Active" <?php echo $filter_status === 'Active' ? 'selected' : ''; ?>>Active</option>
                                <option value="Inactive" <?php echo $filter_status === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Village</label>
                            <input type="text" name="village" class="form-control" value="<?php echo htmlspecialchars($filter_village); ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">From</label>
                            <input type="date" name="from" class="form-control" value="<?php echo htmlspecialchars($filter_from); ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">To</label>
                            <input type="date" name="to" class="form-control" value="<?php echo htmlspecialchars($filter_to); ?>">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Apply</button>
                        </div>
                    </form>

                    <div class="mb-3">
                        <a class="btn btn-success"
                           href="?status=<?php echo urlencode($filter_status); ?>&village=<?php echo urlencode($filter_village); ?>&from=<?php echo urlencode($filter_from); ?>&to=<?php echo urlencode($filter_to); ?>&export=csv">
                            Export CSV
                        </a>
                    </div>

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>IC</th>
                                <th>Village</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($filtered_members)): ?>
                                <tr><td colspan="7" class="text-center">No members found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($filtered_members as $row): ?>
                                    <tr>
                                        <td><?php echo (int)$row['member_id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['ic_number']); ?></td>
                                        <td><?php echo htmlspecialchars($row['village']); ?></td>
                                        <td><?php echo htmlspecialchars($row['phone_no'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                                        <td><?php echo htmlspecialchars($row['created_at'] ?? '-'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
