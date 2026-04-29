<?php
/**
 * KEBANA Management System - Add Member Form
 * File: modules/members/add.php
 *
 * Form to add a new member to the system
 */

$page_title = 'Add Member';
$css_path = '../../src/css/members.css';

require_once '../../includes/header.php';
require_once '../../includes/members_helper.php';

// Initialize variables
$message = '';
$message_type = '';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_data = [
        'full_name' => $_POST['full_name'] ?? '',
        'ic_number' => $_POST['ic_number'] ?? '',
        'village'   => $_POST['village'] ?? '',
        'phone_no'  => $_POST['phone_no'] ?? '',
        'status'    => $_POST['status'] ?? 'Active'
    ];

    $result = addMember($conn, $member_data);

    if ($result['status']) {
        $message = $result['message'];
        $message_type = 'success';
        $_POST = [];
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
                    <h1 class="page-title">Add Member</h1>
                    <p class="page-subtitle">Register a new member into the system</p>
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
                <a href="list.php" class="alert-link">View Members →</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Add Member Form Card -->
            <div class="dashboard-card">
                <div class="card-header-custom">
                    <div>
                        <h3 class="card-title">Member Registration</h3>
                        <p class="card-subtitle">Enter the member's personal details below</p>
                    </div>
                <div class="card-body-custom">
                    <form method="POST" action="" class="member-form" id="addMemberForm">

                        <div class="form-section">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="full_name" class="form-label">Full Name <span class="required">*</span></label>
                                    <input type="text" name="full_name" id="full_name"
                                           class="form-control" placeholder="e.g., Ahmad bin Abdullah"
                                           value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>"
                                           required>
                                </div>

                                <div class="form-group">
                                    <label for="ic_number" class="form-label">IC Number <span class="required">*</span></label>
                                    <input type="text" name="ic_number" id="ic_number"
                                           class="form-control" placeholder="e.g., 900101-01-1234"
                                           value="<?php echo isset($_POST['ic_number']) ? htmlspecialchars($_POST['ic_number']) : ''; ?>"
                                           required>
                                </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="village" class="form-label">Village <span class="required">*</span></label>
                                    <input type="text" name="village" id="village"
                                           class="form-control" placeholder="e.g., Kampung Baru"
                                           value="<?php echo isset($_POST['village']) ? htmlspecialchars($_POST['village']) : ''; ?>"
                                           required>
                                </div>

                                <div class="form-group">
                                    <label for="phone_no" class="form-label">Phone Number</label>
                                    <input type="text" name="phone_no" id="phone_no"
                                           class="form-control" placeholder="e.g., 012-3456789"
                                           value="<?php echo isset($_POST['phone_no']) ? htmlspecialchars($_POST['phone_no']) : ''; ?>">
                                </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="status" class="form-label">Status</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="Active" <?php echo (isset($_POST['status']) && $_POST['status'] === 'Active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="Inactive" <?php echo (isset($_POST['status']) && $_POST['status'] === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <a href="list.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Add Member</button>
                        </div>
                    </form>
                </div>
        </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
