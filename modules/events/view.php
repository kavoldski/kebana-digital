<?php
/**
 * KEBANA Digital Management System - Event Detail View (Premium Editorial Design)
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
    } elseif ($action === 'delete_document' && hasRole([1, 4, 33, 888])) {
        $docId = isset($_GET['doc_id']) ? (int)$_GET['doc_id'] : 0;
        if ($docId > 0) {
            $db = \App\Core\Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT e.status FROM tbl_document d JOIN tbl_event e ON d.event_id = e.event_id WHERE d.doc_id = ? AND d.event_id = ?");
            $stmt->bind_param("ii", $docId, $eventId);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if ($res) {
                $check_status = strtoupper($res['status'] ?? 'DRAFT');
                // Only allow deletion in Draft / Pending Branch Approval states, or if super admin/president
                if ($check_status === 'DRAFT' || $check_status === 'PENDING BRANCH APPROVAL' || hasRole([1, 888])) {
                    if (\App\Helpers\DocumentsHelper::deleteDocument($docId)) {
                        echo '<script>window.location.href = "' . URL_ROOT . '/events/view/' . $eventId . '?msg=doc_deleted";</script>';
                        exit;
                    }
                }
            }
        }
    }
    
    if ($success) {
        echo '<script>window.location.href = "' . URL_ROOT . '/events/view/' . $eventId . '?msg=success";</script>';
        exit;
    }
}

// Handle objective update
if (isset($_POST['update_objective'])) {
    if (EventsHelper::updateEventObjective($eventId, $_POST['objective'])) {
        echo '<script>window.location.href = "' . URL_ROOT . '/events/view/' . $eventId . '?msg=updated";</script>';
        exit;
    }
}

// Handle sub-event creation
if (isset($_POST['add_sub_event'])) {
    $current_user_id = (int)($_SESSION['user_id'] ?? 0);
    $current_cawangan_id = isset($_SESSION['cawangan_id']) ? (int)$_SESSION['cawangan_id'] : null;
    
    // Sub-event specific data
    $_POST['parent_master_event_id'] = $eventId;
    
    $new_id = EventsHelper::addEvent($_POST, $current_user_id, false, $current_cawangan_id);
    
    if ($new_id) {
        if (isset($_FILES['proposal_file']) && $_FILES['proposal_file']['error'] === UPLOAD_ERR_OK) {
            EventsHelper::handleDocumentUpload($new_id, $_FILES['proposal_file'], $current_user_id);
        }
        echo '<script>window.location.href = "' . URL_ROOT . '/events/view/' . $eventId . '?msg=sub_added";</script>';
        exit;
    }
}

require_once APP_ROOT . '/includes/header.php';

$suggestions = EventsHelper::getUniqueLocations();
$event = EventsHelper::getEventById($eventId);

if (!$event) {
    echo '<div class="bg-red-50 p-8 border-l-4 border-red-500 text-red-700 font-black uppercase tracking-widest text-xs">Acara tidak dijumpai.</div>';
    require_once APP_ROOT . '/includes/footer.php';
    exit();
}

$status = !empty($event['status']) && $event['status'] !== '0' ? $event['status'] : 'Draft';

$check_status = strtoupper($status);
if ($check_status === 'APPROVED' || $check_status === 'BRANCH APPROVED') {
    $status_class = 'bg-green-100 text-green-700';
} elseif ($check_status === 'REJECTED') {
    $status_class = 'bg-red-100 text-red-700';
} elseif ($check_status === 'PENDING BRANCH APPROVAL' || $check_status === 'SUBMITTED') {
    $status_class = 'bg-amber-100 text-amber-700';
} elseif ($check_status === 'DRAFT') {
    $status_class = 'bg-blue-100 text-blue-700';
} else {
    $status_class = 'bg-slate-100 text-slate-500';
}

$level = $event['event_level'] ?? 'MASTER';
$level_class = ($level === 'MASTER') ? 'bg-kebana-blue text-white' : 'bg-slate-200 text-slate-600';

$page_title = 'PERINCIAN ACARA';
?>

<?php 
$msg = $_GET['msg'] ?? '';
$msg_text = '';
$msg_class = '';
if ($msg === 'success') {
    $msg_text = 'Tindakan berjaya dilaksanakan.';
    $msg_class = 'bg-green-50 text-green-700 border-l-4 border-green-600';
} elseif ($msg === 'updated') {
    $msg_text = 'Keterangan acara berjaya dikemaskini.';
    $msg_class = 'bg-green-50 text-green-700 border-l-4 border-green-600';
} elseif ($msg === 'doc_deleted') {
    $msg_text = 'Dokumen berjaya dipadam sepenuhnya dari server.';
    $msg_class = 'bg-red-50 text-red-700 border-l-4 border-red-600';
}

if ($msg_text): 
?>
<div class="p-6 <?php echo $msg_class; ?> font-bold text-xs uppercase tracking-widest animate-pulse">
    <?php echo $msg_text; ?>
</div>
<?php endif; ?>

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
                            <p class="text-sm font-black text-kebana-blue">
                                <?php echo htmlspecialchars($event['venue']); ?>
                                <?php if (!empty($event['kawasan'])): ?>
                                    <span class="text-slate-400 font-bold ml-1">· <?php echo htmlspecialchars($event['kawasan']); ?></span>
                                <?php endif; ?>
                            </p>
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
                    <?php elseif (hasRole([1, 888])): ?>
                        <a href="?id=<?php echo $eventId; ?>&action=approve" class="bg-green-600 text-white px-10 py-4 text-xs font-black uppercase tracking-[0.2em] hover:bg-green-700 transition-all text-center shadow-xl">
                            <?php echo hasRole(1) ? 'LULUSKAN TERUS' : 'SAHKAN (SUPER ADMIN)'; ?>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>

                <a href="<?= URL_ROOT ?>/events/attendance?event_id=<?php echo $eventId; ?>" class="bg-slate-800 text-white px-10 py-4 text-xs font-black uppercase tracking-[0.2em] hover:bg-black transition-all text-center shadow-xl">
                    PENGURUSAN KEHADIRAN
                </a>

                <?php if (hasRole([888, 1, 2, 3, 6, 7, 55, 66])): ?>
                <a href="<?= URL_ROOT ?>/finance/event/<?php echo $eventId; ?>" class="bg-emerald-600 text-white px-10 py-4 text-xs font-black uppercase tracking-[0.2em] hover:bg-emerald-700 transition-all text-center shadow-xl">
                    PENGGUNAAN KEWANGAN
                </a>
                <?php endif; ?>
                <div class="grid grid-cols-2 gap-3">
                    <a href="<?= URL_ROOT ?>/events" class="bg-slate-100 text-slate-600 px-6 py-4 text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all text-center">
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
                        <a href="<?= URL_ROOT ?>/events/view/<?php echo $parent['event_id']; ?>" class="text-[9px] font-black bg-slate-100 text-slate-500 px-3 py-1 uppercase tracking-widest hover:bg-kebana-blue hover:text-white transition-all">
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
                    <div class="flex items-center justify-between">
                        <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Objektif & Keterangan</p>
                        <?php if (hasRole([1, 4, 33, 888]) && ($check_status === 'DRAFT' || $check_status === 'PENDING BRANCH APPROVAL' || hasRole([1, 888]))): ?>
                            <button onclick="toggleObjectiveEdit()" class="text-[9px] font-black text-kebana-blue uppercase border-b border-kebana-blue pb-0.5 hover:text-kebana-accent transition-colors">Edit</button>
                        <?php endif; ?>
                    </div>
                    
                    <div id="objective_display" class="prose prose-sm max-w-none text-slate-600 font-medium leading-relaxed">
                        <?php echo !empty($event['objective']) ? nl2br(htmlspecialchars($event['objective'])) : 'Tiada keterangan tambahan disediakan untuk acara ini.'; ?>
                    </div>

                    <form id="objective_form" method="POST" class="hidden space-y-4">
                        <textarea name="objective" rows="5" class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-200 focus:border-kebana-blue focus:bg-white outline-none text-sm font-medium transition-all"><?php echo htmlspecialchars($event['objective'] ?? ''); ?></textarea>
                        <div class="flex gap-4">
                            <button type="submit" name="update_objective" class="bg-kebana-blue text-white px-6 py-2 text-[10px] font-black uppercase tracking-widest hover:bg-kebana-accent transition-all">Simpan</button>
                            <button type="button" onclick="toggleObjectiveEdit()" class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-6 py-2 hover:bg-slate-50 transition-all">Batal</button>
                        </div>
                    </form>
                </div>

                <script>
                    function toggleObjectiveEdit() {
                        const display = document.getElementById('objective_display');
                        const form = document.getElementById('objective_form');
                        display.classList.toggle('hidden');
                        form.classList.toggle('hidden');
                    }
                </script>
            </div>

            <!-- SUB EVENTS SECTION (Only for Master Events) -->
            <?php if ($level === 'MASTER'): ?>
            <div class="bg-white p-10 border border-slate-100 shadow-sm space-y-8">
                <div class="flex justify-between items-center border-b border-slate-100 pb-6">
                    <div>
                        <h3 class="text-xs font-black text-kebana-blue uppercase tracking-[0.3em]">Sub Aktiviti / Program</h3>
                        <p class="text-[9px] font-bold text-slate-400 uppercase mt-1 tracking-widest">Senarai pelaksanaan mengikut cawangan</p>
                    </div>
                    <?php if (hasRole([4, 33, 888])): ?>
                    <button onclick="openSubEventDrawer()" class="bg-kebana-blue text-white px-4 py-2 text-[10px] font-black uppercase tracking-widest hover:bg-kebana-accent transition-all">
                        + Tambah Sub Acara
                    </button>
                    <?php endif; ?>
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
                                        <button onclick="viewSubEvent(<?php echo $sub['event_id']; ?>)" class="text-[10px] font-black text-kebana-blue hover:underline uppercase tracking-widest">
                                            LIHAT →
                                        </button>
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
                    <a href="<?= URL_ROOT ?>/documents/upload?event_id=<?php echo $eventId; ?>" class="text-[10px] font-black text-kebana-blue uppercase hover:underline">+ Tambah Fail</a>
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
                            $fullPath = get_absolute_upload_path($doc['file_path']);
                            if (file_exists($fullPath)) {
                                $bytes = filesize($fullPath);
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
                            <div class="flex items-center space-x-4 opacity-60 group-hover:opacity-100 transition-all">
                                <a href="<?= URL_ROOT ?>/<?php echo $doc['file_path']; ?>" target="_blank" class="text-kebana-blue hover:text-kebana-accent transition-colors" title="Muat Turun / Lihat">
                                    <i class="fa-solid fa-download"></i>
                                </a>
                                <?php 
                                // Enable deletion if event is in draft/mutable states or user is super admin/presiden
                                $can_delete_doc = hasRole([1, 4, 33, 888]) && ($check_status === 'DRAFT' || $check_status === 'PENDING BRANCH APPROVAL' || hasRole([1, 888]));
                                if ($can_delete_doc): 
                                ?>
                                <a href="?id=<?php echo $eventId; ?>&action=delete_document&doc_id=<?php echo $doc['doc_id']; ?>" 
                                   onclick="return confirm('Adakah anda pasti mahu memadamkan dokumen ini? Fail akan dipadam sepenuhnya.');" 
                                   class="text-red-500 hover:text-red-700 transition-colors ml-1" 
                                   title="Padam Dokumen">
                                    <i class="fa-solid fa-trash-can"></i>
                                </a>
                                <?php endif; ?>
                            </div>
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

<!-- Right-Pane Drawer for Sub-Event -->
<div id="subEventOverlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] opacity-0 pointer-events-none transition-opacity duration-300"></div>
<div id="subEventDrawer" class="fixed top-0 right-0 h-full w-full max-w-[500px] bg-white z-[101] translate-x-full transition-transform duration-500 ease-in-out shadow-2xl overflow-y-auto">
    <div class="p-10 space-y-10">
        <div class="flex justify-between items-center border-b border-slate-100 pb-6">
            <div>
                <h3 class="text-lg font-black text-kebana-blue uppercase tracking-tight italic">Daftar Sub-Acara</h3>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">Tambah aktiviti baru di bawah program ini.</p>
            </div>
            <button onclick="closeSubEventDrawer()" class="w-10 h-10 flex items-center justify-center text-slate-300 hover:text-red-500 transition-colors">
                <i class="fa-solid fa-xmark text-2xl"></i>
            </button>
        </div>

        <form method="POST" enctype="multipart/form-data" class="space-y-8">
            <input type="hidden" name="add_sub_event" value="1">
            
            <div>
                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Tajuk Sub-Acara <span class="text-red-500">*</span></label>
                <input type="text" name="event_title" required
                       class="w-full px-5 py-3 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold transition-all"
                       placeholder="Cth: Bengkel Kemahiran Digital">
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Kawasan <span class="text-red-500">*</span></label>
                    <input type="text" name="kawasan" required list="kawasan_list"
                           class="w-full px-5 py-3 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold transition-all"
                           placeholder="Cth: Samalaju">
                </div>
                <div>
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Anggaran Bajet</label>
                    <input type="number" step="0.01" name="budget_est"
                           class="w-full px-5 py-3 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold transition-all"
                           placeholder="0.00">
                </div>
            </div>

            <div>
                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Lokasi / Venue <span class="text-red-500">*</span></label>
                <input type="text" name="venue" required list="venue_list"
                       class="w-full px-5 py-3 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold transition-all"
                       placeholder="Cth: Pusat Komuniti">
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Tarikh Mula <span class="text-red-500">*</span></label>
                    <input type="date" name="event_date" required
                           class="w-full px-5 py-3 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold transition-all">
                </div>
                <div>
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Tarikh Tamat</label>
                    <input type="date" name="event_end_date"
                           class="w-full px-5 py-3 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold transition-all">
                </div>
            </div>

            <div>
                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Kertas Kerja (PDF/Imej)</label>
                
                <div class="space-y-4">
                    <div class="relative group border border-dashed border-slate-200 hover:border-kebana-blue bg-slate-50/50 p-6 text-center transition-all cursor-pointer rounded overflow-hidden" id="widget_sub_proposal_zone">
                        <i class="fa-solid fa-cloud-arrow-up text-3xl text-slate-300 group-hover:text-kebana-blue mb-2 block transition-colors" id="widget_sub_proposal_icon"></i>
                        <input type="file" name="proposal_file" id="widget_sub_proposal_input" accept=".pdf,.jpg,.jpeg,.png" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                        <p id="widget_sub_proposal_label" class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Klik atau seret fail ke sini</p>
                        <p class="text-[8px] text-slate-300 font-bold uppercase mt-0.5 italic">PDF, JPG, JPEG, PNG (Maks: 10MB)</p>
                    </div>
                    
                    <!-- File Preview Container -->
                    <div id="widget_sub_proposal_preview" class="hidden p-3 bg-slate-50 border border-slate-200 justify-between items-center rounded transition-all">
                        <div class="flex items-center space-x-3">
                            <div id="widget_sub_preview_thumbnail" class="w-10 h-10 bg-white flex items-center justify-center text-kebana-blue rounded border border-slate-100 overflow-hidden font-bold shadow-sm">
                                <!-- Thumbnail generated via JS -->
                            </div>
                            <div>
                                <p id="widget_sub_preview_name" class="text-[11px] font-black text-slate-800 uppercase italic truncate max-w-[180px] md:max-w-[280px]">file_name.pdf</p>
                                <p id="widget_sub_preview_size" class="text-[8px] font-bold text-slate-400 uppercase mt-0.5">1.2 MB</p>
                            </div>
                        </div>
                        <button type="button" id="widget_sub_proposal_clear" class="w-7 h-7 flex items-center justify-center bg-red-50 text-red-500 hover:bg-red-500 hover:text-white rounded-full transition-all shadow-sm" title="Batal pilihan">
                            <i class="fa-solid fa-trash-can text-xs"></i>
                        </button>
                    </div>
                </div>
                
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        setupFileInputWidget(
                            'widget_sub_proposal_input', 
                            'widget_sub_proposal_zone', 
                            'widget_sub_proposal_label', 
                            'widget_sub_proposal_icon', 
                            'widget_sub_proposal_preview', 
                            'widget_sub_preview_thumbnail', 
                            'widget_sub_preview_name', 
                            'widget_sub_preview_size', 
                            'widget_sub_proposal_clear'
                        );
                    });
                </script>
            </div>

            <div>
                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Objektif & Keterangan</label>
                <textarea name="objective" rows="4"
                          class="w-full px-5 py-3 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold transition-all"
                          placeholder="Ringkasan objektif program..."></textarea>
            </div>

            <!-- Action selection cards -->
            <div class="space-y-3 pt-4 border-t border-slate-100">
                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Tindakan Pendaftaran <span class="text-red-500">*</span></label>
                
                <div class="grid grid-cols-1 gap-4">
                    <!-- Option 1: Draft -->
                    <label for="sub_submission_draft" class="relative block bg-slate-50 border border-slate-200 p-4 cursor-pointer transition-all hover:bg-slate-50/80 rounded group" id="sub_card_draft">
                        <input type="radio" name="submission_type" id="sub_submission_draft" value="draft" checked class="sr-only" onchange="updateSubSubmissionTypeSelection()">
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 bg-white border border-slate-100 flex items-center justify-center text-slate-400 group-hover:text-kebana-blue transition-colors shadow-sm" id="sub_icon_container_draft">
                                <i class="fa-solid fa-file-signature text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-[11px] font-black text-slate-800 uppercase tracking-wide">Simpan Sebagai Draf</p>
                                <p class="text-[8px] text-slate-400 font-bold uppercase leading-tight mt-0.5">
                                    Simpan sebagai draf di cawangan anda. Anda boleh mengemaskini butiran sebelum dihantar.
                                </p>
                            </div>
                        </div>
                        <!-- Checkmark Indicator -->
                        <div class="absolute top-3 right-3 w-4 h-4 bg-kebana-blue text-white rounded-full flex items-center justify-center opacity-0 transition-opacity" id="sub_check_draft">
                            <i class="fa-solid fa-check text-[8px]"></i>
                        </div>
                    </label>

                    <!-- Option 2: Submit Directly -->
                    <label for="sub_submission_submit" class="relative block bg-slate-50 border border-slate-200 p-4 cursor-pointer transition-all hover:bg-slate-50/80 rounded group" id="sub_card_submit">
                        <input type="radio" name="submission_type" id="sub_submission_submit" value="submit" class="sr-only" onchange="updateSubSubmissionTypeSelection()">
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 bg-white border border-slate-100 flex items-center justify-center text-slate-400 group-hover:text-kebana-blue transition-colors shadow-sm" id="sub_icon_container_submit">
                                <i class="fa-solid fa-paper-plane text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-[11px] font-black text-slate-800 uppercase tracking-wide">Hantar Terus ke Pengerusi</p>
                                <p class="text-[8px] text-slate-400 font-bold uppercase leading-tight mt-0.5">
                                    Daftar dan hantar terus untuk kelulusan Pengerusi Cawangan.
                                </p>
                            </div>
                        </div>
                        <!-- Checkmark Indicator -->
                        <div class="absolute top-3 right-3 w-4 h-4 bg-kebana-blue text-white rounded-full flex items-center justify-center opacity-0 transition-opacity" id="sub_check_submit">
                            <i class="fa-solid fa-check text-[8px]"></i>
                        </div>
                    </label>
                </div>
            </div>

            <script>
            function updateSubSubmissionTypeSelection() {
                const draftRadio = document.getElementById('sub_submission_draft');
                const submitRadio = document.getElementById('sub_submission_submit');
                
                const cardDraft = document.getElementById('sub_card_draft');
                const cardSubmit = document.getElementById('sub_card_submit');
                
                const checkDraft = document.getElementById('sub_check_draft');
                const checkSubmit = document.getElementById('sub_check_submit');
                
                const iconDraft = document.getElementById('sub_icon_container_draft');
                const iconSubmit = document.getElementById('sub_icon_container_submit');
                
                if (draftRadio.checked) {
                    // Highlight draft card
                    cardDraft.classList.remove('border-slate-200', 'bg-slate-50');
                    cardDraft.classList.add('border-kebana-blue', 'bg-white', 'shadow-md');
                    checkDraft.classList.remove('opacity-0');
                    checkDraft.classList.add('opacity-100');
                    iconDraft.classList.add('text-kebana-blue', 'border-kebana-blue/20');
                    
                    // De-highlight submit card
                    cardSubmit.classList.add('border-slate-200', 'bg-slate-50');
                    cardSubmit.classList.remove('border-kebana-blue', 'bg-white', 'shadow-md');
                    checkSubmit.classList.add('opacity-0');
                    checkSubmit.classList.remove('opacity-100');
                    iconSubmit.classList.remove('text-kebana-blue', 'border-kebana-blue/20');
                } else if (submitRadio.checked) {
                    // Highlight submit card
                    cardSubmit.classList.remove('border-slate-200', 'bg-slate-50');
                    cardSubmit.classList.add('border-kebana-blue', 'bg-white', 'shadow-md');
                    checkSubmit.classList.remove('opacity-0');
                    checkSubmit.classList.add('opacity-100');
                    iconSubmit.classList.add('text-kebana-blue', 'border-kebana-blue/20');
                    
                    // De-highlight draft card
                    cardDraft.classList.add('border-slate-200', 'bg-slate-50');
                    cardDraft.classList.remove('border-kebana-blue', 'bg-white', 'shadow-md');
                    checkDraft.classList.add('opacity-0');
                    checkDraft.classList.remove('opacity-100');
                    iconDraft.classList.remove('text-kebana-blue', 'border-kebana-blue/20');
                }
            }

            // Initialize on load or dynamic changes
            document.addEventListener('DOMContentLoaded', function() {
                updateSubSubmissionTypeSelection();
            });
            // Also run it immediately in case DOMContentLoaded already fired
            updateSubSubmissionTypeSelection();
            </script>

            <div class="pt-6">
                <button type="submit" class="w-full bg-kebana-blue text-white py-4 text-[11px] font-black uppercase tracking-[0.2em] hover:bg-kebana-accent transition-all shadow-xl">
                    DAFTAR & PROSES SUB-ACARA
                </button>
            </div>
        </form>
    </div>
</div>

<datalist id="kawasan_list">
    <?php foreach ($suggestions['kawasan'] as $kaw): ?>
    <option value="<?php echo htmlspecialchars($kaw); ?>">
    <?php endforeach; ?>
</datalist>

<datalist id="venue_list">
    <?php foreach ($suggestions['venues'] as $ven): ?>
    <option value="<?php echo htmlspecialchars($ven); ?>">
    <?php endforeach; ?>
</datalist>

<script>
function openSubEventDrawer() {
    const overlay = document.getElementById('subEventOverlay');
    const drawer = document.getElementById('subEventDrawer');
    overlay.classList.remove('opacity-0', 'pointer-events-none');
    overlay.classList.add('opacity-100');
    drawer.classList.remove('translate-x-full');
}

function closeSubEventDrawer() {
    const overlay = document.getElementById('subEventOverlay');
    const drawer = document.getElementById('subEventDrawer');
    overlay.classList.add('opacity-0', 'pointer-events-none');
    overlay.classList.remove('opacity-100');
    drawer.classList.add('translate-x-full');
}

document.getElementById('subEventOverlay')?.addEventListener('click', closeSubEventDrawer);

// View Sub-Event Logic
async function viewSubEvent(id) {
    const overlay = document.getElementById('viewSubEventOverlay');
    const drawer = document.getElementById('viewSubEventDrawer');
    const content = document.getElementById('viewSubEventContent');
    
    // Reset content and show loading
    content.innerHTML = '<div class="py-20 text-center"><i class="fa-solid fa-circle-notch fa-spin text-3xl text-kebana-blue"></i></div>';
    
    overlay.classList.remove('opacity-0', 'pointer-events-none');
    overlay.classList.add('opacity-100');
    drawer.classList.remove('translate-x-full');

    try {
        const response = await fetch(`<?= URL_ROOT ?>/modules/api/events.php?id=${id}`);
        const data = await response.json();

        if (data.error) {
            content.innerHTML = `<div class="p-8 bg-red-50 text-red-700 text-xs font-black uppercase tracking-widest">${data.error}</div>`;
            return;
        }

        content.innerHTML = `
            <div class="space-y-8">
                <div class="space-y-2">
                    <span class="px-3 py-1 bg-kebana-blue text-white text-[9px] font-black uppercase tracking-widest">${data.event_level} EVENT</span>
                    <h4 class="text-3xl font-black text-kebana-blue uppercase tracking-tighter leading-none italic">${data.event_title}</h4>
                </div>
                
                <div class="grid grid-cols-2 gap-6 pt-4">
                    <div class="space-y-1">
                        <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest">Tarikh</p>
                        <p class="text-sm font-black text-slate-700">${data.formatted_date}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest">Status</p>
                        <p class="text-sm font-black text-kebana-blue uppercase">${data.status}</p>
                    </div>
                </div>

                <div class="space-y-1">
                    <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest">Lokasi / Venue</p>
                    <p class="text-sm font-black text-slate-700">${data.venue} ${data.kawasan ? '· ' + data.kawasan : ''}</p>
                </div>

                <div class="space-y-1">
                    <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest">Anggaran Bajet</p>
                    <p class="text-xl font-black text-kebana-blue">RM ${data.formatted_budget}</p>
                </div>

                <div class="pt-6 border-t border-slate-100 space-y-3">
                    <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest">Objektif & Keterangan</p>
                    <div class="text-sm font-medium text-slate-600 leading-relaxed">
                        ${data.objective ? data.objective.replace(/\n/g, '<br>') : 'Tiada keterangan tambahan.'}
                    </div>
                </div>

                <div class="pt-10 flex gap-4">
                    <a href="<?= URL_ROOT ?>/events/view/${data.event_id}" class="flex-1 bg-kebana-dark text-white text-center py-4 text-[10px] font-black uppercase tracking-widest hover:bg-black transition-all">
                        LIHAT PROFIL PENUH
                    </a>
                    <button onclick="closeViewSubEventDrawer()" class="px-8 py-4 border border-slate-200 text-[10px] font-black text-slate-400 uppercase tracking-widest hover:bg-slate-50 transition-all">
                        TUTUP
                    </button>
                </div>
            </div>
        `;
    } catch (err) {
        content.innerHTML = `<div class="p-8 bg-red-50 text-red-700 text-xs font-black uppercase tracking-widest">Gagal memuat turun data.</div>`;
    }
}

function closeViewSubEventDrawer() {
    const overlay = document.getElementById('viewSubEventOverlay');
    const drawer = document.getElementById('viewSubEventDrawer');
    overlay.classList.add('opacity-0', 'pointer-events-none');
    overlay.classList.remove('opacity-100');
    drawer.classList.add('translate-x-full');
}

document.getElementById('viewSubEventOverlay')?.addEventListener('click', closeViewSubEventDrawer);
</script>

<!-- View Sub-Event Drawer UI -->
<div id="viewSubEventOverlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[110] opacity-0 pointer-events-none transition-opacity duration-300"></div>
<div id="viewSubEventDrawer" class="fixed top-0 right-0 h-full w-full max-w-[550px] bg-white z-[111] translate-x-full transition-transform duration-500 ease-in-out shadow-2xl overflow-y-auto">
    <div class="p-10">
        <div class="flex justify-between items-center border-b border-slate-100 pb-6 mb-10">
            <div>
                <h3 class="text-xs font-black text-kebana-blue uppercase tracking-[0.3em]">Perincian Sub-Acara</h3>
            </div>
            <button onclick="closeViewSubEventDrawer()" class="w-10 h-10 flex items-center justify-center text-slate-300 hover:text-red-500 transition-colors">
                <i class="fa-solid fa-xmark text-2xl"></i>
            </button>
        </div>
        <div id="viewSubEventContent">
            <!-- Content loaded via AJAX -->
        </div>
    </div>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'sub_added'): ?>
<!-- Premium Success Pop-out Modal (Glassmorphism + SVG Stroke Draw Tick Animation) -->
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
                Sub-Acara Berjaya Ditambah
            </p>
        </div>
        
        <div class="text-xs font-medium text-slate-500 leading-relaxed uppercase tracking-wider bg-slate-50/50 p-4 border border-slate-100">
            Kertas kerja & perincian sub-acara telah selamat didaftarkan ke dalam sistem.
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
