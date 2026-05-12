<?php
/**
 * KEBANA Management System - Event Attendance (MYDS Inspired)
 * File: modules/events/attendance.php
 */

use App\Helpers\EventsHelper;

require_once APP_ROOT . '/includes/header.php';

$message = '';
$message_type = '';

$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
$event = $event_id > 0 ? EventsHelper::getEventById($event_id) : null;

// Handle attendance submission
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
        $message = "Kehadiran berjaya direkodkan untuk $success_count orang ahli.";
        $message_type = 'success';
    }
}

// Get data for view
$all_events = EventsHelper::getAllEvents();
$members = $event_id > 0 ? EventsHelper::getAllMembersWithAttendance($event_id) : [];
$summary = $event_id > 0 ? EventsHelper::getAttendanceSummary($event_id) : ['Present' => 0, 'Absent' => 0, 'Excused' => 0];

$page_title = 'KEHADIRAN ACARA';
?>

<div class="space-y-12 pb-24">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 border-t-8 border-kebana-blue shadow-sm">
        <div>
            <h2 class="text-2xl font-black text-kebana-blue uppercase tracking-tight italic">Rekod Kehadiran</h2>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Pengurusan Kehadiran Ahli untuk Program Organisasi.</p>
        </div>
        <a href="/kebana-digital/events" class="text-[10px] font-black text-slate-400 hover:text-kebana-blue uppercase tracking-widest flex items-center transition-colors">
            <i class="fa-solid fa-arrow-left mr-3"></i>
            KEMBALI KE SENARAI
        </a>
    </div>

    <?php if ($message): ?>
    <div class="p-6 <?php echo $message_type === 'success' ? 'bg-green-50 text-green-700 border-l-4 border-green-600' : 'bg-red-50 text-red-700 border-l-4 border-red-600'; ?> font-bold text-xs uppercase tracking-widest">
        <?php echo $message; ?>
    </div>
    <?php endif; ?>

    <!-- Event Selector -->
    <div class="bg-white p-8 border border-slate-100 shadow-sm">
        <form method="GET" class="flex flex-col md:flex-row gap-6 items-end">
            <div class="flex-1">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Pilih Acara</label>
                <select name="event_id" onchange="this.form.submit()"
                        class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold uppercase transition-all rounded-none appearance-none">
                    <option value="">-- Pilih Acara dari Senarai --</option>
                    <?php foreach ($all_events as $ev): ?>
                    <option value="<?php echo $ev['event_id']; ?>" <?php echo $event_id === (int)$ev['event_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($ev['event_title']); ?> (<?php echo date('d M Y', strtotime($ev['event_date'])); ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="bg-kebana-dark text-white px-10 py-4 text-xs font-black uppercase tracking-widest hover:bg-black transition-all shadow-lg">
                PAPAR SENARAI
            </button>
        </form>
    </div>

    <?php if ($event): ?>
    <!-- Event Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-0 border border-slate-100 bg-white shadow-sm">
        <div class="p-8 border-r border-slate-50">
            <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest">Tajuk Program</p>
            <p class="text-xs font-black text-kebana-blue mt-2 uppercase"><?php echo htmlspecialchars($event['event_title']); ?></p>
        </div>
        <div class="p-8 border-r border-slate-50 bg-green-50/30">
            <p class="text-[9px] font-black text-green-600 uppercase tracking-widest">Hadir</p>
            <p class="text-3xl font-black text-green-700 mt-2"><?php echo $summary['Present']; ?></p>
        </div>
        <div class="p-8 border-r border-slate-50 bg-red-50/30">
            <p class="text-[9px] font-black text-red-600 uppercase tracking-widest">Tidak Hadir</p>
            <p class="text-3xl font-black text-red-700 mt-2"><?php echo $summary['Absent']; ?></p>
        </div>
        <div class="p-8 bg-slate-50/50">
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Kenyataan (Excused)</p>
            <p class="text-3xl font-black text-slate-500 mt-2"><?php echo $summary['Excused']; ?></p>
        </div>
    </div>

    <!-- Attendance Form -->
    <div class="bg-white border border-slate-100 shadow-sm overflow-hidden">
        <form method="POST" action="?event_id=<?php echo $event_id; ?>">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
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
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-8 py-6">
                                    <p class="text-xs font-black text-kebana-blue uppercase tracking-tight"><?php echo htmlspecialchars($m['full_name']); ?></p>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase mt-1 italic"><?php echo htmlspecialchars($m['ic_number']); ?> • <?php echo htmlspecialchars($m['village']); ?></p>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex gap-2">
                                        <?php foreach (['Present' => 'HADIR', 'Absent' => 'TIDAK HADIR', 'Excused' => 'KENYATAAN'] as $val => $label): ?>
                                        <label class="cursor-pointer group">
                                            <input type="radio" name="attendance[<?php echo $m['member_id']; ?>][status]" value="<?php echo $val; ?>" 
                                                   class="hidden peer" <?php echo $att_status === $val ? 'checked' : ''; ?>>
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
                                           placeholder="Tambah nota jika perlu...">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="p-8 bg-slate-50 border-t border-slate-100 flex justify-end">
                <button type="submit" class="bg-kebana-blue text-white px-12 py-4 text-xs font-black uppercase tracking-[0.2em] hover:bg-kebana-accent transition-all shadow-xl">
                    SIMPAN REKOD KEHADIRAN
                </button>
            </div>
        </form>
    </div>
    <?php else: ?>
    <div class="bg-white p-20 text-center border border-dashed border-slate-200">
        <i class="fa-solid fa-calendar-check text-5xl text-slate-100 mb-6 block"></i>
        <p class="text-xs font-black text-slate-300 uppercase tracking-[0.2em]">Sila pilih acara untuk merekod kehadiran.</p>
    </div>
    <?php endif; ?>
</div>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
