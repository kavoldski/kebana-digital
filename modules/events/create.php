<?php
/**
 * KEBANA Management System - Create Event
 * File: modules/events/create.php
 */

$page_title = 'Create Event';
$css_path = '../../src/css/members.css';

require_once '../../includes/header.php';
require_once '../../includes/events_helper.php';

if (!hasRole(['Secretary', 'Super Admin'])) {
    die('Access denied. Secretary/Super Admin only.');
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = addEvent($conn, $_POST, $user_id);

    if ($result['status']) {
        $message = $result['message'];
        $message_type = 'success';

        $event_id = $result['event_id'];

        // Step 2: Optional proposal upload (after required fields are validated and event is created)
        if (isset($_FILES['proposal_file']) && $_FILES['proposal_file']['error'] === UPLOAD_ERR_OK) {
            $upload_result = handleEventDocumentUpload($conn, $event_id, $_FILES['proposal_file']);
            if (!$upload_result['status']) {
                $message_type = 'error';
                $message = 'Event created, but proposal upload failed: ' . $upload_result['message'];
            } else {
                $message .= ' Proposal uploaded successfully.';
            }
        } elseif (isset($_FILES['proposal_file']) && $_FILES['proposal_file']['error'] !== UPLOAD_ERR_NO_FILE && $_FILES['proposal_file']['error'] !== UPLOAD_ERR_OK) {
            $message_type = 'error';
            $message = 'Event created, but proposal upload failed due to upload error code: ' . (int)$_FILES['proposal_file']['error'];
        }

        // Use JavaScript redirect since HTML has already been output via header.php
        echo '<script>setTimeout(function(){ window.location.href = "list.php"; }, 1200);</script>';
    } else {
        $message = $result['message'];
        $message_type = 'error';
    }
}
?>

<div class="members-container">
    <section class="page-header-section">
        <div class="container-xl">
            <div class="page-header-content">
                <div class="page-header-text">
                    <h1 class="page-title">Create New Event</h1>
                    <p class="page-subtitle">Step 1: Complete required event details, then proceed to proposal upload (optional)</p>
                </div>
                <div class="page-header-action">
                    <a href="list.php" class="btn btn-secondary">← Back to Events</a>
                </div>
            </div>
        </div>
    </section>

    <div class="main-content-area">
        <div class="container-xl">
            <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <span class="alert-icon"><?php echo $message_type === 'success' ? '✓' : '⚠'; ?></span>
                <span class="alert-message"><?php echo htmlspecialchars($message); ?></span>
            </div>
            <?php endif; ?>

            <div class="dashboard-card">
                <div class="card-header-custom">
                    <h3 class="card-title">Event Details & Proposal</h3>
                </div>
                <div class="card-body-custom">
                    <form method="POST" action="" class="form-container" enctype="multipart/form-data">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="event_title" class="form-label">Event Title <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    class="form-input"
                                    id="event_title"
                                    name="event_title"
                                    required
                                    placeholder="e.g., Annual General Meeting 2024"
                                    value="<?php echo isset($_POST['event_title']) ? htmlspecialchars($_POST['event_title']) : ''; ?>"
                                >
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="event_date" class="form-label">Event Date <span class="text-danger">*</span></label>
                                <input
                                    type="date"
                                    class="form-input"
                                    id="event_date"
                                    name="event_date"
                                    required
                                    value="<?php echo isset($_POST['event_date']) ? htmlspecialchars($_POST['event_date']) : ''; ?>"
                                >
                            </div>

                            <div class="form-group">
                                <label for="venue" class="form-label">Venue <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    class="form-input"
                                    id="venue"
                                    name="venue"
                                    required
                                    placeholder="e.g., Community Hall"
                                    value="<?php echo isset($_POST['venue']) ? htmlspecialchars($_POST['venue']) : ''; ?>"
                                >
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="budget_est" class="form-label">Estimated Budget (RM)</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="form-input"
                                    id="budget_est"
                                    name="budget_est"
                                    placeholder="0.00"
                                    value="<?php echo isset($_POST['budget_est']) ? htmlspecialchars($_POST['budget_est']) : ''; ?>"
                                >
                            </div>

                            <div class="form-group">
                                <label for="proposal_file" class="form-label">Step 2: Upload Proposal (PDF/JPG/JPEG/PNG, max 5MB)</label>
                                <input
                                    type="file"
                                    class="form-input"
                                    id="proposal_file"
                                    name="proposal_file"
                                    accept=".pdf,.jpg,.jpeg,.png"
                                >
                                <small class="text-muted">Optional - upload after required fields are completed</small>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Create Event</button>
                            <a href="list.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
