<?php
/**
 * KEBANA Management System - Finance Dashboard (MYDS Inspired)
 * File: modules/finance/dashboard.php
 */

use App\Helpers\FinanceHelper;

require_once APP_ROOT . '/includes/header.php';

// Access Control: Super Admin (888), Bendahari Pusat (6), Bendahari Cawangan (55), Auditor (7/66)
if (!hasRole([888, 6, 55, 7, 66])) {
    echo "<div class='p-12 text-center'><h1 class='text-2xl font-black text-red-600 uppercase tracking-widest'>AKSES DISEKAT</h1></div>";
    require_once APP_ROOT . '/includes/footer.php';
    exit;
}

$totals = FinanceHelper::getTotals();
$recent = FinanceHelper::getRecentTransactions(8);

$page_title = 'PENGURUSAN KEWANGAN';
?>

<div class="space-y-12">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 border-t-8 border-kebana-blue shadow-sm">
        <div>
            <h2 class="text-2xl font-black text-kebana-blue uppercase tracking-tight italic">Ringkasan Kewangan</h2>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Pemantauan Aliran Tunai dan Perbelanjaan Persatuan.</p>
        </div>
        <div class="flex gap-4">
            <a href="/kebana-digital/finance/transactions/list" class="bg-white text-kebana-blue border-2 border-kebana-blue px-8 py-4 text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 transition-all flex items-center">
                <i class="fa-solid fa-list-check mr-3"></i>
                LIHAT SEMUA
            </a>
            <a href="/kebana-digital/finance/transactions/create" class="bg-kebana-blue text-white px-10 py-4 text-xs font-black uppercase tracking-[0.2em] hover:bg-kebana-accent transition-all shadow-xl inline-flex items-center">
                <i class="fa-solid fa-plus-circle mr-4 text-lg"></i>
                REKOD TRANSAKSI
            </a>
        </div>
    </div>

    <!-- KPI Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-0 border border-slate-100 bg-white">
        <div class="p-10 border-r border-slate-50 flex flex-col justify-center hover:bg-slate-50 transition-colors group">
            <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">DANA TERSEDIA (NET)</p>
            <p class="text-4xl font-black text-kebana-blue mt-4">RM <?php echo number_format($totals['balance'], 2); ?></p>
        </div>
        <div class="p-10 border-r border-slate-50 flex flex-col justify-center hover:bg-slate-50 transition-colors group">
            <p class="text-[10px] font-black text-green-600/50 uppercase tracking-widest">JUMLAH PENDAPATAN</p>
            <p class="text-4xl font-black text-green-600 mt-4">RM <?php echo number_format($totals['income'], 2); ?></p>
        </div>
        <div class="p-10 flex flex-col justify-center hover:bg-slate-50 transition-colors group border-b-8 border-kebana-yellow">
            <p class="text-[10px] font-black text-red-600/50 uppercase tracking-widest">JUMLAH PERBELANJAAN</p>
            <p class="text-4xl font-black text-red-600 mt-4">RM <?php echo number_format($totals['expense'], 2); ?></p>
        </div>
    </div>

    <!-- Recent Transactions Section -->
    <div class="bg-white border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-8 border-b border-slate-50 bg-slate-50/50 flex items-center justify-between">
            <h3 class="text-xs font-black text-kebana-blue uppercase tracking-widest">Transaksi Terkini</h3>
            <span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter italic">* Menunjukkan 8 rekod pendaftaran terakhir</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="px-8 py-6 text-[9px] font-black text-slate-300 uppercase tracking-widest">Tarikh</th>
                        <th class="px-8 py-6 text-[9px] font-black text-slate-300 uppercase tracking-widest">Kategori & Projek</th>
                        <th class="px-8 py-6 text-[9px] font-black text-slate-300 uppercase tracking-widest">Mod</th>
                        <th class="px-8 py-6 text-[9px] font-black text-slate-300 uppercase tracking-widest">Amaun</th>
                        <th class="px-8 py-6 text-[9px] font-black text-slate-300 uppercase tracking-widest text-right">Direkod Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if (empty($recent)): ?>
                    <tr>
                        <td colspan="5" class="px-8 py-20 text-center text-[10px] font-black text-slate-200 uppercase tracking-[0.3em]">Tiada Transaksi Direkodkan</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($recent as $t): 
                            $is_income = $t['trans_type'] === 'Income';
                        ?>
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-8 py-6">
                                <p class="text-xs font-black text-slate-400 uppercase tracking-tighter"><?php echo date('d M', strtotime($t['trans_date'])); ?></p>
                                <p class="text-[9px] font-bold text-slate-300 uppercase"><?php echo date('Y', strtotime($t['trans_date'])); ?></p>
                            </td>
                            <td class="px-8 py-6">
                                <p class="text-xs font-black text-kebana-blue uppercase"><?php echo htmlspecialchars($t['category']); ?></p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase mt-1 italic"><?php echo htmlspecialchars($t['event_title'] ?? 'Dana Am Persatuan'); ?></p>
                            </td>
                            <td class="px-8 py-6">
                                <span class="text-[9px] font-black px-3 py-1 bg-slate-100 text-slate-500 uppercase tracking-widest">
                                    <?php echo (!empty($t['payment_mode']) && $t['payment_mode'] !== '0') ? htmlspecialchars($t['payment_mode']) : 'Cash'; ?>
                                </span>
                            </td>
                            <td class="px-8 py-6">
                                <p class="text-sm font-black <?php echo $is_income ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo $is_income ? '+' : '-'; ?> RM <?php echo number_format($t['amount'], 2); ?>
                                </p>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest"><?php echo htmlspecialchars($t['recorder_name'] ?? 'Sistem'); ?></p>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="p-8 bg-slate-50 border-t border-slate-100 text-center">
            <a href="/kebana-digital/finance/transactions/list" class="text-[10px] font-black text-kebana-blue uppercase tracking-widest hover:text-kebana-accent transition-colors">
                LIHAT SEMUA TRANSAKSI <i class="fa-solid fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
</div>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
