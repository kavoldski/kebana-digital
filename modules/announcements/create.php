<?php
/**
 * KEBANA Digital Management System - Create Announcement
 * File: modules/announcements/create.php
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
    if (AnnouncementHelper::addAnnouncement($_POST, $current_user_id)) {
        echo '<script>window.location.href = "' . URL_ROOT . '/announcements?msg=success";</script>';
        exit;
    } else {
        $message = 'Gagal menyimpan hebahan. Sila cuba lagi.';
        $message_type = 'error';
    }
}

$page_title = 'TAMBAH HEBAHAN';
?>

<div class="max-w-4xl mx-auto space-y-12 pb-24">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 border-t-8 border-kebana-blue shadow-sm">
        <div>
            <h2 class="text-2xl font-black text-kebana-blue uppercase tracking-tight italic">Hebahan Baru</h2>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Cipta hebahan untuk paparan umum</p>
        </div>
        <a href="<?= URL_ROOT ?>/announcements" class="text-[10px] font-black text-slate-400 hover:text-kebana-blue uppercase tracking-widest flex items-center transition-colors">
            <i class="fa-solid fa-arrow-left mr-3"></i> KEMBALI
        </a>
    </div>

    <?php if ($message): ?>
    <div class="p-6 bg-red-50 text-red-700 border-l-4 border-red-600 font-bold text-xs uppercase tracking-widest animate-fade-in">
        <?php echo $message; ?>
    </div>
    <?php endif; ?>

    <div class="bg-white p-12 border border-slate-100 shadow-xl">
        <form method="POST" class="space-y-10">
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Tajuk Hebahan <span class="text-red-500">*</span></label>
                <input type="text" name="title" required
                       class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all"
                       placeholder="Cth: Mesyuarat Agung Tahunan ke-42">
            </div>

            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Kandungan <span class="text-red-500">*</span></label>
                <textarea name="content" required rows="8"
                          class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm transition-all"
                          placeholder="Tulis butiran hebahan di sini..."></textarea>
            </div>

            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Status Penyiaran <span class="text-red-500">*</span></label>
                <select name="status" required class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold transition-all rounded-none appearance-none">
                    <option value="Active">Aktif (Papar serta-merta)</option>
                    <option value="Draft">Draf (Simpan dahulu)</option>
                    <option value="Inactive">Tidak Aktif (Sembunyikan)</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Tarikh & Masa Luput (Opsional)</label>
                <input type="datetime-local" name="expires_at"
                       class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all">
                <p class="text-[9px] text-slate-400 mt-2 italic">* Biarkan kosong jika tiada had masa.</p>
            </div>

            <div class="pt-10">
                <button type="submit" class="w-full bg-kebana-blue text-white py-6 text-xs font-black uppercase tracking-[0.3em] hover:bg-kebana-accent transition-all shadow-2xl">
                    SIMPAN HEBAHAN
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
