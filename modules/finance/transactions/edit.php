<?php
/**
 * KEBANA Digital Management System - Edit Transaction
 * File: modules/finance/transactions/edit.php
 */

use App\Helpers\FinanceHelper;
use App\Helpers\EventsHelper;
use App\Core\Database;

require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/dbconnect.php';

if (!hasRole([888, 1, 2, 3, 6, 7, 55, 66])) {
    header("Location: " . URL_ROOT . "/finance/transactions/list?msg=denied");
    exit;
}

$transId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$current_role = (int)($_SESSION['role'] ?? 0);
$current_cawangan_id = isset($_SESSION['cawangan_id']) ? (int)$_SESSION['cawangan_id'] : null;
$CAWANGAN_ROLES = [11, 22, 33, 44, 55, 66];

// Scoping
$scope_cawangan = in_array($current_role, $CAWANGAN_ROLES) ? $current_cawangan_id : null;

// Fetch transaction data with security check
$db = Database::getInstance()->getConnection();
$sql = "SELECT t.* FROM tbl_transaction t 
        LEFT JOIN tbl_event e ON t.event_id = e.event_id
        LEFT JOIN tbl_user u ON t.recorded_by = u.user_id
        WHERE t.trans_id = ?";
if ($scope_cawangan !== null) {
    $sql .= " AND COALESCE(e.cawangan_id, u.cawangan_id) = ?";
    $sql .= " AND (u.role IS NULL OR u.role NOT IN (888, 1, 2, 3, 4, 5, 6, 7))";
}

$stmt = $db->prepare($sql);
if ($scope_cawangan !== null) {
    $stmt->bind_param("ii", $transId, $scope_cawangan);
} else {
    $stmt->bind_param("i", $transId);
}
$stmt->execute();
$res = $stmt->get_result();
$transaction = $res->fetch_assoc();
$stmt->close();

if (!$transaction) {
    header("Location: " . URL_ROOT . "/finance/transactions/list?msg=notfound");
    exit;
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (FinanceHelper::updateTransaction($transId, $_POST, $scope_cawangan)) {
        $message = 'Transaksi berjaya dikemaskini.';
        $message_type = 'success';
        echo '<script>setTimeout(function(){ window.location.href = "' . URL_ROOT . '/finance/transactions/list"; }, 1500);</script>';
        $transaction = array_merge($transaction, $_POST); // Update local copy for display
    } else {
        $message = 'Gagal mengemaskini transaksi. Sila semak input atau kebenaran anda.';
        $message_type = 'error';
    }
}

$categories = FinanceHelper::getCategories($transaction['trans_type']);
$events = EventsHelper::getAllEvents('finance_selection', null, $scope_cawangan);

$page_title = 'KEMASKINI TRANSAKSI';
require_once APP_ROOT . '/includes/header.php';
?>

<div class="max-w-4xl mx-auto space-y-12 pb-24">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 border-t-8 border-kebana-blue shadow-sm">
        <div>
            <h2 class="text-2xl font-black text-kebana-blue uppercase tracking-tight italic">Kemaskini Transaksi</h2>
            <p class="text-sm font-black text-slate-600 uppercase tracking-widest mt-2">ID Transaksi: #<?php echo $transId; ?></p>
        </div>
        <a href="<?= URL_ROOT ?>/finance/transactions/list" class="text-sm font-black text-slate-600 hover:text-kebana-blue uppercase tracking-widest flex items-center transition-colors">
            <i class="fa-solid fa-arrow-left mr-3"></i>
            KEMBALI
        </a>
    </div>

    <?php if ($message): ?>
    <div class="p-6 <?php echo $message_type === 'success' ? 'bg-green-50 text-green-700 border-l-4 border-green-600' : 'bg-red-50 text-red-700 border-l-4 border-red-600'; ?> font-bold text-sm uppercase tracking-widest animate-pulse">
        <?php echo $message; ?>
    </div>
    <?php endif; ?>

    <div class="bg-white p-12 border border-slate-300 shadow-xl">
        <form method="POST" class="space-y-12">
            <!-- Type Selector -->
            <div>
                <label class="block text-sm font-black text-slate-600 uppercase tracking-widest mb-6 text-center">Jenis Transaksi</label>
                <div class="flex gap-6 justify-center">
                    <label class="cursor-pointer group flex-1">
                        <input type="radio" name="trans_type" value="Income" <?php echo $transaction['trans_type'] === 'Income' ? 'checked' : ''; ?> class="hidden peer">
                        <div class="p-6 text-center border-2 border-slate-300 peer-checked:border-green-600 peer-checked:bg-green-50 transition-all">
                            <i class="fa-solid fa-arrow-trend-up text-2xl text-slate-300 group-hover:text-green-600 peer-checked:text-green-600 mb-3 block"></i>
                            <span class="text-sm font-black uppercase tracking-widest text-slate-500 peer-checked:text-green-700">Pendapatan (Masuk)</span>
                        </div>
                    </label>
                    <label class="cursor-pointer group flex-1">
                        <input type="radio" name="trans_type" value="Expense" <?php echo $transaction['trans_type'] === 'Expense' ? 'checked' : ''; ?> class="hidden peer">
                        <div class="p-6 text-center border-2 border-slate-300 peer-checked:border-red-600 peer-checked:bg-red-50 transition-all">
                            <i class="fa-solid fa-arrow-trend-down text-2xl text-slate-300 group-hover:text-red-600 peer-checked:text-red-600 mb-3 block"></i>
                            <span class="text-sm font-black uppercase tracking-widest text-slate-500 peer-checked:text-red-700">Perbelanjaan</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <div>
                    <label class="block text-sm font-black text-slate-600 uppercase tracking-widest mb-3">Amaun (RM) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-6 top-1/2 -translate-y-1/2 text-sm font-black text-slate-500">RM</span>
                        <input type="number" step="0.01" name="amount" value="<?php echo $transaction['amount']; ?>" required
                               class="w-full pl-16 pr-6 py-4 bg-slate-50 border-b-2 border-slate-300 focus:border-kebana-blue focus:bg-white outline-none text-xl font-black transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-black text-slate-600 uppercase tracking-widest mb-3">Tarikh Transaksi <span class="text-red-500">*</span></label>
                    <input type="date" name="trans_date" value="<?php echo $transaction['trans_date']; ?>" required
                           class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-300 focus:border-kebana-blue focus:bg-white outline-none text-base font-bold transition-all">
                </div>
            </div>

            <div>
                <label class="block text-sm font-black text-slate-600 uppercase tracking-widest mb-3">Kategori <span class="text-red-500">*</span></label>
                <input type="text" name="category" id="category-input" list="cat-list" value="<?php echo htmlspecialchars($transaction['category']); ?>" required
                       class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-300 focus:border-kebana-blue focus:bg-white outline-none text-base font-bold transition-all">
                <datalist id="cat-list">
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <div>
                    <label class="block text-sm font-black text-slate-600 uppercase tracking-widest mb-3">Pautkan ke Projek (Opsional)</label>
                    <select name="event_id" class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-300 focus:border-kebana-blue focus:bg-white outline-none text-base font-bold transition-all rounded-none appearance-none">
                        <option value="">-- Dana Am Persatuan --</option>
                        <?php foreach ($events as $ev): ?>
                        <option value="<?php echo $ev['event_id']; ?>" <?php echo $transaction['event_id'] == $ev['event_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ev['event_title']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-black text-slate-600 uppercase tracking-widest mb-3">Mod Pembayaran <span class="text-red-500">*</span></label>
                    <div class="flex gap-4">
                        <label class="cursor-pointer group flex-1">
                            <input type="radio" name="payment_mode" value="Cash" <?php echo $transaction['payment_mode'] === 'Cash' ? 'checked' : ''; ?> required class="hidden peer">
                            <div class="p-4 text-center border-2 border-slate-300 peer-checked:border-kebana-blue peer-checked:bg-blue-50 transition-all">
                                <i class="fa-solid fa-money-bill-wave text-xl text-slate-300 group-hover:text-kebana-blue peer-checked:text-kebana-blue mb-2 block transition-colors"></i>
                                <span class="text-sm font-black uppercase tracking-widest text-slate-500 peer-checked:text-kebana-blue">Tunai (Cash)</span>
                            </div>
                        </label>
                        <label class="cursor-pointer group flex-1">
                            <input type="radio" name="payment_mode" value="Bank" <?php echo $transaction['payment_mode'] === 'Bank' ? 'checked' : ''; ?> required class="hidden peer">
                            <div class="p-4 text-center border-2 border-slate-300 peer-checked:border-kebana-blue peer-checked:bg-blue-50 transition-all">
                                <i class="fa-solid fa-building-columns text-xl text-slate-300 group-hover:text-kebana-blue peer-checked:text-kebana-blue mb-2 block transition-colors"></i>
                                <span class="text-sm font-black uppercase tracking-widest text-slate-500 peer-checked:text-kebana-blue">Bank / Cek</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="pt-10">
                <button type="submit" class="w-full bg-kebana-blue text-white py-6 text-base font-black uppercase tracking-[0.3em] hover:bg-kebana-accent transition-all shadow-2xl">
                    KEMASKINI TRANSAKSI
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
