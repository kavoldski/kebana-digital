<?php
/**
 * KEBANA Digital Management System - Finance Dashboard with Data Visualisation
 * File: modules/finance/dashboard.php
 */

use App\Helpers\FinanceHelper;

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/dbconnect.php';

$session_role = (int)($_SESSION['role'] ?? 0);
$session_cawangan_id = isset($_SESSION['cawangan_id']) ? (int)$_SESSION['cawangan_id'] : null;
$CAWANGAN_ROLES = [11, 22, 33, 44, 55, 66];

$cawangan_name = '';
if (in_array($session_role, $CAWANGAN_ROLES) && $session_cawangan_id) {
    $db = \App\Core\Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT cawangan_name FROM tbl_cawangan WHERE cawangan_id = ?");
    $stmt->bind_param("i", $session_cawangan_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $cawangan_name = $row['cawangan_name'];
    }
    $stmt->close();
}

$page_title = (in_array($session_role, $CAWANGAN_ROLES)) ? "RINGKASAN KEWANGAN CAWANGAN $cawangan_name" : "RINGKASAN KEWANGAN KEBANA PUSAT";

require_once APP_ROOT . '/includes/header.php';

// Access Control: Admin/Executives/Secretaries/Treasurers
if (!hasRole([888, 1, 2, 3, 4, 11, 22, 33, 6, 55, 7, 66])) {
    echo "<div class='p-12 text-center'><h1 class='text-2xl font-black text-red-600 uppercase tracking-widest'>AKSES DISEKAT</h1></div>";
    require_once APP_ROOT . '/includes/footer.php';
    exit;
}

$chart_year = isset($_GET['year']) && is_numeric($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// Scoping based on role
$scope_cawangan = in_array($current_role, $CAWANGAN_ROLES) ? $current_cawangan_id : null;

$totals       = FinanceHelper::getTotals($scope_cawangan);
$recent       = FinanceHelper::getRecentTransactions(8, $scope_cawangan);
$monthly      = FinanceHelper::getMonthlyChartData($chart_year, $scope_cawangan);
$runningBal   = FinanceHelper::getRunningBalanceData($chart_year, $scope_cawangan);
$catBreakdown = FinanceHelper::getCategoryBreakdown($chart_year, $scope_cawangan);

// Branch breakdown for Pusat roles
$branchTotals = ($scope_cawangan === null) ? FinanceHelper::getBranchTotals() : [];

// Prepare JS-safe JSON
$month_labels   = json_encode(['Jan','Feb','Mac','Apr','Mei','Jun','Jul','Ogos','Sep','Okt','Nov','Dis']);
$monthly_income = json_encode(array_values(array_column($monthly, 'income')));
$monthly_expense= json_encode(array_values(array_column($monthly, 'expense')));

$balance_labels = json_encode(array_column($runningBal, 'date'));
$balance_data   = json_encode(array_column($runningBal, 'balance'));

$cat_labels = json_encode(array_column($catBreakdown, 'label'));
$cat_totals = json_encode(array_column($catBreakdown, 'total'));

$page_title = 'PENGURUSAN KEWANGAN';
?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div class="space-y-12">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 border-t-8 border-kebana-blue shadow-sm">
        <div>
            <h2 class="text-2xl font-black text-kebana-blue uppercase tracking-tight italic">Ringkasan Kewangan</h2>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Pemantauan Aliran Tunai dan Perbelanjaan Persatuan.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="<?= URL_ROOT ?>/finance/transactions/list" class="bg-white text-kebana-blue border-2 border-kebana-blue px-7 py-4 text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 transition-all flex items-center">
                <i class="fa-solid fa-list-check mr-3"></i>
                LIHAT SEMUA
            </a>
            <a href="<?= URL_ROOT ?>/finance/budget" class="bg-white text-kebana-blue border-2 border-slate-200 px-7 py-4 text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 transition-all flex items-center">
                <i class="fa-solid fa-chart-bar mr-3"></i>
                ANALISIS BAJET
            </a>
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

    <!-- KPI Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-0 border border-slate-100 bg-white">
        <div class="p-10 border-r border-slate-50 flex flex-col justify-center bg-kebana-blue group relative overflow-hidden transition-all duration-500">
            <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <p class="text-[10px] font-black text-white/60 uppercase tracking-widest relative z-10">DANA TERSEDIA (NET)</p>
            <p class="text-4xl font-black text-white mt-4 relative z-10 tracking-tighter">RM <?php echo number_format($totals['balance'], 2); ?></p>
            <p class="text-[9px] font-black mt-3 uppercase tracking-widest relative z-10 <?php echo $totals['balance'] >= 0 ? 'text-green-400' : 'text-red-400'; ?>">
                <i class="fa-solid fa-circle-dot mr-1"></i>
                <?php echo $totals['balance'] >= 0 ? 'Positif' : 'Defisit'; ?>
            </p>
            <i class="fa-solid fa-wallet absolute -right-4 -bottom-4 text-7xl text-white/5 group-hover:text-white/10 group-hover:scale-110 transition-all duration-700"></i>
        </div>
        <div class="p-10 border-r border-slate-50 flex flex-col justify-center hover:bg-slate-50 transition-colors group">
            <p class="text-[10px] font-black text-green-600/50 uppercase tracking-widest">JUMLAH PENDAPATAN</p>
            <p class="text-4xl font-black text-green-600 mt-4">RM <?php echo number_format($totals['income'], 2); ?></p>
            <?php
                $income_pct = $totals['income'] > 0 && $totals['expense'] > 0 
                    ? round(($totals['income'] / ($totals['income'] + $totals['expense'])) * 100) : 0;
            ?>
            <div class="mt-4 h-1.5 w-full bg-slate-100">
                <div class="h-full bg-green-500 transition-all duration-1000" style="width:<?php echo $income_pct; ?>%"></div>
            </div>
        </div>
        <div class="p-10 flex flex-col justify-center hover:bg-slate-50 transition-colors group">
            <p class="text-[10px] font-black text-red-600/50 uppercase tracking-widest">JUMLAH PERBELANJAAN</p>
            <p class="text-4xl font-black text-red-600 mt-4">RM <?php echo number_format($totals['expense'], 2); ?></p>
            <?php $expense_pct = 100 - $income_pct; ?>
            <div class="mt-4 h-1.5 w-full bg-slate-100">
                <div class="h-full bg-red-500 transition-all duration-1000" style="width:<?php echo $expense_pct; ?>%"></div>
            </div>
        </div>
    </div>
    
    <!-- Branch Fund Breakdown (Pusat Role Only) -->
    <?php if (!empty($branchTotals)): ?>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-black text-kebana-blue uppercase tracking-[0.3em] flex items-center gap-3">
                <i class="fa-solid fa-building-columns text-kebana-yellow"></i>
                Pecahan Dana Mengikut Cawangan
            </h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <?php foreach ($branchTotals as $bt): ?>
            <div class="bg-white p-6 border border-slate-100 shadow-sm hover:border-kebana-blue/30 transition-all group">
                <p class="text-[8px] font-black text-slate-300 uppercase tracking-widest mb-2"><?php echo htmlspecialchars($bt['name']); ?></p>
                <div class="flex items-end justify-between">
                    <p class="text-lg font-black text-kebana-blue">RM <?php echo number_format($bt['balance'], 2); ?></p>
                    <div class="text-right">
                        <p class="text-[7px] font-bold text-green-500 uppercase">+ RM <?php echo number_format($bt['income'], 2); ?></p>
                        <p class="text-[7px] font-bold text-red-400 uppercase">- RM <?php echo number_format($bt['expense'], 2); ?></p>
                    </div>
                </div>
                <div class="mt-4 h-1 w-full bg-slate-50 rounded-full overflow-hidden">
                    <?php 
                        $b_pct = ($bt['income'] + $bt['expense'] > 0) ? round(($bt['income'] / ($bt['income'] + $bt['expense'])) * 100) : 0;
                    ?>
                    <div class="h-full bg-green-500" style="width: <?php echo $b_pct; ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ========== CHARTS SECTION ========== -->
    <!-- Year Selector -->
    <div class="flex items-center justify-between">
        <h2 class="text-xs font-black text-kebana-blue uppercase tracking-[0.3em] flex items-center gap-3">
            <i class="fa-solid fa-chart-mixed text-kebana-yellow"></i>
            Analisis Visual — Tahun <?php echo $chart_year; ?>
        </h2>
        <form method="GET" class="flex items-center gap-3">
            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Tahun:</label>
            <select name="year" onchange="this.form.submit()"
                    class="px-4 py-2 bg-white border border-slate-200 text-xs font-black text-kebana-blue uppercase outline-none focus:border-kebana-blue rounded-none appearance-none cursor-pointer">
                <?php for ($y = (int)date('Y'); $y >= (int)date('Y') - 4; $y--): ?>
                <option value="<?php echo $y; ?>" <?php echo $y === $chart_year ? 'selected' : ''; ?>><?php echo $y; ?></option>
                <?php endfor; ?>
            </select>
        </form>
    </div>

    <!-- Chart Row 1: Monthly Cash Flow (full width) -->
    <div class="bg-white border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-8 border-b border-slate-50 bg-slate-50/50 flex items-center justify-between">
            <div>
                <h3 class="text-xs font-black text-kebana-blue uppercase tracking-widest">
                    <i class="fa-solid fa-chart-column mr-2 text-kebana-yellow"></i>
                    Aliran Tunai Bulanan
                </h3>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-tighter mt-1">Perbandingan pendapatan vs perbelanjaan setiap bulan</p>
            </div>
        </div>
        <div class="p-8" style="height: 320px;">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>

    <!-- Chart Row 2: Running Balance + Category Donut (side by side) -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-0 border border-slate-100 bg-white shadow-sm overflow-hidden">
        <!-- Running Balance (3/5 width) -->
        <div class="lg:col-span-3 border-r border-slate-100">
            <div class="p-8 border-b border-slate-50 bg-slate-50/50">
                <h3 class="text-xs font-black text-kebana-blue uppercase tracking-widest">
                    <i class="fa-solid fa-chart-line mr-2 text-green-500"></i>
                    Baki Kumulatif Dana
                </h3>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-tighter mt-1">Trend kesihatan kewangan sepanjang tahun</p>
            </div>
            <div class="p-8" style="height: 300px;">
                <canvas id="balanceChart"></canvas>
            </div>
        </div>

        <!-- Category Donut (2/5 width) -->
        <div class="lg:col-span-2">
            <div class="p-8 border-b border-slate-50 bg-slate-50/50">
                <h3 class="text-xs font-black text-kebana-blue uppercase tracking-widest">
                    <i class="fa-solid fa-chart-pie mr-2 text-red-500"></i>
                    Pecahan Perbelanjaan
                </h3>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-tighter mt-1">Mengikut kategori perbelanjaan</p>
            </div>
            <div class="p-8 flex items-center justify-center" style="height: 300px;">
                <?php if (empty($catBreakdown)): ?>
                <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest text-center">Tiada data perbelanjaan</p>
                <?php else: ?>
                <canvas id="categoryChart"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- ========== END CHARTS ========== -->

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
                        <th class="px-8 py-6 text-[9px] font-black text-slate-300 uppercase tracking-widest text-center">Resit</th>
                        <th class="px-8 py-6 text-[9px] font-black text-slate-300 uppercase tracking-widest">Amaun</th>
                        <th class="px-8 py-6 text-[9px] font-black text-slate-300 uppercase tracking-widest text-right">Direkod Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if (empty($recent)): ?>
                    <tr>
                        <td colspan="6" class="px-8 py-20 text-center text-[10px] font-black text-slate-200 uppercase tracking-[0.3em]">Tiada Transaksi Direkodkan</td>
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
                            <td class="px-8 py-6 text-center">
                                <?php if (!empty($t['receipt_path'])): ?>
                                <a href="<?= URL_ROOT ?>/<?php echo $t['receipt_path']; ?>" target="_blank" class="text-kebana-blue hover:text-kebana-accent transition-colors" title="Lihat Resit">
                                    <i class="fa-solid fa-file-invoice-dollar text-lg"></i>
                                </a>
                                <?php endif; ?>
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
            <a href="<?= URL_ROOT ?>/finance/transactions/list" class="text-[10px] font-black text-kebana-blue uppercase tracking-widest hover:text-kebana-accent transition-colors">
                LIHAT SEMUA TRANSAKSI <i class="fa-solid fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
</div>

<!-- ===================== Chart.js Init ===================== -->
<script>
(function () {
    // Shared font & color config
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.font.size   = 11;
    Chart.defaults.color       = '#94a3b8';

    const kebanaBlue   = '#003366';
    const kebanaAccent = '#004A99';
    const green500     = '#22c55e';
    const green100     = 'rgba(34,197,94,0.12)';
    const red500       = '#ef4444';
    const red100       = 'rgba(239,68,68,0.12)';
    const yellow400    = '#FFCC00';

    // ── 1. Monthly Cash Flow Bar Chart ───────────────────────────
    const monthlyCtx = document.getElementById('monthlyChart');
    if (monthlyCtx) {
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: <?php echo $month_labels; ?>,
                datasets: [
                    {
                        label: 'Pendapatan (Masuk)',
                        data: <?php echo $monthly_income; ?>,
                        backgroundColor: 'rgba(34,197,94,0.75)',
                        borderColor: green500,
                        borderWidth: 1.5,
                        borderRadius: 3,
                    },
                    {
                        label: 'Perbelanjaan (Keluar)',
                        data: <?php echo $monthly_expense; ?>,
                        backgroundColor: 'rgba(239,68,68,0.75)',
                        borderColor: red500,
                        borderWidth: 1.5,
                        borderRadius: 3,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        labels: { font: { weight: '700', size: 11 }, padding: 20 }
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => ' RM ' + ctx.parsed.y.toLocaleString('ms-MY', {minimumFractionDigits: 2})
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { weight: '700' } } },
                    y: {
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            font: { weight: '700' },
                            callback: v => 'RM ' + v.toLocaleString('ms-MY')
                        }
                    }
                }
            }
        });
    }

    // ── 2. Running Balance Line Chart ────────────────────────────
    const balanceCtx = document.getElementById('balanceChart');
    if (balanceCtx) {
        const balanceData = <?php echo $balance_data; ?>;
        const lastBalance = balanceData.length ? balanceData[balanceData.length - 1] : 0;
        const lineColor   = lastBalance >= 0 ? green500 : red500;
        const fillColor   = lastBalance >= 0 ? green100 : red100;

        new Chart(balanceCtx, {
            type: 'line',
            data: {
                labels: <?php echo $balance_labels; ?>,
                datasets: [{
                    label: 'Baki Dana (RM)',
                    data: balanceData,
                    borderColor: lineColor,
                    backgroundColor: fillColor,
                    fill: true,
                    tension: 0.4,
                    pointRadius: balanceData.length < 30 ? 4 : 2,
                    pointHoverRadius: 6,
                    pointBackgroundColor: lineColor,
                    borderWidth: 2.5,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ' RM ' + ctx.parsed.y.toLocaleString('ms-MY', {minimumFractionDigits: 2})
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { weight: '700' }, maxTicksLimit: 8 }
                    },
                    y: {
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            font: { weight: '700' },
                            callback: v => 'RM ' + v.toLocaleString('ms-MY')
                        }
                    }
                }
            }
        });
    }

    // ── 3. Category Donut Chart ───────────────────────────────────
    const catCtx = document.getElementById('categoryChart');
    if (catCtx) {
        const donutColors = [
            '#003366','#004A99','#0066CC','#0080FF',
            '#FFCC00','#FFB700','#FF8800','#FF5500'
        ];
        new Chart(catCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo $cat_labels; ?>,
                datasets: [{
                    data: <?php echo $cat_totals; ?>,
                    backgroundColor: donutColors,
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { weight: '700', size: 10 },
                            padding: 12,
                            boxWidth: 12,
                            usePointStyle: true,
                            pointStyle: 'rect',
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => ' RM ' + ctx.parsed.toLocaleString('ms-MY', {minimumFractionDigits: 2})
                        }
                    }
                }
            }
        });
    }
})();
</script>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
