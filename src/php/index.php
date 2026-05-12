<?php
/**
 * KEBANA Management System - Dashboard
 * File: src/php/index.php
 * 
 * Main dashboard page - requires authentication
 */

$page_title = 'Dashboard';
$css_path = '../css/dashboard.css';

require_once '../../includes/header.php';
require_once '../../includes/members_helper.php';
require_once '../../includes/dashboard_helper.php';
require_once '../../includes/finance_helper.php';

// Get dashboard statistics (real data only)
$total_members = getMemberCount($conn);
$active_members = count(getMembersByStatus($conn, 'Active'));

$upcoming_events = getUpcomingEventsCount($conn);
$past_events = 0;
$past_events_result = $conn->query("SELECT COUNT(*) AS total FROM tbl_event WHERE event_date < CURDATE()");
if ($past_events_result) {
    $past_events = (int)($past_events_result->fetch_assoc()['total'] ?? 0);
}
$total_events = $upcoming_events + $past_events;

$pending_documents = getPendingDocumentsCount($conn);

// Total documents (real)
$total_documents = 0;
$doc_count_result = $conn->query("SELECT COUNT(*) AS total FROM tbl_document");
if ($doc_count_result) {
    $total_documents = (int)($doc_count_result->fetch_assoc()['total'] ?? 0);
}

// Pending approvals from submitted events awaiting super admin action
$pending_approvals = getPendingApprovalsCount($conn);

$fund_balance = getFundBalance($conn);
$finance_totals = getFinanceTotals($conn);

// Members participation ratio based on Active status (real)
$participation_rate = $total_members > 0 ? round(($active_members / $total_members) * 100) : 0;

// Build recent activity from real records
$recent_activity = [];

// Latest member registrations
$member_activity_result = $conn->query("
    SELECT full_name, created_at
    FROM tbl_member
    ORDER BY created_at DESC
    LIMIT 2
");
if ($member_activity_result) {
    while ($row = $member_activity_result->fetch_assoc()) {
        $recent_activity[] = [
            'text' => 'New member registered: ' . $row['full_name'],
            'time' => $row['created_at'] ?? null
        ];
    }
}

// Latest uploaded documents
$doc_activity_result = $conn->query("
    SELECT doc_name, uploaded_at
    FROM tbl_document
    ORDER BY uploaded_at DESC
    LIMIT 2
");
if ($doc_activity_result) {
    while ($row = $doc_activity_result->fetch_assoc()) {
        $recent_activity[] = [
            'text' => 'Document uploaded: ' . $row['doc_name'],
            'time' => $row['uploaded_at'] ?? null
        ];
    }
}

// Latest transactions
$transactions = getRecentTransactions($conn, 2);
foreach ($transactions as $txn) {
    $recent_activity[] = [
        'text' => $txn['trans_type'] . ' transaction recorded (' . $txn['category'] . ')',
        'time' => $txn['created_at'] ?? null
    ];
}

// Sort activities by datetime desc and keep latest 6
usort($recent_activity, function ($a, $b) {
    $ta = strtotime($a['time'] ?? '1970-01-01 00:00:00');
    $tb = strtotime($b['time'] ?? '1970-01-01 00:00:00');
    return $tb <=> $ta;
});
$recent_activity = array_slice($recent_activity, 0, 6);

function formatRelativeTime($datetime) {
    if (!$datetime) return 'Unknown time';
    $ts = strtotime($datetime);
    if (!$ts) return 'Unknown time';
    $diff = time() - $ts;
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' minute(s) ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hour(s) ago';
    return floor($diff / 86400) . ' day(s) ago';
}

?>

<div class="dashboard-container">
    <!-- Page Header Section -->
    <section class="page-header-section">
        <div class="container-xl">
            <div class="page-header-content">
                <div class="page-header-text">
                    <h1 class="page-title">Welcome back, <?php echo htmlspecialchars($username); ?></h1>
                    <p class="page-subtitle">Here's an overview of your organization's activities and performance metrics.</p>
                </div>
                <div class="page-header-status">
                    <div class="status-item">
                        <span class="status-label">System Status</span>
                        <span class="badge badge-success">Active</span>
                    </div>
                    <div class="status-item">
                        <span class="status-label">Last Updated</span>
                        <span class="status-time"><?php echo date('M d, Y • H:i'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="main-content-area">
        <div class="container-xl">
            <!-- KPI Cards Section -->
            <section class="kpi-section">
                <div class="section-header">
                    <h2 class="section-title">Key Performance Indicators</h2>
                </div>
                <div class="kpi-grid">
                    <div class="kpi-card">
                        <div class="kpi-icon" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <div class="kpi-content">
                            <p class="kpi-label">Total Members</p>
                            <h3 class="kpi-value"><?php echo number_format($total_members); ?></h3>
                            <span class="kpi-change neutral">Total registered</span>
                        </div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-icon" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                        </div>
                        <div class="kpi-content">
                            <p class="kpi-label">Upcoming Events</p>
                            <h3 class="kpi-value"><?php echo number_format($upcoming_events); ?></h3>
                            <span class="kpi-change neutral">All scheduled</span>
                        </div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-icon" style="background: linear-gradient(135deg, #198754 0%, #157347 100%);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                                <polyline points="13 2 13 9 20 9"></polyline>
                            </svg>
                        </div>
                        <div class="kpi-content">
                            <p class="kpi-label">Pending Documents</p>
                            <h3 class="kpi-value"><?php echo number_format($pending_documents); ?></h3>
                            <span class="kpi-change warning">Requires review</span>
                        </div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-icon" style="background: linear-gradient(135deg, #ff9800 0%, #e68900 100%);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                        </div>
                        <div class="kpi-content">
                            <p class="kpi-label">Fund Balance</p>
                            <h3 class="kpi-value"><?php echo formatFundBalance($fund_balance); ?></h3>
                            <span class="kpi-change positive">Available</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Main Dashboard Grid -->
            <section class="dashboard-grid">
                <!-- Left Column -->
                <div class="dashboard-column-main">
                    <!-- Operational Summary -->
                    <div class="dashboard-card">
                        <div class="card-header-custom">
                            <div>
                                <h3 class="card-title">Operational Summary</h3>
                                <p class="card-subtitle">Key metrics for program delivery and community support</p>
                            </div>
                            <a href="#" class="btn-link">View Full Report →</a>
                        </div>
                        <div class="card-body-custom">
                            <div class="metrics-grid">
                                <div class="metric-item">
                                    <span class="metric-icon">👥</span>
                                    <p class="metric-label">Active Members</p>
                                    <h4 class="metric-value"><?php echo number_format($active_members); ?></h4>
                                    <span class="metric-trend neutral">Out of <?php echo number_format($total_members); ?> total members</span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-icon">📊</span>
                                    <p class="metric-label">Total Events</p>
                                    <h4 class="metric-value"><?php echo number_format($total_events); ?></h4>
                                    <span class="metric-trend neutral"><?php echo number_format($upcoming_events); ?> upcoming, <?php echo number_format($past_events); ?> past</span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-icon">📄</span>
                                    <p class="metric-label">Total Documents</p>
                                    <h4 class="metric-value"><?php echo number_format($total_documents); ?></h4>
                                    <span class="metric-trend warning"><?php echo number_format($pending_documents); ?> pending review</span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-icon">⏳</span>
                                    <p class="metric-label">Pending Approvals</p>
                                    <h4 class="metric-value"><?php echo number_format($pending_approvals); ?></h4>
                                    <span class="metric-trend alert">Current pending items</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Members & Finance Row -->
                    <div class="dashboard-row">
                        <div class="dashboard-card">
                            <div class="card-header-custom">
                                <div>
                                    <h3 class="card-title">Members Overview</h3>
                                    <p class="card-subtitle">Active participation metrics</p>
                                </div>
                            </div>
                            <div class="card-body-custom">
                                <div class="progress-section">
                                    <div class="progress-header">
                                        <span class="progress-label">Active Member Ratio</span>
                                        <span class="progress-value"><?php echo $participation_rate; ?>%</span>
                                    </div>
                                    <div class="progress-bar-custom" role="progressbar" style="width: <?php echo $participation_rate; ?>%;" aria-valuenow="<?php echo $participation_rate; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    <p class="progress-note"><?php echo number_format($active_members); ?> active members from <?php echo number_format($total_members); ?> registered members</p>
                                </div>
                            </div>
                        </div>

                        <div class="dashboard-card">
                            <div class="card-header-custom">
                                <div>
                                    <h3 class="card-title">Finance Snapshot</h3>
                                    <p class="card-subtitle">Budget status overview</p>
                                </div>
                            </div>
                            <div class="card-body-custom">
                                <div class="finance-box">
                                    <div class="finance-item">
                                        <span class="finance-label">Available Funds</span>
                                        <p class="finance-value"><?php echo 'RM ' . number_format($fund_balance, 2); ?></p>
                                    </div>
                                    <div class="finance-item">
                                        <span class="finance-label">Status</span>
                                        <p class="finance-badge"><?php echo $fund_balance >= 0 ? 'Positive' : 'Deficit'; ?></p>
                                    </div>
                                </div>
                                <p class="finance-note">Income: RM <?php echo number_format((float)$finance_totals['total_income'], 2); ?> | Expense: RM <?php echo number_format((float)$finance_totals['total_expense'], 2); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column (Sidebar) -->
                <div class="dashboard-column-sidebar">
                    <!-- Recent Activity -->
                    <div class="dashboard-card">
                        <div class="card-header-custom">
                            <h3 class="card-title">Recent Activity</h3>
                        </div>
                        <div class="card-body-custom">
                            <div class="activity-timeline">
                                <?php if (!empty($recent_activity)): ?>
                                    <?php foreach ($recent_activity as $activity): ?>
                                        <div class="activity-item">
                                            <span class="activity-dot"></span>
                                            <p class="activity-text"><?php echo htmlspecialchars($activity['text']); ?></p>
                                            <span class="activity-time"><?php echo htmlspecialchars(formatRelativeTime($activity['time'])); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="activity-item">
                                        <span class="activity-dot"></span>
                                        <p class="activity-text">No recent activity found</p>
                                        <span class="activity-time">—</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>

<!-- End -->