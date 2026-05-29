<?php
/**
 * KEBANA Digital Management System - Add Cawangan
 * File: modules/cawangan/add.php
 */

use App\Helpers\CawanganHelper;

if (!isAdmin()) {
    header('Location: ' . URL_ROOT . '/dashboard');
    exit();
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = CawanganHelper::addCawangan($_POST);
    $message = $result['message'];
    $message_type = $result['status'] ? 'success' : 'error';
    
    if ($result['status']) {
        echo '<script>setTimeout(function(){ window.location.href = "' . URL_ROOT . '/cawangan"; }, 1500);</script>';
    }
}

$page_title = 'DAFTAR CAWANGAN BARU';
require_once APP_ROOT . '/includes/header.php';
?>

<div class="max-w-4xl mx-auto space-y-12 pb-24">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 border border-slate-300 border-t-8 border-t-kebana-blue shadow-sm">
        <div>
            <h2 class="text-2xl font-black text-kebana-blue uppercase tracking-tight italic">Daftar Cawangan Baru</h2>
            <p class="text-xs font-black text-slate-500 uppercase tracking-widest mt-2">Sila isi maklumat cawangan di bawah.</p>
        </div>
        <a href="<?= URL_ROOT ?>/cawangan" class="text-xs font-black text-slate-600 hover:text-kebana-blue uppercase tracking-widest flex items-center transition-colors">
            <i class="fa-solid fa-arrow-left mr-3"></i>
            KEMBALI KE SENARAI
        </a>
    </div>

    <?php if (!empty($message)): ?>
    <div class="p-6 <?php echo $message_type === 'success' ? 'bg-green-50 border-green-600 text-green-900' : 'bg-red-50 border-red-600 text-red-900'; ?> border-l-4 font-black text-sm uppercase tracking-widest shadow-sm">
        <div class="flex items-center">
            <i class="fa-solid <?php echo $message_type === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?> mr-4 text-lg"></i>
            <span><?php echo htmlspecialchars($message); ?></span>
        </div>
    </div>
    <?php endif; ?>

    <div class="bg-white border border-slate-300 border-t-8 border-t-kebana-blue shadow-sm p-10 space-y-10">
        <form action="" method="POST" class="space-y-10">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <div class="space-y-2">
                    <label class="text-xs font-black text-slate-600 uppercase tracking-widest">Nama Cawangan <span class="text-red-500">*</span></label>
                    <input type="text" name="cawangan_name" required placeholder="Contoh: KEBANA CAWANGAN BINTULU" class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-300 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all">
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-black text-slate-600 uppercase tracking-widest">Kod Cawangan <span class="text-red-500">*</span></label>
                    <input type="text" name="cawangan_code" required placeholder="Contoh: BTU" class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-300 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all">
                    <p class="text-xs text-slate-500 font-bold italic">Kod unik 3-5 aksara untuk pengenalan cawangan.</p>
                </div>
            </div>

            <div class="space-y-2">
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" checked class="w-5 h-5 text-kebana-blue focus:ring-kebana-blue border-slate-300 rounded">
                    <span class="text-sm font-black text-kebana-blue uppercase tracking-widest">Cawangan Aktif</span>
                </label>
            </div>

            <button type="submit" class="w-full bg-kebana-blue text-white py-5 text-sm font-black uppercase tracking-[0.2em] hover:bg-kebana-accent transition-all shadow-xl">
                DAFTAR CAWANGAN
            </button>
        </form>
    </div>
</div>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
