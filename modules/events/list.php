<?php
/**
 * KEBANA Digital Management System - Events List (MYDS Inspired)
 * File: modules/events/list.php
 */

use App\Core\Database;
use App\Helpers\EventsHelper;
use App\Helpers\DashboardHelper;

require_once APP_ROOT . '/includes/header.php';

$db = Database::getInstance()->getConnection();

// Workflow actions
$message = '';
$message_type = '';

if (isset($_GET['action']) && isset($_GET['event_id'])) {
    $event_id = (int)$_GET['event_id'];
    $action = $_GET['action'];
    $success = false;

    if ($action === 'submit' && hasRole([888, 1, 4, 33, 6, 7])) {
        $success = EventsHelper::submitEvent($event_id);
        $message = $success ? 'Projek berjaya dihantar untuk semakan.' : 'Gagal menghantar projek.';
    } elseif ($action === 'submit_to_branch' && hasRole(33)) {
        $success = EventsHelper::submitToBranch($event_id);
        $message = $success ? 'Kertas kerja berjaya dihantar kepada Pengerusi Cawangan.' : 'Gagal menghantar kertas kerja.';
    } elseif ($action === 'approve' && hasRole([1, 888])) {
        $success = EventsHelper::approveEvent($event_id);
        $message = $success ? 'Projek telah diluluskan.' : 'Gagal meluluskan projek.';
    } elseif ($action === 'reject' && hasRole([1, 888])) {
        $success = EventsHelper::rejectEvent($event_id);
        $message = $success ? 'Projek telah ditolak.' : 'Gagal menolak projek.';
    }

    $message_type = $success ? 'success' : 'error';
}

if (isset($_GET['delete'])) {
    $event_id = (int)$_GET['delete'];
    if (EventsHelper::deleteEvent($event_id)) {
        $message = 'Acara telah berjaya dipadam.';
        $message_type = 'success';
    } else {
        $message = 'Gagal memadam acara.';
        $message_type = 'error';
    }
}

// Fetch events based on role
$current_role = (int)($_SESSION['role'] ?? 0);
$current_user_id = (int)($_SESSION['user_id'] ?? 0);
$current_cawangan_id = isset($_SESSION['cawangan_id']) ? (int)$_SESSION['cawangan_id'] : null;

$view_mode = 'all';
if (in_array($current_role, [33, 55, 66])) {
    $view_mode = 'cawangan_only';
}

$all_events = EventsHelper::getAllEvents($view_mode, $current_user_id, $current_cawangan_id);

// Search & Filter
$search = trim($_GET['search'] ?? '');
$filter_status = trim($_GET['status'] ?? '');

if ($search) {
    $all_events = array_filter($all_events, function($e) use ($search) {
        return stripos($e['event_title'], $search) !== false || stripos($e['venue'], $search) !== false;
    });
}

if ($filter_status) {
    $all_events = array_filter($all_events, function($e) use ($filter_status) {
        return strcasecmp($e['status'] ?? '', $filter_status) === 0;
    });
}

// Organize into Hierarchy
$master_events = [];
$orphan_subs = [];

foreach ($all_events as $event) {
    if (($event['event_level'] ?? 'MASTER') === 'MASTER') {
        $event['sub_events'] = [];
        $master_events[$event['event_id']] = $event;
    } else {
        $orphan_subs[] = $event;
    }
}

foreach ($orphan_subs as $sub) {
    $parent_id = $sub['parent_event_id'] ?? 0;
    if (isset($master_events[$parent_id])) {
        $master_events[$parent_id]['sub_events'][] = $sub;
    } else {
        // If parent not found, treat as top-level but keep level tag
        $sub['sub_events'] = [];
        $master_events['orphan_' . $sub['event_id']] = $sub;
    }
}

// KPI Stats
$total_events = count($all_events);
$upcoming = DashboardHelper::getUpcomingEventsCount();
$past = DashboardHelper::getPastEventsCount();

$page_title = 'PENGURUSAN ACARA';
?>

<div class="space-y-12">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 border-t-8 border-kebana-blue shadow-sm">
        <div>
            <h2 class="text-2xl font-black text-kebana-blue uppercase tracking-tight italic">Senarai Aktiviti & Program</h2>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Kalendar dan Pengurusan Program Organisasi.</p>
        </div>
        <?php if (hasRole([888, 1, 4, 33])): ?>
        <a href="<?= URL_ROOT ?>/events/create" class="bg-kebana-blue text-white px-10 py-4 text-xs font-black uppercase tracking-[0.2em] hover:bg-kebana-accent transition-all shadow-xl inline-flex items-center">
            <i class="fa-solid fa-calendar-plus mr-4 text-lg"></i>
            DAFTAR ACARA BARU
        </a>
        <?php endif; ?>
    </div>

    <?php if ($message): ?>
    <div class="p-6 <?php echo $message_type === 'success' ? 'bg-green-50 text-green-700 border-l-4 border-green-600' : 'bg-red-50 text-red-700 border-l-4 border-red-600'; ?> font-bold text-xs uppercase tracking-widest animate-pulse">
        <?php echo $message; ?>
    </div>
    <?php endif; ?>

    <!-- KPI Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-0 border border-slate-100 bg-white">
        <div class="p-8 border-r border-slate-50 flex flex-col justify-center hover:bg-slate-50 transition-colors group">
            <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">JUMLAH ACARA</p>
            <p class="text-3xl font-black text-kebana-blue mt-2"><?php echo number_format($total_events); ?></p>
        </div>
        <div class="p-8 border-r border-slate-50 flex flex-col justify-center hover:bg-slate-50 transition-colors group">
            <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest text-green-600">AKAN DATANG</p>
            <p class="text-3xl font-black text-green-600 mt-2"><?php echo number_format($upcoming); ?></p>
        </div>
        <div class="p-8 flex flex-col justify-center hover:bg-slate-50 transition-colors group border-b-4 border-kebana-yellow">
            <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest text-slate-400">ACARA LEPAS</p>
            <p class="text-3xl font-black text-slate-400 mt-2"><?php echo number_format($past); ?></p>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white p-8 border border-slate-100 shadow-sm">
        <form method="GET" class="flex flex-col md:flex-row gap-6">
            <?php if ($filter_status): ?>
            <input type="hidden" name="status" value="<?php echo htmlspecialchars($filter_status); ?>">
            <?php endif; ?>
            <div class="flex-1 relative">
                <i class="fa-solid fa-magnifying-glass absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       class="w-full pl-14 pr-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold uppercase transition-all"
                       placeholder="Cari mengikut tajuk atau lokasi acara...">
            </div>
            <button type="submit" class="bg-kebana-dark text-white px-10 py-4 text-xs font-black uppercase tracking-widest hover:bg-black transition-all shadow-lg">
                TAPIS DATA
            </button>
            <?php if ($search): ?>
            <a href="<?= URL_ROOT ?>/events" class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center hover:text-red-500">
                KOSONGKAN
            </a>
            <?php endif; ?>
        </form>
    </div>

    <div id="live-search-results" class="space-y-12">

    <!-- Events Table -->
    <div class="bg-white border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/50">
                        <th class="px-8 py-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Tahap</th>
                        <th class="px-8 py-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Maklumat Acara</th>
                        <th class="px-8 py-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Tarikh & Lokasi</th>
                        <th class="px-8 py-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                        <th class="px-8 py-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if (empty($master_events)): ?>
                    <tr>
                        <td colspan="5" class="px-8 py-20 text-center text-[10px] font-black text-slate-300 uppercase tracking-[0.3em]">
                            Tiada rekod acara dijumpai.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($master_events as $event): 
                            $status = !empty($event['status']) && $event['status'] !== '0' ? $event['status'] : 'Draft';
                            $status_class = 'bg-slate-100 text-slate-400';
                            
                            $check_status = strtoupper($status);
                            if ($check_status === 'APPROVED') $status_class = 'bg-green-100 text-green-700';
                            elseif ($check_status === 'SUBMITTED') $status_class = 'bg-amber-100 text-amber-700';
                            elseif ($check_status === 'REJECTED') $status_class = 'bg-red-100 text-red-700';
                            elseif ($check_status === 'PENDING BRANCH APPROVAL') $status_class = 'bg-blue-100 text-blue-700';
                            elseif ($check_status === 'BRANCH APPROVED') $status_class = 'bg-emerald-100 text-emerald-700';
                            
                            $level = $event['event_level'] ?? 'MASTER';
                            $is_master = ($level === 'MASTER');
                            $level_class = $is_master ? 'bg-kebana-blue text-white shadow-lg shadow-kebana-blue/20' : 'bg-slate-200 text-slate-600';
                        ?>
                        <!-- Master / Main Row -->
                        <tr class="hover:bg-slate-50/50 transition-colors group <?php echo $is_master ? 'bg-white' : 'bg-slate-50/30'; ?>">
                            <td class="px-8 py-6">
                                <span class="px-3 py-1 text-[8px] font-black uppercase tracking-widest <?php echo $level_class; ?>">
                                    <?php echo $level; ?>
                                </span>
                            </td>
                            <td class="px-8 py-6">
                                <p class="text-xs font-black text-kebana-blue uppercase tracking-tight"><?php echo htmlspecialchars($event['event_title']); ?></p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase mt-1 italic"><?php echo htmlspecialchars($event['cawangan_name'] ?? 'Pusat'); ?></p>
                            </td>
                            <td class="px-8 py-6">
                                <p class="text-[10px] font-black text-slate-600 uppercase tracking-widest flex items-center">
                                    <i class="fa-solid fa-calendar-day mr-2 text-kebana-blue/30"></i>
                                    <?php echo (!empty($event['event_date']) && $event['event_date'] !== '0000-00-00') ? date('d M Y', strtotime($event['event_date'])) : 'TBA'; ?>
                                </p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase mt-1 truncate max-w-[200px]">
                                    <i class="fa-solid fa-location-dot mr-2 text-kebana-blue/30"></i>
                                    <?php 
                                        $loc_display = (!empty($event['venue']) && $event['venue'] !== '0') ? htmlspecialchars($event['venue']) : 'Lokasi TBA';
                                        if (!empty($event['kawasan']) && $loc_display !== 'Lokasi TBA') {
                                            $loc_display .= ' (' . htmlspecialchars($event['kawasan']) . ')';
                                        }
                                        echo $loc_display;
                                    ?>
                                </p>
                            </td>
                            <td class="px-8 py-6">
                                <span class="px-3 py-1.5 text-[9px] font-black uppercase tracking-widest <?php echo $status_class; ?>">
                                    <?php echo $status; ?>
                                </span>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex justify-end gap-2 flex-wrap">
                                    <a href="<?= URL_ROOT ?>/events/view/<?php echo $event['event_id']; ?>" 
                                       class="inline-flex items-center gap-2 px-4 py-2 text-[10px] font-black text-white bg-slate-800 hover:bg-black uppercase tracking-widest transition-all shadow-sm">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                        Papar
                                    </a>
                                    
                                    <a href="<?= URL_ROOT ?>/events/attendance?event_id=<?php echo $event['event_id']; ?>" 
                                       class="inline-flex items-center gap-2 px-4 py-2 text-[10px] font-black text-white bg-kebana-blue hover:bg-kebana-accent uppercase tracking-widest transition-all shadow-sm">
                                        <i class="fa-solid fa-users-check text-xs"></i>
                                        Kehadiran
                                    </a>
                                       
                                    <?php if ($is_master): ?>
                                    <a href="<?= URL_ROOT ?>/events/gantt?event_id=<?php echo $event['event_id']; ?>" 
                                       class="inline-flex items-center gap-2 px-4 py-2 text-[10px] font-black text-white bg-purple-600 hover:bg-purple-700 uppercase tracking-widest transition-all shadow-sm">
                                        <i class="fa-solid fa-chart-gantt text-xs"></i>
                                        Gantt Chart
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($status === 'Draft'): ?>
                                        <?php if ($level === 'MASTER' && hasRole([888, 1, 4, 33])): ?>
                                            <?php if (hasRole(1)): ?>
                                                <a href="?action=approve&event_id=<?php echo $event['event_id']; ?>" 
                                                   class="inline-flex items-center gap-2 px-4 py-2 text-[10px] font-black text-white bg-green-600 hover:bg-green-700 uppercase tracking-widest transition-all shadow-sm">
                                                    <i class="fa-solid fa-check-double text-xs"></i>
                                                    Lulus Terus
                                                </a>
                                            <?php else: ?>
                                                <a href="?action=submit&event_id=<?php echo $event['event_id']; ?>" 
                                                   class="inline-flex items-center gap-2 px-4 py-2 text-[10px] font-black text-white bg-amber-600 hover:bg-amber-700 uppercase tracking-widest transition-all shadow-sm">
                                                    <i class="fa-solid fa-paper-plane text-xs"></i>
                                                    Hantar Ke Presiden
                                                </a>
                                            <?php endif; ?>
                                        <?php elseif ($level === 'SUB' && hasRole(33)): ?>
                                            <a href="?action=submit_to_branch&event_id=<?php echo $event['event_id']; ?>" 
                                               class="inline-flex items-center gap-2 px-4 py-2 text-[10px] font-black text-white bg-amber-600 hover:bg-amber-700 uppercase tracking-widest transition-all shadow-sm">
                                                <i class="fa-solid fa-paper-plane text-xs"></i>
                                                Hantar Ke Pengerusi
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if ($check_status === 'SUBMITTED' && hasRole([1, 888])): ?>
                                    <a href="?action=approve&event_id=<?php echo $event['event_id']; ?>" 
                                       class="inline-flex items-center gap-2 px-4 py-2 text-[10px] font-black text-white bg-green-600 hover:bg-green-700 uppercase tracking-widest transition-all shadow-sm">
                                        <i class="fa-solid fa-check-double text-xs"></i>
                                        Lulus
                                    </a>
                                    <a href="?action=reject&event_id=<?php echo $event['event_id']; ?>" 
                                       class="inline-flex items-center gap-2 px-4 py-2 text-[10px] font-black text-white bg-red-600 hover:bg-red-700 uppercase tracking-widest transition-all shadow-sm">
                                        <i class="fa-solid fa-xmark text-xs"></i>
                                        Tolak
                                    </a>
                                    <?php endif; ?>

                                    <a href="?delete=<?php echo $event['event_id']; ?>" 
                                       onclick="return confirm('Adakah anda pasti mahu memadam acara ini?');"
                                       class="inline-flex items-center justify-center w-9 h-9 text-slate-300 hover:text-red-600 hover:bg-red-50 border border-slate-100 transition-all"
                                       title="Padam">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- Sub Events Nested -->
                        <?php foreach ($event['sub_events'] as $sub): 
                             $s_status = !empty($sub['status']) && $sub['status'] !== '0' ? $sub['status'] : 'Draft';
                             $s_status_class = 'bg-slate-100 text-slate-400';
                             
                             $s_check = strtoupper($s_status);
                             if ($s_check === 'APPROVED') $s_status_class = 'bg-green-100 text-green-700';
                             elseif ($s_check === 'SUBMITTED') $s_status_class = 'bg-amber-100 text-amber-700';
                             elseif ($s_check === 'BRANCH APPROVED') $s_status_class = 'bg-emerald-100 text-emerald-700';
                        ?>
                        <tr class="hover:bg-slate-50 transition-colors group bg-slate-50/20">
                            <td class="px-8 py-4 text-center">
                                <div class="flex items-center justify-center">
                                    <div class="h-6 w-[2px] bg-slate-200"></div>
                                    <div class="w-4 h-[2px] bg-slate-200"></div>
                                    <span class="px-2 py-0.5 text-[7px] font-black uppercase tracking-widest bg-slate-100 text-slate-400 ml-2">
                                        SUB
                                    </span>
                                </div>
                            </td>
                            <td class="px-8 py-4 pl-12">
                                <p class="text-[11px] font-bold text-slate-600 uppercase tracking-tight group-hover:text-kebana-blue transition-colors">
                                    <?php echo htmlspecialchars($sub['event_title']); ?>
                                </p>
                                <p class="text-[8px] font-bold text-slate-300 uppercase mt-0.5"><?php echo htmlspecialchars($sub['cawangan_name'] ?? 'Cawangan'); ?></p>
                            </td>
                            <td class="px-8 py-4">
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">
                                    <i class="fa-solid fa-calendar-day mr-2 text-slate-200"></i>
                                    <?php echo date('d M Y', strtotime($sub['event_date'])); ?>
                                </p>
                            </td>
                            <td class="px-8 py-4">
                                <span class="px-2 py-1 text-[8px] font-black uppercase tracking-widest <?php echo $s_status_class; ?>">
                                    <?php echo $s_status; ?>
                                </span>
                            </td>
                            <td class="px-8 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="<?= URL_ROOT ?>/events/view/<?php echo $sub['event_id']; ?>" 
                                       class="inline-flex items-center gap-2 px-3 py-1.5 text-[9px] font-black text-slate-600 bg-slate-100 hover:bg-slate-200 uppercase tracking-widest transition-all">
                                        <i class="fa-solid fa-eye"></i>
                                        Lihat
                                    </a>
                                    <a href="<?= URL_ROOT ?>/events/attendance?event_id=<?php echo $sub['event_id']; ?>" 
                                       class="inline-flex items-center gap-2 px-3 py-1.5 text-[9px] font-black text-kebana-blue bg-blue-50 hover:bg-blue-100 uppercase tracking-widest transition-all">
                                        <i class="fa-solid fa-users-check"></i>
                                        Hadir
                                    </a>
                                    <?php if ($s_status === 'Draft' && hasRole(33)): ?>
                                    <a href="?action=submit_to_branch&event_id=<?php echo $sub['event_id']; ?>" 
                                       class="inline-flex items-center gap-2 px-3 py-1.5 text-[9px] font-black text-white bg-amber-600 hover:bg-amber-700 uppercase tracking-widest transition-all">
                                        <i class="fa-solid fa-paper-plane"></i>
                                        Hantar Ke Pengerusi
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($s_check === 'SUBMITTED' && hasRole([1, 888])): ?>
                                    <a href="?action=approve&event_id=<?php echo $sub['event_id']; ?>" 
                                       class="inline-flex items-center gap-2 px-3 py-1.5 text-[9px] font-black text-white bg-green-600 hover:bg-green-700 uppercase tracking-widest transition-all">
                                        <i class="fa-solid fa-check-double text-xs"></i>
                                        Lulus
                                    </a>
                                    <a href="?action=reject&event_id=<?php echo $sub['event_id']; ?>" 
                                       class="inline-flex items-center gap-2 px-3 py-1.5 text-[9px] font-black text-white bg-red-600 hover:bg-red-700 uppercase tracking-widest transition-all">
                                        <i class="fa-solid fa-xmark text-xs"></i>
                                        Tolak
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    </div>
</div>
<?php if (isset($_GET['msg']) && $_GET['msg'] === 'created'): ?>
<!-- Premium Success Pop-out Modal for Master Event (Glassmorphism + SVG Stroke Draw Tick Animation) -->
<div id="successModalOverlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[150] opacity-0 pointer-events-none transition-all duration-500 ease-out flex items-center justify-center p-4">
    <div id="successCard" class="bg-white/95 backdrop-blur-md max-w-sm w-full p-8 md:p-10 shadow-2xl border-t-8 border-green-500 text-center transform scale-95 opacity-0 transition-all duration-500 ease-out space-y-6">
        <!-- SVG Traced Checkmark Drawing Animation -->
        <div class="flex justify-center">
            <div class="checkmark-wrapper">
                <svg class="checkmark-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                    <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                    <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                </svg>
            </div>
        </div>
        
        <div class="space-y-2">
            <h3 class="text-3xl font-black text-kebana-blue tracking-tighter uppercase italic leading-none">
                Berjaya!
            </h3>
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.25em] block">
                Acara Utama Berjaya Didaftarkan
            </p>
        </div>
        
        <div class="text-xs font-medium text-slate-500 leading-relaxed uppercase tracking-wider bg-slate-50/50 p-4 border border-slate-100">
            Aktiviti atau program utama baharu telah selamat didaftarkan ke dalam sistem.
        </div>
        
        <div>
            <button id="successModalCloseBtn" class="w-full bg-green-600 text-white py-4 text-xs font-black uppercase tracking-[0.2em] hover:bg-green-700 hover:shadow-green-200/50 hover:shadow-xl transition-all active:scale-95 duration-150">
                TERUSKAN
            </button>
        </div>
    </div>
</div>

<style>
/* CSS Keyframes for Real-time SVG Draw Animation */
.checkmark-wrapper {
    width: 80px;
    height: 80px;
    position: relative;
}
.checkmark-svg {
    width: 80px;
    height: 80px;
    display: block;
}
.checkmark-circle {
    stroke-dasharray: 166;
    stroke-dashoffset: 166;
    stroke-width: 4;
    stroke-miterlimit: 10;
    stroke: #16a34a; /* green-600 */
    fill: none;
    animation: stroke-draw-circle 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
}
.checkmark-check {
    transform-origin: 50% 50%;
    stroke-dasharray: 48;
    stroke-dashoffset: 48;
    stroke-width: 4;
    stroke-linecap: round;
    stroke: #16a34a; /* green-600 */
    fill: none;
    animation: stroke-draw-check 0.4s cubic-bezier(0.65, 0, 0.45, 1) 0.55s forwards;
}

@keyframes stroke-draw-circle {
    100% {
        stroke-dashoffset: 0;
    }
}
@keyframes stroke-draw-check {
    100% {
        stroke-dashoffset: 0;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('successModalOverlay');
    const card = document.getElementById('successCard');
    const closeBtn = document.getElementById('successModalCloseBtn');
    
    // Smooth intro sequence after small render delay
    setTimeout(() => {
        if (overlay && card) {
            overlay.classList.remove('opacity-0', 'pointer-events-none');
            overlay.classList.add('opacity-100');
            
            card.classList.remove('scale-95', 'opacity-0');
            card.classList.add('scale-100', 'opacity-100');
        }
    }, 150);
    
    function dismissSuccessModal() {
        if (!overlay || !card) return;
        
        // Outro transitions
        overlay.classList.remove('opacity-100');
        overlay.classList.add('opacity-0', 'pointer-events-none');
        
        card.classList.remove('scale-100', 'opacity-100');
        card.classList.add('scale-95', 'opacity-0');
        
        // Clean URL parameter without reloading
        setTimeout(() => {
            const url = new URL(window.location.href);
            url.searchParams.delete('msg');
            window.history.replaceState({}, '', url);
        }, 500);
    }
    
    closeBtn?.addEventListener('click', dismissSuccessModal);
    overlay?.addEventListener('click', function(e) {
        if (e.target === overlay) {
            dismissSuccessModal();
        }
    });
});
</script>
<?php endif; ?>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>

