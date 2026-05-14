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
                echo '<script>setTimeout(function(){ window.location.href = "/kebana-digital/documents"; }, 1500);</script>';
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
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 border-t-8 border-kebana-blue shadow-sm">
        <div>
            <h2 class="text-2xl font-black text-kebana-blue uppercase tracking-tight italic">Muat Naik Dokumen</h2>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Simpan fail ke dalam arkib berpusat dengan sistem tagging.</p>
        </div>
        <a href="/kebana-digital/documents" class="text-[10px] font-black text-slate-400 hover:text-kebana-blue uppercase tracking-widest flex items-center transition-colors">
            <i class="fa-solid fa-arrow-left mr-3"></i>
            KEMBALI KE ARKIB
        </a>
    </div>

    <?php if ($message): ?>
    <div class="p-6 <?php echo $message_type === 'success' ? 'bg-green-50 text-green-700 border-l-4 border-green-600' : 'bg-red-50 text-red-700 border-l-4 border-red-600'; ?> font-bold text-xs uppercase tracking-widest animate-pulse">
        <?php echo $message; ?>
    </div>
    <?php endif; ?>

    <div class="bg-white p-12 border border-slate-100 shadow-xl">
        <form method="POST" enctype="multipart/form-data" class="space-y-12">
            <!-- File Input -->
            <div class="relative group">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 text-center">Pilih Fail Untuk Diarkibkan</label>
                <div id="drop-zone" class="border-4 border-dashed border-slate-100 p-12 text-center group-hover:border-kebana-blue/30 transition-all cursor-pointer relative">
                    <i id="upload-icon" class="fa-solid fa-cloud-arrow-up text-6xl text-slate-100 group-hover:text-kebana-blue/20 mb-6 block transition-colors"></i>
                    <input type="file" name="doc_file" id="doc_file" required class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                    <p id="file-label" class="text-xs font-black text-slate-400 uppercase tracking-widest">Klik atau seret fail ke sini</p>
                    <p class="text-[8px] text-slate-300 font-bold uppercase mt-2 italic">PDF, JPG, PNG, DOCX, XLSX (Maks: 10MB)</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <!-- Tags -->
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Tag Dokumen (Asingkan dengan koma)</label>
                    <input type="text" name="tags" placeholder="Cth: Laporan, Kewangan, 2026"
                           class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold uppercase transition-all">
                </div>
                <!-- Project Link -->
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Pautkan ke Projek (Opsional)</label>
                    <select name="event_id" class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold uppercase transition-all rounded-none appearance-none">
                        <option value="">-- Tiada Projek Khusus --</option>
                        <?php foreach ($events as $ev): ?>
                        <option value="<?php echo $ev['event_id']; ?>"><?php echo htmlspecialchars($ev['event_title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="pt-10">
                <button type="submit" class="w-full bg-kebana-blue text-white py-6 text-xs font-black uppercase tracking-[0.3em] hover:bg-kebana-accent transition-all shadow-2xl">
                    MUAT NAIK SEKARANG
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('doc_file').addEventListener('change', function(e) {
    const fileLabel = document.getElementById('file-label');
    const uploadIcon = document.getElementById('upload-icon');
    const dropZone = document.getElementById('drop-zone');
    
    if (this.files && this.files[0]) {
        const fileName = this.files[0].name;
        fileLabel.textContent = "FAIL DIPILIH: " + fileName;
        fileLabel.classList.remove('text-slate-400');
        fileLabel.classList.add('text-kebana-blue', 'font-black');
        
        uploadIcon.classList.remove('text-slate-100');
        uploadIcon.classList.add('text-kebana-blue', 'opacity-50');
        
        dropZone.classList.add('border-kebana-blue', 'bg-slate-50');
    } else {
        fileLabel.textContent = "Klik atau seret fail ke sini";
        fileLabel.classList.add('text-slate-400');
        fileLabel.classList.remove('text-kebana-blue', 'font-black');
        
        uploadIcon.classList.add('text-slate-100');
        uploadIcon.classList.remove('text-kebana-blue', 'opacity-50');
        
        dropZone.classList.remove('border-kebana-blue', 'bg-slate-50');
    }
});
</script>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
