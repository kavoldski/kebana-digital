<?php
/**
 * KEBANA Digital Management System - Budget vs Actual (Phase A Enhanced)
 * File: modules/finance/budget.php
 */

use App\Helpers\FinanceHelper;

require_once APP_ROOT . '/includes/header.php';

if (!hasRole([888, 1, 2, 3, 6, 7, 55, 66])) {
    echo "<div class='p-12 text-center'><h1 class='text-2xl font-black text-red-600 uppercase tracking-widest'>AKSES DISEKAT</h1></div>";
    require_once APP_ROOT . '/includes/footer.php';
    exit;
}

$filters = [
    'year'   => isset($_GET['year'])   && is_numeric($_GET['year']) ? (int)$_GET['year'] : (int)date('Y'),
    'search' => trim($_GET['search'] ?? ''),
    'cawangan_id' => in_array($current_role, $CAWANGAN_ROLES) ? $current_cawangan_id : null
];

// Fetch current total funds for the cawangan (Option A)
$actual_totals = FinanceHelper::getTotals($filters['cawangan_id']);

$budgets_raw = FinanceHelper::getBudgetSummary($filters);

// ── Build Master → Sub hierarchy ─────────────────────────────────────────────
$masters = [];
$subs    = [];

foreach ($budgets_raw as $b) {
    $level = $b['event_level'] ?? 'MASTER';
    if ($level === 'MASTER') {
        $b['sub_events'] = [];
        $masters[$b['event_id']] = $b;
    } else {
        $subs[] = $b;
    }
}
foreach ($subs as $s) {
    $pid = $s['parent_event_id'] ?? 0;
    if (isset($masters[$pid])) {
        $masters[$pid]['sub_events'][] = $s;
    } else {
        // orphan sub — show as top-level
        $s['sub_events'] = [];
        $masters['orphan_' . $s['event_id']] = $s;
    }
}

// ── Roll up Sub totals into each Master (Cascading Budget Model) ──────────────────
// Master's displayed expense/income = its own direct transactions + all sub totals.
// Sub rows continue to show their own individual figures only.
foreach ($masters as &$m) {
    $m['own_expense'] = (float)$m['actual_expense'];
    $m['own_income']  = (float)$m['actual_income'];
    $sub_expense = 0;
    $sub_income  = 0;
    $sub_budget  = 0;
    foreach ($m['sub_events'] as $s) {
        $sub_expense += (float)$s['actual_expense'];
        $sub_income  += (float)$s['actual_income'];
        $sub_budget  += (float)$s['planned_budget'];
    }
    $m['actual_expense']  = $m['own_expense'] + $sub_expense;
    $m['actual_income']   = $m['own_income']  + $sub_income;
    $m['sub_budget_used'] = $sub_budget; // total budget allocated to subs
}
unset($m); // break reference

// ── KPI Totals (MASTER only for summary) ─────────────────────────────────────
$total_planned = 0; $total_expense = 0; $total_income = 0;
foreach ($masters as $m) {
    $total_planned += (float)$m['planned_budget'];
    $total_expense += (float)$m['actual_expense'];
    $total_income  += (float)$m['actual_income'];
}
$total_variance = $total_planned - $total_expense;
$total_net_pl   = $total_income - $total_expense;

// ── Chart data (top 10 events with budget set) ───────────────────────────────
$chart_events  = [];
$chart_budget  = [];
$chart_expense = [];
foreach ($masters as $m) {
    if ($m['planned_budget'] > 0 || $m['actual_expense'] > 0) {
        $chart_events[]  = addslashes($m['event_title']);
        $chart_budget[]  = (float)$m['planned_budget'];
        $chart_expense[] = (float)$m['actual_expense'];
        if (count($chart_events) >= 10) break;
    }
}

$js_events  = json_encode($chart_events);
$js_budget  = json_encode($chart_budget);
$js_expense = json_encode($chart_expense);

$page_title = 'ANALISIS BAJET';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div class="space-y-12">

    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 border-t-8 border-kebana-blue shadow-sm">
        <div>
            <h2 class="text-2xl font-black text-kebana-blue uppercase tracking-tight italic">Analisis Bajet vs Sebenar</h2>
            <p class="text-sm font-black text-slate-600 uppercase tracking-widest mt-2">Semakan Penggunaan Dana dan Prestasi Kewangan Setiap Program.</p>
        </div>
        <a href="<?= URL_ROOT ?>/finance" class="text-sm font-black text-slate-600 hover:text-kebana-blue uppercase tracking-widest flex items-center transition-colors">
            <i class="fa-solid fa-arrow-left mr-3"></i>
            KEMBALI KE DASHBOARD
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-8 border border-slate-300 shadow-sm">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
            <div>
                <label class="block text-sm font-black text-slate-600 uppercase tracking-widest mb-3">Tahun</label>
                <select name="year" class="w-full px-5 py-4 bg-slate-50 border-b-2 border-slate-300 focus:border-kebana-blue focus:bg-white outline-none text-base font-bold uppercase transition-all rounded-none appearance-none">
                    <?php for ($y = (int)date('Y'); $y >= (int)date('Y') - 4; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php echo $y === (int)$filters['year'] ? 'selected' : ''; ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-black text-slate-600 uppercase tracking-widest mb-3">Cari Nama Acara</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-lg"></i>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($filters['search']); ?>"
                           placeholder="Cari Nama Acara..."
                           class="w-full pl-12 pr-5 py-4 bg-slate-50 border-b-2 border-slate-300 focus:border-kebana-blue focus:bg-white outline-none text-base font-bold uppercase transition-all">
                </div>
            </div>
            <button type="submit" class="bg-kebana-dark text-white py-4 text-base font-black uppercase tracking-widest hover:bg-black transition-all shadow-lg">
                JANA ANALISIS
            </button>
        </form>
    </div>

    <!-- Actual Fund Summary (Option A - Premium Glass Style) -->
    <div class="relative overflow-hidden bg-slate-900 p-10 shadow-2xl">
        <!-- Abstract Background Shapes -->
        <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-kebana-blue/20 blur-3xl"></div>
        <div class="absolute -left-20 -bottom-20 h-64 w-64 rounded-full bg-kebana-yellow/10 blur-3xl"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-8">
            <div class="flex items-center gap-6">
                <div class="w-16 h-16 bg-white/5 backdrop-blur-md flex items-center justify-center border border-white/10">
                    <i class="fa-solid fa-vault text-2xl text-kebana-yellow animate-pulse"></i>
                </div>
                <div>
                    <p class="text-sm font-black text-kebana-yellow uppercase tracking-[0.4em] mb-2">Kedudukan Kewangan Semasa</p>
                    <h3 class="text-4xl font-black text-white tracking-tighter uppercase italic">Dana Tersedia</h3>
                </div>
            </div>
            
            <div class="text-center md:text-right">
                <div class="inline-block px-8 py-4 bg-white/5 backdrop-blur-xl border border-white/10 rounded-sm">
                    <p class="text-4xl font-black text-kebana-yellow tracking-tighter drop-shadow-2xl">
                        <span class="text-xl mr-1 font-bold">RM</span><?php echo number_format($actual_totals['balance'], 2); ?>
                    </p>
                </div>
                <p class="text-sm font-black text-slate-300 uppercase tracking-widest mt-4">
                    <i class="fa-solid fa-shield-check mr-2 text-green-500"></i> Baki tunai bersih dalam sistem
                </p>
            </div>
        </div>
    </div>

    <!-- KPI Stats (Option B - Premium Minimalist) -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-0 border border-slate-300 bg-white shadow-lg">
        <div class="p-10 border-r border-slate-300 flex flex-col justify-center hover:bg-slate-50/50 transition-colors">
            <p class="text-sm font-black text-slate-600 uppercase tracking-widest mb-1">JUMLAH BAJET PROGRAM</p>
            <p class="text-4xl font-black text-kebana-blue tracking-tighter">RM <?php echo number_format($total_planned, 2); ?></p>
            <div class="mt-4 flex items-center gap-2">
                <span class="h-1 w-8 bg-kebana-blue/20"></span>
                <span class="text-sm font-bold text-slate-600 uppercase tracking-tighter">Anggaran peruntukan</span>
            </div>
        </div>
        <div class="p-10 border-r border-slate-300 flex flex-col justify-center hover:bg-slate-50/50 transition-colors">
            <p class="text-sm font-black text-red-700 uppercase tracking-widest mb-1">PERBELANJAAN PROGRAM</p>
            <p class="text-4xl font-black text-red-600 tracking-tighter">RM <?php echo number_format($total_expense, 2); ?></p>
            <div class="mt-4 flex items-center gap-2">
                <span class="h-1 w-8 bg-red-600/20"></span>
                <span class="text-sm font-bold text-slate-600 uppercase tracking-tighter">Wang keluar (Program)</span>
            </div>
        </div>
        <div class="p-10 border-r border-slate-300 flex flex-col justify-center hover:bg-slate-50/50 transition-colors">
            <p class="text-sm font-black text-green-700 uppercase tracking-widest mb-1">HASIL / SUMBANGAN</p>
            <p class="text-4xl font-black text-green-600 tracking-tighter">RM <?php echo number_format($total_income, 2); ?></p>
            <div class="mt-4 flex items-center gap-2">
                <span class="h-1 w-8 bg-green-600/20"></span>
                <span class="text-sm font-bold text-slate-600 uppercase tracking-tighter">Wang masuk (Program)</span>
            </div>
        </div>
        <div class="p-10 flex flex-col justify-center border-b-8 <?php echo $total_net_pl >= 0 ? 'border-green-500' : 'border-red-500'; ?> bg-slate-50/30">
            <p class="text-sm font-black uppercase tracking-widest mb-1 <?php echo $total_net_pl >= 0 ? 'text-green-700' : 'text-red-700'; ?>">SURPLUS / DEFISIT PROGRAM</p>
            <p class="text-4xl font-black tracking-tighter <?php echo $total_net_pl >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                <?php echo ($total_net_pl >= 0 ? '+' : ''); ?>RM <?php echo number_format($total_net_pl, 2); ?>
            </p>
            <div class="mt-4 flex items-center gap-2">
                <i class="fa-solid <?php echo $total_net_pl >= 0 ? 'fa-check-double text-green-500' : 'fa-triangle-exclamation text-red-500'; ?> text-xs"></i>
                <span class="text-sm font-black uppercase tracking-wider <?php echo $total_net_pl >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                    <?php echo $total_net_pl >= 0 ? 'Prestasi Baik' : 'Semak Perbelanjaan'; ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Budget vs Actual Bar Chart -->
    <?php if (!empty($chart_events)): ?>
    <div class="bg-white border border-slate-300 shadow-sm overflow-hidden">
        <div class="p-8 border-b border-slate-300 bg-slate-50/50 flex items-center justify-between">
            <div>
                <h3 class="text-base font-black text-kebana-blue uppercase tracking-widest">
                    <i class="fa-solid fa-chart-bar mr-2 text-kebana-yellow"></i>
                    Bajet Dirancang vs Belanja Sebenar
                </h3>
                <p class="text-sm font-black text-slate-600 uppercase tracking-tighter mt-1">Perbandingan visual setiap program — Tahun <?php echo $filters['year']; ?></p>
            </div>
        </div>
        <div class="p-8" style="height: <?php echo min(80 + count($chart_events) * 48, 480); ?>px">
            <canvas id="budgetChart"></canvas>
        </div>
    </div>
    <?php endif; ?>

    <!-- Budget Table with Master/Sub Hierarchy -->
    <div class="bg-white border border-slate-300 shadow-sm overflow-hidden">
        <div class="p-8 border-b border-slate-300 bg-slate-50/50 flex items-center justify-between">
            <h3 class="text-base font-black text-kebana-blue uppercase tracking-widest">
                <i class="fa-solid fa-table-list mr-2"></i>
                Senarai Terperinci Per Program
            </h3>
            <div class="flex items-center gap-4 text-sm font-black uppercase tracking-widest">
                <span class="flex items-center gap-1"><span class="w-2 h-2 bg-green-500 inline-block"></span> Under Budget</span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 bg-red-500 inline-block"></span> Over Budget</span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 bg-slate-300 inline-block"></span> Tiada Bajet</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-300 bg-slate-100">
                        <th class="px-8 py-5 text-sm font-black text-slate-600 uppercase tracking-widest">Program</th>
                        <th class="px-6 py-5 text-sm font-black text-slate-600 uppercase tracking-widest text-right">Bajet</th>
                        <th class="px-6 py-5 text-sm font-black text-slate-600 uppercase tracking-widest text-right">Belanja</th>
                        <th class="px-6 py-5 text-sm font-black text-slate-600 uppercase tracking-widest text-right">Pendapatan</th>
                        <th class="px-6 py-5 text-sm font-black text-slate-600 uppercase tracking-widest text-right">P&L</th>
                        <th class="px-8 py-5 text-sm font-black text-slate-600 uppercase tracking-widest">Penggunaan Bajet</th>
                        <th class="px-6 py-5 text-sm font-black text-slate-600 uppercase tracking-widest text-center">Status</th>
                        <th class="px-6 py-5 text-sm font-black text-slate-600 uppercase tracking-widest text-center"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php if (empty($masters)): ?>
                    <tr>
                        <td colspan="8" class="px-8 py-20 text-center text-sm font-black text-slate-500 uppercase tracking-[0.3em]">
                            Tiada data dijumpai untuk kriteria ini.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($masters as $b):
                            $v          = (float)$b['planned_budget'] - (float)$b['actual_expense'];
                            $net_pl     = (float)$b['actual_income'] - (float)$b['actual_expense'];
                            $utilPct    = $b['planned_budget'] > 0 ? min(round(($b['actual_expense'] / $b['planned_budget']) * 100), 999) : null;
                            $isOver     = $b['planned_budget'] > 0 && $b['actual_expense'] > $b['planned_budget'];
                            $hasbudget  = $b['planned_budget'] > 0;
                            $barColor   = !$hasbudget ? 'bg-slate-200' : ($isOver ? 'bg-red-500' : 'bg-green-500');
                            $barWidth   = $hasbudget ? min($utilPct, 100) : 0;
                            $level      = $b['event_level'] ?? 'MASTER';
                        ?>
                        <!-- Master Row -->
                        <tr class="hover:bg-slate-50/30 transition-colors group bg-white">
                            <td class="px-8 py-5">
                                <div class="flex items-start gap-3">
                                    <span class="mt-0.5 px-2 py-0.5 text-sm font-black uppercase tracking-widest bg-kebana-blue text-white shrink-0"><?php echo $level; ?></span>
                                    <div>
                                        <p class="text-base font-black text-kebana-blue uppercase tracking-tight"><?php echo htmlspecialchars($b['event_title']); ?></p>
                                        <p class="text-sm font-bold text-slate-600 uppercase mt-0.5 italic">
                                            <?php echo htmlspecialchars($b['cawangan_name'] ?? 'Pusat'); ?>
                                            <?php if (!empty($b['event_date']) && $b['event_date'] !== '0000-00-00'): ?>
                                            &nbsp;·&nbsp; <?php echo date('d M Y', strtotime($b['event_date'])); ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <p class="text-base font-black text-slate-800 uppercase tracking-widest">
                                    <?php echo $hasbudget ? 'RM ' . number_format($b['planned_budget'], 2) : '<span class="text-slate-500 text-sm">—</span>'; ?>
                                </p>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <p class="text-base font-black text-red-700 uppercase tracking-widest">
                                    RM <?php echo number_format($b['actual_expense'], 2); ?>
                                </p>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <p class="text-base font-black text-green-700 uppercase tracking-widest">
                                    RM <?php echo number_format($b['actual_income'], 2); ?>
                                </p>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <p class="text-base font-black uppercase tracking-widest <?php echo $net_pl >= 0 ? 'text-green-700' : 'text-red-700'; ?>">
                                    <?php echo ($net_pl >= 0 ? '+' : '') . 'RM ' . number_format($net_pl, 2); ?>
                                </p>
                            </td>
                            <td class="px-8 py-5">
                                <?php if ($hasbudget): ?>
                                <div class="space-y-1">
                                    <div class="flex justify-between text-sm font-black uppercase text-slate-600">
                                        <span><?php echo $utilPct; ?>% digunakan</span>
                                        <?php if ($isOver): ?><span class="text-red-550">MELEBIHI</span><?php endif; ?>
                                    </div>
                                    <div class="h-2 w-full bg-slate-200 rounded-full overflow-hidden shadow-inner">
                                         <div class="h-full <?php echo $barColor; ?> rounded-full transition-all duration-1000 relative overflow-hidden"
                                              style="width: <?php echo $barWidth; ?>%">
                                              <div class="absolute inset-0 bg-white/20 skew-x-[-20deg] translate-x-[-100%] animate-[shimmer_2s_infinite]"></div>
                                         </div>
                                     </div>
                                </div>
                                <?php else: ?>
                                <span class="text-sm font-black text-slate-500 uppercase tracking-widest">Tiada bajet ditetapkan</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <?php if (!$hasbudget): ?>
                                <span class="px-3 py-1.5 text-sm font-black uppercase tracking-widest bg-slate-100 text-slate-650 border border-slate-300 whitespace-nowrap">TIADA BAJET</span>
                                <?php elseif ($isOver): ?>
                                <span class="px-3 py-1.5 text-sm font-black uppercase tracking-widest bg-red-100 text-red-800 border border-red-300 whitespace-nowrap">OVER BUDGET</span>
                                <?php else: ?>
                                <span class="px-3 py-1.5 text-sm font-black uppercase tracking-widest bg-green-100 text-green-800 border border-green-300 whitespace-nowrap">UNDER BUDGET</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <a href="<?= URL_ROOT ?>/finance/event/<?php echo $b['event_id']; ?>"
                                   class="text-sm font-black text-kebana-blue hover:text-white hover:bg-kebana-blue border border-kebana-blue/30 hover:border-kebana-blue px-4 py-2 transition-all uppercase tracking-widest inline-flex items-center gap-2">
                                    <i class="fa-solid fa-magnifying-glass-chart"></i> Lihat
                                </a>
                            </td>
                        </tr>

                        <!-- Sub Event Rows -->
                        <?php foreach ($b['sub_events'] as $s):
                            $sv      = (float)$s['planned_budget'] - (float)$s['actual_expense'];
                            $s_pl    = (float)$s['actual_income'] - (float)$s['actual_expense'];
                            $sUtil   = $s['planned_budget'] > 0 ? min(round(($s['actual_expense'] / $s['planned_budget']) * 100), 999) : null;
                            $sOver   = $s['planned_budget'] > 0 && $s['actual_expense'] > $s['planned_budget'];
                            $sHasBud = $s['planned_budget'] > 0;
                            $sBar    = !$sHasBud ? 'bg-slate-200' : ($sOver ? 'bg-red-400' : 'bg-emerald-400');
                            $sWidth  = $sHasBud ? min($sUtil, 100) : 0;
                        ?>
                        <tr class="hover:bg-slate-50 transition-colors group bg-slate-50/30">
                            <td class="px-8 py-4 pl-16">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-[1px] bg-slate-400"></div>
                                    <span class="px-1.5 py-0.5 text-sm font-black uppercase tracking-widest bg-slate-200 text-slate-700 shrink-0">SUB</span>
                                    <div>
                                        <p class="text-sm font-bold text-slate-800 uppercase tracking-tight group-hover:text-kebana-blue transition-colors"><?php echo htmlspecialchars($s['event_title']); ?></p>
                                        <p class="text-sm font-bold text-slate-500 uppercase mt-0.5">
                                            <?php echo htmlspecialchars($s['cawangan_name'] ?? 'Cawangan'); ?>
                                            <?php if (!empty($s['event_date']) && $s['event_date'] !== '0000-00-00'): ?>
                                            &nbsp;·&nbsp; <?php echo date('d M Y', strtotime($s['event_date'])); ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <p class="text-sm font-bold text-slate-750 uppercase tracking-widest">
                                    <?php echo $sHasBud ? 'RM ' . number_format($s['planned_budget'], 2) : '<span class="text-slate-500">—</span>'; ?>
                                </p>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <p class="text-sm font-bold text-red-700 uppercase tracking-widest">RM <?php echo number_format($s['actual_expense'], 2); ?></p>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <p class="text-sm font-bold text-green-700 uppercase tracking-widest">RM <?php echo number_format($s['actual_income'], 2); ?></p>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <p class="text-sm font-bold uppercase tracking-widest <?php echo $s_pl >= 0 ? 'text-green-700' : 'text-red-700'; ?>">
                                    <?php echo ($s_pl >= 0 ? '+' : '') . 'RM ' . number_format($s_pl, 2); ?>
                                </p>
                            </td>
                            <td class="px-8 py-4">
                                <?php if ($sHasBud): ?>
                                <div class="space-y-1">
                                    <div class="h-1.5 w-full bg-slate-200 rounded-full overflow-hidden">
                                        <div class="h-full <?php echo $sBar; ?> rounded-full transition-all duration-700"
                                             style="width: <?php echo $sWidth; ?>%"></div>
                                    </div>
                                    <p class="text-sm font-black text-slate-600 uppercase"><?php echo $sUtil; ?>%</p>
                                </div>
                                <?php else: ?>
                                <span class="text-sm font-black text-slate-500 uppercase">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if (!$sHasBud): ?>
                                <span class="text-sm font-black text-slate-500 uppercase">—</span>
                                <?php elseif ($sOver): ?>
                                <span class="px-2 py-1 text-sm font-black uppercase bg-red-100 text-red-700 border border-red-300 whitespace-nowrap">OVER</span>
                                <?php else: ?>
                                <span class="px-2 py-1 text-sm font-black uppercase bg-green-100 text-green-700 border border-green-300 whitespace-nowrap">OK</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="<?= URL_ROOT ?>/finance/event/<?php echo $s['event_id']; ?>"
                                   class="text-sm font-black text-slate-600 hover:text-white hover:bg-kebana-blue border border-slate-400 hover:border-kebana-blue px-3 py-1.5 transition-all uppercase tracking-widest inline-flex items-center gap-2">
                                    <i class="fa-solid fa-magnifying-glass-chart"></i> Lihat
                                </a>
                            </td>
                        </tr>

                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Budget vs Actual Chart -->
<?php if (!empty($chart_events)): ?>
<script>
(function() {
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.font.size   = 13;
    Chart.defaults.color       = '#475569'; // Darker gray for better contrast

    const ctx = document.getElementById('budgetChart');
    if (!ctx) return;

    // Create Gradients
    const budgetGradient = ctx.getContext('2d').createLinearGradient(0, 0, 400, 0);
    budgetGradient.addColorStop(0, 'rgba(0, 51, 102, 0.9)');
    budgetGradient.addColorStop(1, 'rgba(0, 74, 153, 0.7)');

    const expenseGradient = ctx.getContext('2d').createLinearGradient(0, 0, 400, 0);
    expenseGradient.addColorStop(0, 'rgba(239, 68, 68, 0.9)');
    expenseGradient.addColorStop(1, 'rgba(239, 68, 68, 0.6)');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo $js_events; ?>,
            datasets: [
                {
                    label: 'Bajet Dirancang',
                    data: <?php echo $js_budget; ?>,
                    backgroundColor: budgetGradient,
                    borderColor: '#003366',
                    borderWidth: 1,
                    borderRadius: 4,
                    barThickness: 16,
                },
                {
                    label: 'Belanja Sebenar',
                    data: <?php echo $js_expense; ?>,
                    backgroundColor: expenseGradient,
                    borderColor: '#ef4444',
                    borderWidth: 1,
                    borderRadius: 4,
                    barThickness: 16,
                }
            ]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            categoryPercentage: 0.8,
            barPercentage: 0.9,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end',
                    labels: { 
                        font: { weight: '800', size: 12 }, 
                        padding: 25, 
                        usePointStyle: true,
                        pointStyle: 'rectRounded'
                    }
                },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleFont: { weight: '800', size: 12 },
                    bodyFont: { weight: '600' },
                    padding: 12,
                    cornerRadius: 0,
                    callbacks: {
                        label: ctx => ' RM ' + ctx.parsed.x.toLocaleString('ms-MY', { minimumFractionDigits: 2 })
                    }
                }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(226, 232, 240, 0.8)', drawBorder: false }, // Better contrast grid lines
                    ticks: {
                        font: { weight: '800', size: 12 },
                        padding: 10,
                        callback: v => 'RM ' + v.toLocaleString('ms-MY')
                    }
                },
                y: {
                    grid: { display: false },
                    ticks: {
                        font: { weight: '800', size: 12 },
                        color: '#003366',
                        padding: 15,
                        callback: function(val) {
                            const label = this.getLabelForValue(val);
                            return label.length > 25 ? label.substring(0, 25) + '…' : label;
                        }
                    }
                }
            }
        }
    });
})();
</script>
<?php endif; ?>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
