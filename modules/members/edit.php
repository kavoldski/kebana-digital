<?php
/**
 * KEBANA Management System - Edit Member
 * File: modules/members/edit.php
 *
 * Form to edit member profile details
 */

$page_title = 'Edit Member';
$css_path = '../../src/css/members.css';

require_once '../../includes/header.php';
require_once '../../includes/members_helper.php';

// Initialize variables
$message = '';
$message_type = '';
$member = null;

// Get member ID from URL
$member_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($member_id <= 0) {
    $message = 'Invalid member ID';
    $message_type = 'error';
} else {
    $member = getMemberById($conn, $member_id);
    if (empty($member)) {
        $message = 'Member not found';
        $message_type = 'error';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($member)) {
    $member_data = [
        'full_name' => $_POST['full_name'] ?? '',
        'ic_number' => $_POST['ic_number'] ?? '',
        'village'   => $_POST['village'] ?? '',
        'phone_no'  => $_POST['phone_no'] ?? '',
        'status'    => $_POST['status'] ?? 'Active'
    ];

    $result = updateMember($conn, $member_id, $member_data);

    if ($result['status']) {
        $message = $result['message'];
        $message_type = 'success';
        $member = getMemberById($conn, $member_id);
    } else {
        $message = $result['message'];
        $message_type = 'error';
    }
}
?>

<div class="members-container">
    <!-- Page Header Section -->
    <section class="page-header-section">
        <div class="container-xl">
            <div class="page-header-content">
                <div class="page-header-text">
                    <h1 class="page-title">Edit Member</h1>
                    <p class="page-subtitle">Update member information</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="main-content-area">
        <div class="container-xl">

            <!-- Message Alert -->
            <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?>" role="alert">
                <span class="alert-icon">
                    <?php echo $message_type === 'success' ? '✓' : '⚠'; ?>
                </span>
                <span class="alert-message"><?php echo htmlspecialchars($message); ?></span>
                <?php if ($message_type === 'success'): ?>
                <a href="list.php" class="alert-link">Back to Members →</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($member)): ?>
            <!-- Edit Member Form Card -->
            <div class="form-card">
                <form method="POST" action="" class="member-form">

                    <div class="form-section">
                        <h2 class="section-title">Member Information</h2>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="full_name" class="form-label">Full Name <span class="required">*</span></label>
                                <input type="text" name="full_name" id="full_name"
                                       class="form-control"
                                       value="<?php echo htmlspecialchars($member['full_name']); ?>"
                                       required>
                            </div>

                            <div class="form-group">
                                <label for="ic_number" class="form-label">IC Number <span class="required">*</span></label>
                                <input type="text" name="ic_number" id="ic_number"
                                       class="form-control"
                                       value="<?php echo htmlspecialchars($member['ic_number']); ?>"
                                       required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="village" class="form-label">Village <span class="required">*</span></label>
                                <input type="text" name="village" id="village"
                                       class="form-control"
                                       value="<?php echo htmlspecialchars($member['village']); ?>"
                                       required>
                            </div>

                            <div class="form-group">
                                <label for="phone_no" class="form-label">Phone Number</label>
                                <input type="text" name="phone_no" id="phone_no"
                                       class="form-control"
                                       value="<?php echo htmlspecialchars($member['phone_no'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="Active" <?php echo ($member['status'] === 'Active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="Inactive" <?php echo ($member['status'] === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Member Since</label>
                                <input type="text" class="form-control"
                                       value="<?php echo date('M d, Y', strtotime($member['created_at'])); ?>"
                                       disabled>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Last Updated</label>
                                <input type="text" class="form-control"
                                       value="<?php echo date('M d, Y at H:i', strtotime($member['updated_at'])); ?>"
                                       disabled>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <a href="list.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>

            <?php else: ?>
            <!-- Error State -->
            <div class="form-card" style="text-align: center; padding: 3rem 2rem;">
                <div style="font-size: 2rem; margin-bottom: 1rem;">⚠️</div>
                <h2 style="color: #212529; margin-bottom: 0.5rem;">Member Not Found</h2>
                <p style="color: #6c757d; margin-bottom: 1.5rem;">The member you're looking for does not exist.</p>
                <a href="list.php" class="btn btn-primary">Back to Members</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>

