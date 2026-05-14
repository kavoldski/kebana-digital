<?php
/**
 * KEBANA Digital Management System - Public QR Check-in
 * File: modules/events/checkin.php
 */

use App\Helpers\EventsHelper;

$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
$token = $_GET['token'] ?? '';

// Validate event and token
if ($event_id <= 0 || empty($token) || !EventsHelper::validateCheckinToken($event_id, $token)) {
    die('<div style="padding: 50px; text-align: center; font-family: sans-serif;">
            <h2 style="color: #e11d48;">Pautan Tidak Sah</h2>
            <p>Maaf, kod QR ini telah tamat tempoh atau tidak sah. Sila hubungi urus setia acara.</p>
            <a href="/kebana-digital/portal" style="color: #2563eb; text-decoration: none; font-weight: bold;">Kembali ke Portal</a>
         </div>');
}

$event = EventsHelper::getEventById($event_id);
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-in Kehadiran - <?php echo htmlspecialchars($event['event_title']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .kebana-blue { color: #003366; }
        .bg-kebana-blue { background-color: #003366; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col items-center justify-center p-6">
    
    <div class="w-full max-w-md bg-white shadow-2xl overflow-hidden rounded-none border-t-8 border-kebana-blue">
        <div class="p-8 text-center border-b border-slate-100">
            <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fa-solid fa-calendar-check text-2xl text-kebana-blue"></i>
            </div>
            <h1 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-2">REKOD KEHADIRAN DIGITAL</h1>
            <h2 class="text-2xl font-black text-kebana-blue uppercase tracking-tighter leading-none italic mb-4">
                <?php echo htmlspecialchars($event['event_title']); ?>
            </h2>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">
                <?php echo date('d F Y', strtotime($event['event_date'])); ?> • <?php echo htmlspecialchars($event['venue']); ?>
            </p>
        </div>

        <div class="p-8" id="form-container">
            <form id="checkin-form" class="space-y-6">
                <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
                <input type="hidden" name="token" value="<?php echo $token; ?>">
                
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Sila Masukkan No. Kad Pengenalan</label>
                    <input type="text" name="ic_number" id="ic_number" required
                           class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-200 focus:border-kebana-blue focus:bg-white outline-none text-lg font-black tracking-widest transition-all text-center"
                           placeholder="850101-13-5577">
                </div>

                <button type="submit" id="submit-btn"
                        class="w-full bg-kebana-blue text-white py-5 text-xs font-black uppercase tracking-[0.2em] hover:bg-blue-900 transition-all shadow-xl flex items-center justify-center gap-3">
                    SAHKAN KEHADIRAN
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>
            
            <p class="mt-8 text-center text-[10px] font-bold text-slate-300 uppercase tracking-widest">
                © 2026 KEBANA DIGITAL MANAGEMENT
            </p>
        </div>

        <!-- Success View (Hidden) -->
        <div class="p-10 text-center hidden" id="success-container">
            <div class="w-20 h-20 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-8 animate-bounce">
                <i class="fa-solid fa-check text-4xl text-green-600"></i>
            </div>
            <h3 class="text-2xl font-black text-green-700 uppercase tracking-tighter mb-2">KEHADIRAN DISAHKAN!</h3>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-8">Selamat Datang,</p>
            <div class="bg-slate-50 p-6 border-b-2 border-green-500 mb-10">
                <p id="member-name" class="text-xl font-black text-kebana-blue uppercase italic"></p>
            </div>
            <button onclick="window.location.reload()" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] hover:text-kebana-blue transition-colors">
                KEMBALI KE LAMAN UTAMA
            </button>
        </div>

        <!-- Error View (Hidden) -->
        <div class="p-10 text-center hidden" id="error-container">
            <div class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-8">
                <i class="fa-solid fa-triangle-exclamation text-4xl text-red-600"></i>
            </div>
            <h3 class="text-2xl font-black text-red-700 uppercase tracking-tighter mb-4">RALAT REKOD</h3>
            <p id="error-message" class="text-sm font-bold text-slate-600 mb-10"></p>
            <button onclick="hideError()" class="w-full bg-slate-800 text-white py-4 text-[10px] font-black uppercase tracking-widest">
                CUBA LAGI
            </button>
        </div>
    </div>

    <script>
        // IC auto-formatting
        const icInput = document.getElementById('ic_number');
        icInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^0-9]/g, '');
            if (value.length > 12) value = value.slice(0, 12);
            
            let formatted = '';
            if (value.length > 0) formatted += value.slice(0, 6);
            if (value.length > 6) formatted += '-' + value.slice(6, 8);
            if (value.length > 8) formatted += '-' + value.slice(8, 12);
            
            e.target.value = formatted;
        });

        const form = document.getElementById('checkin-form');
        const submitBtn = document.getElementById('submit-btn');
        const formContainer = document.getElementById('form-container');
        const successContainer = document.getElementById('success-container');
        const errorContainer = document.getElementById('error-container');
        const memberName = document.getElementById('member-name');
        const errorMessage = document.getElementById('error-message');

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> MEMPROSES...';
            
            const formData = new FormData(form);
            
            try {
                const response = await fetch('/kebana-digital/modules/api/checkin_api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.status) {
                    memberName.innerText = data.full_name;
                    formContainer.classList.add('hidden');
                    successContainer.classList.remove('hidden');
                } else {
                    errorMessage.innerText = data.message;
                    formContainer.classList.add('hidden');
                    errorContainer.classList.remove('hidden');
                }
            } catch (err) {
                errorMessage.innerText = 'Ralat sambungan rangkaian. Sila cuba lagi.';
                formContainer.classList.add('hidden');
                errorContainer.classList.remove('hidden');
            }
        });

        function hideError() {
            errorContainer.classList.add('hidden');
            formContainer.classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'SAHKAN KEHADIRAN <i class="fa-solid fa-arrow-right ml-2"></i>';
        }
    </script>
</body>
</html>
