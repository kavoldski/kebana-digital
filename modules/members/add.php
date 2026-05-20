<?php
/**
 * KEBANA Digital Management System - Add Member Form (MYDS Inspired)
 * File: modules/members/add.php
 */

$page_title = 'DAFTAR AHLI BARU';

use App\Helpers\MembersHelper;

require_once APP_ROOT . '/includes/header.php';

if (!hasRole([888, 1, 4, 33])) {
    echo "<div class='p-12 text-center'><h1 class='text-2xl font-black text-red-600 uppercase tracking-widest'>AKSES DISEKAT</h1></div>";
    require_once APP_ROOT . '/includes/footer.php';
    exit;
}

// Initialize variables
$message = '';
$message_type = '';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_data = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'gender'    => $_POST['gender'] ?? null,
        'ic_number' => trim($_POST['ic_number'] ?? ''),
        'village'   => trim($_POST['village'] ?? ''),
        'phone_no'  => trim($_POST['phone_no'] ?? ''),
        'status'    => $_POST['status'] ?? 'Active'
    ];

    $result = MembersHelper::addMember($member_data);

    if ($result['status']) {
        $message = $result['message'];
        $message_type = 'success';
        // Success alert with redirect hint
        echo "<script>setTimeout(() => { window.location.href = '" . URL_ROOT . "/members'; }, 2000);</script>";
    } else {
        $message = $result['message'];
        $message_type = 'error';
    }
}
?>

<div class="space-y-12">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 border-t-8 border-kebana-blue shadow-sm">
        <div>
            <h2 class="text-2xl font-black text-kebana-blue uppercase tracking-tight italic">Pendaftaran Ahli</h2>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Sila masukkan maklumat lengkap ahli baru.</p>
        </div>
        <a href="<?= URL_ROOT ?>/members" class="text-[10px] font-black text-slate-400 hover:text-kebana-blue uppercase tracking-widest flex items-center transition-colors">
            <i class="fa-solid fa-arrow-left mr-3"></i>
            KEMBALI KE SENARAI
        </a>
    </div>

    <?php if (!empty($message)): ?>
    <div class="p-6 <?php echo $message_type === 'success' ? 'bg-green-50 border-green-600 text-green-800' : 'bg-red-50 border-red-600 text-red-800'; ?> border-l-4 font-black text-xs uppercase tracking-widest shadow-sm">
        <div class="flex items-center">
            <i class="fa-solid <?php echo $message_type === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?> mr-4 text-lg"></i>
            <span><?php echo htmlspecialchars($message); ?></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- OCR Card Section -->
    <div class="bg-white border-t-8 border-kebana-yellow shadow-sm p-8 md:p-12 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-black text-kebana-blue uppercase tracking-tight italic">PENGIMBASAN BORANG PENDAFTARAN (OCR)</h3>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Imbas borang bercetak menggunakan kamera telefon atau fail gambar untuk mengisi maklumat secara automatik.</p>
            </div>
            <div class="w-12 h-12 bg-kebana-yellow/10 flex items-center justify-center text-kebana-blue">
                <i class="fa-solid fa-qrcode text-2xl"></i>
            </div>
        </div>

        <div class="border-2 border-dashed border-slate-200 hover:border-kebana-blue bg-slate-50/50 hover:bg-slate-50 transition-all p-8 flex flex-col items-center justify-center text-center cursor-pointer relative group" id="ocr_dropzone">
            <input type="file" id="ocr_file_input" accept="image/*" capture="environment" class="hidden">
            <i class="fa-solid fa-camera-retro text-4xl text-slate-300 group-hover:text-kebana-blue mb-4 transition-colors"></i>
            <span class="text-xs font-black text-kebana-blue uppercase tracking-widest block">AMBIL GAMBAR / PILIH DOKUMEN BORANG</span>
            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wide mt-1 block">Format: JPG, PNG, WEBP (Maksima 5MB)</span>
        </div>

        <!-- OCR Processing State (Hidden by default) -->
        <div id="ocr_process_state" class="hidden bg-slate-50 p-6 border-l-4 border-kebana-blue space-y-4">
            <div class="flex items-center justify-between">
                <span id="ocr_status_text" class="text-[10px] font-black text-kebana-blue uppercase tracking-widest">Memuatkan Enjin OCR...</span>
                <span id="ocr_percentage_text" class="text-xs font-black text-kebana-blue italic">0%</span>
            </div>
            <div class="w-full bg-slate-200 h-3 overflow-hidden">
                <div id="ocr_progress_bar" class="bg-kebana-blue h-full transition-all duration-300" style="width: 0%"></div>
            </div>
        </div>

        <!-- OCR Alert Result (Hidden by default) -->
        <div id="ocr_result_alert" class="hidden p-6 bg-green-50 border-green-600 text-green-800 border-l-4 font-black text-xs uppercase tracking-widest shadow-sm">
            <div class="flex items-start">
                <i class="fa-solid fa-circle-check mr-4 text-lg mt-0.5"></i>
                <div>
                    <span>Maklumat berjaya diekstrak secara automatik!</span>
                    <p class="text-[9px] font-bold text-green-600 lowercase mt-1 tracking-wide">Sila semak semula semua medan di bawah sebelum menyimpan maklumat.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Section -->
    <div class="bg-white border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-8 md:p-12">
            <form method="POST" class="space-y-10">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <!-- Full Name -->
                    <div class="md:col-span-2">
                        <label for="full_name" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">NAMA PENUH (SEPERTI DALAM IC)</label>
                        <input type="text" id="full_name" name="full_name" required 
                               class="w-full px-6 py-5 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold tracking-tight transition-all rounded-none"
                               placeholder="Contoh: AHMAD BIN ABDULLAH"
                               value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                    </div>

                    <!-- Gender -->
                    <div>
                        <label for="gender" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">JANTINA</label>
                        <select id="gender" name="gender" required 
                                class="w-full px-6 py-5 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all rounded-none appearance-none">
                            <option value="" disabled <?php echo !isset($_POST['gender']) ? 'selected' : ''; ?>>PILIH JANTINA</option>
                            <option value="Lelaki" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Lelaki') ? 'selected' : ''; ?>>LELAKI</option>
                            <option value="Wanita" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Wanita') ? 'selected' : ''; ?>>WANITA</option>
                        </select>
                    </div>

                    <!-- IC Number -->
                    <div>
                        <label for="ic_number" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">NO. KAD PENGENALAN</label>
                        <input type="text" id="ic_number" name="ic_number" required 
                               class="w-full px-6 py-5 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all rounded-none"
                               placeholder="Contoh: 900101-13-5555"
                               value="<?php echo isset($_POST['ic_number']) ? htmlspecialchars($_POST['ic_number']) : ''; ?>">
                    </div>

                    <!-- Phone Number -->
                    <div>
                        <label for="phone_no" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">NO. TELEFON</label>
                        <input type="text" id="phone_no" name="phone_no" 
                               class="w-full px-6 py-5 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all rounded-none"
                               placeholder="Contoh: 012-3456789"
                               value="<?php echo isset($_POST['phone_no']) ? htmlspecialchars($_POST['phone_no']) : ''; ?>">
                    </div>

                    <!-- Village -->
                    <div>
                        <label for="village" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">KAWASAN / KAMPUNG</label>
                        <input type="text" id="village" name="village" required 
                               class="w-full px-6 py-5 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold tracking-tight transition-all rounded-none"
                               placeholder="Contoh: KAMPUNG DATA KAKUS"
                               value="<?php echo isset($_POST['village']) ? htmlspecialchars($_POST['village']) : ''; ?>">
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">STATUS KEAHLIAN</label>
                        <select id="status" name="status" required 
                                class="w-full px-6 py-5 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all rounded-none appearance-none">
                            <option value="Active" <?php echo (isset($_POST['status']) && $_POST['status'] === 'Active') ? 'selected' : ''; ?>>AKTIF</option>
                            <option value="Inactive" <?php echo (isset($_POST['status']) && $_POST['status'] === 'Inactive') ? 'selected' : ''; ?>>TIDAK AKTIF</option>
                            <option value="Pending" <?php echo (isset($_POST['status']) && $_POST['status'] === 'Pending') ? 'selected' : ''; ?>>MENUNGGU</option>
                        </select>
                    </div>
                </div>

                <div class="pt-10 flex flex-col md:flex-row gap-6">
                    <button type="submit" class="flex-1 bg-kebana-blue text-white py-6 text-xs font-black uppercase tracking-[0.3em] hover:bg-kebana-accent transition-all shadow-xl">
                        SIMPAN MAKLUMAT AHLI
                    </button>
                    <button type="reset" class="px-10 py-6 border-2 border-slate-100 text-slate-400 text-xs font-black uppercase tracking-widest hover:bg-slate-50 transition-all">
                        SET SEMULA
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Info Box -->
    <div class="bg-slate-50 p-8 border border-slate-100">
        <div class="flex items-start space-x-6">
            <div class="w-12 h-12 bg-kebana-blue/5 rounded-none flex items-center justify-center text-kebana-blue">
                <i class="fa-solid fa-circle-info text-xl"></i>
            </div>
            <div>
                <h4 class="text-xs font-black text-kebana-blue uppercase tracking-widest">Nota Penting</h4>
                <p class="text-[10px] text-slate-400 font-bold uppercase mt-2 leading-relaxed tracking-tight">
                    Semua maklumat yang didaftarkan adalah sulit dan tertakluk di bawah Akta Perlindungan Data Peribadi 2010. Sila pastikan No. Kad Pengenalan adalah tepat untuk mengelakkan ralat pendaftaran.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- CDN for Tesseract.js -->
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ocrDropzone = document.getElementById('ocr_dropzone');
    const ocrFileInput = document.getElementById('ocr_file_input');
    const ocrProcessState = document.getElementById('ocr_process_state');
    const ocrProgressBar = document.getElementById('ocr_progress_bar');
    const ocrPercentageText = document.getElementById('ocr_percentage_text');
    const ocrStatusText = document.getElementById('ocr_status_text');
    const ocrResultAlert = document.getElementById('ocr_result_alert');

    // Trigger file dialog on dropzone click
    ocrDropzone.addEventListener('click', () => {
        ocrFileInput.click();
    });

    // Handle drag and drop styling
    ocrDropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        ocrDropzone.classList.add('border-kebana-blue', 'bg-slate-100');
    });

    ocrDropzone.addEventListener('dragleave', () => {
        ocrDropzone.classList.remove('border-kebana-blue', 'bg-slate-100');
    });

    ocrDropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        ocrDropzone.classList.remove('border-kebana-blue', 'bg-slate-100');
        if (e.dataTransfer.files.length > 0) {
            ocrFileInput.files = e.dataTransfer.files;
            processOCR(e.dataTransfer.files[0]);
        }
    });

    // Handle file input change
    ocrFileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            processOCR(e.target.files[0]);
        }
    });

    function processOCR(file) {
        if (!file) return;

        // Show progress state, hide previous alert
        ocrProcessState.classList.remove('hidden');
        ocrResultAlert.classList.add('hidden');
        
        // Reset progress bar
        ocrProgressBar.style.width = '0%';
        ocrPercentageText.innerText = '0%';
        ocrStatusText.innerText = 'Memulakan Enjin Pengimbasan...';

        Tesseract.recognize(
            file,
            'eng', // English/Malay characters are same latin character set
            {
                logger: m => {
                    console.log(m);
                    if (m.status === 'recognizing text') {
                        ocrStatusText.innerText = 'Sedang Mengimbas & Mengekstrak Data...';
                        const progress = Math.round(m.progress * 100);
                        ocrProgressBar.style.width = progress + '%';
                        ocrPercentageText.innerText = progress + '%';
                    } else if (m.status === 'loading tesseract core') {
                        ocrStatusText.innerText = 'Memuatkan Core Pengimbas...';
                    } else if (m.status === 'initializing api') {
                        ocrStatusText.innerText = 'Memulakan API Pengimbasan...';
                    }
                }
            }
        ).then(({ data: { text } }) => {
            ocrProgressBar.style.width = '100%';
            ocrPercentageText.innerText = '100%';
            ocrStatusText.innerText = 'Selesai Imbas!';
            
            // Auto-populate fields
            const extracted = parseOCRText(text);
            
            if (extracted.name) {
                document.getElementById('full_name').value = extracted.name;
                highlightField('full_name');
            }
            if (extracted.ic) {
                document.getElementById('ic_number').value = extracted.ic;
                highlightField('ic_number');
            }
            if (extracted.gender) {
                document.getElementById('gender').value = extracted.gender;
                highlightField('gender');
            }
            if (extracted.phone) {
                document.getElementById('phone_no').value = extracted.phone;
                highlightField('phone_no');
            }
            if (extracted.village) {
                document.getElementById('village').value = extracted.village;
                highlightField('village');
            }

            // Show success alert
            ocrResultAlert.classList.remove('hidden');
            
            // Smooth scroll down to first field
            document.getElementById('full_name').scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Hide progress bar after 2 seconds
            setTimeout(() => {
                ocrProcessState.classList.add('hidden');
            }, 2000);
        }).catch(err => {
            console.error("OCR Error:", err);
            ocrStatusText.innerText = 'Ralat Pengimbasan! Sila isi secara manual.';
            ocrProgressBar.classList.add('bg-red-500');
        });
    }

    function highlightField(id) {
        const el = document.getElementById(id);
        if (!el) return;
        
        // Add dynamic border flash effect
        el.classList.add('ring-2', 'ring-green-400', 'border-green-400');
        setTimeout(() => {
            el.classList.remove('ring-2', 'ring-green-400', 'border-green-400');
        }, 3000);
    }

    function parseOCRText(text) {
        console.log("Extracted Text:", text);
        
        const lines = text.split('\n').map(line => line.trim()).filter(line => line.length > 0);
        
        let ic = '';
        let phone = '';
        let name = '';
        let village = '';
        let gender = '';

        const icRegex = /\b(\d{6})[- ]?(\d{2})[- ]?(\d{4})\b/;
        const phoneRegex = /\b(01[0-9])[- ]?([0-9]{7,8})\b/;

        // 1. Try to find IC
        const icMatch = text.match(icRegex);
        if (icMatch) {
            ic = `${icMatch[1]}-${icMatch[2]}-${icMatch[3]}`;
            // Deduce gender from IC
            const lastDigit = parseInt(icMatch[3].slice(-1));
            gender = (lastDigit % 2 === 0) ? 'Wanita' : 'Lelaki';
        }

        // 2. Try to find Phone
        const phoneMatch = text.match(phoneRegex);
        if (phoneMatch) {
            phone = `${phoneMatch[1]}-${phoneMatch[2]}`;
        }

        // 3. Robust Name Parsing (allowing optional bullet points/numbers at the start)
        for (let i = 0; i < lines.length; i++) {
            const line = lines[i];
            const upperLine = line.toUpperCase();
            
            if (/^\s*[^a-zA-Z0-9]*(?:NAMA|NAMA PENUH|NAME)\b/i.test(upperLine)) {
                let potentialName = line.replace(/^\s*[^a-zA-Z0-9]*(?:NAMA PENUH|NAMA|NAME)\s*[:\-=\s]*/i, '').trim();
                
                if (potentialName.length >= 3) {
                    name = potentialName;
                    break;
                } else {
                    // Check if name is on the next line
                    if (i + 1 < lines.length) {
                        const nextLine = lines[i+1].trim();
                        if (nextLine.length >= 2 && !/^(?:IC|NO|TEL|PHONE|JANTINA|GENDER|KAMPUNG|KAWASAN|ALAMAT|VILLAGE|STATUS)/i.test(nextLine)) {
                            name = nextLine;
                            break;
                        }
                    }
                }
            }
        }

        // 4. Robust Kampung / Kawasan Parsing
        for (let i = 0; i < lines.length; i++) {
            const line = lines[i];
            const upperLine = line.toUpperCase();
            
            if (/^\s*[^a-zA-Z0-9]*(?:KAWASAN|KAMPUNG|ALAMAT|VILLAGE|ADDRESS)\b/i.test(upperLine)) {
                let potentialVillage = line.replace(/^\s*[^a-zA-Z0-9]*(?:KAWASAN|KAMPUNG|ALAMAT|VILLAGE|ADDRESS)\s*[:\-=\s]*/i, '').trim();
                
                if (potentialVillage.length >= 3) {
                    village = potentialVillage;
                    break;
                } else {
                    // Check if village is on the next line
                    if (i + 1 < lines.length) {
                        const nextLine = lines[i+1].trim();
                        if (nextLine.length >= 2 && !/^(?:IC|NO|TEL|PHONE|JANTINA|GENDER|NAMA|NAME|STATUS)/i.test(nextLine)) {
                            village = nextLine;
                            break;
                        }
                    }
                }
            }
        }

        // Clean-up values
        if (name) {
            name = name.toUpperCase()
                       .replace(/[^A-Z\s@']/g, '')
                       .trim();
        }
        if (village) {
            village = village.toUpperCase().trim();
        }

        return { ic, phone, name, village, gender };
    }
});
</script>
<?php require_once APP_ROOT . '/includes/footer.php'; ?>
