<?php
/**
 * KEBANA Management System - Events List
 * File: modules/events/list.php
 */

$page_title = 'Events List';
$css_path = '../../src/css/members.css';

require_once '../../includes/header.php';
require_once '../../includes/events_helper.php';

// Workflow/Delete actions
$message = '';
$message_type = '';

if (isset($_GET['action']) && isset($_GET['event_id'])) {
    $event_id = (int)$_GET['event_id'];
    $action = $_GET['action'];
    $result = ['status' => false, 'message' => 'Invalid action'];

    if ($action === 'submit' && hasRole([4, 33])) {
        $result = submitEventProposal($conn, $event_id);
    } elseif ($action === 'approve' && hasRole(888)) {
        $result = approveEventProposal($conn, $event_id);
    } elseif ($action === 'reject' && hasRole(888)) {
        $result = rejectEventProposal($conn, $event_id);
    }

    $message = $result['message'];
    $message_type = $result['status'] ? 'success' : 'error';
}

if (isset($_GET['delete']) && isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    $event_id = (int)$_GET['delete'];
    $result = deleteEvent($conn, $event_id);

    if ($result['status']) {
        $message = $result['message'];
        $message_type = 'success';
        // Use JavaScript redirect since HTML has already been output via header.php
        echo '<script>setTimeout(function(){ window.location.href = "list.php"; }, 1000);</script>';
    } else {
        $message = $result['message'];
        $message_type = 'error';
    }
}

$search = trim($_GET['search'] ?? '');

$pusat_event_creators = [888, 4]; // Super Admin, Setiausaha Pusat
$current_role = isset($_SESSION['role']) ? (int)$_SESSION['role'] : 0;
$current_user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$cawangan_id = isset($_SESSION['cawangan_id']) && $_SESSION['cawangan_id'] !== null ? (int)$_SESSION['cawangan_id'] : null;

$event_view_mode = 'creator_only';
if (in_array($current_role, $pusat_event_creators, true)) {
    $event_view_mode = 'all';
} elseif ($current_role === 33) {
    $event_view_mode = 'creator_only';
}

$events = getAllEvents($conn, $event_view_mode, $current_user_id, $cawangan_id);
if ($search) {
    $events = array_filter($events, function($event) use ($search) {
        $search_lower = strtolower($search);
        return strpos(strtolower($event['event_title']), $search_lower) !== false ||
               strpos(strtolower($event['venue']), $search_lower) !== false ||
               strpos(strtolower($event['creator_name'] ?? ''), $search_lower) !== false;
    });
}
$total_events = count($events);
$upcoming = getUpcomingEventsCount($conn);
$past = getPastEventsCount($conn);

$delete_confirm = isset($_GET['delete']) && (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes');
$delete_event_id = isset($_GET['delete']) ? (int)$_GET['delete'] : 0;

?>

<div class="members-container">
    <section class="page-header-section">
        <div class="container-xl">
            <div class="page-header-content">
                <div class="page-header-text">
                    <h1 class="page-title">Events Management</h1>
                    <p class="page-subtitle">View and manage all organization events</p>
                </div>

                <div class="page-header-action">
                    <a href="create.php" class="btn btn-primary">+ Create Event</a>
                </div>
            </div>
        </div>
    </section>

    <div class="search-section">
        <div class="container-xl">
            <form method="GET" class="search-form">
                <div class="search-input-group">
                    <div class="form-group" style="flex: 1;">
                        <label for="search" class="form-label sr-only">Search Events</label>
                        <div class="input-group">
                            <input type="text" class="form-input" id="search" name="search" placeholder="🔍 Search events by title, venue or creator..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-primary search-btn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <?php if ($search): ?>
                    <div class="search-results-info">
                        <small class="text-muted">
                            Found <?php echo $total_events; ?> result<?php echo $total_events !== 1 ? 's' : ''; ?> for <strong>"<?php echo htmlspecialchars($search); ?>"</strong>
                            <a href="list.php" class="btn-link ms-2">Clear</a>
                        </small>
                    </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="main-content-area">
        <div class="container-xl">

            <?php if ($delete_confirm): ?>
            <div class="modal-overlay" id="deleteModal" style="display: flex !important;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title">Confirm Delete</h2>
                        <button class="modal-close" onclick="window.location='list.php'">×</button>
                    </div>
                    <div class="modal-body">
                        <p>Delete this event and all its documents/attendance records? This cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <a href="list.php" class="btn btn-secondary">Cancel</a>
                        <a href="?delete=<?php echo $delete_event_id; ?>&confirm=yes" class="btn btn-danger">Delete</a>
                    </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <span class="alert-icon"><?php echo $message_type === 'success' ? '✓' : '⚠'; ?></span>
                <span class="alert-message"><?php echo htmlspecialchars($message); ?></span>
            </div>
            <?php endif; ?>

            <section class="kpi-section">
                <div class="kpi-grid">
                    <div class="kpi-card">
                        <div class="kpi-icon" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                        </div>
                        <div class="kpi-content">
                            <p class="kpi-label">Total Events</p>
                            <h3 class="kpi-value"><?php echo number_format($total_events); ?></h3>
                            <span class="kpi-change neutral">All events</span>
                        </div>
                    <div class="kpi-card">
                        <div class="kpi-icon" style="background: linear-gradient(135deg, #198754 0%, #157347 100%);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                            </svg>
                        </div>
                        <div class="kpi-content">
                            <p class="kpi-label">Upcoming</p>
                            <h3 class="kpi-value"><?php echo number_format($upcoming); ?></h3>
                            <span class="kpi-change positive">Scheduled</span>
                        </div>
                    <div class="kpi-card">
                        <div class="kpi-icon" style="background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </div>
                        <div class="kpi-content">
                            <p class="kpi-label">Past Events</p>
                            <h3 class="kpi-value"><?php echo number_format($past); ?></h3>
                            <span class="kpi-change neutral">Completed</span>
                        </div>
                </div>
            </section>

            <div class="dashboard-card">
                <div class="card-header-custom">
                    <div>
                        <h3 class="card-title">Event Directory</h3>
                        <p class="card-subtitle">Complete list of all events</p>
                    </div>
                <div class="card-body-custom">
                    <?php if (empty($events)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📅</div>
                        <h3 class="empty-title">No Events Yet</h3>
                        <p class="empty-text">Start by creating your first event.</p>
                        <a href="create.php" class="btn btn-primary">Create First Event</a>
                    </div>
                    <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Event ID</th>
                                <th>Event Title</th>
                                <th>Date</th>
                                <th>Venue</th>
                                <th>Budget (RM)</th>
                                <th>Created By</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $event):
                                $is_upcoming = strtotime($event['event_date']) >= strtotime(date('Y-m-d'));
                            ?>
                            <tr>
                                <td class="table-id">#<?php echo str_pad($event['event_id'], 4, '0', STR_PAD_LEFT); ?></td>
                                <td class="table-name"><?php echo htmlspecialchars($event['event_title']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($event['event_date'])); ?></td>
                                <td><?php echo htmlspecialchars($event['venue']); ?></td>
                                <td><?php echo $event['budget_est'] ? 'RM ' . number_format($event['budget_est'], 2) : '-'; ?></td>
                                <td><?php echo htmlspecialchars($event['creator_name'] ?? 'System'); ?></td>
                                <td>
                                    <?php
                                        $workflow_status = $event['status'] ?? 'Draft';
                                        $badge_class = 'secondary';
                                        if ($workflow_status === 'Approved') $badge_class = 'success';
                                        elseif ($workflow_status === 'Submitted') $badge_class = 'warning';
                                        elseif ($workflow_status === 'Rejected') $badge_class = 'danger';
                                    ?>
                                    <span class="badge badge-<?php echo $badge_class; ?>">
                                        <?php echo htmlspecialchars($workflow_status); ?>
                                    </span>
                                </td>
                                <td class="table-actions">
                                    <a href="attendance.php?event_id=<?php echo $event['event_id']; ?>" class="action-btn" title="Attendance">📋</a>

                                    <?php if (($event['status'] ?? 'Draft') === 'Draft' && hasRole([4, 33, 888])): ?>
                                        <a href="?action=submit&event_id=<?php echo $event['event_id']; ?>" class="action-btn" title="Submit Proposal">📤</a>
                                    <?php endif; ?>

                                    <?php if (($event['status'] ?? 'Draft') === 'Submitted' && hasRole(888)): ?>
                                        <a href="?action=approve&event_id=<?php echo $event['event_id']; ?>" class="action-btn" title="Approve">✅</a>
                                        <a href="?action=reject&event_id=<?php echo $event['event_id']; ?>" class="action-btn action-delete" title="Reject">❌</a>
                                    <?php endif; ?>

                                    <a href="?delete=<?php echo $event['event_id']; ?>" class="action-btn action-delete" title="Delete">🗑️</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
        </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
