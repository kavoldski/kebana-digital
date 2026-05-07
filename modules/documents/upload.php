<?php
/**
 * KEBANA Management System - Documents: Upload
 * File: modules/documents/upload.php
 */

$page_title = 'Documents - Upload';
$css_path = '../../src/css/members.css';

require_once '../../includes/header.php';
require_once '../../includes/events_helper.php';

if (!hasRole(['Secretary', 'Treasurer', 'Super Admin'])) {
    die('Access denied. Secretary/Treasurer/Super Admin only.');
}

$message = '';
$message_type = '';

$doc_type = $_POST['doc_type'] ?? '';
$event_id = $_POST['event_id'] ?? '';

$events = getAllEvents($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doc_type = trim((string)($_POST['doc_type'] ?? ''));
    $event_id_input = $_POST['event_id'] ?? '';
    $event_id = ($event_id_input !== '') ? (int)$event_id_input : null;

    if (!isset($_FILES['document_file'])) {
        $message = 'Please select a file to upload.';
        $message_type = 'error';
    } else {
        $result = uploadGeneralDocument($conn, $event_id, $doc_type, $_FILES['document_file']);
        $message = $result['message'];
        $message_type = $result['status'] ? 'success' : 'error';

        if ($result['status']) {
            $doc_type = '';
            $event_id = '';
        }
    }
}
?>

<div class="members-container">
    <section class="page-header-section">
        <div class="container-xl">
            <div class="page-header-content">
                <div class="page-header-text">
                    <h1 class="page-title">Upload Document</h1>
                    <p class="page-subtitle">Upload proposal, minutes, or report files</p>
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
                    <div>
                        <h3 class="card-title">Document Upload Form</h3>
                        <p class="card-subtitle">Allowed files: PDF, JPG, JPEG, PNG (max 5MB)</p>
                    </div>
                </div>
                <div class="card-body-custom">
                    <form method="POST" enctype="multipart/form-data" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="doc_type">Document Type</label>
                            <select name="doc_type" id="doc_type" class="form-select" required>
                                <option value="">Select Type</option>
                                <option value="Proposal" <?php echo $doc_type === 'Proposal' ? 'selected' : ''; ?>>Proposal</option>
                                <option value="Minutes" <?php echo $doc_type === 'Minutes' ? 'selected' : ''; ?>>Minutes</option>
                                <option value="Report" <?php echo $doc_type === 'Report' ? 'selected' : ''; ?>>Report</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="event_id">Link to Event (Optional)</label>
                            <select name="event_id" id="event_id" class="form-select">
                                <option value="">General / Not linked</option>
                                <?php foreach ($events as $event): ?>
                                    <option value="<?php echo (int)$event['event_id']; ?>" <?php echo ((string)$event_id === (string)$event['event_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($event['event_title']); ?> (<?php echo htmlspecialchars($event['event_date']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="document_file">Choose File</label>
                            <input
                                type="file"
                                class="form-control"
                                id="document_file"
                                name="document_file"
                                accept=".pdf,.jpg,.jpeg,.png"
                                required
                            >
                        </div>

                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Upload Document</button>
                            <a href="proposals.php" class="btn btn-outline-secondary">Back to Documents</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>
