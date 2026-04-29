<?php
/**
 * KEBANA Management System - Dashboard
 * File: src/php/index.php
 * 
 * Main dashboard page - requires authentication
 */

$page_title = 'Dashboard';
$css_path = '../css/dashboard.css';

require_once '../../includes/header.php';
require_once '../../includes/members_helper.php';

// Get dashboard statistics
$total_members = getMemberCount($conn);

require_once '../../includes/dashboard_helper.php';
$upcoming_events = getUpcomingEventsCount($conn);
$pending_documents = getPendingDocumentsCount($conn);
$fund_balance = getFundBalance($conn);


?>

<div class="dashboard-container">
    <!-- Page Header Section -->
    <section class="page-header-section">
        <div class="container-xl">
            <div class="page-header-content">
                <div class="page-header-text">
                    <h1 class="page-title">Welcome back, <?php echo htmlspecialchars($username); ?></h1>
                    <p class="page-subtitle">Here's an overview of your organization's activities and performance metrics.</p>
                </div>
                <div class="page-header-status">
                    <div class="status-item">
                        <span class="status-label">System Status</span>
                        <span class="badge badge-success">Active</span>
                    </div>
                    <div class="status-item">
                        <span class="status-label">Last Updated</span>
                        <span class="status-time"><?php echo date('M d, Y • H:i'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="main-content-area">
        <div class="container-xl">
            <!-- KPI Cards Section -->
            <section class="kpi-section">
                <div class="section-header">
                    <h2 class="section-title">Key Performance Indicators</h2>
                </div>
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
                            <span class="kpi-change neutral">Total registered</span>
                        </div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-icon" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                        </div>
                        <div class="kpi-content">
                            <p class="kpi-label">Upcoming Events</p>
                            <h3 class="kpi-value"><?php echo number_format($upcoming_events); ?></h3>
                            <span class="kpi-change neutral">All scheduled</span>
                        </div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-icon" style="background: linear-gradient(135deg, #198754 0%, #157347 100%);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                                <polyline points="13 2 13 9 20 9"></polyline>
                            </svg>
                        </div>
                        <div class="kpi-content">
                            <p class="kpi-label">Pending Documents</p>
                            <h3 class="kpi-value"><?php echo number_format($pending_documents); ?></h3>
                            <span class="kpi-change warning">Requires review</span>
                        </div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-icon" style="background: linear-gradient(135deg, #ff9800 0%, #e68900 100%);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                        </div>
                        <div class="kpi-content">
                            <p class="kpi-label">Fund Balance</p>
                            <h3 class="kpi-value"><?php echo formatFundBalance($fund_balance); ?></h3>
                            <span class="kpi-change positive">Available</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Main Dashboard Grid -->
            <section class="dashboard-grid">
                <!-- Left Column -->
                <div class="dashboard-column-main">
                    <!-- Operational Summary -->
                    <div class="dashboard-card">
                        <div class="card-header-custom">
                            <div>
                                <h3 class="card-title">Operational Summary</h3>
                                <p class="card-subtitle">Key metrics for program delivery and community support</p>
                            </div>
                            <a href="#" class="btn-link">View Full Report →</a>
                        </div>
                        <div class="card-body-custom">
                            <div class="metrics-grid">
                                <div class="metric-item">
                                    <span class="metric-icon">👥</span>
                                    <p class="metric-label">Community Visits</p>
                                    <h4 class="metric-value">48</h4>
                                    <span class="metric-trend positive">+12% from last month</span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-icon">📊</span>
                                    <p class="metric-label">Active Projects</p>
                                    <h4 class="metric-value">7</h4>
                                    <span class="metric-trend neutral">Stable progress</span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-icon">📄</span>
                                    <p class="metric-label">Total Documents</p>
                                    <h4 class="metric-value">62</h4>
                                    <span class="metric-trend warning">5 pending approval</span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-icon">⏳</span>
                                    <p class="metric-label">Pending Approvals</p>
                                    <h4 class="metric-value">5</h4>
                                    <span class="metric-trend alert">Requires immediate action</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Members & Finance Row -->
                    <div class="dashboard-row">
                        <div class="dashboard-card">
                            <div class="card-header-custom">
                                <div>
                                    <h3 class="card-title">Members Overview</h3>
                                    <p class="card-subtitle">Active participation metrics</p>
                                </div>
                            </div>
                            <div class="card-body-custom">
                                <div class="progress-section">
                                    <div class="progress-header">
                                        <span class="progress-label">Active Participation Rate</span>
                                        <span class="progress-value">84%</span>
                                    </div>
                                    <div class="progress-bar-custom" role="progressbar" style="width: 84%;" aria-valuenow="84" aria-valuemin="0" aria-valuemax="100"></div>
                                    <p class="progress-note">Strong engagement this quarter across all programs</p>
                                </div>
                            </div>
                        </div>

                        <div class="dashboard-card">
                            <div class="card-header-custom">
                                <div>
                                    <h3 class="card-title">Finance Snapshot</h3>
                                    <p class="card-subtitle">Budget status overview</p>
                                </div>
                            </div>
                            <div class="card-body-custom">
                                <div class="finance-box">
                                    <div class="finance-item">
                                        <span class="finance-label">Available Funds</span>
                                        <p class="finance-value"><?php echo 'RM ' . number_format($fund_balance, 2); ?></p>
                                    </div>
                                    <div class="finance-item">
                                        <span class="finance-label">Status</span>
                                        <p class="finance-badge">Healthy</p>
                                    </div>
                                </div>
                                <p class="finance-note">Monitor planned disbursements and approvals</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column (Sidebar) -->
                <div class="dashboard-column-sidebar">
                    <!-- Recent Activity -->
                    <div class="dashboard-card">
                        <div class="card-header-custom">
                            <h3 class="card-title">Recent Activity</h3>
                        </div>
                        <div class="card-body-custom">
                            <div class="activity-timeline">
                                <div class="activity-item">
                                    <span class="activity-dot"></span>
                                    <p class="activity-text">New member registration approved</p>
                                    <span class="activity-time">2 hours ago</span>
                                </div>
                                <div class="activity-item">
                                    <span class="activity-dot"></span>
                                    <p class="activity-text">March event minutes uploaded</p>
                                    <span class="activity-time">5 hours ago</span>
                                </div>
                                <div class="activity-item">
                                    <span class="activity-dot"></span>
                                    <p class="activity-text">Proposal draft shared with committee</p>
                                    <span class="activity-time">1 day ago</span>
                                </div>
                                <div class="activity-item">
                                    <span class="activity-dot"></span>
                                    <p class="activity-text">Finance request submitted for review</p>
                                    <span class="activity-time">2 days ago</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>

