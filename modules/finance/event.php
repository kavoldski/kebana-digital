<?php
/**
 * KEBANA Digital Management System - Event Financial Drilldown (Phase B)
 * File: modules/finance/event.php
 * Route: /kebana-digital/finance/event/{id}
 */

use App\Helpers\FinanceHelper;
use App\Helpers\EventsHelper;

require_once APP_ROOT . '/includes/header.php';

if (!hasRole([888, 1, 2, 3, 6, 7, 11, 33, 55, 66])) {
    echo "<div class='p-12 text-center'><h1 class='text-2xl font-black text-red-600 uppercase tracking-widest'>AKSES DISEKAT</h1></div>";
    require_once APP_ROOT . '/includes/footer.php';
    exit;
}

$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($event_id <= 0) {
    header('Location: ' . URL_ROOT . '/finance/budget');
    exit;
}

// ── Fetch event details ───────────────────────────────────────────────────────
$event = EventsHelper::getEventById($event_id);
if (!$event) {
    echo "<div class='p-12 text-center'><p class='text-slate-400 uppercase font-black text-sm'>Acara tidak dijumpai.</p></div>";
    require_once APP_ROOT . '/includes/footer.php';
    exit;
}

// Access Control - Branch restriction
if (in_array($current_role, $CAWANGAN_ROLES) && (int)$event['cawangan_id'] !== (int)$current_cawangan_id) {
    echo "<div class='p-12 text-center'><h1 class='text-2xl font-black text-red-600 uppercase tracking-widest'>AKSES DISEKAT</h1><p class='text-slate-400 mt-4 uppercase font-bold text-xs'>Anda tidak mempunyai kebenaran untuk melihat laporan kewangan cawangan lain.</p></div>";
    require_once APP_ROOT . '/includes/footer.php';
    exit;
}

// ── Financial data ────────────────────────────────────────────────────────────
$transactions    = FinanceHelper::getTransactionsByEvent($event_id);
$cat_breakdown   = FinanceHelper::getEventCategoryBreakdown($event_id);

$total_income  = 0;
$total_expense = 0;
foreach ($transactions as $t) {
    if ($t['trans_type'] === 'Income')  $total_income  += $t['amount'];
    else                                $total_expense += $t['amount'];
}
$net_pl       = $total_income - $total_expense;
$budget_est   = (float)($event['budget_est'] ?? 0);
$variance     = $budget_est > 0 ? $budget_est - $total_expense : null;
$util_pct     = $budget_est > 0 ? min(round(($total_expense / $budget_est) * 100), 999) : null;
$is_over      = $budget_est > 0 && $total_expense > $budget_est;

// Running balance for line chart
$running = 0;
$bal_labels = [];
$bal_data   = [];
foreach ($transactions as $t) {
    $running += ($t['trans_type'] === 'Income') ? $t['amount'] : -$t['amount'];
    $bal_labels[] = $t['trans_date'];
    $bal_data[]   = round($running, 2);
}

// Sub-events P&L (if master)
$sub_events = [];
if (($event['event_level'] ?? 'MASTER') === 'MASTER') {
    $sub_events = EventsHelper::getSubEvents($event_id);
}

$js_bal_labels  = json_encode($bal_labels);
$js_bal_data    = json_encode($bal_data);
$js_cat_labels  = json_encode(array_column($cat_breakdown, 'label'));
$js_cat_totals  = json_encode(array_column($cat_breakdown, 'total'));

$page_title = 'LAPORAN KEWANGAN ACARA';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div class="space-y-12">

    <!-- Breadcrumb + Action Bar -->
    <div class="flex flex-col md:flex-row md:items-start justify-between gap-6 bg-white p-8 border-t-8 border-kebana-blue shadow-sm">
        <div class="min-w-0">
            <!-- Breadcrumb -->
            <div class="flex items-center gap-2 text-sm font-black text-slate-600 uppercase tracking-widest mb-4">
                <a href="<?= URL_ROOT ?>/finance" class="hover:text-kebana-blue transition-colors">Kewangan</a>
                <i class="fa-solid fa-chevron-right text-slate-350"></i>
                <a href="<?= URL_ROOT ?>/finance/budget" class="hover:text-kebana-blue transition-colors">Analisis Bajet</a>
                <i class="fa-solid fa-chevron-right text-slate-355"></i>
                <span class="text-kebana-blue truncate max-w-xs"><?php echo htmlspecialchars($event['event_title']); ?></span>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <span class="px-3 py-1 text-sm font-black uppercase tracking-widest <?php echo ($event['event_level'] ?? 'MASTER') === 'MASTER' ? 'bg-kebana-blue text-white' : 'bg-slate-200 text-slate-700'; ?>">
                    <?php echo $event['event_level'] ?? 'MASTER'; ?>
                </span>
                <h2 class="text-2xl font-black text-kebana-blue uppercase tracking-tight italic">
                    <?php echo htmlspecialchars($event['event_title']); ?>
                </h2>
            </div>
            <div class="flex items-center gap-4 mt-3 flex-wrap">
                <?php if (!empty($event['event_date']) && $event['event_date'] !== '0000-00-00'): ?>
                <p class="text-sm font-black text-slate-500 uppercase tracking-widest flex items-center gap-2">
                    <i class="fa-solid fa-calendar-day text-kebana-blue/30"></i>
                    <?php echo date('d F Y', strtotime($event['event_date'])); ?>
                    <?php if (!empty($event['event_end_date']) && $event['event_end_date'] !== '0000-00-00' && $event['event_end_date'] !== $event['event_date']): ?>
                    &nbsp;— <?php echo date('d F Y', strtotime($event['event_end_date'])); ?>
                    <?php endif; ?>
                </p>
                <?php endif; ?>
                <?php if (!empty($event['venue'])): ?>
                <p class="text-sm font-black text-slate-500 uppercase tracking-widest flex items-center gap-2">
                    <i class="fa-solid fa-location-dot text-kebana-blue/30"></i>
                    <?php echo htmlspecialchars($event['venue']); ?>
                </p>
                <?php endif; ?>
                <?php 
                $ev_status = $event['status'] ?? 'Draft';
                $s_cls = match(strtoupper($ev_status)) {
                    'APPROVED' => 'bg-green-100 text-green-800 border border-green-300',
                    'SUBMITTED' => 'bg-amber-100 text-amber-800 border border-amber-300',
                    'REJECTED' => 'bg-red-100 text-red-800 border border-red-300',
                    default => 'bg-slate-100 text-slate-600 border border-slate-300',
                };
                ?>
                <span class="px-3 py-1 text-sm font-black uppercase tracking-widest <?php echo $s_cls; ?>"><?php echo $ev_status; ?></span>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 shrink-0">
            <a href="<?= URL_ROOT ?>/finance/transactions/create?type=Expense&event_id=<?php echo $event_id; ?>" 
               class="bg-red-600 text-white px-7 py-4 text-sm font-black uppercase tracking-[0.15em] hover:bg-red-700 transition-all shadow-lg inline-flex items-center justify-center">
                <i class="fa-solid fa-arrow-trend-down mr-3"></i>
                REKOD KELUAR
            </a>
            <a href="<?= URL_ROOT ?>/finance/transactions/create?type=Income&event_id=<?php echo $event_id; ?>"
               class="bg-green-600 text-white px-7 py-4 text-sm font-black uppercase tracking-[0.15em] hover:bg-green-700 transition-all shadow-lg inline-flex items-center justify-center">
                <i class="fa-solid fa-arrow-trend-up mr-3"></i>
                REKOD MASUK
            </a>
            <a href="<?= URL_ROOT ?>/events/view/<?php echo $event_id; ?>"
               class="bg-white text-kebana-blue border-2 border-slate-300 px-7 py-4 text-sm font-black uppercase tracking-widest hover:bg-slate-50 transition-all inline-flex items-center justify-center">
                <i class="fa-solid fa-calendar-star mr-3"></i>
                LIHAT ACARA
            </a>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-0 border border-slate-300 bg-white">
        <div class="p-8 border-r border-slate-300 flex flex-col justify-center">
            <p class="text-sm font-black text-slate-600 uppercase tracking-widest">BAJET DIRANCANG</p>
            <?php if ($budget_est > 0): ?>
            <p class="text-3xl font-black text-kebana-blue mt-3">RM <?php echo number_format($budget_est, 2); ?></p>
            <div class="mt-3 space-y-1">
                <div class="flex justify-between text-sm font-black text-slate-600 uppercase">
                    <span><?php echo $util_pct; ?>% digunakan</span>
                    <?php if ($is_over): ?><span class="text-red-500">MELEBIHI</span><?php endif; ?>
                </div>
                <div class="h-1.5 w-full bg-slate-200 rounded-full overflow-hidden">
                    <div class="h-full <?php echo $is_over ? 'bg-red-500' : 'bg-green-500'; ?> rounded-full"
                          style="width:<?php echo min($util_pct, 100); ?>%"></div>
                </div>
            </div>
            <?php else: ?>
            <p class="text-xl font-black text-slate-400 mt-3">Tiada Bajet</p>
            <?php endif; ?>
        </div>
        <div class="p-8 border-r border-slate-300 flex flex-col justify-center">
            <p class="text-sm font-black text-red-700 uppercase tracking-widest">BELANJA SEBENAR</p>
            <p class="text-3xl font-black text-red-600 mt-3">RM <?php echo number_format($total_expense, 2); ?></p>
            <?php if ($variance !== null): ?>
            <p class="text-sm font-black mt-2 uppercase tracking-widest <?php echo $variance >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                Varian: <?php echo ($variance >= 0 ? '+' : '') . 'RM ' . number_format($variance, 2); ?>
            </p>
            <?php endif; ?>
        </div>
        <div class="p-8 border-r border-slate-300 flex flex-col justify-center">
            <p class="text-sm font-black text-green-700 uppercase tracking-widest">PENDAPATAN TERKUMPUL</p>
            <p class="text-3xl font-black text-green-600 mt-3">RM <?php echo number_format($total_income, 2); ?></p>
            <p class="text-sm font-black text-slate-600 mt-2 uppercase"><?php echo count(array_filter($transactions, fn($t) => $t['trans_type']==='Income')); ?> rekod masuk</p>
        </div>
        <div class="p-8 flex flex-col justify-center border-b-8 <?php echo $net_pl >= 0 ? 'border-green-500' : 'border-red-500'; ?>">
            <p class="text-sm font-black uppercase tracking-widest <?php echo $net_pl >= 0 ? 'text-green-700' : 'text-red-700'; ?>">UNTUNG / RUGI BERSIH</p>
            <p class="text-3xl font-black mt-3 <?php echo $net_pl >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                <?php echo ($net_pl >= 0 ? '+' : '') . 'RM ' . number_format($net_pl, 2); ?>
            </p>
            <p class="text-sm font-black mt-2 uppercase tracking-wider <?php echo $net_pl >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                <?php echo $net_pl >= 0 ? '✓ Surplus' : '⚠ Defisit'; ?>
            </p>
        </div>
    </div>

    <?php if (!empty($transactions)): ?>
    <!-- Charts Row: Running Balance + Category Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-0 border border-slate-300 bg-white shadow-sm overflow-hidden">

        <!-- Running Balance Line Chart (3/5) -->
        <div class="lg:col-span-3 border-r border-slate-300">
            <div class="p-8 border-b border-slate-300 bg-slate-50/50">
                <h3 class="text-base font-black text-kebana-blue uppercase tracking-widest">
                    <i class="fa-solid fa-chart-line mr-2 <?php echo $net_pl >= 0 ? 'text-green-500' : 'text-red-500'; ?>"></i>
                    Aliran Dana — <?php echo htmlspecialchars($event['event_title']); ?>
                </h3>
                <p class="text-sm font-black text-slate-600 uppercase tracking-tighter mt-1">Baki kumulatif mengikut urutan transaksi</p>
            </div>
            <div class="p-8" style="height: 280px;">
                <canvas id="balChart"></canvas>
            </div>
        </div>

        <!-- Expense Category Donut (2/5) -->
        <div class="lg:col-span-2">
            <div class="p-8 border-b border-slate-300 bg-slate-50/50">
                <h3 class="text-base font-black text-kebana-blue uppercase tracking-widest">
                    <i class="fa-solid fa-chart-pie mr-2 text-red-500"></i>
                    Pecahan Perbelanjaan
                </h3>
                <p class="text-sm font-black text-slate-600 uppercase tracking-tighter mt-1">Mengikut kategori</p>
            </div>
            <div class="p-8 flex items-center justify-center" style="height: 280px;">
                <?php if (!empty($cat_breakdown)): ?>
                <canvas id="catChart"></canvas>
                <?php else: ?>
                <p class="text-sm font-black text-slate-400 uppercase tracking-widest text-center">Tiada perbelanjaan direkodkan</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Transaction Log -->
    <div class="bg-white border border-slate-300 shadow-sm overflow-hidden">
        <div class="p-8 border-b border-slate-300 bg-slate-50/50 flex items-center justify-between">
            <h3 class="text-base font-black text-kebana-blue uppercase tracking-widest">
                <i class="fa-solid fa-receipt mr-2"></i>
                Log Transaksi Program
            </h3>
            <span class="text-sm font-black text-slate-600 uppercase tracking-tighter italic">
                <?php echo count($transactions); ?> rekod dijumpai
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-300 bg-slate-100">
                        <th class="px-8 py-5 text-sm font-black text-slate-600 uppercase tracking-widest">Tarikh</th>
                        <th class="px-8 py-5 text-sm font-black text-slate-600 uppercase tracking-widest">Jenis</th>
                        <th class="px-8 py-5 text-sm font-black text-slate-600 uppercase tracking-widest">Kategori</th>
                        <th class="px-8 py-5 text-sm font-black text-slate-600 uppercase tracking-widest">Mod</th>
                        <th class="px-8 py-5 text-sm font-black text-slate-600 uppercase tracking-widest text-center">Resit</th>
                        <th class="px-8 py-5 text-sm font-black text-slate-600 uppercase tracking-widest text-right">Amaun</th>
                        <th class="px-8 py-5 text-sm font-black text-slate-600 uppercase tracking-widest text-right">Direkod Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="7" class="px-8 py-20 text-center text-sm font-black text-slate-500 uppercase tracking-[0.3em]">
                            Tiada transaksi direkodkan untuk program ini.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $t):
                            $is_income = $t['trans_type'] === 'Income';
                        ?>
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-8 py-5">
                                <p class="text-sm font-black text-slate-600 uppercase"><?php echo date('d M Y', strtotime($t['trans_date'])); ?></p>
                            </td>
                            <td class="px-8 py-5">
                                <span class="px-3 py-1 text-sm font-black uppercase tracking-widest <?php echo $is_income ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300'; ?>">
                                    <?php echo $is_income ? 'Masuk' : 'Keluar'; ?>
                                </span>
                            </td>
                            <td class="px-8 py-5">
                                <p class="text-base font-black text-kebana-blue uppercase tracking-tight"><?php echo htmlspecialchars($t['category']); ?></p>
                            </td>
                            <td class="px-8 py-5">
                                <span class="text-sm font-black px-3 py-1 bg-slate-200 text-slate-700 uppercase tracking-widest">
                                    <?php echo (!empty($t['payment_mode']) && $t['payment_mode'] !== '0') ? htmlspecialchars($t['payment_mode']) : 'Cash'; ?>
                                </span>
                            </td>
                            <td class="px-8 py-5 text-center">
                                <?php if (!empty($t['receipt_path'])): ?>
                                <a href="<?= URL_ROOT ?>/<?php echo htmlspecialchars($t['receipt_path']); ?>" target="_blank"
                                   class="text-kebana-blue hover:text-kebana-accent transition-colors" title="Lihat Resit">
                                    <i class="fa-solid fa-file-invoice-dollar text-lg"></i>
                                </a>
                                <?php else: ?>
                                <span class="text-slate-400 text-sm font-black uppercase">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <p class="text-base font-black <?php echo $is_income ? 'text-green-600' : 'text-red-700'; ?>">
                                    <?php echo $is_income ? '+' : '-'; ?> RM <?php echo number_format($t['amount'], 2); ?>
                                </p>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <p class="text-sm font-black text-slate-600 uppercase tracking-widest"><?php echo htmlspecialchars($t['recorder_name'] ?? 'Sistem'); ?></p>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($transactions)): ?>
                <tfoot class="border-t-2 border-slate-300">
                    <tr class="bg-slate-50/50">
                        <td colspan="5" class="px-8 py-5 text-sm font-black text-slate-600 uppercase tracking-widest">Jumlah Keseluruhan</td>
                        <td class="px-8 py-5 text-right">
                            <p class="text-base font-black text-green-700">+ RM <?php echo number_format($total_income, 2); ?></p>
                            <p class="text-base font-black text-red-700">− RM <?php echo number_format($total_expense, 2); ?></p>
                            <div class="border-t border-slate-300 mt-2 pt-2">
                                <p class="text-base font-black <?php echo $net_pl >= 0 ? 'text-green-700' : 'text-red-700'; ?>">
                                    <?php echo ($net_pl >= 0 ? '+' : '') . 'RM ' . number_format($net_pl, 2); ?>
                                </p>
                            </div>
                        </td>
                        <td class="px-8 py-5"></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <?php if (!empty($sub_events)): ?>
    <!-- Sub-Events linked to this Master -->
    <div class="bg-white border border-slate-300 shadow-sm overflow-hidden">
        <div class="p-8 border-b border-slate-300 bg-slate-50/50">
            <h3 class="text-base font-black text-kebana-blue uppercase tracking-widest">
                <i class="fa-solid fa-sitemap mr-2 text-kebana-yellow"></i>
                Sub-Aktiviti Berkaitan (<?php echo count($sub_events); ?>)
            </h3>
        </div>
        <div class="divide-y divide-slate-200">
            <?php foreach ($sub_events as $sub): ?>
            <a href="<?= URL_ROOT ?>/finance/event/<?php echo $sub['event_id']; ?>"
               class="flex items-center justify-between px-8 py-5 hover:bg-slate-50 transition-colors group">
                <div>
                    <p class="text-sm font-black text-slate-700 uppercase group-hover:text-kebana-blue transition-colors"><?php echo htmlspecialchars($sub['event_title']); ?></p>
                    <p class="text-xs font-bold text-slate-550 uppercase mt-1"><?php echo htmlspecialchars($sub['cawangan_name'] ?? 'Cawangan'); ?> &nbsp;·&nbsp; <?php echo !empty($sub['event_date']) ? date('d M Y', strtotime($sub['event_date'])) : 'TBA'; ?></p>
                </div>
                <div class="flex items-center gap-4">
                    <?php
                    $s_status = $sub['status'] ?? 'Draft';
                    $ss_cls = match(strtoupper($s_status)) {
                        'APPROVED' => 'bg-green-100 text-green-800 border border-green-300',
                        'SUBMITTED' => 'bg-amber-100 text-amber-800 border border-amber-300',
                        'REJECTED' => 'bg-red-100 text-red-800 border border-red-300',
                        default => 'bg-slate-100 text-slate-600 border border-slate-300'
                    };
                    ?>
                    <span class="px-3 py-1 text-sm font-black uppercase tracking-widest <?php echo $ss_cls; ?>"><?php echo $s_status; ?></span>
                    <i class="fa-solid fa-arrow-right text-slate-400 group-hover:text-kebana-blue transition-colors text-sm"></i>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- Charts Init -->
<script>
(function() {
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.font.size   = 13;
    Chart.defaults.color       = '#475569'; // Darker gray for better contrast

    // ── Running Balance Line Chart ──────────────────────────────────
    const balCtx = document.getElementById('balChart');
    if (balCtx) {
        const balData  = <?php echo $js_bal_data; ?>;
        const lastBal  = balData.length ? balData[balData.length - 1] : 0;
        const lineCol  = lastBal >= 0 ? '#22c55e' : '#ef4444';
        const fillCol  = lastBal >= 0 ? 'rgba(34,197,94,0.1)' : 'rgba(239,68,68,0.1)';

        new Chart(balCtx, {
            type: 'line',
            data: {
                labels: <?php echo $js_bal_labels; ?>,
                datasets: [{
                    label: 'Baki Dana (RM)',
                    data: balData,
                    borderColor: lineCol,
                    backgroundColor: fillCol,
                    fill: true,
                    tension: 0.4,
                    pointRadius: balData.length < 20 ? 5 : 3,
                    pointHoverRadius: 7,
                    pointBackgroundColor: lineCol,
                    borderWidth: 2.5,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: c => ' RM ' + c.parsed.y.toLocaleString('ms-MY', {minimumFractionDigits:2}) } }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { weight:'700', size: 12 }, maxTicksLimit: 8 } },
                    y: { grid: { color: '#e2e8f0' }, ticks: { font: { weight:'700', size: 12 }, callback: v => 'RM ' + v.toLocaleString('ms-MY') } }
                }
            }
        });
    }

    // ── Expense Category Donut ──────────────────────────────────────
    const catCtx = document.getElementById('catChart');
    if (catCtx) {
        new Chart(catCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo $js_cat_labels; ?>,
                datasets: [{
                    data: <?php echo $js_cat_totals; ?>,
                    backgroundColor: ['#003366','#004A99','#0066CC','#0080FF','#FFCC00','#FFB700','#FF8800','#FF5500'],
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { position: 'bottom', labels: { font: { weight:'700', size: 12 }, padding: 12, boxWidth: 12 } },
                    tooltip: { callbacks: { label: c => ' RM ' + c.parsed.toLocaleString('ms-MY', {minimumFractionDigits:2}) } }
                }
            }
        });
    }
})();
</script>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
