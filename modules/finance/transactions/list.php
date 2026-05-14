<?php
/**
 * KEBANA Management System - Transaction List (MYDS Inspired)
 * File: modules/finance/transactions/list.php
 */

use App\Helpers\FinanceHelper;
use App\Core\Database;

require_once APP_ROOT . '/includes/header.php';

if (!hasRole([888, 6, 55, 7, 66])) {
    echo "<div class='p-12 text-center'><h1 class='text-2xl font-black text-red-600 uppercase tracking-widest'>AKSES DISEKAT</h1></div>";
    require_once APP_ROOT . '/includes/footer.php';
    exit;
}

$db = Database::getInstance()->getConnection();

// Filters
$from_date = $_GET['from'] ?? '';
$to_date = $_GET['to'] ?? '';
$type_filter = $_GET['type'] ?? '';
$category_filter = $_GET['category'] ?? '';

// Build dynamic query
$where = "1=1";
$params = [];
$types = "";

if ($from_date) { $where .= " AND t.trans_date >= ?"; $params[] = $from_date; $types .= "s"; }
if ($to_date) { $where .= " AND t.trans_date <= ?"; $params[] = $to_date; $types .= "s"; }
if ($type_filter) { $where .= " AND t.trans_type = ?"; $params[] = $type_filter; $types .= "s"; }
if ($category_filter) { $where .= " AND t.category LIKE ?"; $params[] = "%$category_filter%"; $types .= "s"; }

// Scoping based on role
if (in_array($current_role, $CAWANGAN_ROLES)) {
    $where .= " AND COALESCE(e.cawangan_id, u.cawangan_id) = ?";
    $params[] = $current_cawangan_id;
    $types .= "i";
}

$sql = "SELECT t.*, e.event_title, u.username as recorder_name, u.role as recorder_role 
        FROM tbl_transaction t 
        LEFT JOIN tbl_event e ON t.event_id = e.event_id 
        LEFT JOIN tbl_user u ON t.recorded_by = u.user_id 
        WHERE $where 
        ORDER BY t.trans_date DESC, t.trans_id DESC";

$stmt = $db->prepare($sql);
$transactions = [];
if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
    $stmt->close();
}

// Calculate filtered totals
$filtered_income = 0;
$filtered_expense = 0;
foreach ($transactions as $t) {
    if ($t['trans_type'] === 'Income') $filtered_income += $t['amount'];
    else $filtered_expense += $t['amount'];
}
$filtered_balance = $filtered_income - $filtered_expense;

$page_title = 'SENARAI TRANSAKSI';
?>

<div class="space-y-12">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 border-t-8 border-kebana-blue shadow-sm">
        <div>
            <h2 class="text-2xl font-black text-kebana-blue uppercase tracking-tight italic">Senarai Transaksi</h2>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Rekod Terperinci Aliran Masuk dan Keluar Dana.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="/kebana-digital/finance/transactions/create?type=Income" class="bg-green-600 text-white px-8 py-4 text-[10px] font-black uppercase tracking-[0.15em] hover:bg-green-700 transition-all shadow-lg inline-flex items-center">
                <i class="fa-solid fa-arrow-trend-up mr-3 text-base"></i>
                REKOD MASUK
            </a>
            <a href="/kebana-digital/finance/transactions/create?type=Expense" class="bg-red-600 text-white px-8 py-4 text-[10px] font-black uppercase tracking-[0.15em] hover:bg-red-700 transition-all shadow-lg inline-flex items-center">
                <i class="fa-solid fa-arrow-trend-down mr-3 text-base"></i>
                REKOD KELUAR
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-8 border border-slate-100 shadow-sm">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
            <div>
                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3">Dari Tarikh</label>
                <input type="date" name="from" value="<?php echo htmlspecialchars($from_date); ?>" 
                       class="w-full px-5 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold uppercase transition-all">
            </div>
            <div>
                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3">Hingga Tarikh</label>
                <input type="date" name="to" value="<?php echo htmlspecialchars($to_date); ?>" 
                       class="w-full px-5 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold uppercase transition-all">
            </div>
            <div>
                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3">Jenis</label>
                <select name="type" class="w-full px-5 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold uppercase transition-all rounded-none appearance-none">
                    <option value="">Semua</option>
                    <option value="Income" <?php echo $type_filter === 'Income' ? 'selected' : ''; ?>>Pendapatan</option>
                    <option value="Expense" <?php echo $type_filter === 'Expense' ? 'selected' : ''; ?>>Perbelanjaan</option>
                </select>
            </div>
            <button type="submit" class="bg-kebana-dark text-white py-4 text-xs font-black uppercase tracking-widest hover:bg-black transition-all shadow-lg">
                TAPIS REKOD
            </button>
        </form>
    </div>

    <!-- Filtered Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-0 border border-slate-100 bg-white shadow-sm">
        <div class="p-8 border-r border-slate-50">
            <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest">Baki Dalam Tempoh</p>
            <p class="text-2xl font-black text-kebana-blue mt-2">RM <?php echo number_format($filtered_balance, 2); ?></p>
        </div>
        <div class="p-8 border-r border-slate-50">
            <p class="text-[9px] font-black text-green-600/50 uppercase tracking-widest text-green-600">Total Pendapatan</p>
            <p class="text-2xl font-black text-green-600 mt-2">RM <?php echo number_format($filtered_income, 2); ?></p>
        </div>
        <div class="p-8 border-b-4 border-kebana-yellow">
            <p class="text-[9px] font-black text-red-600/50 uppercase tracking-widest text-red-600">Total Perbelanjaan</p>
            <p class="text-2xl font-black text-red-600 mt-2">RM <?php echo number_format($filtered_expense, 2); ?></p>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/50">
                        <th class="px-8 py-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Tarikh</th>
                        <th class="px-8 py-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Kategori & Projek</th>
                        <th class="px-8 py-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Mod</th>
                        <th class="px-8 py-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Resit</th>
                        <th class="px-8 py-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Amaun</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="4" class="px-8 py-20 text-center text-[10px] font-black text-slate-300 uppercase tracking-[0.3em]">
                            Tiada rekod transaksi dijumpai.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $t): 
                            $is_income = $t['trans_type'] === 'Income';
                            
                            // Edit/Delete Permissions Check
                            $can_edit_delete = true;
                            if (in_array($current_role, $CAWANGAN_ROLES)) {
                                $rec_role = (int)($t['recorder_role'] ?? 0);
                                if (in_array($rec_role, [888, 1, 2, 3, 4, 5, 6, 7])) {
                                    $can_edit_delete = false; // Branch cannot edit HQ transactions
                                }
                            }
                        ?>
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-8 py-6">
                                <p class="text-xs font-black text-slate-400 uppercase"><?php echo date('d M Y', strtotime($t['trans_date'])); ?></p>
                            </td>
                            <td class="px-8 py-6">
                                <p class="text-xs font-black text-kebana-blue uppercase tracking-tight"><?php echo htmlspecialchars($t['category']); ?></p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase mt-1 italic"><?php echo htmlspecialchars($t['event_title'] ?? 'Dana Am Persatuan'); ?></p>
                            </td>
                            <td class="px-8 py-6">
                                <span class="px-3 py-1 text-[8px] font-black uppercase tracking-widest bg-slate-100 text-slate-500">
                                    <?php echo (!empty($t['payment_mode']) && $t['payment_mode'] !== '0') ? htmlspecialchars($t['payment_mode']) : 'Cash'; ?>
                                </span>
                            </td>
                            <td class="px-8 py-6 text-center">
                                <?php if (!empty($t['receipt_path'])): ?>
                                <a href="/kebana-digital/<?php echo $t['receipt_path']; ?>" target="_blank" class="text-kebana-blue hover:text-kebana-accent transition-colors" title="Lihat Resit">
                                    <i class="fa-solid fa-file-invoice-dollar text-lg"></i>
                                </a>
                                <?php else: ?>
                                <span class="text-slate-200 text-[10px] uppercase font-black tracking-tighter">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <p class="text-sm font-black <?php echo $is_income ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo $is_income ? '+' : '-'; ?> RM <?php echo number_format($t['amount'], 2); ?>
                                </p>
                                <p class="text-[8px] font-bold text-slate-300 uppercase mt-1">Oleh: <?php echo htmlspecialchars($t['recorder_name'] ?? 'Sistem'); ?></p>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex justify-end gap-2">
                                    <?php if ($can_edit_delete): ?>
                                        <a href="/kebana-digital/finance/transactions/edit?id=<?php echo $t['trans_id']; ?>" 
                                           class="p-2 text-slate-300 hover:text-kebana-blue transition-colors" title="Kemaskini">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="/kebana-digital/finance/transactions/delete?id=<?php echo $t['trans_id']; ?>" 
                                           onclick="return confirm('Adakah anda pasti mahu memadam transaksi ini?')"
                                           class="p-2 text-slate-300 hover:text-red-600 transition-colors" title="Padam">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="p-2 text-slate-200 cursor-not-allowed" title="Direkod oleh Ibu Pejabat Pusat">
                                            <i class="fa-solid fa-lock"></i>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
