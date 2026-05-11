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
$filter = $_GET['filter'] ?? 'all'; // all, pusat, cawangan

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

// Filter events by category (for Setiausaha Pusat view)
$pusat_events = [];
$cawangan_events = [];

if ($event_view_mode === 'all') {
    foreach ($events as $ev) {
        $level = $ev['event_level'] ?? 'MASTER';
        $caw_id = $ev['cawangan_id'] ?? null;
        
        if ($caw_id === null) {
            // No cawangan assigned = Pusat/HQ event
            $pusat_events[] = $ev;
        } else {
            // Assigned to cawangan = Cawangan event
            if (!isset($cawangan_events[$caw_id])) {
                $cawangan_events[$caw_id] = [
                    'label' => $ev['cawangan_name'] ?? 'Unknown Branch',
                    'masters' => [],
                    'subs' => []
                ];
            }
            
            if ($level === 'MASTER') {
                $cawangan_events[$caw_id]['masters'][] = $ev;
            } else {
                $cawangan_events[$caw_id]['subs'][] = $ev;
            }
        }
    }
}

// Apply filter
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
            
            <!-- Filter Tabs for Pusat View -->
            <?php if ($event_view_mode === 'all'): ?>
            <div class="filter-tabs" style="display: flex; gap: 0.5rem; margin-top: 1rem; flex-wrap: wrap;">
                <a href="?filter=all<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                   class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>"
                   style="padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.9rem; font-weight: 500; transition: all 0.2s; <?php echo $filter === 'all' ? 'background:#0d6efd;color:#fff;' : 'background:#f8f9fa;color:#495057;'; ?>">
                    📊 All Events
                </a>
                <a href="?filter=pusat<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                   class="filter-tab <?php echo $filter === 'pusat' ? 'active' : ''; ?>"
                   style="padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.9rem; font-weight: 500; transition: all 0.2s; <?php echo $filter === 'pusat' ? 'background:#198754;color:#fff;' : 'background:#f8f9fa;color:#495057;'; ?>">
                    🏢 Pusat Events
                    <span style="font-size: 0.75rem; opacity: 0.8;">(<?php echo count($pusat_events); ?>)</span>
                </a>
                <a href="?filter=cawangan<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                   class="filter-tab <?php echo $filter === 'cawangan' ? 'active' : ''; ?>"
                   style="padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.9rem; font-weight: 500; transition: all 0.2s; <?php echo $filter === 'cawangan' ? 'background:#6f42c1;color:#fff;' : 'background:#f8f9fa;color:#495057;'; ?>">
                    🏬 Cawangan Events
                    <span style="font-size: 0.75rem; opacity: 0.8;">(<?php echo count($cawangan_events); ?>)</span>
                </a>
            </div>
            <?php endif; ?>
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

<?php if ($event_view_mode === 'all'): ?>
            <?php
                // Build display groups based on filter
                $display_groups = [];

                if ($filter === 'pusat' || $filter === 'all') {
                    // Show Pusat events (no cawangan assigned)
                    $pusat_masters = [];
                    $pusat_subs = [];
                    foreach ($pusat_events as $ev) {
                        if (($ev['event_level'] ?? 'MASTER') === 'MASTER') {
                            $pusat_masters[] = $ev;
                        } else {
                            $pusat_subs[] = $ev;
                        }
                    }
                    $display_groups['pusat'] = [
                        'label' => 'Pusat / Headquarters',
                        'masters' => $pusat_masters,
                        'subs' => $pusat_subs,
                        'style' => 'border-left: 4px solid #198754; background: linear-gradient(135deg, #19875411 0%, #15734711 100%);'
                    ];
                }

                if ($filter === 'cawangan' || $filter === 'all') {
                    // Show Cawangan events (grouped by branch)
                    foreach ($cawangan_events as $caw_id => $cw) {
                        $display_groups['cawangan_' . $caw_id] = [
                            'label' => $cw['label'],
                            'masters' => $cw['masters'],
                            'subs' => $cw['subs'],
                            'style' => 'border-left: 4px solid #6f42c1; background: linear-gradient(135deg, #6f42c111 0%, #5a326711 100%);'
                        ];
                    }
                }
            ?>

<?php 
            // Helper function to render a single event row
            function renderEventRowHelper($event, $is_sub = false) {
                global $filter, $search;
                
                $workflow_status = $event['status'] ?? 'Draft';
                $badge_class = 'secondary';
                if ($workflow_status === 'Approved') $badge_class = 'success';
                elseif ($workflow_status === 'Submitted') $badge_class = 'warning';
                elseif ($workflow_status === 'Rejected') $badge_class = 'danger';

                $level = $event['event_level'] ?? 'MASTER';
                $level_badge = $level === 'MASTER'
                    ? '<span class="badge" style="background:#0d6efd;color:#fff;font-size:0.7rem;">MASTER</span>'
                    : '<span class="badge" style="background:#6f42c1;color:#fff;font-size:0.7rem;">SUB</span>';

                $indent = $is_sub ? 'padding-left: 2rem; border-left: 3px solid #6f42c1; background: #f8f5ff;' : '';
                $title_prefix = $is_sub ? '<span style="color:#6f42c1; margin-right:4px;">↳</span>' : '';

                echo '<tr style="' . $indent . '">';
                echo '<td class="table-id">#' . str_pad($event['event_id'], 4, '0', STR_PAD_LEFT) . '</td>';
                echo '<td>' . $level_badge . '</td>';
                echo '<td class="table-name">' . $title_prefix . htmlspecialchars($event['event_title']) . '</td>';
                echo '<td>' . date('M d, Y', strtotime($event['event_date'])) . '</td>';
                echo '<td>' . htmlspecialchars($event['venue']) . '</td>';
                echo '<td>' . ($event['budget_est'] ? 'RM ' . number_format($event['budget_est'], 2) : '-') . '</td>';
                echo '<td>' . htmlspecialchars($event['creator_name'] ?? 'System') . '</td>';
                echo '<td><span class="badge badge-' . $badge_class . '">' . htmlspecialchars($workflow_status) . '</span></td>';
                echo '<td class="table-actions">';
                echo '<a href="attendance.php?event_id=' . $event['event_id'] . '" class="action-btn" title="Attendance">📋</a>';
                if ($workflow_status === 'Draft' && hasRole([4, 33, 888])) {
                    echo '<a href="?action=submit&event_id=' . $event['event_id'] . '&filter=' . $filter . ($search ? '&search=' . urlencode($search) : '') . '" class="action-btn" title="Submit Proposal">📤</a>';
                }
                if ($workflow_status === 'Submitted' && hasRole(888)) {
                    echo '<a href="?action=approve&event_id=' . $event['event_id'] . '&filter=' . $filter . ($search ? '&search=' . urlencode($search) : '') . '" class="action-btn" title="Approve">✅</a>';
                    echo '<a href="?action=reject&event_id=' . $event['event_id'] . '&filter=' . $filter . ($search ? '&search=' . urlencode($search) : '') . '" class="action-btn action-delete" title="Reject">❌</a>';
                }
                echo '<a href="?delete=' . $event['event_id'] . '&filter=' . $filter . ($search ? '&search=' . urlencode($search) : '') . '" class="action-btn action-delete" title="Delete">🗑️</a>';
                echo '</td>';
                echo '</tr>';
            }
            ?>

            <?php foreach ($display_groups as $group_key => $group): ?>
            <div class="dashboard-card" style="margin-bottom: 2rem;">
                <div class="card-header-custom" style="<?php echo $group['style']; ?>">
                    <div>
                        <?php if (strpos($group_key, 'pusat') === 0): ?>
                        <h3 class="card-title" style="color: #198754;">
                            🏢 <?php echo htmlspecialchars($group['label']); ?>
                        </h3>
                        <?php else: ?>
                        <h3 class="card-title" style="color: #6f42c1;">
                            🏬 <?php echo htmlspecialchars($group['label']); ?>
                        </h3>
                        <?php endif; ?>
                        <p class="card-subtitle">
                            <?php echo count($group['masters']); ?> Master Event(s) &nbsp;·&nbsp;
                            <?php echo count($group['subs']); ?> Sub Event(s)
                        </p>
                    </div>
                </div>
                <div class="card-body-custom">
                    <?php if (empty($group['masters']) && empty($group['subs'])): ?>
                        <p class="text-muted" style="padding: 1rem;">No events in this category.</p>
                    <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Level</th>
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
                        <?php
                            // Build index of subs by parent_event_id
                            $subs_by_parent = [];
                            foreach ($group['subs'] as $sub) {
                                $pid = $sub['parent_event_id'] ?? 0;
                                $subs_by_parent[$pid][] = $sub;
                            }

                            // Render all masters first
                            foreach ($group['masters'] as $master_event) {
                                renderEventRowHelper($master_event, false);
                                
                                // Then render sub-events under this master
                                if (isset($subs_by_parent[$master_event['event_id']])) {
                                    foreach ($subs_by_parent[$master_event['event_id']] as $sub_event) {
                                        renderEventRowHelper($sub_event, true);
                                    }
                                }
                            }

                            // Render orphan subs (parent_event_id not in masters)
                            $master_ids = array_column($group['masters'], 'event_id');
                            foreach ($group['subs'] as $sub) {
                                $pid = $sub['parent_event_id'] ?? 0;
                                if (!in_array($pid, $master_ids, true)) {
                                    renderEventRowHelper($sub, true);
                                }
                            }
                        ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <?php elseif ($event_view_mode !== 'all'): ?>
            <div class="dashboard-card">
                <div class="card-header-custom">
                    <div>
                        <h3 class="card-title">My Events</h3>
                        <p class="card-subtitle">Events created by you</p>
                    </div>
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
                                <th>ID</th>
                                <th>Level</th>
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
                                $workflow_status = $event['status'] ?? 'Draft';
                                $badge_class = 'secondary';
                                if ($workflow_status === 'Approved') $badge_class = 'success';
                                elseif ($workflow_status === 'Submitted') $badge_class = 'warning';
                                elseif ($workflow_status === 'Rejected') $badge_class = 'danger';
                                $level = $event['event_level'] ?? 'MASTER';
                                $level_badge = $level === 'MASTER'
                                    ? '<span class="badge" style="background:#0d6efd;color:#fff;font-size:0.7rem;">MASTER</span>'
                                    : '<span class="badge" style="background:#6f42c1;color:#fff;font-size:0.7rem;">SUB</span>';
                            ?>
                            <tr>
                                <td class="table-id">#<?php echo str_pad($event['event_id'], 4, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo $level_badge; ?></td>
                                <td class="table-name"><?php echo htmlspecialchars($event['event_title']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($event['event_date'])); ?></td>
                                <td><?php echo htmlspecialchars($event['venue']); ?></td>
                                <td><?php echo $event['budget_est'] ? 'RM ' . number_format($event['budget_est'], 2) : '-'; ?></td>
                                <td><?php echo htmlspecialchars($event['creator_name'] ?? 'System'); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $badge_class; ?>">
                                        <?php echo htmlspecialchars($workflow_status); ?>
                                    </span>
                                </td>
                                <td class="table-actions">
                                    <a href="attendance.php?event_id=<?php echo $event['event_id']; ?>" class="action-btn" title="Attendance">📋</a>
                                    <?php if ($workflow_status === 'Draft' && hasRole([4, 33, 888])): ?>
                                        <a href="?action=submit&event_id=<?php echo $event['event_id']; ?>" class="action-btn" title="Submit Proposal">📤</a>
                                    <?php endif; ?>
                                    <?php if ($workflow_status === 'Submitted' && hasRole(888)): ?>
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

            <?php else: ?>
            <div class="dashboard-card">
                <div class="card-body-custom">
                    <div class="empty-state">
                        <div class="empty-icon">📅</div>
                        <h3 class="empty-title">No Events Yet</h3>
                        <p class="empty-text">Start by creating your first event.</p>
                        <a href="create.php" class="btn btn-primary">Create First Event</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>
