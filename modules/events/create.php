<?php
/**
 * KEBANA Digital Management System - Create Event (MYDS Inspired)
 * File: modules/events/create.php
 */

use App\Helpers\EventsHelper;

require_once APP_ROOT . '/includes/header.php';

if (!hasRole([1, 4, 33, 888])) {
    echo "<div class='p-12 text-center'><h1 class='text-2xl font-black text-red-600 uppercase tracking-widest'>AKSES DISEKAT</h1></div>";
    require_once APP_ROOT . '/includes/footer.php';
    exit;
}

$current_role = (int)($_SESSION['role'] ?? 0);
$current_user_id = (int)($_SESSION['user_id'] ?? 0);
$current_cawangan_id = isset($_SESSION['cawangan_id']) ? (int)$_SESSION['cawangan_id'] : null;

// Determine if creator is Pusat (can create Master Events)
$is_pusat_creator = in_array($current_role, [888, 1, 4]) && $current_cawangan_id === null;

$cawangan_options = EventsHelper::getAllCawangan();
$master_event_options = [];
if (!$is_pusat_creator && $current_cawangan_id !== null) {
    $master_event_options = EventsHelper::getMasterEventsByCawangan($current_cawangan_id);
}

$suggestions = EventsHelper::getUniqueLocations();

$message = '';
$message_type = '';
$preselected_parent_id = isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : 0;

if ($preselected_parent_id > 0 && empty($master_event_options) && !$is_pusat_creator) {
    // If we have a preselected ID but it's not in the cawangan list, 
    // we should still allow it if it's a valid master event for this cawangan
    $db = \App\Core\Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT event_id, event_title FROM tbl_event WHERE event_id = ? AND event_level = 'MASTER' AND (cawangan_id = ? OR cawangan_id IS NULL)");
    $stmt->bind_param("ii", $preselected_parent_id, $current_cawangan_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $master_event_options[] = $row;
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = EventsHelper::addEvent($_POST, $current_user_id, $is_pusat_creator, $current_cawangan_id);

    if ($event_id) {
        $message = 'Acara berjaya didaftarkan.';
        $message_type = 'success';

        // Optional proposal upload
        if (isset($_FILES['proposal_file']) && $_FILES['proposal_file']['error'] === UPLOAD_ERR_OK) {
            EventsHelper::handleDocumentUpload($event_id, $_FILES['proposal_file'], $current_user_id);
        }

        echo '<script>setTimeout(function(){ window.location.href = "' . URL_ROOT . '/events"; }, 1500);</script>';
    } else {
        $message = 'Gagal mendaftar acara. Sila pastikan semua maklumat wajib diisi.';
        $message_type = 'error';
    }
}

$page_title = 'DAFTAR ACARA';
?>

<div class="max-w-4xl mx-auto space-y-12 pb-24">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 border-t-8 border-kebana-blue shadow-sm">
        <div>
            <h2 class="text-2xl font-black text-kebana-blue uppercase tracking-tight italic">Daftar Acara Baru</h2>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Sila lengkapkan butiran program di bawah.</p>
        </div>
        <a href="<?= URL_ROOT ?>/events" class="text-[10px] font-black text-slate-400 hover:text-kebana-blue uppercase tracking-widest flex items-center transition-colors">
            <i class="fa-solid fa-arrow-left mr-3"></i>
            KEMBALI
        </a>
    </div>

    <?php if ($message): ?>
    <div class="p-6 <?php echo $message_type === 'success' ? 'bg-green-50 text-green-700 border-l-4 border-green-600' : 'bg-red-50 text-red-700 border-l-4 border-red-600'; ?> font-bold text-xs uppercase tracking-widest animate-pulse">
        <?php echo $message; ?>
    </div>
    <?php endif; ?>

    <div class="bg-white p-12 border border-slate-100 shadow-xl">
        <form method="POST" enctype="multipart/form-data" class="space-y-10">
            <!-- Basic Info -->
            <div class="grid grid-cols-1 gap-10">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Tajuk Acara <span class="text-red-500">*</span></label>
                    <input type="text" name="event_title" required
                           class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all"
                           placeholder="Cth: Mesyuarat Agung Tahunan 2024">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <?php if ($is_pusat_creator): ?>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Tugaskan ke Cawangan <span class="text-red-500">*</span></label>
                    <select name="assigned_cawangan_id" required
                            class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all rounded-none appearance-none">
                        <option value="">-- Pilih Cawangan --</option>
                        <?php foreach ($cawangan_options as $caw): ?>
                        <option value="<?php echo $caw['cawangan_id']; ?>"><?php echo htmlspecialchars($caw['cawangan_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php else: ?>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Pilih Acara Induk (Master) <span class="text-red-500">*</span></label>
                    <select name="parent_master_event_id" required
                            class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all rounded-none appearance-none">
                        <option value="">-- Pilih Acara Master --</option>
                        <?php foreach ($master_event_options as $master): ?>
                        <option value="<?php echo $master['event_id']; ?>" <?php echo ($preselected_parent_id == $master['event_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($master['event_title']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Kawasan / Daerah <span class="text-red-500">*</span></label>
                    <input type="text" name="kawasan" required list="kawasan_list"
                           class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all"
                           placeholder="Cth: Samalaju / Kidurong">
                    <datalist id="kawasan_list">
                        <?php foreach ($suggestions['kawasan'] as $kaw): ?>
                        <option value="<?php echo htmlspecialchars($kaw); ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Lokasi / Venue <span class="text-red-500">*</span></label>
                    <input type="text" name="venue" required list="venue_list"
                           class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all"
                           placeholder="Cth: Dewan Komuniti Bintulu">
                    <datalist id="venue_list">
                        <?php foreach ($suggestions['venues'] as $ven): ?>
                        <option value="<?php echo htmlspecialchars($ven); ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Anggaran Bajet (RM)</label>
                    <input type="number" step="0.01" name="budget_est"
                           class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all"
                           placeholder="0.00">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Tarikh Mula <span class="text-red-500">*</span></label>
                    <input type="date" name="event_date" required
                           class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Tarikh Tamat (Opsional)</label>
                    <input type="date" name="event_end_date"
                           class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all">
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3"><?php echo $is_pusat_creator ? 'Muat Naik Guideline (PDF)' : 'Muat Naik Kertas Kerja (PDF/Imej)'; ?></label>
                
                <div class="space-y-4">
                    <div class="relative group border-2 border-dashed border-slate-200 hover:border-kebana-blue bg-slate-50/50 p-8 text-center transition-all cursor-pointer rounded overflow-hidden" id="widget_proposal_zone">
                        <i class="fa-solid fa-cloud-arrow-up text-4xl text-slate-300 group-hover:text-kebana-blue mb-3 block transition-colors" id="widget_proposal_icon"></i>
                        <input type="file" name="proposal_file" id="widget_proposal_input" accept=".pdf,.jpg,.jpeg,.png" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                        <p id="widget_proposal_label" class="text-xs font-black text-slate-400 uppercase tracking-widest">Klik atau seret fail ke sini</p>
                        <p class="text-[8px] text-slate-300 font-bold uppercase mt-1 italic">PDF, JPG, JPEG, PNG (Maks: 10MB)</p>
                    </div>
                    
                    <!-- File Preview Container -->
                    <div id="widget_proposal_preview" class="hidden p-4 bg-slate-50 border border-slate-200 justify-between items-center rounded transition-all">
                        <div class="flex items-center space-x-4">
                            <div id="widget_preview_thumbnail" class="w-12 h-12 bg-white flex items-center justify-center text-kebana-blue rounded border border-slate-100 overflow-hidden font-bold shadow-sm">
                                <!-- Thumbnail generated via JS -->
                            </div>
                            <div>
                                <p id="widget_preview_name" class="text-xs font-black text-slate-800 uppercase italic truncate max-w-[250px] md:max-w-[400px]">file_name.pdf</p>
                                <p id="widget_preview_size" class="text-[9px] font-bold text-slate-400 uppercase mt-1">1.2 MB</p>
                            </div>
                        </div>
                        <button type="button" id="widget_proposal_clear" class="w-8 h-8 flex items-center justify-center bg-red-50 text-red-500 hover:bg-red-500 hover:text-white rounded-full transition-all shadow-sm" title="Batal pilihan">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </div>
                
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        setupFileInputWidget(
                            'widget_proposal_input', 
                            'widget_proposal_zone', 
                            'widget_proposal_label', 
                            'widget_proposal_icon', 
                            'widget_proposal_preview', 
                            'widget_preview_thumbnail', 
                            'widget_preview_name', 
                            'widget_preview_size', 
                            'widget_proposal_clear'
                        );
                    });
                </script>
            </div>

            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Objektif & Keterangan</label>
                <textarea name="objective" rows="4"
                          class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all"
                          placeholder="Sila nyatakan objektif program, kumpulan sasaran, atau keterangan ringkas..."></textarea>
            </div>

            <div class="pt-10">
                <button type="submit" class="w-full bg-kebana-blue text-white py-6 text-xs font-black uppercase tracking-[0.3em] hover:bg-kebana-accent transition-all shadow-2xl">
                    <?php echo $is_pusat_creator ? 'DAFTAR ACARA MASTER' : 'DAFTAR ACARA SUB'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
