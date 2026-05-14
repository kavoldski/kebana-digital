<?php
/**
 * KEBANA Digital Management System - Advanced QR & Bulk Attendance
 * File: modules/events/attendance.php
 */

use App\Helpers\EventsHelper;

$message = '';
$message_type = '';

$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
$village_filter = $_GET['village'] ?? '';

// Handle Token Generation - MUST BE BEFORE ANY HTML OUTPUT (including header.php)
if (isset($_GET['generate_token']) && $event_id > 0) {
    if (EventsHelper::generateCheckinToken($event_id)) {
        header("Location: /kebana-digital/events/attendance?event_id=$event_id&msg=token_generated");
        exit;
    }
}

require_once APP_ROOT . '/includes/header.php';

// Handle success messages from redirects
if (isset($_GET['msg']) && $_GET['msg'] === 'token_generated') {
    $message = "Kod QR berjaya dijana. Sila paparkan kepada ahli untuk imbasan.";
    $message_type = 'success';
}

// Handle attendance submission (Manual updates)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $event_id > 0) {
    if (isset($_POST['attendance']) && is_array($_POST['attendance'])) {
        $success_count = 0;
        $current_user_id = (int)($_SESSION['user_id'] ?? 0);
        
        foreach ($_POST['attendance'] as $member_id => $data) {
            $status = $data['status'] ?? 'Absent';
            $notes = $data['notes'] ?? '';
            if (EventsHelper::markAttendance($event_id, (int)$member_id, $status, $notes, $current_user_id)) {
                $success_count++;
            }
        }
        $message = "Rekod kehadiran berjaya dikemaskini secara manual.";
        $message_type = 'success';
    }
}

// Get data for view
$all_events = EventsHelper::getAllEvents();
$event = $event_id > 0 ? EventsHelper::getEventById($event_id) : null;
$members = $event_id > 0 ? EventsHelper::getAllMembersWithAttendance($event_id) : [];
$villages = EventsHelper::getDistinctVillages();
$summary = $event_id > 0 ? EventsHelper::getAttendanceSummary($event_id) : ['Present' => 0, 'Absent' => 0, 'Excused' => 0];

$page_title = 'PENGURUSAN KEHADIRAN DIGITAL';
?>

<!-- QR Code Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<div class="space-y-12 pb-24">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 border-t-8 border-kebana-blue shadow-sm">
        <div>
            <h2 class="text-2xl font-black text-kebana-blue uppercase tracking-tight italic">Kawalan Kehadiran</h2>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Sistem Imbasan QR & Kemaskini Pukal Ahli.</p>
        </div>
        <div class="flex gap-4">
            <a href="/kebana-digital/events" class="bg-slate-100 text-slate-500 px-8 py-4 text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all flex items-center">
                <i class="fa-solid fa-arrow-left mr-3"></i>
                SENARAI ACARA
            </a>
        </div>
    </div>

    <?php if ($message): ?>
    <div class="p-6 <?php echo $message_type === 'success' ? 'bg-green-50 text-green-700 border-l-4 border-green-600' : 'bg-red-50 text-red-700 border-l-4 border-red-600'; ?> font-bold text-xs uppercase tracking-widest shadow-sm">
        <?php echo $message; ?>
    </div>
    <?php endif; ?>

    <!-- Event Selector & Global Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-1 bg-white p-8 border border-slate-100 shadow-sm">
            <form method="GET" class="space-y-6">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Pilih Acara</label>
                    <select name="event_id" onchange="this.form.submit()"
                            class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold uppercase transition-all rounded-none appearance-none">
                        <option value="">-- Pilih Acara --</option>
                        <?php foreach ($all_events as $ev): ?>
                        <option value="<?php echo $ev['event_id']; ?>" <?php echo $event_id === (int)$ev['event_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ev['event_title']); ?> (<?php echo date('d M Y', strtotime($ev['event_date'])); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="w-full bg-kebana-dark text-white px-10 py-4 text-xs font-black uppercase tracking-widest hover:bg-black transition-all shadow-lg">
                    MUAT DATA KEHADIRAN
                </button>
            </form>
        </div>

        <?php if ($event): ?>
        <div class="lg:col-span-2 bg-kebana-blue text-white p-8 flex flex-col md:flex-row justify-between items-center gap-8 shadow-xl">
            <div class="space-y-2 text-center md:text-left">
                <p class="text-[9px] font-black text-white/40 uppercase tracking-[0.3em]">SEDANG BERLANGSUNG</p>
                <h3 class="text-2xl font-black uppercase italic tracking-tighter leading-none"><?php echo htmlspecialchars($event['event_title']); ?></h3>
                <p class="text-[10px] font-bold text-white/60 uppercase tracking-widest"><?php echo date('d F Y', strtotime($event['event_date'])); ?> • <?php echo htmlspecialchars($event['venue']); ?></p>
            </div>
            
            <div class="flex gap-4">
                <div class="text-center px-6 py-4 bg-white/5 border border-white/10">
                    <p class="text-[9px] font-black text-white/40 uppercase mb-1">HADIR</p>
                    <p class="text-3xl font-black text-white" id="live-present-count"><?php echo $summary['Present']; ?></p>
                </div>
                <div class="text-center px-6 py-4 bg-white/5 border border-white/10">
                    <p class="text-[9px] font-black text-white/40 uppercase mb-1">JUMLAH AHLI</p>
                    <p class="text-3xl font-black text-white/40" id="live-total-count">...</p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($event): ?>
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-12">
        <!-- LEFT: QR & LIVE FEED -->
        <div class="lg:col-span-1 space-y-12">
            <!-- QR Panel -->
            <div class="bg-white border border-slate-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-50 bg-slate-50/50 flex justify-between items-center">
                    <h4 class="text-[10px] font-black text-kebana-blue uppercase tracking-widest">KOD QR KEHADIRAN</h4>
                    <?php if (!empty($event['checkin_token'])): ?>
                    <button onclick="window.print()" class="text-[9px] font-black text-slate-400 hover:text-kebana-blue"><i class="fa-solid fa-print"></i></button>
                    <?php endif; ?>
                </div>
                <div class="p-8 text-center">
                    <?php if (empty($event['checkin_token'])): ?>
                        <div class="py-10 space-y-4">
                            <i class="fa-solid fa-qrcode text-5xl text-slate-100"></i>
                            <p class="text-[9px] font-bold text-slate-400 uppercase leading-relaxed">Kod QR belum dijana untuk acara ini.</p>
                            <a href="?event_id=<?php echo $event_id; ?>&generate_token=1" 
                               class="inline-block bg-kebana-blue text-white px-6 py-3 text-[10px] font-black uppercase tracking-widest hover:bg-kebana-accent transition-all shadow-lg">
                                JANA KOD QR SEKARANG
                            </a>
                        </div>
                    <?php else: ?>
                        <div id="qrcode" class="mx-auto flex justify-center mb-6"></div>
                        <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest mb-4">IMBAS UNTUK PENGESAHAN</p>
                        <div class="p-3 bg-slate-50 border border-slate-100 rounded text-[9px] font-mono break-all text-slate-400 mb-4 select-all" id="checkin-url">
                            <?php 
                                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                                $url = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/kebana-digital/events/checkin?event_id=" . $event_id . "&token=" . $event['checkin_token'];
                                echo $url;
                            ?>
                        </div>
                        <button onclick="copyURL()" class="text-[9px] font-black text-kebana-blue uppercase hover:underline">SALIN PAUTAN</button>
                        
                        <script>
                            new QRCode(document.getElementById("qrcode"), {
                                text: "<?php echo $url; ?>",
                                width: 200,
                                height: 200,
                                colorDark : "#003366",
                                colorLight : "#ffffff",
                                correctLevel : QRCode.CorrectLevel.H
                            });

                            function copyURL() {
                                const url = document.getElementById('checkin-url').innerText;
                                navigator.clipboard.writeText(url);
                                alert('Pautan check-in telah disalin!');
                            }
                        </script>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Live Feed Panel -->
            <div class="bg-white border border-slate-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-50 bg-slate-50/50">
                    <h4 class="text-[10px] font-black text-kebana-blue uppercase tracking-widest">KEMASUKAN TERKINI</h4>
                </div>
                <div class="p-0 divide-y divide-slate-50" id="live-feed">
                    <div class="p-8 text-center text-[10px] font-bold text-slate-300 uppercase italic">Memuatkan data...</div>
                </div>
            </div>
        </div>

        <!-- RIGHT: MANUAL CONTROLS & TABLE -->
        <div class="lg:col-span-3 space-y-12">
            <!-- Toolbar: Filtering & Bulk Actions -->
            <div class="bg-white p-8 border border-slate-100 shadow-sm flex flex-col md:flex-row justify-between items-end gap-6">
                <div class="flex-1 w-full md:w-auto">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Tapis Mengikut Kampung / Kawasan</label>
                    <select id="village-filter" onchange="filterMembers()"
                            class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold uppercase transition-all rounded-none appearance-none">
                        <option value="">-- Semua Kawasan --</option>
                        <?php foreach ($villages as $v): ?>
                        <option value="<?php echo htmlspecialchars($v); ?>"><?php echo htmlspecialchars($v); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="flex-1 w-full md:w-auto relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                    <input type="text" id="name-search" onkeyup="filterMembers()" placeholder="CARI NAMA / NO. IC..." 
                           class="w-full pl-14 pr-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold uppercase transition-all rounded-none">
                </div>

                <div class="flex gap-3">
                    <button onclick="bulkAction('Present')" class="bg-green-600 text-white px-6 py-4 text-[9px] font-black uppercase tracking-widest hover:bg-green-700 transition-all shadow-lg">
                        HADIR SEMUA
                    </button>
                    <button onclick="bulkAction('Absent')" class="bg-slate-800 text-white px-6 py-4 text-[9px] font-black uppercase tracking-widest hover:bg-black transition-all shadow-lg">
                        TIDAK HADIR SEMUA
                    </button>
                </div>
            </div>

            <!-- Attendance Form -->
            <div class="bg-white border border-slate-100 shadow-sm overflow-hidden">
                <form method="POST" action="?event_id=<?php echo $event_id; ?>" id="attendance-form">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse" id="members-table">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50/50">
                                    <th class="px-8 py-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Maklumat Ahli</th>
                                    <th class="px-8 py-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Status Kehadiran</th>
                                    <th class="px-8 py-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Nota / Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php if (empty($members)): ?>
                                <tr>
                                    <td colspan="3" class="px-8 py-20 text-center text-[10px] font-black text-slate-300 uppercase tracking-[0.3em]">
                                        Tiada ahli aktif dijumpai dalam sistem.
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($members as $m): 
                                        $att_status = $m['attendance_status'] ?? 'Absent';
                                    ?>
                                    <tr class="member-row hover:bg-slate-50/50 transition-colors" 
                                        data-village="<?php echo htmlspecialchars($m['village']); ?>"
                                        data-name="<?php echo htmlspecialchars(strtolower($m['full_name'])); ?>"
                                        data-ic="<?php echo htmlspecialchars($m['ic_number']); ?>">
                                        <td class="px-8 py-6">
                                            <p class="text-xs font-black text-kebana-blue uppercase tracking-tight"><?php echo htmlspecialchars($m['full_name']); ?></p>
                                            <p class="text-[9px] font-bold text-slate-400 uppercase mt-1 italic"><?php echo htmlspecialchars($m['ic_number']); ?> • <?php echo htmlspecialchars($m['village']); ?></p>
                                        </td>
                                        <td class="px-8 py-6">
                                            <div class="flex gap-2">
                                                <?php foreach (['Present' => 'HADIR', 'Absent' => 'TIDAK HADIR', 'Excused' => 'KENYATAAN'] as $val => $label): ?>
                                                <label class="cursor-pointer group">
                                                    <input type="radio" name="attendance[<?php echo $m['member_id']; ?>][status]" value="<?php echo $val; ?>" 
                                                           class="hidden peer status-radio-<?php echo $val; ?>" <?php echo $att_status === $val ? 'checked' : ''; ?>>
                                                    <span class="px-4 py-2 text-[8px] font-black uppercase tracking-widest border border-slate-200 block peer-checked:bg-kebana-blue peer-checked:text-white peer-checked:border-kebana-blue group-hover:bg-slate-50 transition-all">
                                                        <?php echo $label; ?>
                                                    </span>
                                                </label>
                                                <?php endforeach; ?>
                                            </div>
                                        </td>
                                        <td class="px-8 py-6">
                                            <input type="text" name="attendance[<?php echo $m['member_id']; ?>][notes]" 
                                                   value="<?php echo htmlspecialchars($m['attendance_notes'] ?? ''); ?>"
                                                   class="w-full px-4 py-2 bg-slate-50 border-b border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-[10px] font-bold uppercase transition-all"
                                                   placeholder="Tambah nota...">
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="p-8 bg-slate-50 border-t border-slate-100 flex justify-between items-center">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest" id="visible-count">MEMAPARKAN <?php echo count($members); ?> AHLI</p>
                        <button type="submit" class="bg-kebana-blue text-white px-12 py-4 text-xs font-black uppercase tracking-[0.2em] hover:bg-kebana-accent transition-all shadow-xl">
                            SIMPAN SEMUA PERUBAHAN
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-white p-20 text-center border border-dashed border-slate-200">
        <i class="fa-solid fa-calendar-check text-5xl text-slate-100 mb-6 block"></i>
        <p class="text-xs font-black text-slate-300 uppercase tracking-[0.2em]">Sila pilih acara untuk merekod kehadiran.</p>
    </div>
    <?php endif; ?>
</div>

<?php if ($event_id > 0): ?>
<script>
    // Filtering Logic
    function filterMembers() {
        const village = document.getElementById('village-filter').value;
        const search = document.getElementById('name-search').value.toLowerCase();
        const rows = document.querySelectorAll('.member-row');
        let visible = 0;

        rows.forEach(row => {
            const rowVillage = row.getAttribute('data-village');
            const rowName = row.getAttribute('data-name');
            const rowIC = row.getAttribute('data-ic');
            
            const villageMatch = village === '' || rowVillage === village;
            const searchMatch = search === '' || rowName.includes(search) || rowIC.includes(search);
            
            if (villageMatch && searchMatch) {
                row.style.display = '';
                visible++;
            } else {
                row.style.display = 'none';
            }
        });
        
        document.getElementById('visible-count').innerText = `MEMAPARKAN ${visible} AHLI`;
    }

    // Bulk Action Logic
    function bulkAction(status) {
        const rows = document.querySelectorAll('.member-row');
        let affected = 0;
        
        rows.forEach(row => {
            if (row.style.display !== 'none') {
                const radio = row.querySelector(`.status-radio-${status}`);
                if (radio) {
                    radio.checked = true;
                    affected++;
                }
            }
        });
        
        // Show subtle notification
        console.log(`Updated ${affected} members to ${status}`);
    }

    // Live Dashboard Updates (Polling)
    async function updateLiveDashboard() {
        try {
            const response = await fetch(`/kebana-digital/modules/api/checkin_api.php?event_id=<?php echo $event_id; ?>`);
            const data = await response.json();
            
            if (data.error) return;

            // Update Counts
            document.getElementById('live-present-count').innerText = data.summary.Present;
            document.getElementById('live-total-count').innerText = data.total_members;

            // Update Feed
            const feedContainer = document.getElementById('live-feed');
            if (data.recent.length === 0) {
                feedContainer.innerHTML = '<div class="p-8 text-center text-[10px] font-bold text-slate-300 uppercase italic">Menunggu kemasukan...</div>';
            } else {
                feedContainer.innerHTML = data.recent.map(entry => `
                    <div class="p-6 flex items-center justify-between group hover:bg-slate-50 transition-all">
                        <div class="flex items-center gap-4">
                            <div class="w-8 h-8 bg-green-50 text-green-600 flex items-center justify-center rounded-full text-xs">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-kebana-blue uppercase truncate w-32">${entry.name}</p>
                                <p class="text-[8px] font-bold text-slate-300 uppercase tracking-widest mt-0.5">${entry.time}</p>
                            </div>
                        </div>
                        <span class="text-[8px] font-black text-green-500 uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-opacity">BARU</span>
                    </div>
                `).join('');
            }
        } catch (err) {
            console.error('Failed to poll attendance data:', err);
        }
    }

    // Start polling every 5 seconds
    setInterval(updateLiveDashboard, 5000);
    updateLiveDashboard(); // Initial call
</script>
<?php endif; ?>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
