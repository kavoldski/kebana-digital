<?php
/**
 * KEBANA Digital Management System - Documents: Proposals
 * File: modules/documents/proposals.php
 */

$page_title = 'Documents - Proposals';
$css_path = $base_path . 'src/css/members.css';

require_once APP_ROOT . '/includes/header.php';
require_once APP_ROOT . '/includes/events_helper.php';

$message = '';
$message_type = '';

// Handle proposal submission for approval
if (isset($_GET['action']) && isset($_GET['event_id'])) {
    $event_id = (int)$_GET['event_id'];
    $action = $_GET['action'];
    $result = ['status' => false, 'message' => 'Invalid action'];

    if ($action === 'submit' && hasRole([4, 33])) {
        $result = submitEventProposal($conn, $event_id);
    } elseif ($action === 'approve' && hasRole([888])) {
        $result = approveEventProposal($conn, $event_id);
    } elseif ($action === 'reject' && hasRole([888])) {
        $result = rejectEventProposal($conn, $event_id);
    }

    $message = $result['message'];
    $message_type = $result['status'] ? 'success' : 'error';
}

if (isset($_GET['delete']) && isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    if (!isAdmin()) {
        $message = 'Access denied. Super Admin only.';
        $message_type = 'error';
    } else {
        $doc_id = (int)$_GET['delete'];
        $result = deleteEventDocument($conn, $doc_id);
        $message = $result['message'];
        $message_type = $result['status'] ? 'success' : 'error';
        if ($result['status']) {
            echo '<script>setTimeout(function(){ window.location.href = "proposals.php"; }, 1000);</script>';
        }
    }
}

$search = trim($_GET['search'] ?? '');
$documents = getAllDocuments($conn, $search);
$total_documents = count($documents);

$delete_confirm = isset($_GET['delete']) && (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes');
$delete_doc_id = isset($_GET['delete']) ? (int)$_GET['delete'] : 0;
?>

<div class="members-container">
    <section class="page-header-section">
        <div class="container-xl">
            <div class="page-header-content">
                <div class="page-header-text">
                    <h1 class="page-title">Documents - Proposals</h1>
                    <p class="page-subtitle">View uploaded event proposal documents</p>
                </div>
            </div>
        </div>
    </section>

    <div class="search-section">
        <div class="container-xl">
            <form method="GET" class="search-form">
                <div class="search-input-group">
                    <div class="form-group" style="flex: 1;">
                        <label for="search" class="form-label sr-only">Search Documents</label>
                        <div class="input-group">
                            <input
                                type="text"
                                class="form-input"
                                id="search"
                                name="search"
                                placeholder="🔍 Search by document name or event title..."
                                value="<?php echo htmlspecialchars($search); ?>"
                            >
                            <button type="submit" class="btn btn-primary search-btn">Search</button>
                        </div>
                    </div>
                    <?php if ($search): ?>
                    <div class="search-results-info">
                        <small class="text-muted">
                            Found <?php echo $total_documents; ?> result<?php echo $total_documents !== 1 ? 's' : ''; ?> for
                            <strong>"<?php echo htmlspecialchars($search); ?>"</strong>
                            <a href="proposals.php" class="btn-link ms-2">Clear</a>
                        </small>
                    </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="main-content-area">
        <div class="container-xl">

            <?php if ($delete_confirm && isAdmin()): ?>
            <div class="modal-overlay" id="deleteModal" style="display: flex !important;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title">Confirm Delete</h2>
                        <button class="modal-close" onclick="window.location='proposals.php'">×</button>
                    </div>
                    <div class="modal-body">
                        <p>Delete this document? This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <a href="proposals.php" class="btn btn-secondary">Cancel</a>
                        <a href="?delete=<?php echo $delete_doc_id; ?>&confirm=yes" class="btn btn-danger">Delete</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <span class="alert-icon"><?php echo $message_type === 'success' ? '✓' : '⚠'; ?></span>
                <span class="alert-message"><?php echo htmlspecialchars($message); ?></span>
            </div>
            <?php endif; ?>

            <div class="dashboard-card">
                <div class="card-header-custom">
                    <div>
                        <h3 class="card-title">Proposal Documents</h3>
                        <p class="card-subtitle">Total: <?php echo number_format($total_documents); ?> document(s)</p>
                    </div>
                </div>
                <div class="card-body-custom">
                    <?php if (empty($documents)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📄</div>
                        <h3 class="empty-title">No Documents Found</h3>
                        <p class="empty-text">Upload proposal files from the Create Event page.</p>
                        <a href="../events/create.php" class="btn btn-primary">Go to Create Event</a>
                    </div>
                    <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Doc ID</th>
                                <th>Document Name</th>
                                <th>Event</th>
                                <th>Event Status</th>
                                <th>Uploaded At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($documents as $doc): ?>
                            <?php
                                $event_status = $doc['event_status'] ?? 'N/A';
                                $badge_class = 'secondary';
                                if ($event_status === 'Approved') $badge_class = 'success';
                                elseif ($event_status === 'Submitted') $badge_class = 'warning';
                                elseif ($event_status === 'Rejected') $badge_class = 'danger';
                            ?>
                            <tr>
                                <td class="table-id">#<?php echo str_pad((int)$doc['doc_id'], 4, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($doc['doc_name']); ?></td>
                                <td><?php echo htmlspecialchars($doc['event_title'] ?? 'Unknown Event'); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $badge_class; ?>"><?php echo htmlspecialchars($event_status); ?></span>
                                </td>
                                <td><?php echo !empty($doc['uploaded_at']) ? date('M d, Y H:i', strtotime($doc['uploaded_at'])) : '-'; ?></td>
                                <td class="table-actions">
                                    <a
                                        href="../../<?php echo htmlspecialchars($doc['file_path']); ?>"
                                        class="action-btn action-view"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        title="View/Download"
                                    >📄</a>
                                    <?php if ($event_status === 'Draft' && hasRole([4, 33])): ?>
                                        <a href="?action=submit&event_id=<?php echo (int)$doc['event_id']; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="action-btn action-submit" title="Submit for Approval">✓ Submit</a>
                                    <?php endif; ?>
                                    <?php if ($event_status === 'Submitted' && hasRole([888])): ?>
                                        <a href="?action=approve&event_id=<?php echo (int)$doc['event_id']; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="action-btn action-approve" title="Approve Proposal">✓ Approve</a>
                                        <a href="?action=reject&event_id=<?php echo (int)$doc['event_id']; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="action-btn action-reject" title="Reject Proposal">✕ Reject</a>
                                    <?php endif; ?>
                                    <?php if (isAdmin()): ?>
                                        <a href="?delete=<?php echo (int)$doc['doc_id']; ?>" class="action-btn action-delete" title="Delete">🗑️</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
