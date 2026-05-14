<?php
/**
 * KEBANA Management System - Events List (MYDS Inspired)
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

    if ($action === 'submit' && hasRole([888, 4, 33, 6, 7])) {
        $success = EventsHelper::submitEvent($event_id);
        $message = $success ? 'Projek berjaya dihantar untuk semakan.' : 'Gagal menghantar projek.';
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

// Search & Filter
$search = trim($_GET['search'] ?? '');
if ($search) {
    $all_events = array_filter($all_events, function($e) use ($search) {
        return stripos($e['event_title'], $search) !== false || stripos($e['venue'], $search) !== false;
    });
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
        <?php if (hasRole([888, 4, 33])): ?>
        <a href="/kebana-digital/events/create" class="bg-kebana-blue text-white px-10 py-4 text-xs font-black uppercase tracking-[0.2em] hover:bg-kebana-accent transition-all shadow-xl inline-flex items-center">
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
            <a href="/kebana-digital/events" class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center hover:text-red-500">
                KOSONGKAN
            </a>
            <?php endif; ?>
        </form>
    </div>

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
                            <td class="px-8 py-6 text-right space-x-4">
                                <div class="flex justify-end gap-3 opacity-30 group-hover:opacity-100 transition-opacity">
                                    <a href="/kebana-digital/events/view/<?php echo $event['event_id']; ?>" 
                                       class="text-[9px] font-black text-slate-800 uppercase border-b border-slate-200 hover:border-kebana-blue pb-0.5 transition-all">Papar</a>
                                    
                                    <a href="/kebana-digital/events/attendance?event_id=<?php echo $event['event_id']; ?>" 
                                       class="text-[9px] font-black text-kebana-blue uppercase border-b border-kebana-blue/20 hover:border-kebana-blue pb-0.5 transition-all">Kehadiran</a>
                                    
                                    <?php if ($status === 'Draft' && hasRole([888, 4, 33, 6, 7])): ?>
                                    <a href="?action=submit&event_id=<?php echo $event['event_id']; ?>" 
                                       class="text-[9px] font-black text-amber-600 uppercase border-b border-amber-600/20 hover:border-amber-600 pb-0.5 transition-all">Hantar</a>
                                    <?php endif; ?>

                                    <?php if ($status === 'Submitted' && hasRole([1, 888])): ?>
                                    <a href="?action=approve&event_id=<?php echo $event['event_id']; ?>" 
                                       class="text-[9px] font-black text-green-600 uppercase border-b border-green-600/20 hover:border-green-600 pb-0.5 transition-all">Lulus</a>
                                    <a href="?action=reject&event_id=<?php echo $event['event_id']; ?>" 
                                       class="text-[9px] font-black text-red-600 uppercase border-b border-red-600/20 hover:border-red-600 pb-0.5 transition-all">Tolak</a>
                                    <?php endif; ?>

                                    <a href="?delete=<?php echo $event['event_id']; ?>" 
                                       onclick="return confirm('Adakah anda pasti mahu memadam acara ini?');"
                                       class="text-[9px] font-black text-slate-300 hover:text-red-600 uppercase transition-colors">
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
                                <div class="flex justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="/kebana-digital/events/view/<?php echo $sub['event_id']; ?>" class="text-[8px] font-black text-slate-400 uppercase hover:text-kebana-blue">Lihat</a>
                                    <a href="/kebana-digital/events/attendance?event_id=<?php echo $sub['event_id']; ?>" class="text-[8px] font-black text-slate-400 uppercase hover:text-kebana-blue">Hadir</a>
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

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
