<?php
/**
 * KEBANA Digital Management System - Header Component (MYDS Inspired)
 * File: includes/header.php
 * 
 * Clean, sharp design based on MYDS without official government branding.
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/dbconnect.php';

$session_role_raw = $_SESSION['role'] ?? null;
$current_role = is_numeric($session_role_raw) ? (int)$session_role_raw : 0;
$current_cawangan_id = isset($_SESSION['cawangan_id']) && $_SESSION['cawangan_id'] !== null && $_SESSION['cawangan_id'] !== '' ? (int)$_SESSION['cawangan_id'] : null;
$username = $_SESSION['username'] ?? 'User';
$current_user_id = $_SESSION['user_id'] ?? 0;
$page_title = $page_title ?? '';

// Role Constants (Pusat)
$ROLE_SUPER_ADMIN = 888;
$ROLE_PRESIDEN = 1;
$ROLE_TIMBALAN_1 = 2;
$ROLE_TIMBALAN_2 = 3;
$ROLE_SETIAUSAHA_PUSAT = 4;
$ROLE_PEN_SETIAUSAHA_PUSAT = 5;
$ROLE_BENDAHARI_PUSAT = 6;
$ROLE_PEN_BENDAHARI_PUSAT = 7;

// Role Constants (Cawangan)
$ROLE_PENGERUSI = 11;
$ROLE_TIMB_PENGERUSI = 22;
$ROLE_SETIAUSAHA_CAWANGAN = 33;
$ROLE_PEN_SETIAUSAHA_CAWANGAN = 44;
$ROLE_BENDAHARI_CAWANGAN = 55;
$ROLE_PEN_BENDAHARI_CAWANGAN = 66;

$PUSAT_ROLES = [888, 1, 2, 3, 4, 5, 6, 7];
$CAWANGAN_ROLES = [11, 22, 33, 44, 55, 66];
$FINANCE_ROLES = [6, 7, 55, 66];

$is_known_role = in_array($current_role, $PUSAT_ROLES, true) || in_array($current_role, $CAWANGAN_ROLES, true);

// Visibility Flags
$can_view_members = true;
$can_view_events = true;
$can_view_documents = true;
$can_view_finance = ($is_known_role && (in_array($current_role, $FINANCE_ROLES, true) || in_array($current_role, [888, 1, 2, 3], true)));
$can_view_projects = in_array($current_role, $PUSAT_ROLES, true);

// Role Name Mapping
$role_names = [
    888 => 'Super Admin',
    1   => 'Presiden',
    2   => 'Timbalan Presiden 1',
    3   => 'Timbalan Presiden 2',
    4   => 'Setiausaha Pusat',
    5   => 'Penolong Setiausaha Pusat',
    6   => 'Bendahari Kehormat',
    7   => 'Penolong Bendahari Kehormat',
    11  => 'Pengerusi Cawangan',
    22  => 'Timb. Pengerusi Cawangan',
    33  => 'Setiausaha Cawangan',
    44  => 'Pen. Setiausaha Cawangan',
    55  => 'Bendahari Cawangan',
    66  => 'Pen. Bendahari Cawangan'
];

$role_name = $role_names[$current_role] ?? 'Ahli / Pengguna';

?>
<!doctype html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - KEBANA' : 'KEBANA'; ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo LOGO_ICON; ?>">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        kebana: {
                            blue: '#003366',      // MYDS Blue
                            yellow: '#FFCC00',    // MYDS Yellow
                            light: '#F8FAFC',
                            dark: '#0F172A',
                            accent: '#004A99'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-transition { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    </style>
</head>
<body class="h-full">
    <div class="min-h-full flex">
        <!-- Sidebar -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-[#0f172a] text-white transform -translate-x-full lg:translate-x-0 lg:static lg:inset-0 sidebar-transition flex flex-col shadow-2xl overflow-hidden border-r border-white/5">
            <!-- Subtle Gradient Accent -->
            <div class="absolute inset-0 bg-gradient-to-b from-kebana-blue/10 to-transparent pointer-events-none"></div>

            <div class="h-24 flex items-center px-8 relative z-10 border-b border-white/5">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 flex items-center justify-center">
                        <img src="<?php echo LOGO_ICON; ?>" alt="KEBANA Logo" class="w-full h-full object-contain">
                    </div>
                    <span class="text-2xl font-black tracking-tighter italic uppercase">KEBANA<span class="text-kebana-yellow">.</span></span>
                </div>
            </div>

            <div class="p-6 relative z-10 border-b border-white/5 bg-black/20">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-kebana-yellow text-kebana-blue flex items-center justify-center font-black text-lg">
                        <?php echo strtoupper(substr($username, 0, 1)); ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[10px] font-black text-white truncate uppercase tracking-tighter"><?php echo htmlspecialchars($username); ?></p>
                        <p class="text-[8px] text-slate-500 truncate font-black uppercase tracking-[0.2em]"><?php echo htmlspecialchars((string)$role_name); ?></p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 overflow-y-auto py-8 px-4 space-y-1 relative z-10">
                <!-- Dashboard Link -->
                <?php $is_dash = ($page_title == 'PAPARAN UTAMA'); ?>
                <a href="<?php echo $base_path; ?>dashboard" class="group flex items-center px-4 py-3.5 text-[10px] font-black uppercase tracking-[0.2em] transition-all <?php echo $is_dash ? 'bg-kebana-blue text-white shadow-xl shadow-kebana-blue/20' : 'text-slate-400 hover:text-white hover:bg-white/5'; ?>">
                    <i class="fa-solid fa-layer-group w-8 text-lg <?php echo $is_dash ? 'text-kebana-yellow' : 'text-slate-600 group-hover:text-kebana-yellow'; ?>"></i>
                    <span>Dashboard</span>
                </a>

                <div class="pt-10 pb-4 text-[9px] font-black text-slate-600 uppercase tracking-[0.4em] px-4">Modul Utama</div>
                
                <?php 
                $unreadChatCount = \App\Helpers\ChatHelper::getTotalUnreadCount($current_user_id);
                $nav_items = [
                    ['link' => 'members', 'icon' => 'fa-users-between-lines', 'label' => 'Pengurusan Ahli', 'visible' => $can_view_members],
                    ['link' => 'events', 'icon' => 'fa-calendar-check', 'label' => 'Aktiviti & Program', 'visible' => $can_view_events],
                    ['link' => 'finance', 'icon' => 'fa-wallet', 'label' => 'Laporan Kewangan', 'visible' => $can_view_finance],
                    ['link' => 'documents', 'icon' => 'fa-box-archive', 'label' => 'Pusat Arkib Fail', 'visible' => $can_view_documents],
                    ['link' => 'announcements', 'icon' => 'fa-bullhorn', 'label' => 'Pengurusan Hebahan', 'visible' => in_array($current_role, [888, 1, 4])],
                    ['link' => 'users', 'icon' => 'fa-user-gear', 'label' => 'Pengurusan Pengguna', 'visible' => ($current_role === 888)],
                    ['link' => 'cawangan', 'icon' => 'fa-building-shield', 'label' => 'Pengurusan Cawangan', 'visible' => ($current_role === 888)],
                    ['link' => 'chat', 'icon' => 'fa-comments', 'label' => 'Pusat Komunikasi', 'visible' => true, 'badge' => $unreadChatCount],
                ];

                foreach ($nav_items as $item): 
                    if (!$item['visible']) continue;
                    $isActive = (stripos($_SERVER['REQUEST_URI'], $item['link']) !== false);
                ?>
                <a href="<?php echo $base_path . $item['link']; ?>" class="group flex items-center px-4 py-3.5 text-[10px] font-black uppercase tracking-[0.2em] transition-all <?php echo $isActive ? 'bg-kebana-blue text-white shadow-xl shadow-kebana-blue/10' : 'text-slate-400 hover:text-white hover:bg-white/5'; ?>">
                    <i class="fa-solid <?php echo $item['icon']; ?> w-8 text-lg <?php echo $isActive ? 'text-kebana-yellow' : 'text-slate-600 group-hover:text-kebana-yellow'; ?>"></i>
                    <span class="flex-1"><?php echo $item['label']; ?></span>
                    <?php if (isset($item['badge']) && $item['badge'] > 0): ?>
                        <span class="bg-red-500 text-white text-[8px] px-2 py-0.5 rounded-full font-black animate-pulse"><?php echo $item['badge']; ?></span>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>

                <div class="pt-10 pb-4 text-[9px] font-black text-slate-600 uppercase tracking-[0.4em] px-4">Sistem</div>
                
                <a href="<?php echo $base_path; ?>logout" class="group flex items-center px-4 py-3.5 text-[10px] font-black uppercase tracking-[0.2em] text-red-400/60 hover:text-red-400 hover:bg-red-500/5 transition-all">
                    <i class="fa-solid fa-power-off w-8 text-lg text-red-500/20 group-hover:text-red-500"></i>
                    <span>Log Keluar</span>
                </a>
            </nav>


        </aside>

        <!-- Overlay -->
        <div id="sidebarOverlay" class="fixed inset-0 z-40 bg-slate-900/50 hidden lg:hidden"></div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-white">
            <!-- Topbar -->
            <header class="h-16 flex items-center justify-between px-8 border-b border-slate-100 sticky top-0 bg-white/80 backdrop-blur-md z-30">
                <div class="flex items-center">
                    <button id="mobileToggle" class="lg:hidden p-2 -ml-2 text-slate-600 hover:bg-slate-50">
                        <i class="fa-solid fa-bars-staggered text-xl"></i>
                    </button>
                    <div class="h-8 w-8 lg:hidden ml-2 flex items-center justify-center">
                        <img src="<?php echo LOGO_ICON; ?>" alt="K" class="w-full h-full object-contain">
                    </div>
                </div>

                <div class="flex items-center space-x-6">
                    <a href="/kebana-digital/chat" class="relative p-2 text-slate-400 hover:text-kebana-blue transition-colors focus:outline-none">
                        <i class="fa-regular fa-comments text-xl"></i>
                        <?php if (isset($unreadChatCount) && $unreadChatCount > 0): ?>
                            <span class="absolute top-1.5 right-1.5 h-4 w-4 bg-red-500 ring-2 ring-white rounded-full text-[10px] text-white flex items-center justify-center font-bold animate-bounce"><?php echo $unreadChatCount; ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="hidden sm:flex items-center text-[10px] font-black text-slate-400 uppercase tracking-widest space-x-2">
                        <i class="fa-solid fa-clock text-kebana-blue/30"></i>
                        <span><?php echo date('d F Y • h:i A'); ?></span>
                    </div>
                    <div class="h-6 w-[1px] bg-slate-100 hidden sm:block"></div>
                    <div class="relative inline-block text-left" id="notificationDropdown">
                        <button id="notificationBtn" class="relative p-2 text-slate-400 hover:text-kebana-blue transition-colors focus:outline-none">
                            <i class="fa-regular fa-bell text-xl"></i>
                            <span id="notificationBadge" class="absolute top-1.5 right-1.5 hidden h-4 w-4 bg-red-500 ring-2 ring-white rounded-full text-[10px] text-white flex items-center justify-center font-bold">0</span>
                        </button>
                        
                        <div id="notificationMenu" class="absolute right-0 mt-3 w-80 origin-top-right bg-white shadow-2xl ring-1 ring-slate-200 focus:outline-none hidden z-50 rounded-none border-t-4 border-kebana-blue">
                            <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                                <h3 class="text-xs font-black text-kebana-blue uppercase tracking-widest">Notifications</h3>
                                <div class="flex items-center space-x-3">
                                    <button onclick="markAllRead()" class="text-[10px] font-black text-slate-400 hover:text-kebana-blue uppercase transition-colors">Mark all read</button>
                                    <span class="text-slate-200">|</span>
                                    <button onclick="clearAllNotifications()" class="text-[10px] font-black text-red-400 hover:text-red-600 uppercase transition-colors">Clear All</button>
                                </div>
                            </div>
                            <div id="notificationList" class="max-h-96 overflow-y-auto">
                                <div class="p-8 text-center text-slate-300">
                                    <i class="fa-regular fa-bell-slash text-2xl mb-2 block"></i>
                                    <p class="text-[10px] font-bold uppercase">Checking for updates...</p>
                                </div>
                            </div>
                            <div class="p-3 border-t border-slate-100 text-center">
                                <a href="/kebana-digital/notifications" class="text-[10px] font-black text-kebana-blue uppercase tracking-tighter hover:underline">Papar Semua Notifikasi</a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6 lg:p-12">
                <div class="max-w-7xl mx-auto space-y-10">
                    <!-- Title Bar -->
                    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 pb-8 border-b-2 border-slate-50">
                        <div>
                            <h1 class="text-4xl font-black text-kebana-blue tracking-tighter uppercase italic"><?php echo htmlspecialchars($page_title ?? ''); ?></h1>
                            <p class="text-sm text-slate-400 mt-2 font-bold uppercase tracking-tight">Kebana Digital Management System</p>
                        </div>
                    </div>
