<?php
/**
 * KEBANA Digital Management System - Dashboard (MYDS Inspired)
 * File: src/php/index.php
 */

$page_title = 'PAPARAN UTAMA';

use App\Core\Database;
use App\Helpers\MembersHelper;
use App\Helpers\FinanceHelper;
use App\Helpers\DashboardHelper;
use App\Helpers\AuditHelper;

$db = Database::getInstance()->getConnection();
$username = $_SESSION['username'] ?? 'User';

require_once APP_ROOT . '/includes/header.php';

$current_cawangan_id = isset($_SESSION['cawangan_id']) ? (int)$_SESSION['cawangan_id'] : null;

// Data fetching
$total_members = MembersHelper::getMemberCount();
$active_members = count(MembersHelper::getMembersByStatus('Active'));
$upcoming_events = DashboardHelper::getUpcomingEventsCount($current_cawangan_id);
$past_events = DashboardHelper::getPastEventsCount($current_cawangan_id);
$total_events = $upcoming_events + $past_events;

$current_role = (int)($_SESSION['role'] ?? 0);
$current_cawangan_id = isset($_SESSION['cawangan_id']) ? (int)$_SESSION['cawangan_id'] : null;

$pending_docs = DashboardHelper::getPendingDocumentsCount($current_role, $current_cawangan_id);
$total_docs = DashboardHelper::getTotalDocumentsCount();

$fund_balance = DashboardHelper::getFundBalance();
$finance_totals = FinanceHelper::getFinanceTotals();

$pending_approvals = DashboardHelper::getPendingApprovalsCount($current_role, $current_cawangan_id);

$recent_activities = AuditHelper::getRecentLogs(5);

// Participation Rate
$participation_rate = $total_members > 0 ? round(($active_members / $total_members) * 100, 1) : 0;
?>

<div class="space-y-12">
    <!-- Stat Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-0 border border-slate-100">
        <!-- Members -->
        <div class="bg-white p-8 border-r border-slate-50 last:border-r-0 hover:bg-slate-50 transition-colors group">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest">JUMLAH AHLI</span>
                <i class="fa-solid fa-users text-kebana-blue opacity-10 group-hover:opacity-100 transition-opacity"></i>
            </div>
            <h3 class="text-5xl font-black text-kebana-blue tracking-tighter"><?php echo number_format($total_members); ?></h3>
            <div class="mt-6 flex items-center text-[10px] font-black text-slate-400">
                <span class="text-kebana-blue"><?php echo number_format($active_members); ?></span>
                <span class="mx-2">AKTIF</span>
            </div>
        </div>

        <!-- Events -->
        <div class="bg-white p-8 border-r border-slate-50 last:border-r-0 hover:bg-slate-50 transition-colors group">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest">PROGRAM AKTIF</span>
                <i class="fa-solid fa-calendar-star text-kebana-blue opacity-10 group-hover:opacity-100 transition-opacity"></i>
            </div>
            <h3 class="text-5xl font-black text-kebana-blue tracking-tighter"><?php echo number_format($upcoming_events); ?></h3>
            <div class="mt-6 flex items-center text-[10px] font-black text-slate-400">
                <span class="text-kebana-blue uppercase tracking-widest"><?php echo $total_events; ?> KESELURUHAN</span>
            </div>
        </div>

        <?php if ($can_view_finance): ?>
        <!-- Finance -->
        <div class="bg-white p-8 border-r border-slate-50 last:border-r-0 hover:bg-slate-50 transition-colors group">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest">BAKI TABUNG</span>
                <i class="fa-solid fa-wallet text-kebana-blue opacity-10 group-hover:opacity-100 transition-opacity"></i>
            </div>
            <h3 class="text-5xl font-black text-kebana-blue tracking-tighter"><?php echo DashboardHelper::formatFundBalance($fund_balance); ?></h3>
            <div class="mt-6 flex items-center text-[10px] font-black <?php echo $fund_balance >= 0 ? 'text-green-600' : 'text-red-500'; ?>">
                <span class="uppercase tracking-widest"><?php echo $fund_balance >= 0 ? 'Positif' : 'Defisit'; ?></span>
            </div>
        </div>
        <?php endif; ?>

        <?php if (in_array($current_role, [888, 1, 2, 3, 11, 22])): ?>
        <!-- Approvals -->
        <div class="bg-white p-8 hover:bg-slate-50 transition-colors group border-b-4 border-kebana-yellow">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest">TINDAKAN</span>
                <i class="fa-solid fa-bell-exclamation text-kebana-blue opacity-10 group-hover:opacity-100 transition-opacity"></i>
            </div>
            <h3 class="text-5xl font-black text-kebana-blue tracking-tighter"><?php echo str_pad($pending_approvals + $pending_docs, 2, '0', STR_PAD_LEFT); ?></h3>
            <div class="mt-6 flex items-center text-[10px] font-black text-amber-500 uppercase tracking-widest">
                <span>Perlu Kelulusan / Semakan</span>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-12">
            <div class="bg-white border-t-8 border-kebana-blue shadow-sm p-10 space-y-10">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-black text-kebana-blue tracking-tight uppercase italic">Statistik Organisasi</h2>
                    <?php if (in_array($current_role, [888, 1, 2, 3])): ?>
                    <a href="/kebana-digital/members/report" class="bg-kebana-blue text-white px-6 py-3 text-[10px] font-black uppercase tracking-widest hover:bg-kebana-accent transition-all">Analisis Data</a>
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                    <div class="space-y-6">
                        <div class="flex justify-between items-baseline">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Kadar Keaktifan Ahli</span>
                            <span class="text-2xl font-black text-kebana-blue italic"><?php echo $participation_rate; ?>%</span>
                        </div>
                        <div class="h-3 w-full bg-slate-50 border border-slate-100">
                            <div class="h-full bg-kebana-blue shadow-lg shadow-kebana-blue/20 transition-all duration-1000" style="width: <?php echo $participation_rate; ?>%"></div>
                        </div>
                    </div>

                    <?php if ($can_view_finance): ?>
                    <div class="space-y-6 border-l border-slate-50 pl-8 hidden md:block">
                        <div class="flex items-center justify-between py-2">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Dana Masuk</span>
                            <span class="text-sm font-black text-kebana-blue">RM <?php echo number_format($finance_totals['total_income'], 2); ?></span>
                        </div>
                        <div class="flex items-center justify-between py-2">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Dana Keluar</span>
                            <span class="text-sm font-black text-red-500">RM <?php echo number_format($finance_totals['total_expense'], 2); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Access -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="/kebana-digital/members/add" class="p-8 bg-kebana-blue text-white flex flex-col items-center justify-center space-y-4 hover:bg-kebana-accent transition-all group">
                    <i class="fa-solid fa-user-plus text-3xl group-hover:scale-110 transition-transform"></i>
                    <span class="text-[10px] font-black uppercase tracking-widest">Daftar Ahli</span>
                </a>
                <a href="/kebana-digital/documents" class="p-8 bg-white border border-slate-100 text-kebana-blue flex flex-col items-center justify-center space-y-4 hover:bg-slate-50 transition-all group border-b-4 border-kebana-yellow">
                    <i class="fa-solid fa-cloud-arrow-up text-3xl group-hover:scale-110 transition-transform"></i>
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Pusat Fail</span>
                </a>
                <a href="/kebana-digital/events/create" class="p-8 bg-white border border-slate-100 text-kebana-blue flex flex-col items-center justify-center space-y-4 hover:bg-slate-50 transition-all group">
                    <i class="fa-solid fa-calendar-plus text-3xl group-hover:scale-110 transition-transform"></i>
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Acara Baru</span>
                </a>
                <?php if ($can_view_finance): ?>
                <a href="/kebana-digital/finance" class="p-8 bg-white border border-slate-100 text-kebana-blue flex flex-col items-center justify-center space-y-4 hover:bg-slate-50 transition-all group">
                    <i class="fa-solid fa-chart-line-up text-3xl group-hover:scale-110 transition-transform"></i>
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Kewangan</span>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-10">
            <div class="bg-white border-t-8 border-kebana-yellow shadow-sm p-10">
                <h3 class="text-[10px] font-black text-kebana-blue uppercase tracking-[0.2em] mb-10 pb-4 border-b border-slate-50 flex items-center justify-between">
                    Log Aktiviti
                    <i class="fa-solid fa-list-check opacity-20"></i>
                </h3>
                <div class="space-y-10">
                    <?php if (empty($recent_activities)): ?>
                        <p class="text-[10px] text-slate-300 font-bold uppercase tracking-widest text-center py-10">Tiada aktiviti terbaru.</p>
                    <?php else: ?>
                        <?php foreach ($recent_activities as $log): 
                            $icon = 'fa-circle-dot';
                            $color = 'text-slate-400';
                            if ($log['module'] == 'AUTH') { $icon = 'fa-shield-keyhole'; $color = 'text-amber-500'; }
                            if ($log['module'] == 'CHAT') { $icon = 'fa-comments'; $color = 'text-kebana-blue'; }
                            if ($log['module'] == 'MEMBERS') { $icon = 'fa-user-check'; $color = 'text-purple-500'; }
                            if ($log['module'] == 'EVENTS') { $icon = 'fa-calendar-star'; $color = 'text-blue-500'; }
                            if ($log['module'] == 'FINANCE') { $icon = 'fa-money-bill-transfer'; $color = 'text-green-600'; }
                        ?>
                        <div class="relative pl-8 border-l-2 border-slate-50">
                            <div class="absolute -left-[6px] top-0 w-3 h-3 bg-white border-2 border-slate-200 ring-4 ring-white"></div>
                            <p class="text-[10px] font-black <?php echo $color; ?> uppercase italic flex items-center">
                                <i class="fa-solid <?php echo $icon; ?> mr-2"></i>
                                <?php echo DashboardHelper::formatRelativeTime($log['created_at']); ?>
                            </p>
                            <p class="text-sm text-slate-700 mt-3 font-bold">
                                <?php echo htmlspecialchars($log['username'] ?? 'SYSTEM'); ?>: 
                                <?php echo htmlspecialchars($log['action']); ?>
                            </p>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php if (in_array($current_role, [888, 1, 4, 6])): ?>
                <a href="/kebana-digital/audit" class="block w-full mt-12 py-4 text-[10px] font-black text-slate-400 border border-slate-100 uppercase tracking-widest hover:bg-slate-50 hover:text-kebana-blue transition-all text-center">Lihat Semua Aktiviti</a>
                <?php endif; ?>
            </div>

            <div class="bg-kebana-dark p-10 text-white shadow-2xl relative overflow-hidden group">
                <div class="relative z-10">
                    <div class="flex items-center space-x-3 mb-8">
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse shadow-[0_0_10px_rgba(34,197,94,0.5)]"></div>
                        <span class="text-[10px] font-black uppercase tracking-widest">Status Sistem: Aktif</span>
                    </div>
                    <p class="text-[10px] text-white/30 font-bold leading-relaxed uppercase tracking-widest">Pangkalan data disinkronisasi ke pusat data utama KEBANA.</p>
                </div>
                <i class="fa-solid fa-server absolute -right-4 -bottom-4 text-7xl text-white/5 group-hover:text-white/10 transition-all duration-700"></i>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
