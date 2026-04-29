<?php
/**
 * KEBANA Management System - View Member
 * File: modules/members/view.php
 *
 * Display detailed member profile information
 */

$page_title = 'View Member';
$css_path = '../../src/css/members.css';

require_once '../../includes/header.php';
require_once '../../includes/members_helper.php';

// Get member ID from URL
$member_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($member_id <= 0) {
    header('Location: list.php');
    exit;
}

// Get member details
$member = getMemberById($conn, $member_id);

if (empty($member)) {
    header('Location: list.php');
    exit;
}
?>

<div class="members-container">
    <!-- Page Header Section -->
    <section class="page-header-section">
        <div class="container-xl">
            <div class="page-header-content">
                <div class="page-header-text">
                    <h1 class="page-title"><?php echo htmlspecialchars($member['full_name']); ?></h1>
                    <p class="page-subtitle">Member ID: #<?php echo str_pad($member['member_id'], 4, '0', STR_PAD_LEFT); ?></p>
                </div>
                <div class="page-header-action">
                    <a href="edit.php?id=<?php echo $member['member_id']; ?>" class="btn btn-primary">Edit Member</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="main-content-area">
        <div class="container-xl">
            <!-- Member Profile Cards -->
            <div class="member-profile-grid">

                <!-- Personal Information Card -->
                <div class="profile-card">
                    <div class="card-header">
                        <h2 class="card-title">Personal Information</h2>
                    </div>
                    <div class="card-body">
                        <div class="detail-row">
                            <span class="detail-label">Full Name</span>
                            <span class="detail-value"><?php echo htmlspecialchars($member['full_name']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">IC Number</span>
                            <span class="detail-value"><?php echo htmlspecialchars($member['ic_number']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Phone</span>
                            <span class="detail-value"><?php echo htmlspecialchars($member['phone_no'] ?? 'Not provided'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Location Card -->
                <div class="profile-card">
                    <div class="card-header">
                        <h2 class="card-title">Location</h2>
                    </div>
                    <div class="card-body">
                        <div class="detail-row">
                            <span class="detail-label">Village</span>
                            <span class="detail-value"><?php echo htmlspecialchars($member['village']); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Status Card -->
                <div class="profile-card">
                    <div class="card-header">
                        <h2 class="card-title">Account Status</h2>
                    </div>
                    <div class="card-body">
                        <div class="detail-row">
                            <span class="detail-label">Status</span>
                            <span class="detail-value">
                                <span class="badge badge-<?php echo strtolower($member['status']) === 'active' ? 'success' : 'warning'; ?>">
                                    <?php echo htmlspecialchars($member['status']); ?>
                                </span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Member Since</span>
                            <span class="detail-value"><?php echo date('M d, Y', strtotime($member['created_at'])); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Last Updated</span>
                            <span class="detail-value"><?php echo date('M d, Y at H:i', strtotime($member['updated_at'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="list.php" class="btn btn-secondary">Back to List</a>
                <a href="edit.php?id=<?php echo $member['member_id']; ?>" class="btn btn-primary">Edit Member</a>
                <a href="list.php?delete=<?php echo $member['member_id']; ?>" class="btn btn-danger">Delete Member</a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>

