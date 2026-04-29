<?php
/**
 * KEBANA Management System - Event Attendance
 * File: modules/events/attendance.php
 */

$page_title = 'Event Attendance';
$css_path = '../../src/css/members.css';

require_once '../../includes/header.php';
require_once '../../includes/events_helper.php';
require_once '../../includes/members_helper.php';

$message = '';
$message_type = '';

// Get selected event
$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
$event = $event_id > 0 ? getEventById($conn, $event_id) : null;

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $event_id > 0) {
    if (isset($_POST['attendance']) && is_array($_POST['attendance'])) {
        $success_count = 0;
        foreach ($_POST['attendance'] as $member_id => $data) {
            $status = $data['status'] ?? 'Absent';
            $notes = $data['notes'] ?? '';
            $result = markAttendance($conn, $event_id, (int)$member_id, $status, $notes, $user_id);
            if ($result['status']) {
                $success_count++;
            }
        }
        $message = "Attendance recorded for $success_count member(s).";
        $message_type = 'success';
    }
}

// Get all active members with their attendance status
$members = $event_id > 0 ? getAllMembersWithAttendance($conn, $event_id) : [];
$summary = $event_id > 0 ? getAttendanceSummary($conn, $event_id) : ['Present' => 0, 'Absent' => 0, 'Excused' => 0];
?>

<div class="members-container">
    <section class="page-header-section">
        <div class="container-xl">
            <div class="page-header-content">
                <div class="page-header-text">
                    <h1 class="page-title">Event Attendance</h1>
                    <p class="page-subtitle">Mark attendance for organization events</p>
                </div>
                <div class="page-header-action">
                    <a href="list.php" class="btn btn-secondary">← Back to Events</a>
                </div>
        </div>
    </section>

    <div class="main-content-area">
        <div class="container-xl">

            <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <span class="alert-icon">✓</span>
                <span class="alert-message"><?php echo htmlspecialchars($message); ?></span>
            </div>
            <?php endif; ?>

            <!-- Event Selector -->
            <div class="dashboard-card" style="margin-bottom: 1.5rem;">
                <div class="card-header-custom">
                    <h3 class="card-title">Select Event</h3>
                </div>
                <div class="card-body-custom">
                    <form method="GET" action="" class="form-row" style="align-items: flex-end;">
                        <div class="form-group" style="flex: 1;">
                            <label for="event_id" class="form-label">Event</label>
                            <select class="form-input" id="event_id" name="event_id" onchange="this.form.submit()">
                                <option value="">-- Select an Event --</option>
                                <?php
                                $all_events = getAllEvents($conn);
                                foreach ($all_events as $ev):
                                ?>
                                <option value="<?php echo $ev['event_id']; ?>" <?php echo $event_id === (int)$ev['event_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($ev['event_title']); ?> (<?php echo date('M d, Y', strtotime($ev['event_date'])); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>

            <?php if ($event): ?>

            <!-- Event Info & Summary -->
            <div class="dashboard-card" style="margin-bottom: 1.5rem;">
                <div class="card-header-custom">
                    <div>
                        <h3 class="card-title"><?php echo htmlspecialchars($event['event_title']); ?></h3>
                        <p class="card-subtitle">
                            📅 <?php echo date('M d, Y', strtotime($event['event_date'])); ?> &nbsp;|&nbsp;
                            📍 <?php echo htmlspecialchars($event['venue']); ?>
                        </p>
                    </div>
                <div class="card-body-custom">
                    <div class="metrics-grid">
                        <div class="metric-item">
                            <span class="metric-icon">✅</span>
                            <p class="metric-label">Present</p>
                            <h4 class="metric-value"><?php echo $summary['Present']; ?></h4>
                        </div>
                        <div class="metric-item">
                            <span class="metric-icon">❌</span>
                            <p class="metric-label">Absent</p>
                            <h4 class="metric-value"><?php echo $summary['Absent']; ?></h4>
                        </div>
                        <div class="metric-item">
                            <span class="metric-icon">📝</span>
                            <p class="metric-label">Excused</p>
                            <h4 class="metric-value"><?php echo $summary['Excused']; ?></h4>
                        </div>
                </div>

            <!-- Attendance Form -->
            <div class="dashboard-card">
                <div class="card-header-custom">
                    <div>
                        <h3 class="card-title">Member Attendance</h3>
                        <p class="card-subtitle">Mark attendance for all active members</p>
                    </div>
                <div class="card-body-custom">
                    <?php if (empty($members)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">👥</div>
                        <h3 class="empty-title">No Active Members</h3>
                        <p class="empty-text">Add members first before recording attendance.</p>
                    </div>
                    <?php else: ?>
                    <form method="POST" action="?event_id=<?php echo $event_id; ?>">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Member</th>
                                    <th>IC Number</th>
                                    <th>Village</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($members as $m): ?>
                                <tr>
                                    <td class="table-name"><?php echo htmlspecialchars($m['full_name']); ?></td>
                                    <td class="table-ic"><?php echo htmlspecialchars($m['ic_number']); ?></td>
                                    <td><?php echo htmlspecialchars($m['village']); ?></td>
                                    <td>
                                        <select name="attendance[<?php echo $m['member_id']; ?>][status]" class="form-input" style="min-width: 120px;">
                                            <option value="Absent" <?php echo ($m['attendance_status'] ?? '') === 'Absent' ? 'selected' : ''; ?>>Absent</option>
                                            <option value="Present" <?php echo ($m['attendance_status'] ?? '') === 'Present' ? 'selected' : ''; ?>>Present</option>
                                            <option value="Excused" <?php echo ($m['attendance_status'] ?? '') === 'Excused' ? 'selected' : ''; ?>>Excused</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="attendance[<?php echo $m['member_id']; ?>][notes]" class="form-input"
                                               placeholder="Optional notes" value="<?php echo htmlspecialchars($m['attendance_notes'] ?? ''); ?>">
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="form-actions" style="margin-top: 1.5rem;">
                            <button type="submit" class="btn btn-primary">Save Attendance</button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>

            <?php else: ?>

            <div class="dashboard-card">
                <div class="card-body-custom">
                    <div class="empty-state">
                        <div class="empty-icon">📅</div>
                        <h3 class="empty-title">Select an Event</h3>
                        <p class="empty-text">Choose an event from the dropdown above to record attendance.</p>
                    </div>
            </div>

            <?php endif; ?>

        </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
