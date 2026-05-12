<?php
/**
 * KEBANA Management System - Create Master Event
 * File: modules/events/create_master.php
 * 
 * Allows Setiausaha Pusat (Role 4) to create Master Events with PDF guideline upload
 * Event workflow: Draft -> Pending President -> Approved by President
 */

$page_title = 'Create Master Event';
$css_path = '../../src/css/members.css';

require_once '../../includes/header.php';
require_once '../../includes/events_helper.php';

// Role check: Only Setiausaha Pusat (role 4) can access this page
$pusat_event_creators = [888, 4]; // Super Admin, Setiausaha Pusat

$current_role = isset($_SESSION['role']) ? (int)$_SESSION['role'] : 0;

if (!in_array($current_role, $pusat_event_creators, true)) {
    die('Access denied. Setiausaha Pusat or Super Admin only.');
}

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Validate required fields
    $event_title = trim($_POST['event_title'] ?? '');
    $event_date = $_POST['event_date'] ?? '';
    $event_end_date = !empty($_POST['event_end_date']) ? $_POST['event_end_date'] : null;
    $venue = trim($_POST['venue'] ?? '');
    $budget_est = !empty($_POST['budget_est']) ? (float)$_POST['budget_est'] : null;
    
    if (empty($event_title) || empty($event_date) || empty($venue)) {
        $message = 'Event title, date, and venue are required.';
        $message_type = 'error';
    } else {
        // Process file upload if provided
        $guideline_file_path = null;
        
        if (isset($_FILES['guideline_file']) && $_FILES['guideline_file']['error'] === UPLOAD_ERR_OK) {
            $file_data = $_FILES['guideline_file'];
            
            // Validate PDF file
            $allowed_extensions = ['pdf'];
            $file_name = $file_data['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            if (!in_array($file_ext, $allowed_extensions, true)) {
                $message = 'Only PDF files are allowed for guideline document.';
                $message_type = 'error';
            } elseif ($file_data['size'] > 10 * 1024 * 1024) { // 10MB limit
                $message = 'File size cannot exceed 10MB.';
                $message_type = 'error';
            } else {
                // Create uploads/guidelines directory if not exists
                $upload_dir = __DIR__ . '/../../uploads/guidelines';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                // Generate unique filename: guideline_master_TIMESTAMP_RANDOM.pdf
                $unique_name = 'guideline_master_' . time() . '_' . mt_rand(1000, 9999) . '.pdf';
                $target_path = $upload_dir . '/' . $unique_name;
                
                if (move_uploaded_file($file_data['tmp_name'], $target_path)) {
                    $guideline_file_path = 'uploads/guidelines/' . $unique_name;
                } else {
                    $message = 'Failed to upload guideline file. Please try again.';
                    $message_type = 'error';
                }
            }
        }
        
        // If no error, proceed to insert event
        if (empty($message)) {
            try {
                // Insert the Master Event
                $stmt = $conn->prepare("
INSERT INTO tbl_event (
                        event_title, 
                        event_date, 
                        event_end_date, 
                        venue, 
                        budget_est, 
                        created_by, 
                        event_level,
                        parent_event_id,
                        cawangan_id,
                        guideline_file,
                        approval_status,
                        status
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, 
                        'MASTER',
                        NULL,
                        NULL,
                        ?,
                        'Pending',
                        'Draft'
                    )
                ");
                
                $stmt->bind_param(
                    "sssdiiss",
                    $event_title,
                    $event_date,
                    $event_end_date,
                    $venue,
                    $budget_est,
                    $user_id,
                    $guideline_file_path
                );
                
                if ($stmt->execute()) {
                    $event_id = $stmt->insert_id;
                    $stmt->close();
                    
$message = 'Master Event created successfully! Click Submit to send to President for approval.';
                    $message_type = 'success';
                    
                    // ============================================================
                    // TODO: Insert notification logic here to alert the President (Role 1)
                    // Example:
                    // sendNotificationToPresident($conn, $event_id, 'Master Event', $event_title);
                    // ============================================================
                    
                    // Redirect after success
                    echo '<script>setTimeout(function(){ window.location.href = "list.php"; }, 1500);</script>';
                } else {
                    $message = 'Failed to create event. Please try again.';
                    $message_type = 'error';
                }
            } catch (Exception $e) {
                error_log("Create Master Event Error: " . $e->getMessage());
                $message = 'An error occurred. Please try again.';
                $message_type = 'error';
            }
        }
    }
}
?>

<div class="members-container">
    <section class="page-header-section">
        <div class="container-xl">
            <div class="page-header-content">
                <div class="page-header-text">
                    <h1 class="page-title">Create Master Event</h1>
                    <p class="page-subtitle">Create a Master Event with guideline document for branches (Cawangan)</p>
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
                    <h3 class="card-title">Master Event Details</h3>
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
                                <label for="event_date" class="form-label">Start Date <span class="text-danger">*</span></label>
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
                                <label for="event_end_date" class="form-label">End Date</label>
                                <input
                                    type="date"
                                    class="form-input"
                                    id="event_end_date"
                                    name="event_end_date"
                                    value="<?php echo isset($_POST['event_end_date']) ? htmlspecialchars($_POST['event_end_date']) : ''; ?>"
                                >
                                <small class="text-muted">Optional - leave blank for single-day events</small>
                            </div>
                        </div>

                        <div class="form-row">
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
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="guideline_file" class="form-label">Guideline Document (PDF only) <span class="text-danger">*</span></label>
                                <input
                                    type="file"
                                    class="form-input"
                                    id="guideline_file"
                                    name="guideline_file"
                                    accept=".pdf"
                                    required
                                >
                                <small class="text-muted">Required - Upload PDF guideline document for branch (Cawangan) reference. Max size: 10MB.</small>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Create Master Event</button>
                            <a href="list.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
