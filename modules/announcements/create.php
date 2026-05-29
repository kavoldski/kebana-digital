<?php
/**
 * KEBANA Digital Management System - Create Announcement
 * File: modules/announcements/create.php
 * 
 */

use App\Helpers\AnnouncementHelper;

require_once APP_ROOT . '/includes/header.php';

// Only allow Setiausaha Pusat, Super Admin, Presiden
if (!in_array($current_role, [888, 1, 4])) {
    echo "<div class='p-12 text-center'><h1 class='text-2xl font-black text-red-600 uppercase tracking-widest'>AKSES DISEKAT</h1></div>";
    require_once APP_ROOT . '/includes/footer.php';
    exit;
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Detect silent PHP post_max_size exceeded: PHP drops $_POST & $_FILES entirely
    if (empty($_POST) && isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
        $message = 'Jumlah saiz gambar terlalu besar. Sila muat naik gambar yang lebih kecil (maksimum 10MB setiap satu, 25MB keseluruhannya).';
        $message_type = 'error';
    } else {
        try {
            $ann_id = AnnouncementHelper::addAnnouncement($_POST, $current_user_id);

            if ($ann_id) {
                $upload_error = null;

                if (!empty($_FILES['announcement_images']['name'][0])) {
                    $upload_result = AnnouncementHelper::uploadAnnouncementImages($ann_id, $_FILES['announcement_images']);
                    if ($upload_result !== true) {
                        // uploadAnnouncementImages returns a string message on failure
                        $upload_error = is_string($upload_result) ? $upload_result
                            : 'Gagal memuat naik gambar. Sila pastikan format betul (JPG, PNG, WEBP, GIF) dan saiz tidak melebihi 10MB setiap satu.';
                    }
                }

                if ($upload_error === null) {
                    // Full success
                    echo '<script>window.location.href = "' . URL_ROOT . '/announcements?msg=success";</script>';
                    exit;
                } else {
                    // Announcement created but image upload failed — show error but keep the record
                    $message = 'Hebahan disimpan, tetapi: ' . $upload_error . ' Anda boleh menambah gambar melalui halaman kemaskini.';
                    $message_type = 'error';
                }
            } else {
                $message = 'Gagal menyimpan hebahan. Sila cuba lagi.';
                $message_type = 'error';
            }
        } catch (\Throwable $e) {
            $message = 'Ralat sistem: ' . htmlspecialchars($e->getMessage()) . '. Sila hubungi pentadbir.';
            $message_type = 'error';
        }
    }
}

$page_title = 'TAMBAH HEBAHAN';
?>
<div class="max-w-4xl mx-auto space-y-12 pb-24">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 border border-slate-300 border-t-8 border-t-kebana-blue shadow-sm">
        <div>
            <h2 class="text-2xl font-black text-kebana-blue uppercase tracking-tight italic">Hebahan Baru</h2>
            <p class="text-xs font-black text-slate-500 uppercase tracking-widest mt-2">Cipta hebahan untuk paparan umum</p>
        </div>
        <a href="<?= URL_ROOT ?>/announcements" class="text-xs font-black text-slate-600 hover:text-kebana-blue uppercase tracking-widest flex items-center transition-colors">
            <i class="fa-solid fa-arrow-left mr-3"></i> KEMBALI
        </a>
    </div>

    <?php if ($message): ?>
    <div class="p-6 bg-red-50 text-red-900 border-l-4 border-red-600 font-bold text-sm uppercase tracking-widest animate-fade-in">
        <?php echo $message; ?>
    </div>
    <?php endif; ?>

    <div class="bg-white p-12 border border-slate-300 shadow-xl">
        <form method="POST" enctype="multipart/form-data" class="space-y-10" id="announcementForm">
            <div>
                <label class="block text-xs font-black text-slate-600 uppercase tracking-widest mb-3">Tajuk Hebahan <span class="text-red-500">*</span></label>
                <input type="text" name="title" required
                       class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-300 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all"
                       placeholder="Cth: Mesyuarat Agung Tahunan ke-42">
            </div>

            <div>
                <div class="flex justify-between items-center mb-3">
                    <label class="block text-xs font-black text-slate-600 uppercase tracking-widest">Kandungan <span class="text-red-500">*</span></label>
                    <button type="button" id="btnOpenAIModal" class="inline-flex items-center space-x-2 text-xs font-black text-kebana-blue hover:text-white hover:bg-kebana-blue transition-all bg-kebana-blue/5 px-4 py-2 border border-kebana-blue/30 rounded-full focus:outline-none shadow-sm">
                        <i class="fa-solid fa-wand-magic-sparkles text-amber-500"></i>
                        <span>✨ Jana dengan AI</span>
                    </button>
                </div>
                <textarea name="content" id="announcementContent" required rows="8"
                          class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-300 focus:border-kebana-blue focus:bg-white outline-none text-sm transition-all"
                          placeholder="Tulis butiran hebahan di sini..."></textarea>
            </div>

            <div>
                <label class="block text-xs font-black text-slate-600 uppercase tracking-widest mb-3">Muat Naik Gambar Hebahan (Maksimum 5 Gambar)</label>
                <div id="dropzone" class="border-2 border-dashed border-slate-300 rounded-[1.5rem] bg-slate-50/50 p-10 text-center cursor-pointer hover:border-kebana-blue hover:bg-slate-50 transition-all flex flex-col items-center justify-center min-h-[180px] group">
                    <div class="w-12 h-12 bg-white rounded-2xl shadow-sm flex items-center justify-center mb-4 text-slate-400 group-hover:text-kebana-blue transition-colors border border-slate-300">
                        <i class="fa-regular fa-image text-2xl"></i>
                    </div>
                    <p class="text-xs font-black text-slate-600 uppercase tracking-wider mb-2">Seret & letak fail di sini atau klik untuk memilih</p>
                    <p class="text-xs text-slate-500 italic">Format dibenarkan: JPG, JPEG, PNG, WEBP, GIF. Maksimum 5 fail.</p>
                    <input type="file" name="announcement_images[]" id="fileInput" multiple accept="image/*" class="hidden">
                </div>
                
                <!-- Previews Container -->
                <div id="previewsContainer" class="grid grid-cols-2 sm:grid-cols-5 gap-4 mt-6"></div>
            </div>

            <div>
                <label class="block text-xs font-black text-slate-600 uppercase tracking-widest mb-3">Status Penyiaran <span class="text-red-500">*</span></label>
                <select name="status" required class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-300 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all rounded-none appearance-none">
                    <option value="Active">Aktif (Papar serta-merta)</option>
                    <option value="Draft">Draf (Simpan dahulu)</option>
                    <option value="Inactive">Tidak Aktif (Sembunyikan)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-black text-slate-600 uppercase tracking-widest mb-3">Tarikh & Masa Luput (Opsional)</label>
                <input type="datetime-local" name="expires_at"
                       class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-300 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all">
                <p class="text-xs text-slate-500 mt-2 italic">* Biarkan kosong jika tiada had masa.</p>
            </div>

            <div class="pt-10">
                <button type="submit" class="w-full bg-kebana-blue text-white py-6 text-sm font-black uppercase tracking-[0.3em] hover:bg-kebana-accent transition-all shadow-2xl">
                    SIMPAN HEBAHAN
                </button>
            </div>
        </form>
    </div>
</div>

<!-- AI Generator Modal -->
<div id="aiModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden">
    <div class="bg-white w-full max-w-2xl shadow-2xl border border-slate-300 border-t-8 border-t-kebana-blue flex flex-col max-h-[90vh] rounded-none">
        <!-- Modal Header -->
        <div class="p-8 border-b border-slate-300 flex justify-between items-center bg-slate-50">
            <div>
                <h3 class="text-lg font-black text-kebana-blue uppercase tracking-tight italic">Rangka Teks dengan AI</h3>
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mt-1">Pembantu AI Pintar KEBANA Digital</p>
            </div>
            <button type="button" id="btnCloseAIModal" class="text-slate-500 hover:text-slate-700 focus:outline-none">
                <i class="fa-solid fa-xmark text-2xl"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div class="p-10 space-y-8 overflow-y-auto">
            <div>
                <label class="block text-xs font-black text-slate-600 uppercase tracking-widest mb-3">Apakah topik atau isi utama hebahan anda? <span class="text-red-500">*</span></label>
                <textarea id="aiPrompt" rows="4" class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-300 focus:border-kebana-blue focus:bg-white outline-none text-sm transition-all"
                          placeholder="Cth: Mesyuarat Agung Tahunan Cawangan Kuching pada 15 Jun 2026 jam 2:00 petang di Hotel Hilton Kuching. Semua ahli dijemput hadir. Agenda utama adalah pembubaran jawatankuasa lama dan pemilihan jawatankuasa baru."></textarea>
            </div>
            
            <div>
                <label class="block text-xs font-black text-slate-600 uppercase tracking-widest mb-3">Nada / Gaya Penulisan</label>
                <div class="grid grid-cols-3 gap-4">
                    <label class="cursor-pointer">
                        <input type="radio" name="ai_tone" value="Professional" checked class="peer hidden">
                        <div class="p-4 text-center border-2 border-slate-300 peer-checked:border-kebana-blue peer-checked:bg-kebana-blue/5 text-xs font-black uppercase tracking-wider text-slate-655 peer-checked:text-kebana-blue transition-all">
                            👔 Professional
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="ai_tone" value="Mesra / Kasual" class="peer hidden">
                        <div class="p-4 text-center border-2 border-slate-300 peer-checked:border-kebana-blue peer-checked:bg-kebana-blue/5 text-xs font-black uppercase tracking-wider text-slate-655 peer-checked:text-kebana-blue transition-all">
                            🤝 Mesra / Kasual
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="ai_tone" value="Hebahan Rasmi" class="peer hidden">
                        <div class="p-4 text-center border-2 border-slate-300 peer-checked:border-kebana-blue peer-checked:bg-kebana-blue/5 text-xs font-black uppercase tracking-wider text-slate-655 peer-checked:text-kebana-blue transition-all">
                            📢 Rasmi / Notis
                        </div>
                    </label>
                </div>
            </div>
            
            <!-- AI Output / Loading -->
            <div id="aiOutputContainer" class="hidden">
                <label class="block text-xs font-black text-slate-600 uppercase tracking-widest mb-3">Draf Hasil AI</label>
                <div id="aiLoader" class="hidden py-12 text-center flex flex-col items-center justify-center space-y-4">
                    <div class="w-10 h-10 border-4 border-slate-300 border-t-kebana-blue rounded-full animate-spin"></div>
                    <p class="text-xs font-black text-kebana-blue uppercase tracking-widest animate-pulse">AI sedang merangka kandungan...</p>
                </div>
                <textarea id="aiOutputText" rows="6" readonly class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-300 outline-none text-sm transition-all focus:bg-white focus:border-kebana-blue"></textarea>
            </div>
        </div>
        
        <!-- Modal Footer -->
        <div class="p-8 border-t border-slate-300 bg-slate-50 flex justify-end space-x-4">
            <button type="button" id="btnCancelAI" class="px-6 py-3 border border-slate-300 hover:border-slate-400 text-slate-600 text-xs font-black uppercase tracking-widest transition-all">Batal</button>
            <button type="button" id="btnGenerateAI" class="px-8 py-3 bg-kebana-blue hover:bg-kebana-accent text-white text-xs font-black uppercase tracking-widest transition-all shadow-lg shadow-kebana-blue/20">Jana Kandungan</button>
            <button type="button" id="btnUseText" class="px-8 py-3 bg-green-600 hover:bg-green-700 text-white text-xs font-black uppercase tracking-widest transition-all shadow-lg shadow-green-600/20 hidden">Gunakan Teks Ini</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Drag and Drop File Uploader ---
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('fileInput');
    const previewsContainer = document.getElementById('previewsContainer');
    let selectedFiles = [];

    dropzone.addEventListener('click', (e) => {
        // Only trigger file picker if clicking the dropzone itself, not bubbled events
        if (e.target === dropzone || e.target.closest('#dropzone') === dropzone) {
            e.stopPropagation();
            fileInput.click();
        }
    });

    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.classList.add('border-kebana-blue', 'bg-slate-100');
    });

    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('border-kebana-blue', 'bg-slate-100');
    });

    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('border-kebana-blue', 'bg-slate-100');
        handleFiles(e.dataTransfer.files);
    });

    fileInput.addEventListener('change', (e) => {
        handleFiles(e.target.files);
    });

    const MAX_FILE_SIZE_MB = 10;
    const MAX_FILE_SIZE_BYTES = MAX_FILE_SIZE_MB * 1024 * 1024;

    function handleFiles(files) {
        const filesArray = Array.from(files);
        
        if (selectedFiles.length + filesArray.length > 5) {
            alert('Anda hanya boleh memuat naik maksimum 5 gambar.');
            return;
        }

        filesArray.forEach(file => {
            if (!file.type.startsWith('image/')) {
                alert('Hanya fail gambar dibenarkan.');
                return;
            }
            if (file.size > MAX_FILE_SIZE_BYTES) {
                alert(`Fail "${file.name}" terlalu besar (${(file.size / 1024 / 1024).toFixed(1)}MB). Maksimum ${MAX_FILE_SIZE_MB}MB setiap gambar.`);
                return;
            }
            selectedFiles.push(file);
            
            // Render preview card
            const reader = new FileReader();
            reader.onload = (e) => {
                const index = selectedFiles.length - 1;
                const previewCard = document.createElement('div');
                previewCard.className = 'relative group border border-slate-200 rounded-xl overflow-hidden aspect-video shadow-sm';
                previewCard.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                        <button type="button" class="btn-remove-preview w-8 h-8 rounded-full bg-red-600 text-white hover:bg-red-700 flex items-center justify-center shadow transition-transform transform hover:scale-110">
                            <i class="fa-solid fa-trash-can text-sm"></i>
                        </button>
                    </div>
                `;
                
                previewCard.querySelector('.btn-remove-preview').addEventListener('click', () => {
                    removeFile(file);
                    previewCard.remove();
                });
                
                previewsContainer.appendChild(previewCard);
            };
            reader.readAsDataURL(file);
        });

        updateFileInput();
    }

    function removeFile(fileToRemove) {
        selectedFiles = selectedFiles.filter(file => file !== fileToRemove);
        updateFileInput();
    }

    function updateFileInput() {
        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => dataTransfer.items.add(file));
        fileInput.files = dataTransfer.files;
    }

    // --- AI Modal Operations ---
    const aiModal = document.getElementById('aiModal');
    const btnOpenAIModal = document.getElementById('btnOpenAIModal');
    const btnCloseAIModal = document.getElementById('btnCloseAIModal');
    const btnCancelAI = document.getElementById('btnCancelAI');
    const btnGenerateAI = document.getElementById('btnGenerateAI');
    const btnUseText = document.getElementById('btnUseText');
    const aiPrompt = document.getElementById('aiPrompt');
    const aiOutputContainer = document.getElementById('aiOutputContainer');
    const aiLoader = document.getElementById('aiLoader');
    const aiOutputText = document.getElementById('aiOutputText');
    const announcementContent = document.getElementById('announcementContent');

    btnOpenAIModal.addEventListener('click', () => {
        aiModal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    });

    const closeModal = () => {
        aiModal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        // Reset state
        aiPrompt.value = '';
        aiOutputContainer.classList.add('hidden');
        aiOutputText.value = '';
        btnUseText.classList.add('hidden');
        btnGenerateAI.classList.remove('hidden');
    };

    btnCloseAIModal.addEventListener('click', closeModal);
    btnCancelAI.addEventListener('click', closeModal);

    btnGenerateAI.addEventListener('click', function() {
        const promptVal = aiPrompt.value.trim();
        if (!promptVal) {
            alert('Sila masukkan isi utama atau ringkasan hebahan.');
            return;
        }

        const toneRadio = document.querySelector('input[name="ai_tone"]:checked');
        const toneVal = toneRadio ? toneRadio.value : 'Professional';

        aiOutputContainer.classList.remove('hidden');
        aiLoader.classList.remove('hidden');
        aiOutputText.classList.add('hidden');
        btnGenerateAI.classList.add('hidden');
        btnUseText.classList.add('hidden');

        const formData = new FormData();
        formData.append('prompt', promptVal);
        formData.append('tone', toneVal);

        fetch('<?= URL_ROOT ?>/api/generate_ai', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            aiLoader.classList.add('hidden');
            aiOutputText.classList.remove('hidden');
            
            if (data.success) {
                aiOutputText.value = data.content;
                btnUseText.classList.remove('hidden');
            } else {
                aiOutputText.value = 'Ralat: ' + (data.error || 'Gagal menjana draf daripada AI.');
                btnGenerateAI.classList.remove('hidden');
            }
        })
        .catch(err => {
            aiLoader.classList.add('hidden');
            aiOutputText.classList.remove('hidden');
            aiOutputText.value = 'Ralat sambungan: Gagal menghubungi server.';
            btnGenerateAI.classList.remove('hidden');
        });
    });

    btnUseText.addEventListener('click', () => {
        announcementContent.value = aiOutputText.value;
        closeModal();
    });
});
</script>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
