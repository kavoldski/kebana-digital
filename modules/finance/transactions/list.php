<?php
/**
 * KEBANA Digital Management System - Transaction List (MYDS Inspired)
 * File: modules/finance/transactions/list.php
 */

use App\Helpers\FinanceHelper;
use App\Core\Database;

$page_title = 'SENARAI TRANSAKSI';
require_once APP_ROOT . '/includes/header.php';

if (!hasRole([888, 1, 2, 3, 6, 7, 55, 66])) {
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
$search = $_GET['search'] ?? '';

// Build dynamic query
$where = "1=1";
$params = [];
$types = "";

if ($from_date) { $where .= " AND t.trans_date >= ?"; $params[] = $from_date; $types .= "s"; }
if ($to_date) { $where .= " AND t.trans_date <= ?"; $params[] = $to_date; $types .= "s"; }
if ($type_filter) { $where .= " AND t.trans_type = ?"; $params[] = $type_filter; $types .= "s"; }
if ($category_filter) { $where .= " AND t.category LIKE ?"; $params[] = "%$category_filter%"; $types .= "s"; }
if ($search) { 
    $where .= " AND (t.category LIKE ? OR e.event_title LIKE ? OR u.username LIKE ? OR t.payment_mode LIKE ?)"; 
    $params[] = "%$search%"; 
    $params[] = "%$search%"; 
    $params[] = "%$search%"; 
    $params[] = "%$search%"; 
    $types .= "ssss"; 
}

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
?>

<div class="space-y-12">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 border-t-8 border-kebana-blue shadow-sm">
        <div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Rekod Terperinci Aliran Masuk dan Keluar Dana.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <?php if (hasRole([888, 1, 2, 3, 6, 55])): ?>
            <button onclick="openReportModal()" class="bg-kebana-blue text-white px-8 py-4 text-[10px] font-black uppercase tracking-[0.15em] hover:bg-kebana-accent transition-all shadow-lg inline-flex items-center">
                <i class="fa-solid fa-file-invoice mr-3 text-base"></i>
                JANA LAPORAN
            </button>
            <?php endif; ?>
            <a href="<?= URL_ROOT ?>/finance/transactions/create?type=Income" class="bg-green-600 text-white px-8 py-4 text-[10px] font-black uppercase tracking-[0.15em] hover:bg-green-700 transition-all shadow-lg inline-flex items-center">
                <i class="fa-solid fa-arrow-trend-up mr-3 text-base"></i>
                REKOD MASUK
            </a>
            <a href="<?= URL_ROOT ?>/finance/transactions/create?type=Expense" class="bg-red-600 text-white px-8 py-4 text-[10px] font-black uppercase tracking-[0.15em] hover:bg-red-700 transition-all shadow-lg inline-flex items-center">
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
            <div class="md:col-span-2">
                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3">Carian Kata Kunci</label>
                <div class="flex gap-2">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari kategori, projek atau butiran..."
                           class="flex-1 px-5 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold uppercase transition-all">
                    <button type="submit" class="bg-kebana-dark text-white px-8 py-4 text-xs font-black uppercase tracking-widest hover:bg-black transition-all shadow-lg">
                        CARI
                    </button>
                    <?php if ($search || $from_date || $to_date || $type_filter): ?>
                    <a href="<?= URL_ROOT ?>/finance/transactions" class="px-4 py-4 text-[10px] font-black text-slate-400 uppercase flex items-center hover:text-red-500">KOSONGKAN</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <div id="live-search-results" class="space-y-12">

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
                        <th class="px-8 py-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="6" class="px-8 py-20 text-center text-[10px] font-black text-slate-300 uppercase tracking-[0.3em]">
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
                                <a href="<?= URL_ROOT ?>/<?php echo $t['receipt_path']; ?>" target="_blank" class="text-kebana-blue hover:text-kebana-accent transition-colors" title="Lihat Resit">
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
                                        <a href="<?= URL_ROOT ?>/finance/transactions/edit?id=<?php echo $t['trans_id']; ?>" 
                                           class="p-2 text-slate-300 hover:text-kebana-blue transition-colors" title="Kemaskini">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="<?= URL_ROOT ?>/finance/transactions/delete?id=<?php echo $t['trans_id']; ?>" 
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
</div>

<!-- Report Modal -->
<?php if (hasRole([888, 1, 2, 3, 6, 55])): ?>
<div id="reportModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white border-t-8 border-kebana-blue shadow-2xl max-w-lg w-full p-8 relative space-y-6">
        <button onclick="closeReportModal()" class="absolute top-6 right-6 text-slate-400 hover:text-red-500 transition-colors">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>
        <div>
            <h3 class="text-lg font-black text-kebana-blue uppercase tracking-tight italic">Jana Laporan Kewangan</h3>
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">Sila pilih tempoh laporan dan format fail.</p>
        </div>
        
        <form action="<?= URL_ROOT ?>/finance/transactions/generate" method="GET" target="_blank" onsubmit="return validateReportForm()" class="space-y-6">
            <div class="space-y-2">
                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Jenis Tempoh (Period Type)</label>
                <select name="period_type" id="report_period_type" required onchange="handlePeriodChange()"
                        class="w-full px-5 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold uppercase transition-all">
                    <option value="daily">Harian (Daily)</option>
                    <option value="monthly" selected>Bulanan (Monthly)</option>
                    <option value="yearly">Tahunan (Yearly)</option>
                </select>
            </div>
            
            <div class="space-y-2" id="date_input_container">
                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest" id="date_input_label">Pilih Julat Tempoh (Select Period Range)</label>
                <div id="dynamic_date_field"></div>
            </div>
            
            <div class="space-y-2">
                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Format Fail</label>
                <select name="format" required
                        class="w-full px-5 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold uppercase transition-all">
                    <option value="pdf">Dokumen PDF (.pdf)</option>
                    <option value="csv">Excel / CSV (.csv)</option>
                </select>
            </div>
            
            <div class="pt-4 flex gap-4">
                <button type="button" onclick="closeReportModal()" class="flex-1 bg-slate-100 text-slate-600 py-4 text-xs font-black uppercase tracking-widest hover:bg-slate-200 transition-all text-center">
                    BATAL
                </button>
                <button type="submit" class="flex-1 bg-kebana-blue text-white py-4 text-xs font-black uppercase tracking-widest hover:bg-kebana-accent transition-all text-center shadow-lg">
                    JANA FAIL
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openReportModal() {
    document.getElementById('reportModal').classList.remove('hidden');
    handlePeriodChange();
}

function closeReportModal() {
    document.getElementById('reportModal').classList.add('hidden');
}

function handlePeriodChange() {
    const periodType = document.getElementById('report_period_type').value;
    const label = document.getElementById('date_input_label');
    const container = document.getElementById('dynamic_date_field');
    
    label.innerText = 'Pilih Julat Tempoh (Select Period Range)';
    let html = '';
    
    if (periodType === 'daily') {
        const today = new Date().toISOString().split('T')[0];
        html = `
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Dari Tarikh</label>
                <input type="date" name="date_start" id="report_date_start" value="${today}" required 
                       class="w-full px-5 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold uppercase transition-all">
            </div>
            <div>
                <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Hingga Tarikh</label>
                <input type="date" name="date_end" id="report_date_end" value="${today}" required 
                       class="w-full px-5 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold uppercase transition-all">
            </div>
        </div>`;
    } else if (periodType === 'monthly') {
        const today = new Date();
        const currentMonth = today.toISOString().substring(0, 7); // YYYY-MM
        html = `
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Dari Bulan</label>
                <input type="month" name="date_start" id="report_date_start" value="${currentMonth}" required 
                       class="w-full px-5 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold uppercase transition-all">
            </div>
            <div>
                <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Hingga Bulan</label>
                <input type="month" name="date_end" id="report_date_end" value="${currentMonth}" required 
                       class="w-full px-5 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold uppercase transition-all">
            </div>
        </div>`;
    } else if (periodType === 'yearly') {
        const currentYear = new Date().getFullYear();
        let startOptions = '';
        let endOptions = '';
        for (let y = currentYear; y >= currentYear - 10; y--) {
            startOptions += `<option value="${y}" ${y === currentYear ? 'selected' : ''}>${y}</option>`;
            endOptions += `<option value="${y}" ${y === currentYear ? 'selected' : ''}>${y}</option>`;
        }
        html = `
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Dari Tahun</label>
                <select name="date_start" id="report_date_start" required 
                        class="w-full px-5 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold uppercase transition-all rounded-none appearance-none">
                    ${startOptions}
                </select>
            </div>
            <div>
                <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Hingga Tahun</label>
                <select name="date_end" id="report_date_end" required 
                        class="w-full px-5 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold uppercase transition-all rounded-none appearance-none">
                    ${endOptions}
                </select>
            </div>
        </div>`;
    }
    
    container.innerHTML = html;
}

function validateReportForm() {
    const startVal = document.getElementById('report_date_start').value;
    const endVal = document.getElementById('report_date_end').value;
    const periodType = document.getElementById('report_period_type').value;

    if (startVal && endVal) {
        if (periodType === 'yearly') {
            if (parseInt(startVal) > parseInt(endVal)) {
                alert('Tahun mula tidak boleh melebihi tahun tamat.');
                return false;
            }
        } else {
            if (startVal > endVal) {
                const term = periodType === 'daily' ? 'Tarikh' : 'Bulan';
                alert(`${term} mula tidak boleh melebihi ${term.toLowerCase()} tamat.`);
                return false;
            }
        }
    }
    
    setTimeout(closeReportModal, 300);
    return true;
}
</script>
<?php endif; ?>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
