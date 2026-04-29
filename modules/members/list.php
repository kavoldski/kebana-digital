<?php
/**
 * KEBANA Management System - Members List
 * File: modules/members/list.php
 *
 * Display all members with options to view, edit, and delete
 */

$page_title = 'Members List';
$css_path = '../../src/css/members.css';

require_once '../../includes/header.php';
require_once '../../includes/members_helper.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;

// Get members
$members_data = getAllMembers($conn, $page, $per_page);
$members = $members_data['members'];
$total_members = $members_data['total'];
$total_pages = ceil($total_members / $per_page);

// Get stats for KPI cards
$active_members = count(getMembersByStatus($conn, 'Active'));
$inactive_members = count(getMembersByStatus($conn, 'Inactive'));

// Delete member if requested
$delete_message = '';
$delete_message_type = '';

if (isset($_GET['delete']) && isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    $member_id = (int)$_GET['delete'];
    $result = deleteMember($conn, $member_id);

    if ($result['status']) {
        $delete_message = $result['message'];
        $delete_message_type = 'success';
        header("Refresh:2; url=list.php");
    } else {
        $delete_message = $result['message'];
        $delete_message_type = 'error';
    }
}

$delete_confirm = isset($_GET['delete']) && (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes');
$delete_member_id = isset($_GET['delete']) ? (int)$_GET['delete'] : 0;
?>

<div class="members-container">
    <!-- Page Header Section -->
    <section class="page-header-section">
        <div class="container-xl">
            <div class="page-header-content">
                <div class="page-header-text">
                    <h1 class="page-title">Members Management</h1>
                    <p class="page-subtitle">View and manage all registered members</p>
                </div>
                <div class="page-header-action">
                    <a href="add.php" class="btn btn-primary">+ Add Member</a>
                </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="main-content-area">
        <div class="container-xl">

            <!-- Delete Confirmation Modal -->
            <?php if ($delete_confirm): ?>
            <div class="modal-overlay" id="deleteModal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title">Confirm Delete</h2>
                        <button class="modal-close" onclick="window.location='list.php'">×</button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this member? This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <a href="list.php" class="btn btn-secondary">Cancel</a>
                        <a href="?delete=<?php echo $delete_member_id; ?>&confirm=yes" class="btn btn-danger">Delete</a>
                    </div>
            </div>
            <?php endif; ?>

            <!-- Delete Message -->
            <?php if (!empty($delete_message)): ?>
            <div class="alert alert-<?php echo $delete_message_type; ?>">
                <span class="alert-icon"><?php echo $delete_message_type === 'success' ? '✓' : '⚠'; ?></span>
                <span class="alert-message"><?php echo htmlspecialchars($delete_message); ?></span>
            </div>
            <?php endif; ?>

            <!-- KPI Cards Section -->
            <section class="kpi-section">
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
                            <span class="kpi-change neutral">All registered</span>
                        </div>

                    <div class="kpi-card">
                        <div class="kpi-icon" style="background: linear-gradient(135deg, #198754 0%, #157347 100%);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                        </div>
                        <div class="kpi-content">
                            <p class="kpi-label">Active Members</p>
                            <h3 class="kpi-value"><?php echo number_format($active_members); ?></h3>
                            <span class="kpi-change positive">Currently active</span>
                        </div>

                    <div class="kpi-card">
                        <div class="kpi-icon" style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                        </div>
                        <div class="kpi-content">
                            <p class="kpi-label">Inactive Members</p>
                            <h3 class="kpi-value"><?php echo number_format($inactive_members); ?></h3>
                            <span class="kpi-change warning">On hold</span>
                        </div>
                </div>
            </section>

            <!-- Members Table Card -->
            <div class="dashboard-card">
                <div class="card-header-custom">
                    <div>
                        <h3 class="card-title">Member Directory</h3>
                        <p class="card-subtitle">Complete list of all registered members</p>
                    </div>
                <div class="card-body-custom">
                    <?php if (empty($members)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">👥</div>
                        <h3 class="empty-title">No Members Yet</h3>
                        <p class="empty-text">Start by adding a member profile.</p>
                        <a href="add.php" class="btn btn-primary">Add First Member</a>
                    </div>
                    <?php else: ?>

                    <!-- Members Table -->
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Member ID</th>
                                <th>Full Name</th>
                                <th>IC Number</th>
                                <th>Village</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($members as $member): ?>
                            <tr>
                                <td class="table-id">#<?php echo str_pad($member['member_id'], 4, '0', STR_PAD_LEFT); ?></td>
                                <td class="table-name"><?php echo htmlspecialchars($member['full_name']); ?></td>
                                <td class="table-ic"><?php echo htmlspecialchars($member['ic_number']); ?></td>
                                <td class="table-village"><?php echo htmlspecialchars($member['village']); ?></td>
                                <td class="table-phone"><?php echo htmlspecialchars($member['phone_no'] ?? 'N/A'); ?></td>
                                <td class="table-status">
                                    <span class="badge badge-<?php echo strtolower($member['status']) === 'active' ? 'success' : 'warning'; ?>">
                                        <?php echo htmlspecialchars($member['status']); ?>
                                    </span>
                                </td>
                                <td class="table-actions">
                                    <a href="view.php?id=<?php echo $member['member_id']; ?>" class="action-btn action-view" title="View">👁️</a>
                                    <a href="edit.php?id=<?php echo $member['member_id']; ?>" class="action-btn action-edit" title="Edit">✏️</a>
                                    <a href="?delete=<?php echo $member['member_id']; ?>" class="action-btn action-delete" title="Delete">🗑️</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                        <a href="?page=1" class="pagination-link">« First</a>
                        <a href="?page=<?php echo $page - 1; ?>" class="pagination-link">‹ Previous</a>
                        <?php endif; ?>

                        <?php
                        $start = max(1, $page - 2);
                        $end = min($total_pages, $page + 2);
                        for ($i = $start; $i <= $end; $i++):
                        ?>
                        <a href="?page=<?php echo $i; ?>"
                           class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>" class="pagination-link">Next ›</a>
                        <a href="?page=<?php echo $total_pages; ?>" class="pagination-link">Last »</a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <?php endif; ?>
                </div>
        </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
