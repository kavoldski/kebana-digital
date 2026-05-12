<?php
/**
 * KEBANA Management System - Budget Management (MYDS Inspired)
 * File: modules/finance/budget.php
 */

use App\Helpers\FinanceHelper;

require_once APP_ROOT . '/includes/header.php';

if (!hasRole([888, 6, 55, 7, 66])) {
    echo "<div class='p-12 text-center'><h1 class='text-2xl font-black text-red-600 uppercase tracking-widest'>AKSES DISEKAT</h1></div>";
    require_once APP_ROOT . '/includes/footer.php';
    exit;
}

$filters = [
    'year' => $_GET['year'] ?? date('Y'),
    'search' => $_GET['search'] ?? ''
];

$budgets = FinanceHelper::getBudgetSummary($filters);

// KPI Calcs
$total_planned = 0;
$total_actual = 0;
foreach ($budgets as $b) {
    $total_planned += $b['planned_budget'];
    $total_actual += $b['actual_expense'];
}
$variance = $total_planned - $total_actual;

$page_title = 'PENGURUSAN BAJET';
?>

<div class="space-y-12">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 border-t-8 border-kebana-blue shadow-sm">
        <div>
            <h2 class="text-2xl font-black text-kebana-blue uppercase tracking-tight italic">Pengurusan Bajet Acara</h2>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Analisis Bajet Berbanding Perbelanjaan Sebenar untuk Setiap Program.</p>
        </div>
        <a href="/kebana-digital/finance" class="text-[10px] font-black text-slate-400 hover:text-kebana-blue uppercase tracking-widest flex items-center transition-colors">
            <i class="fa-solid fa-arrow-left mr-3"></i>
            KEMBALI KE DASHBOARD
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-8 border border-slate-100 shadow-sm">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
            <div>
                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3">Tahun</label>
                <input type="number" name="year" value="<?php echo htmlspecialchars($filters['year']); ?>" 
                       class="w-full px-5 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold transition-all">
            </div>
            <div>
                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3">Cari Nama Acara</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($filters['search']); ?>" placeholder="Cth: Mesyuarat Agung..."
                       class="w-full px-5 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold uppercase transition-all">
            </div>
            <button type="submit" class="bg-kebana-dark text-white py-4 text-xs font-black uppercase tracking-widest hover:bg-black transition-all shadow-lg">
                JANA ANALISIS
            </button>
        </form>
    </div>

    <!-- KPI Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-0 border border-slate-100 bg-white">
        <div class="p-10 border-r border-slate-50">
            <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">JUMLAH BAJET DIRANCANG</p>
            <p class="text-3xl font-black text-kebana-blue mt-4">RM <?php echo number_format($total_planned, 2); ?></p>
        </div>
        <div class="p-10 border-r border-slate-50">
            <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">PERBELANJAAN SEBENAR</p>
            <p class="text-3xl font-black text-red-600 mt-4">RM <?php echo number_format($total_actual, 2); ?></p>
        </div>
        <div class="p-10 border-b-8 <?php echo $variance >= 0 ? 'border-green-500' : 'border-red-500'; ?>">
            <p class="text-[10px] font-black <?php echo $variance >= 0 ? 'text-green-600/50' : 'text-red-600/50'; ?> uppercase tracking-widest">VARIAN BAJET (BAKI)</p>
            <p class="text-3xl font-black <?php echo $variance >= 0 ? 'text-green-600' : 'text-red-600'; ?> mt-4">RM <?php echo number_format($variance, 2); ?></p>
        </div>
    </div>

    <!-- Budget Table -->
    <div class="bg-white border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/50">
                        <th class="px-8 py-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Acara & Tarikh</th>
                        <th class="px-8 py-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Bajet Dirancang</th>
                        <th class="px-8 py-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Belanja Sebenar</th>
                        <th class="px-8 py-6 text-[9px] font-black text-slate-400 uppercase tracking-widest">Varian</th>
                        <th class="px-8 py-6 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if (empty($budgets)): ?>
                    <tr>
                        <td colspan="5" class="px-8 py-20 text-center text-[10px] font-black text-slate-300 uppercase tracking-[0.3em]">
                            Tiada data bajet dijumpai untuk kriteria ini.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($budgets as $b): 
                            $v = $b['planned_budget'] - $b['actual_expense'];
                        ?>
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-8 py-6">
                                <p class="text-xs font-black text-kebana-blue uppercase tracking-tight"><?php echo htmlspecialchars($b['event_title']); ?></p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase mt-1 italic"><?php echo date('d M Y', strtotime($b['event_date'])); ?></p>
                            </td>
                            <td class="px-8 py-6">
                                <p class="text-xs font-black text-slate-600 uppercase tracking-widest">RM <?php echo number_format($b['planned_budget'], 2); ?></p>
                            </td>
                            <td class="px-8 py-6">
                                <p class="text-xs font-black text-slate-400 uppercase tracking-widest">RM <?php echo number_format($b['actual_expense'], 2); ?></p>
                            </td>
                            <td class="px-8 py-6">
                                <p class="text-xs font-black <?php echo $v >= 0 ? 'text-green-600' : 'text-red-600'; ?> uppercase tracking-widest">
                                    RM <?php echo number_format($v, 2); ?>
                                </p>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <?php if ($v >= 0): ?>
                                <span class="px-4 py-2 text-[8px] font-black uppercase tracking-widest bg-green-50 text-green-700 border border-green-200">UNDER BUDGET</span>
                                <?php else: ?>
                                <span class="px-4 py-2 text-[8px] font-black uppercase tracking-widest bg-red-50 text-red-700 border border-red-200">OVER BUDGET</span>
                                <?php endif; ?>
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
