<?php
/**
 * KEBANA Management System - Event Detail View (Premium Editorial Design)
 * File: modules/events/view.php
 */

use App\Helpers\EventsHelper;
use App\Helpers\DashboardHelper;

$eventId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Workflow actions - MUST BE BEFORE ANY HTML OUTPUT
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $success = false;
    
    if ($action === 'submit_to_branch' && hasRole(33)) {
        $success = EventsHelper::submitToBranch($eventId);
    } elseif ($action === 'branch_approve' && hasRole(11)) {
        $success = EventsHelper::branchApprove($eventId);
    } elseif ($action === 'submit_to_pusat' && hasRole([33, 4])) {
        $success = EventsHelper::submitEvent($eventId);
    } elseif ($action === 'approve' && hasRole([1, 888])) {
        $success = EventsHelper::approveEvent($eventId);
    } elseif ($action === 'reject' && hasRole([1, 11, 888])) {
        $success = EventsHelper::rejectEvent($eventId);
    }
    
    if ($success) {
        header("Location: /kebana-digital/events/view/$eventId?msg=success");
        exit;
    }
}

require_once APP_ROOT . '/includes/header.php';

$event = EventsHelper::getEventById($eventId);

if (!$event) {
    echo '<div class="bg-red-50 p-8 border-l-4 border-red-500 text-red-700 font-black uppercase tracking-widest text-xs">Acara tidak dijumpai.</div>';
    require_once APP_ROOT . '/includes/footer.php';
    exit();
}

$status = !empty($event['status']) && $event['status'] !== '0' ? $event['status'] : 'Draft';
$status_class = 'bg-slate-100 text-slate-500';

$check_status = strtoupper($status);
if ($check_status === 'APPROVED') $status_class = 'bg-green-100 text-green-700';
elseif ($check_status === 'SUBMITTED') $status_class = 'bg-amber-100 text-amber-700';
elseif ($check_status === 'REJECTED') $status_class = 'bg-red-100 text-red-700';
elseif ($check_status === 'PENDING BRANCH APPROVAL') $status_class = 'bg-blue-100 text-blue-700';
elseif ($check_status === 'BRANCH APPROVED') $status_class = 'bg-emerald-100 text-emerald-700';

$level = $event['event_level'] ?? 'MASTER';
$level_class = ($level === 'MASTER') ? 'bg-kebana-blue text-white' : 'bg-slate-200 text-slate-600';

$page_title = 'PERINCIAN ACARA';
?>

<div class="space-y-12">
    <!-- Header Hero -->
    <div class="bg-white border-t-8 border-kebana-blue shadow-sm overflow-hidden">
        <div class="p-10 md:p-16 flex flex-col md:flex-row justify-between items-start md:items-center gap-10">
            <div class="flex-1 space-y-4">
                <div class="flex items-center space-x-4">
                    <span class="px-4 py-1 text-[10px] font-black uppercase tracking-[0.2em] <?php echo $level_class; ?>">
                        <?php echo $level; ?> EVENT
                    </span>
                    <span class="px-4 py-1 text-[10px] font-black uppercase tracking-[0.2em] <?php echo $status_class; ?>">
                        STATUS: <?php echo $status; ?>
                    </span>
                </div>
                <h2 class="text-5xl font-black text-kebana-blue uppercase tracking-tighter leading-none italic italic">
                    <?php echo htmlspecialchars($event['event_title']); ?>
                </h2>
                <div class="flex flex-wrap gap-8 pt-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-slate-50 flex items-center justify-center text-kebana-blue">
                            <i class="fa-solid fa-calendar-day"></i>
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">TARIKH ACARA</p>
                            <p class="text-sm font-black text-kebana-blue"><?php echo date('d F Y', strtotime($event['event_date'])); ?></p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-slate-50 flex items-center justify-center text-kebana-blue">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">LOKASI / VENUE</p>
                            <p class="text-sm font-black text-kebana-blue"><?php echo htmlspecialchars($event['venue']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full md:w-auto flex flex-col gap-3">
                <!-- Workflow Actions -->
                <?php if ($check_status === 'DRAFT' && $level === 'SUB' && hasRole(33)): ?>
                    <a href="?id=<?php echo $eventId; ?>&action=submit_to_branch" class="bg-amber-500 text-white px-10 py-4 text-xs font-black uppercase tracking-[0.2em] hover:bg-amber-600 transition-all text-center shadow-xl">
                        HANTAR KE PENGERUSI
                    </a>
                <?php elseif ($check_status === 'PENDING BRANCH APPROVAL' && hasRole(11)): ?>
                    <div class="flex flex-col gap-2">
                        <a href="?id=<?php echo $eventId; ?>&action=branch_approve" class="bg-green-600 text-white px-10 py-4 text-xs font-black uppercase tracking-[0.2em] hover:bg-green-700 transition-all text-center shadow-xl">
                            SAHKAN KERTAS KERJA
                        </a>
                        <a href="?id=<?php echo $eventId; ?>&action=reject" class="bg-red-600 text-white px-10 py-4 text-xs font-black uppercase tracking-[0.2em] hover:bg-red-700 transition-all text-center shadow-xl">
                            TOLAK
                        </a>
                    </div>
                <?php elseif ($check_status === 'BRANCH APPROVED' && hasRole(33)): ?>
                    <a href="?id=<?php echo $eventId; ?>&action=submit_to_pusat" class="bg-kebana-blue text-white px-10 py-4 text-xs font-black uppercase tracking-[0.2em] hover:bg-kebana-accent transition-all text-center shadow-xl">
                        HANTAR KE PUSAT
                    </a>
                <?php elseif ($check_status === 'SUBMITTED' && hasRole([1, 888])): ?>
                    <div class="flex flex-col gap-2">
                        <a href="?id=<?php echo $eventId; ?>&action=approve" class="bg-green-600 text-white px-10 py-4 text-xs font-black uppercase tracking-[0.2em] hover:bg-green-700 transition-all text-center shadow-xl">
                            LULUSKAN ACARA
                        </a>
                        <a href="?id=<?php echo $eventId; ?>&action=reject" class="bg-red-600 text-white px-10 py-4 text-xs font-black uppercase tracking-[0.2em] hover:bg-red-700 transition-all text-center shadow-xl">
                            TOLAK ACARA
                        </a>
                    </div>
                <?php elseif ($check_status === 'DRAFT' && $level === 'MASTER'): ?>
                    <?php if (hasRole(4)): ?>
                        <a href="?id=<?php echo $eventId; ?>&action=submit_to_pusat" class="bg-amber-500 text-white px-10 py-4 text-xs font-black uppercase tracking-[0.2em] hover:bg-amber-600 transition-all text-center shadow-xl">
                            HANTAR KE PRESIDEN
                        </a>
                    <?php elseif (hasRole(888)): ?>
                        <a href="?id=<?php echo $eventId; ?>&action=approve" class="bg-green-600 text-white px-10 py-4 text-xs font-black uppercase tracking-[0.2em] hover:bg-green-700 transition-all text-center shadow-xl">
                            SAHKAN (SUPER ADMIN)
                        </a>
                    <?php endif; ?>
                <?php endif; ?>

                <a href="/kebana-digital/events/attendance?event_id=<?php echo $eventId; ?>" class="bg-slate-800 text-white px-10 py-4 text-xs font-black uppercase tracking-[0.2em] hover:bg-black transition-all text-center shadow-xl">
                    PENGURUSAN KEHADIRAN
                </a>
                <div class="grid grid-cols-2 gap-3">
                    <a href="/kebana-digital/events" class="bg-slate-100 text-slate-600 px-6 py-4 text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all text-center">
                        KEMBALI
                    </a>
                    <button onclick="window.print()" class="bg-kebana-yellow text-kebana-blue px-6 py-4 text-[10px] font-black uppercase tracking-widest hover:bg-yellow-400 transition-all text-center">
                        CETAK
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
        <!-- Left Column: Details -->
        <div class="lg:col-span-2 space-y-12">
            <div class="bg-white p-10 border border-slate-100 shadow-sm space-y-8">
                <div class="border-b border-slate-100 pb-6 flex justify-between items-center">
                    <h3 class="text-xs font-black text-kebana-blue uppercase tracking-[0.3em]">Maklumat Lanjut Program</h3>
                    <?php if ($level === 'SUB' && !empty($event['parent_event_id'])): ?>
                        <?php $parent = EventsHelper::getParentEvent($event['parent_event_id']); ?>
                        <?php if ($parent): ?>
                        <a href="/kebana-digital/events/view/<?php echo $parent['event_id']; ?>" class="text-[9px] font-black bg-slate-100 text-slate-500 px-3 py-1 uppercase tracking-widest hover:bg-kebana-blue hover:text-white transition-all">
                            Master: <?php echo htmlspecialchars($parent['event_title']); ?>
                        </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div class="space-y-2">
                        <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Dicipta Oleh</p>
                        <p class="text-sm font-bold text-slate-700"><?php echo htmlspecialchars($event['creator_name'] ?? 'System Admin'); ?></p>
                    </div>
                    <div class="space-y-2">
                        <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Cawangan</p>
                        <p class="text-sm font-bold text-slate-700"><?php echo htmlspecialchars($event['cawangan_name'] ?? 'HQ Pusat'); ?></p>
                    </div>
                    <div class="space-y-2">
                        <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Anggaran Bajet</p>
                        <p class="text-xl font-black text-kebana-blue">RM <?php echo number_format($event['budget_est'] ?? 0, 2); ?></p>
                    </div>
                    <div class="space-y-2">
                        <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Status Kelulusan</p>
                        <p class="text-sm font-bold text-slate-700 uppercase italic"><?php echo htmlspecialchars($event['approval_status']); ?></p>
                    </div>
                </div>

                <div class="pt-8 border-t border-slate-50 space-y-4">
                    <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Objektif & Keterangan</p>
                    <div class="prose prose-sm max-w-none text-slate-600 font-medium leading-relaxed">
                        <?php echo nl2br(htmlspecialchars($event['description'] ?? 'Tiada keterangan tambahan disediakan untuk acara ini.')); ?>
                    </div>
                </div>
            </div>

            <!-- SUB EVENTS SECTION (Only for Master Events) -->
            <?php if ($level === 'MASTER'): ?>
            <div class="bg-white p-10 border border-slate-100 shadow-sm space-y-8">
                <div class="flex justify-between items-center border-b border-slate-100 pb-6">
                    <div>
                        <h3 class="text-xs font-black text-kebana-blue uppercase tracking-[0.3em]">Sub Aktiviti / Program</h3>
                        <p class="text-[9px] font-bold text-slate-400 uppercase mt-1 tracking-widest">Senarai pelaksanaan mengikut cawangan</p>
                    </div>
                    <a href="/kebana-digital/events/create?parent_id=<?php echo $eventId; ?>" class="bg-kebana-blue text-white px-4 py-2 text-[10px] font-black uppercase tracking-widest hover:bg-kebana-accent transition-all">
                        + Tambah Sub Acara
                    </a>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-slate-50">
                                <th class="py-4 text-[10px] font-black text-slate-300 uppercase tracking-widest">Aktiviti Cawangan</th>
                                <th class="py-4 text-[10px] font-black text-slate-300 uppercase tracking-widest">Cawangan</th>
                                <th class="py-4 text-[10px] font-black text-slate-300 uppercase tracking-widest">Tarikh</th>
                                <th class="py-4 text-[10px] font-black text-slate-300 uppercase tracking-widest">Status</th>
                                <th class="py-4 text-[10px] font-black text-slate-300 uppercase tracking-widest text-right">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php 
                            $subEvents = EventsHelper::getSubEvents($eventId);
                            if (empty($subEvents)): 
                            ?>
                            <tr>
                                <td colspan="5" class="py-10 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">
                                    Tiada sub-acara didaftarkan setakat ini.
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($subEvents as $sub): 
                                    $s_status = $sub['status'] ?? 'Draft';
                                    $s_class = 'text-slate-400';
                                    if ($s_status === 'Approved') $s_class = 'text-green-600';
                                    elseif ($s_status === 'Submitted') $s_class = 'text-amber-600';
                                ?>
                                <tr class="group hover:bg-slate-50 transition-colors">
                                    <td class="py-5">
                                        <p class="text-xs font-black text-kebana-blue uppercase tracking-tight group-hover:italic transition-all"><?php echo htmlspecialchars($sub['event_title']); ?></p>
                                    </td>
                                    <td class="py-5">
                                        <p class="text-[10px] font-bold text-slate-600 uppercase"><?php echo htmlspecialchars($sub['cawangan_name'] ?? 'N/A'); ?></p>
                                    </td>
                                    <td class="py-5">
                                        <p class="text-[10px] font-bold text-slate-600 uppercase"><?php echo date('d M Y', strtotime($sub['event_date'])); ?></p>
                                    </td>
                                    <td class="py-5">
                                        <span class="text-[9px] font-black uppercase tracking-widest <?php echo $s_class; ?>">
                                            ● <?php echo $s_status; ?>
                                        </span>
                                    </td>
                                    <td class="py-5 text-right">
                                        <a href="/kebana-digital/events/view/<?php echo $sub['event_id']; ?>" class="text-[10px] font-black text-kebana-blue hover:underline uppercase tracking-widest">
                                            LIHAT →
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Documents linked to this event -->
            <div class="bg-white p-10 border border-slate-100 shadow-sm space-y-8">
                <div class="flex justify-between items-center border-b border-slate-100 pb-6">
                    <h3 class="text-xs font-black text-kebana-blue uppercase tracking-[0.3em]">Dokumen & Kertas Kerja</h3>
                    <a href="/kebana-digital/documents/upload?event_id=<?php echo $eventId; ?>" class="text-[10px] font-black text-kebana-blue uppercase hover:underline">+ Tambah Fail</a>
                </div>
                
                <div class="space-y-4">
                    <?php 
                    $docs = EventsHelper::getDocumentsByEventId($eventId);
                    if (empty($docs)):
                    ?>
                        <p class="py-10 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">
                            Tiada dokumen dimuatnaik bagi acara ini.
                        </p>
                    <?php else: ?>
                        <?php foreach ($docs as $doc): 
                            $ext = strtolower(pathinfo($doc['file_path'], PATHINFO_EXTENSION));
                            $icon = 'fa-file';
                            $iconColor = 'text-slate-400';
                            
                            if ($ext === 'pdf') {
                                $icon = 'fa-file-pdf';
                                $iconColor = 'text-red-500';
                            } elseif (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                                $icon = 'fa-file-image';
                                $iconColor = 'text-blue-500';
                            }
                            
                            // Mocking file size for now since we don't store it in DB
                            $fileSize = '---';
                            if (file_exists(APP_ROOT . '/' . $doc['file_path'])) {
                                $bytes = filesize(APP_ROOT . '/' . $doc['file_path']);
                                if ($bytes >= 1048576) $fileSize = number_format($bytes / 1048576, 1) . ' MB';
                                elseif ($bytes >= 1024) $fileSize = number_format($bytes / 1024, 0) . ' KB';
                                else $fileSize = $bytes . ' B';
                            }
                        ?>
                        <div class="p-6 bg-slate-50 border border-slate-100 flex items-center justify-between group hover:border-kebana-blue transition-colors">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-white flex items-center justify-center <?php echo $iconColor; ?> shadow-sm">
                                    <i class="fa-solid <?php echo $icon; ?> text-xl"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-black text-slate-800 uppercase italic"><?php echo htmlspecialchars($doc['doc_name']); ?></p>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase mt-1">
                                        Uploaded on <?php echo date('d M Y', strtotime($doc['uploaded_at'])); ?> • <?php echo $fileSize; ?>
                                    </p>
                                </div>
                            </div>
                            <a href="/kebana-digital/<?php echo $doc['file_path']; ?>" target="_blank" class="text-kebana-blue opacity-0 group-hover:opacity-100 transition-opacity">
                                <i class="fa-solid fa-download"></i>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column: Sidebar Stats -->
        <div class="space-y-12">
            <!-- Attendance Summary Card -->
            <div class="bg-kebana-dark text-white p-10 shadow-2xl relative overflow-hidden group">
                <div class="absolute -right-10 -bottom-10 opacity-10 group-hover:scale-110 transition-transform duration-700">
                    <i class="fa-solid fa-users-viewfinder text-[150px]"></i>
                </div>
                <div class="relative z-10 space-y-8">
                    <h3 class="text-xs font-black text-kebana-yellow uppercase tracking-[0.3em]">Statistik Kehadiran</h3>
                    
                    <div class="space-y-6">
                        <?php 
                        $summary = EventsHelper::getAttendanceSummary($eventId); 
                        $total = array_sum($summary);
                        $present = $summary['Present'] ?? 0;
                        $percent = ($total > 0) ? round(($present / $total) * 100) : 0;
                        ?>
                        <div>
                            <div class="flex justify-between items-end mb-2">
                                <p class="text-3xl font-black text-white"><?php echo $percent; ?><span class="text-lg text-kebana-yellow">%</span></p>
                                <p class="text-[10px] font-black text-white/40 uppercase"><?php echo $present; ?> / <?php echo $total; ?> HADIR</p>
                            </div>
                            <div class="w-full h-2 bg-white/10">
                                <div class="h-full bg-kebana-yellow transition-all duration-1000" style="width: <?php echo $percent; ?>%"></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-4 bg-white/5 border border-white/10">
                                <p class="text-[9px] font-black text-white/40 uppercase">Ditolak</p>
                                <p class="text-lg font-black text-red-400"><?php echo $summary['Absent'] ?? 0; ?></p>
                            </div>
                            <div class="p-4 bg-white/5 border border-white/10">
                                <p class="text-[9px] font-black text-white/40 uppercase">Bersebab</p>
                                <p class="text-lg font-black text-kebana-yellow"><?php echo $summary['Excused'] ?? 0; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audit Trail -->
            <div class="bg-white p-10 border border-slate-100 shadow-sm space-y-8">
                <h3 class="text-xs font-black text-kebana-blue uppercase tracking-[0.3em]">Audit Log</h3>
                <div class="space-y-6">
                    <div class="relative pl-8 border-l-2 border-slate-100 py-2">
                        <div class="absolute -left-[9px] top-2 w-4 h-4 rounded-full bg-kebana-blue border-4 border-white shadow-sm"></div>
                        <p class="text-[10px] font-black text-kebana-blue uppercase tracking-widest">Acara Dicipta</p>
                        <p class="text-[9px] text-slate-400 font-bold mt-1 uppercase"><?php echo date('d M Y • H:i', strtotime($event['created_at'])); ?></p>
                    </div>
                    <?php if ($status !== 'Draft'): ?>
                    <div class="relative pl-8 border-l-2 border-slate-100 py-2">
                        <?php 
                        $audit_label = 'Dihantar Untuk Semakan';
                        $audit_color = 'bg-amber-500';
                        if ($status === 'Branch Approved') {
                            $audit_label = 'Disahkan Oleh Cawangan';
                            $audit_color = 'bg-emerald-500';
                        } elseif ($status === 'Approved') {
                            $audit_label = 'Diluluskan Sepenuhnya';
                            $audit_color = 'bg-green-600';
                        } elseif ($status === 'Pending Branch Approval') {
                            $audit_label = 'Menunggu Pengesahan Cawangan';
                            $audit_color = 'bg-blue-500';
                        }
                        ?>
                        <div class="absolute -left-[9px] top-2 w-4 h-4 rounded-full <?php echo $audit_color; ?> border-4 border-white shadow-sm"></div>
                        <p class="text-[10px] font-black uppercase tracking-widest" style="color: <?php echo str_replace('bg-', '', $audit_color); ?>"><?php echo $audit_label; ?></p>
                        <p class="text-[9px] text-slate-400 font-bold mt-1 uppercase">Sistem Dikemaskini</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
