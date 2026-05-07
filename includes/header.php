<?php
/**
 * KEBANA Management System - Header Component with Sidebar
 * File: includes/header.php
 * 
 * HTML head section and sidebar navigation
 * Include this file at the top of your page
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/dbconnect.php';

// Calculate CSS base path relative to the calling page
if (!isset($css_base_path)) {
    // Use relative path calculation from the includes directory to src/css
    $script = $_SERVER['SCRIPT_FILENAME'];
    $includes_dir = __DIR__;
    
    // Normalize slashes for Windows compatibility
    $script_dir_normalized = str_replace('\\', '/', dirname($script));
    $base_dir_normalized = str_replace('\\', '/', dirname($includes_dir));
    
    // Calculate relative path from the script's directory to kebana-digital root
    // Use case-insensitive replace for Windows drive letters
    $rel_path = str_ireplace($base_dir_normalized, '', $script_dir_normalized);
    $up_count = substr_count($rel_path, '/');
    
    // Base path for general links
    $base_path = $up_count > 0 ? str_repeat('../', $up_count) : './';
    
    // The path should be enough '../' to get from script location to kebana-digital root,
    // then into src/css/
    $css_base_path = $base_path . 'src/css/';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="KEBANA Digital Management System for NGO Administration">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - KEBANA' : 'KEBANA Management System'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GtvK6y1nZGn9L9k1iKMdDoV6nupN9zL+ZSLR0sZOsY/hyx3D+0DGz1h/6URyhu2M" crossorigin="anonymous">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVJkEZSMUkrQ6usKICqvgiA1jz9tBXsdNGWWXFsX8Z5K8G9N1e6zqLyX9Di+G/CNm+rRnSpoC4A==" crossorigin="anonymous" referrerpolicy="no-referrer">
    
    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="<?php echo $css_base_path; ?>sidebar.css">
    
    <!-- Custom CSS -->
<link rel="stylesheet" href="<?php echo isset($css_path) ? htmlspecialchars($css_path) : $css_base_path . 'dashboard.css'; ?>">
        <?php if (isset($extra_css)): ?>
        <!-- extra_css (page-scoped) loaded after sidebar.
             Use a cache-busting querystring to avoid stale/broken CSS during dev. -->
        <link rel="stylesheet" href="<?php echo htmlspecialchars($extra_css); ?>?v=<?php echo urlencode(date('YmdHis')); ?>">
    <?php endif; ?>



    
    <style>
        :root {
            --primary-color: #0d6efd;
            --primary-dark: #0b5ed7;
        }
    </style>
</head>
<body>
    <!-- Sidebar Toggle Button -->
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar" title="Menu">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
        </svg>
    </button>

    <!-- Sidebar Overlay (click to close) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar Navigation -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2>KEBANA</h2>
            <button class="sidebar-close" id="sidebarClose" aria-label="Close sidebar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <nav>
            <ul class="sidebar-menu">
                <li>
                    <a href="<?php echo $base_path; ?>src/php/index.php" class="sidebar-menu-item active">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="12 3 20 7.5 20 16.5 12 21 4 16.5 4 7.5 12 3"></polyline>
                            <line x1="12" y1="12" x2="20" y2="7.5"></line>
                            <line x1="12" y1="12" x2="12" y2="21"></line>
                            <line x1="12" y1="12" x2="4" y2="7.5"></line>
                        </svg>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li>
                    <a href="#" class="sidebar-menu-item has-submenu">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span>Members</span>
                    </a>
                    <ul class="sidebar-submenu">
                        <li><a href="<?php echo $base_path; ?>modules/members/list.php" class="sidebar-submenu-item">Member List</a></li>
                        <li><a href="<?php echo $base_path; ?>modules/members/add.php" class="sidebar-submenu-item">Add Member</a></li>
                        <li><a href="<?php echo $base_path; ?>modules/members/report.php" class="sidebar-submenu-item">Reports</a></li>
                    </ul>
                </li>

                <li>
                    <a href="#" class="sidebar-menu-item has-submenu">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <span>Events</span>
                    </a>
                    <ul class="sidebar-submenu">
                        <li><a href="<?php echo $base_path; ?>modules/events/list.php" class="sidebar-submenu-item">Event List</a></li>
                        <li><a href="<?php echo $base_path; ?>modules/events/create.php" class="sidebar-submenu-item">Create Event</a></li>
                        <li><a href="<?php echo $base_path; ?>modules/events/attendance.php" class="sidebar-submenu-item">Attendance</a></li>
                    </ul>
                </li>

                <li>
                    <a href="#" class="sidebar-menu-item has-submenu">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                            <polyline points="13 2 13 9 20 9"></polyline>
                        </svg>
                        <span>Documents</span>
                    </a>
                    <ul class="sidebar-submenu">
                        <li><a href="#" class="sidebar-submenu-item">Proposals</a></li>
                        <li><a href="#" class="sidebar-submenu-item">Minutes</a></li>
                        <li><a href="#" class="sidebar-submenu-item">Reports</a></li>
                    </ul>
                </li>

                <li>
                    <a href="#" class="sidebar-menu-item has-submenu">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>Finance</span>
                    </a>
                    <ul class="sidebar-submenu">
<li><a href="<?php echo $base_path; ?>modules/finance/dashboard.php" class="sidebar-submenu-item">Dashboard</a></li>

                        <li><a href="#" class="sidebar-submenu-item">Budget</a></li>
                        <li><a href="#" class="sidebar-submenu-item">Reports</a></li>
                    </ul>
                </li>

                <li>
                    <a href="#" class="sidebar-menu-item has-submenu">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="1"></circle>
                            <path d="M12 1v6m6.66 1.34l-4.24 4.24M19 12h6m-1.34 6.66l-4.24-4.24M12 19v6m-6.66-1.34l4.24-4.24M5 12H-1m1.34-6.66l4.24 4.24"></path>
                        </svg>
                        <span>Projects</span>
                    </a>
                    <ul class="sidebar-submenu">
                        <li><a href="#" class="sidebar-submenu-item">Active Projects</a></li>
                        <li><a href="#" class="sidebar-submenu-item">New Project</a></li>
                        <li><a href="#" class="sidebar-submenu-item">Reports</a></li>
                    </ul>
                </li>

                <?php if (isAdmin()): ?>
                <li>
                    <hr style="margin: 1rem 0; border-color: rgba(255, 255, 255, 0.1);">
                </li>
                <li>
                    <a href="#" class="sidebar-menu-item has-submenu">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="1"></circle>
                            <path d="M12 1v6m6.66 1.34l-4.24 4.24M19 12h6m-1.34 6.66l-4.24-4.24M12 19v6m-6.66-1.34l4.24-4.24M5 12H-1m1.34-6.66l4.24 4.24"></path>
                        </svg>
                        <span>Administration</span>
                    </a>
                    <ul class="sidebar-submenu">
                        <li><a href="#" class="sidebar-submenu-item">User Management</a></li>
                        <li><a href="#" class="sidebar-submenu-item">System Settings</a></li>
                        <li><a href="#" class="sidebar-submenu-item">Audit Logs</a></li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-user-avatar"><?php echo strtoupper(substr($username, 0, 1)); ?></div>
                <div class="sidebar-user-info">
                    <p class="sidebar-user-name"><?php echo htmlspecialchars($username); ?></p>
                    <p class="sidebar-user-role"><?php echo ucfirst($role); ?></p>
                </div>
            </div>
            <a href="<?php echo $base_path; ?>modules/auth/logout.php" style="display: block; padding: 0.75rem; color: rgba(255, 255, 255, 0.85); text-decoration: none; text-align: center; font-size: 0.9rem; margin-top: 0.75rem; border-top: 1px solid rgba(255, 255, 255, 0.1); transition: all 0.3s ease;" onmouseover="this.style.color='white'; this.style.background='rgba(255, 255, 255, 0.1)';" onmouseout="this.style.color='rgba(255, 255, 255, 0.85)'; this.style.background='transparent';">
                Logout
            </a>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="main-content" id="mainContent">

    <script>

        // Sidebar toggle functionality
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarClose = document.getElementById('sidebarClose');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const mainContent = document.getElementById('mainContent');

        let sidebarLocked = false; // Track if sidebar is locked open

        // Toggle sidebar on button click
        sidebarToggle.addEventListener('click', () => {
            sidebarLocked = !sidebarLocked;
            updateSidebarState();
        });

        // Close sidebar on close button click
        sidebarClose.addEventListener('click', () => {
            sidebarLocked = false;
            updateSidebarState();
        });

        // Close sidebar when clicking overlay
        sidebarOverlay.addEventListener('click', () => {
            sidebarLocked = false;
            updateSidebarState();
        });

        // Show sidebar on hover when not locked
        sidebar.addEventListener('mouseenter', () => {
            if (!sidebarLocked) {
                sidebar.classList.add('active');
                sidebarOverlay.classList.add('active');
            }
        });

        // Hide sidebar on mouse leave when not locked
        sidebar.addEventListener('mouseleave', () => {
            if (!sidebarLocked) {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
            }
        });

        // Show sidebar on hover of toggle button when not locked
        sidebarToggle.addEventListener('mouseenter', () => {
            if (!sidebarLocked) {
                sidebar.classList.add('active');
                sidebarOverlay.classList.add('active');
            }
        });

        // Submenu toggle
        document.querySelectorAll('.sidebar-menu-item.has-submenu').forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                item.classList.toggle('expanded');
            });
        });

        // Update sidebar state on page load
        updateSidebarState();

        // Update sidebar state
        function updateSidebarState() {
            if (sidebarLocked) {
                sidebar.classList.add('active');
                sidebarOverlay.classList.add('active');
            } else {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
            }
        }

        // Close sidebar when clicking on a menu item (except submenus)
        document.querySelectorAll('.sidebar-submenu-item').forEach(item => {
            item.addEventListener('click', (e) => {
                if (!sidebarLocked) {
                    sidebar.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                }
            });
        });
    </script>


