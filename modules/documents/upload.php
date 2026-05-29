<?php
/**
 * KEBANA Digital Management System - Direct File Upload (MYDS Inspired)
 * File: modules/documents/upload.php
 */

use App\Helpers\DocumentsHelper;
use App\Helpers\EventsHelper;

require_once APP_ROOT . '/includes/header.php';

if (!hasRole([888, 1, 2, 3, 11, 22, 4, 33, 6, 55, 7, 66])) {
    echo "<div class='p-12 text-center'><h1 class='text-2xl font-black text-red-600 uppercase tracking-widest'>AKSES DISEKAT</h1></div>";
    require_once APP_ROOT . '/includes/footer.php';
    exit;
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['doc_file'])) {
        $error = $_FILES['doc_file']['error'];
        
        if ($error === UPLOAD_ERR_OK) {
            $userId = (int)($_SESSION['user_id'] ?? 0);
            $eventId = !empty($_POST['event_id']) ? (int)$_POST['event_id'] : null;
            $tags = trim($_POST['tags'] ?? '');
            
            if (DocumentsHelper::uploadDocument($_FILES['doc_file'], $userId, $eventId, $tags)) {
                $message = 'Fail berjaya dimuat naik dan diarkibkan.';
                $message_type = 'success';
                echo '<script>setTimeout(function(){ window.location.href = "' . URL_ROOT . '/documents"; }, 1500);</script>';
            } else {
                $message = 'Gagal memuat naik fail. Sila pastikan format fail dibenarkan (PDF, JPG, PNG, DOCX, XLSX).';
                $message_type = 'error';
            }
        } else {
            switch ($error) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $message = 'Saiz fail melebihi had yang dibenarkan.';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $message = 'Fail hanya dimuat naik sebahagian sahaja.';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $message = 'Sila pilih fail untuk dimuat naik.';
                    break;
                default:
                    $message = 'Ralat sistem berlaku semasa memuat naik fail.';
                    break;
            }
            $message_type = 'error';
        }
    }
}

$events = EventsHelper::getAllEvents();
$page_title = 'MUAT NAIK FAIL';
?>

<div class="max-w-4xl mx-auto space-y-12 pb-24">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 border border-slate-300 border-t-8 border-t-kebana-blue shadow-sm">
        <div>
            <h2 class="text-2xl font-black text-kebana-blue uppercase tracking-tight italic">Muat Naik Dokumen</h2>
            <p class="text-xs font-black text-slate-500 uppercase tracking-widest mt-2">Simpan fail ke dalam arkib berpusat dengan sistem tagging.</p>
        </div>
        <a href="<?= URL_ROOT ?>/documents" class="text-xs font-black text-slate-600 hover:text-kebana-blue uppercase tracking-widest flex items-center transition-colors">
            <i class="fa-solid fa-arrow-left mr-3"></i>
            KEMBALI KE ARKIB
        </a>
    </div>

    <?php if ($message): ?>
    <div class="p-6 <?php echo $message_type === 'success' ? 'bg-green-50 text-green-900 border-l-4 border-green-600' : 'bg-red-50 text-red-900 border-l-4 border-red-600'; ?> font-black text-sm uppercase tracking-widest animate-pulse">
        <?php echo $message; ?>
    </div>
    <?php endif; ?>

    <div class="bg-white p-12 border border-slate-300 shadow-xl">
        <form method="POST" enctype="multipart/form-data" class="space-y-12">
            <!-- File Input -->
            <div class="space-y-4">
                <label class="block text-xs font-black text-slate-600 uppercase tracking-widest mb-4 text-center">Pilih Fail Untuk Diarkibkan</label>
                
                <div class="relative group border-4 border-dashed border-slate-300 p-12 text-center hover:border-kebana-blue/30 transition-all cursor-pointer relative overflow-hidden" id="drop-zone">
                    <i id="upload-icon" class="fa-solid fa-cloud-arrow-up text-6xl text-slate-300 group-hover:text-kebana-blue/20 mb-6 block transition-colors"></i>
                    <input type="file" name="doc_file" id="doc_file" required class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                    <p id="file-label" class="text-sm font-black text-slate-500 uppercase tracking-widest">Klik atau seret fail ke sini</p>
                    <p class="text-xs text-slate-500 font-bold uppercase mt-2 italic">PDF, JPG, PNG, DOCX, XLSX (Maks: 10MB)</p>
                </div>
                
                <!-- File Preview Container -->
                <div id="widget_doc_preview" class="hidden p-6 bg-slate-50 border border-slate-350 justify-between items-center transition-all">
                    <div class="flex items-center space-x-4">
                        <div id="widget_doc_thumbnail" class="w-16 h-16 bg-white flex items-center justify-center text-kebana-blue rounded border border-slate-300 overflow-hidden font-bold shadow-sm">
                            <!-- Thumbnail generated via JS -->
                        </div>
                        <div>
                            <p id="widget_doc_name" class="text-sm font-black text-slate-800 uppercase italic truncate max-w-[250px] md:max-w-[450px]">file_name.pdf</p>
                            <p id="widget_doc_size" class="text-xs font-bold text-slate-500 uppercase mt-1">1.2 MB</p>
                        </div>
                    </div>
                    <button type="button" id="widget_doc_clear" class="w-10 h-10 flex items-center justify-center bg-red-50 text-red-500 hover:bg-red-500 hover:text-white rounded-full transition-all shadow-sm border border-red-300" title="Batal pilihan">
                        <i class="fa-solid fa-trash-can text-lg"></i>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <!-- Tags -->
                <div>
                    <label class="block text-xs font-black text-slate-600 uppercase tracking-widest mb-3">Tag Dokumen (Asingkan dengan koma)</label>
                    <input type="text" name="tags" placeholder="Cth: Laporan, Kewangan, 2026"
                           class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-300 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold uppercase transition-all">
                </div>
                <!-- Project Link -->
                <div>
                    <label class="block text-xs font-black text-slate-600 uppercase tracking-widest mb-3">Pautkan ke Projek (Opsional)</label>
                    <select name="event_id" class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-300 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold uppercase transition-all rounded-none appearance-none">
                        <option value="">-- Tiada Projek Khusus --</option>
                        <?php foreach ($events as $ev): ?>
                        <option value="<?php echo $ev['event_id']; ?>"><?php echo htmlspecialchars($ev['event_title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="pt-10">
                <button type="submit" class="w-full bg-kebana-blue text-white py-6 text-sm font-black uppercase tracking-[0.3em] hover:bg-kebana-accent transition-all shadow-2xl">
                    MUAT NAIK SEKARANG
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    setupFileInputWidget(
        'doc_file', 
        'drop-zone', 
        'file-label', 
        'upload-icon', 
        'widget_doc_preview', 
        'widget_doc_thumbnail', 
        'widget_doc_name', 
        'widget_doc_size', 
        'widget_doc_clear'
    );
});
</script>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
