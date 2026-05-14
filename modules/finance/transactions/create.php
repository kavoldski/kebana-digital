<?php
/**
 * KEBANA Management System - New Transaction (MYDS Inspired)
 * File: modules/finance/transactions/create.php
 */

use App\Helpers\FinanceHelper;
use App\Helpers\EventsHelper;

require_once APP_ROOT . '/includes/header.php';

if (!hasRole([888, 6, 55])) {
    echo "<div class='p-12 text-center'><h1 class='text-2xl font-black text-red-600 uppercase tracking-widest'>AKSES DISEKAT</h1></div>";
    require_once APP_ROOT . '/includes/footer.php';
    exit;
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_user_id = (int)($_SESSION['user_id'] ?? 0);
    $current_cawangan_id = isset($_SESSION['cawangan_id']) ? (int)$_SESSION['cawangan_id'] : null;
    
    $receiptFile = $_FILES['receipt'] ?? null;
    
    if (FinanceHelper::addTransaction($_POST, $current_user_id, $current_cawangan_id, $receiptFile)) {
        $message = 'Transaksi berjaya direkodkan.';
        $message_type = 'success';
        echo '<script>setTimeout(function(){ window.location.href = "/kebana-digital/finance"; }, 1500);</script>';
    } else {
        $message = 'Gagal merekod transaksi. Sila semak input anda.';
        $message_type = 'error';
    }
}

$preselected_type = isset($_GET['type']) && in_array($_GET['type'], ['Income', 'Expense']) ? $_GET['type'] : 'Income';
$categories = FinanceHelper::getCategories($preselected_type);

// Filter events based on role
$current_role = (int)($_SESSION['role'] ?? 0);
$current_cawangan_id = isset($_SESSION['cawangan_id']) ? (int)$_SESSION['cawangan_id'] : null;
$scope_cawangan = in_array($current_role, $CAWANGAN_ROLES) ? $current_cawangan_id : null;

$events = EventsHelper::getAllEvents('finance_selection', null, $scope_cawangan);

$page_title = $preselected_type === 'Income' ? 'REKOD MASUK' : 'REKOD KELUAR';
?>

<div class="max-w-4xl mx-auto space-y-12 pb-24">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 border-t-8 <?php echo $preselected_type === 'Income' ? 'border-green-600' : 'border-red-600'; ?> shadow-sm">
        <div>
            <h2 class="text-2xl font-black uppercase tracking-tight italic <?php echo $preselected_type === 'Income' ? 'text-green-700' : 'text-red-700'; ?>">
                <?php echo $preselected_type === 'Income' ? 'Rekod Wang Masuk (Pendapatan)' : 'Rekod Wang Keluar (Perbelanjaan)'; ?>
            </h2>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Sila isi butiran <?php echo $preselected_type === 'Income' ? 'pendapatan / penerimaan' : 'perbelanjaan / pembayaran'; ?> di bawah.</p>
        </div>
        <a href="/kebana-digital/finance" class="text-[10px] font-black text-slate-400 hover:text-kebana-blue uppercase tracking-widest flex items-center transition-colors">
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
        <form method="POST" enctype="multipart/form-data" class="space-y-12">
            <!-- Type Selector -->
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6 text-center">Jenis Transaksi</label>
                <div class="flex gap-6 justify-center">
                        <label class="cursor-pointer group flex-1">
                        <input type="radio" name="trans_type" value="Income" <?php echo $preselected_type === 'Income' ? 'checked' : ''; ?> class="hidden peer">
                        <div class="p-6 text-center border-2 border-slate-100 peer-checked:border-green-600 peer-checked:bg-green-50 transition-all">
                            <i class="fa-solid fa-arrow-trend-up text-2xl text-slate-200 group-hover:text-green-600 peer-checked:text-green-600 mb-3 block"></i>
                            <span class="text-xs font-black uppercase tracking-widest text-slate-400 peer-checked:text-green-700">Pendapatan (Masuk)</span>
                        </div>
                    </label>
                    <label class="cursor-pointer group flex-1">
                        <input type="radio" name="trans_type" value="Expense" <?php echo $preselected_type === 'Expense' ? 'checked' : ''; ?> class="hidden peer">
                        <div class="p-6 text-center border-2 border-slate-100 peer-checked:border-red-600 peer-checked:bg-red-50 transition-all">
                            <i class="fa-solid fa-arrow-trend-down text-2xl text-slate-200 group-hover:text-red-600 peer-checked:text-red-600 mb-3 block"></i>
                            <span class="text-xs font-black uppercase tracking-widest text-slate-400 peer-checked:text-red-700">Perbelanjaan</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Amaun (RM) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-6 top-1/2 -translate-y-1/2 text-xs font-black text-slate-300">RM</span>
                        <input type="number" step="0.01" name="amount" required
                               class="w-full pl-16 pr-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xl font-black transition-all"
                               placeholder="0.00">
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Tarikh Transaksi <span class="text-red-500">*</span></label>
                    <input type="date" name="trans_date" value="<?php echo date('Y-m-d'); ?>" required
                           class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all">
                </div>
            </div>

            <div class="grid grid-cols-1 gap-10">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Kategori <span class="text-red-500">*</span></label>
                    <input type="text" name="category" id="category-input" list="cat-list" required
                           class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all"
                           placeholder="Cth: Yuran Ahli, Sewa Dewan, dsb.">
                    <datalist id="cat-list">
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>">
                        <?php endforeach; ?>
                    </datalist>

                    <?php if (!empty($categories)): ?>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <span class="text-[8px] font-black text-slate-300 uppercase tracking-widest mr-2 self-center">Cadangan:</span>
                        <?php 
                        // Show top 6 suggestions as quick tags
                        $display_cats = array_slice($categories, 0, 6);
                        foreach ($display_cats as $cat): 
                        ?>
                        <button type="button" onclick="document.getElementById('category-input').value = '<?php echo addslashes($cat); ?>'"
                                class="px-3 py-1 bg-slate-50 border border-slate-100 text-[9px] font-black text-slate-400 uppercase tracking-tighter hover:bg-kebana-blue hover:text-white hover:border-kebana-blue transition-all">
                            <?php echo htmlspecialchars($cat); ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Pautkan ke Projek (Opsional)</label>
                    <select name="event_id" class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold transition-all rounded-none appearance-none">
                        <option value="">-- Dana Am Persatuan --</option>
                        <?php foreach ($events as $ev): ?>
                        <option value="<?php echo $ev['event_id']; ?>"><?php echo htmlspecialchars($ev['event_title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Mod Pembayaran</label>
                    <select name="payment_mode" required class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold transition-all rounded-none appearance-none">
                        <option value="Cash">Tunai (Cash)</option>
                        <option value="Bank">Pindahan Bank / Cek</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-10">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Muat Naik Resit / Bukti Pembayaran (PDF/Imej)</label>
                    <input type="file" name="receipt" accept=".pdf,.jpg,.jpeg,.png"
                           class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold transition-all">
                </div>
            </div>

            <div class="pt-10">
                <button type="submit" class="w-full bg-kebana-blue text-white py-6 text-xs font-black uppercase tracking-[0.3em] hover:bg-kebana-accent transition-all shadow-2xl">
                    SIMPAN TRANSAKSI
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
