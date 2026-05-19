<?php
/**
 * KEBANA Digital Management System - Audit Log List
 * File: modules/audit/list.php
 */

use App\Helpers\AuditHelper;
use App\Helpers\DashboardHelper;

require_once APP_ROOT . '/includes/header.php';

// Access Control - Only Pusat Roles can see audit logs
if (!in_array($current_role, [888, 1, 4, 6])) {
    echo "<div class='p-12 text-center'><h1 class='text-2xl font-black text-red-600 uppercase tracking-widest'>AKSES DISEKAT</h1></div>";
    require_once APP_ROOT . '/includes/footer.php';
    exit;
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$module = isset($_GET['module']) ? $_GET['module'] : null;

$logs = AuditHelper::getLogs($page, $limit, $module);
$totalLogs = AuditHelper::getTotalCount($module);
$totalPages = ceil($totalLogs / $limit);

$page_title = 'LOG AKTIVITI SISTEM';
?>

<div class="space-y-8">
    <div id="live-search-results" class="space-y-8">
    <!-- Filters -->
    <div class="bg-white p-6 border border-slate-100 flex flex-wrap gap-4 items-center justify-between shadow-sm">
        <div class="flex gap-2">
            <a href="<?= URL_ROOT ?>/audit" class="px-4 py-2 text-[10px] font-black uppercase tracking-widest border <?php echo !$module ? 'bg-kebana-blue text-white border-kebana-blue' : 'text-slate-400 border-slate-100 hover:bg-slate-50'; ?>">Semua</a>
            <a href="<?= URL_ROOT ?>/audit?module=AUTH" class="px-4 py-2 text-[10px] font-black uppercase tracking-widest border <?php echo $module == 'AUTH' ? 'bg-kebana-blue text-white border-kebana-blue' : 'text-slate-400 border-slate-100 hover:bg-slate-50'; ?>">Auth</a>
            <a href="<?= URL_ROOT ?>/audit?module=EVENTS" class="px-4 py-2 text-[10px] font-black uppercase tracking-widest border <?php echo $module == 'EVENTS' ? 'bg-kebana-blue text-white border-kebana-blue' : 'text-slate-400 border-slate-100 hover:bg-slate-50'; ?>">Events</a>
            <a href="<?= URL_ROOT ?>/audit?module=MEMBERS" class="px-4 py-2 text-[10px] font-black uppercase tracking-widest border <?php echo $module == 'MEMBERS' ? 'bg-kebana-blue text-white border-kebana-blue' : 'text-slate-400 border-slate-100 hover:bg-slate-50'; ?>">Members</a>
            <a href="<?= URL_ROOT ?>/audit?module=FINANCE" class="px-4 py-2 text-[10px] font-black uppercase tracking-widest border <?php echo $module == 'FINANCE' ? 'bg-kebana-blue text-white border-kebana-blue' : 'text-slate-400 border-slate-100 hover:bg-slate-50'; ?>">Finance</a>
            <a href="<?= URL_ROOT ?>/audit?module=CHAT" class="px-4 py-2 text-[10px] font-black uppercase tracking-widest border <?php echo $module == 'CHAT' ? 'bg-kebana-blue text-white border-kebana-blue' : 'text-slate-400 border-slate-100 hover:bg-slate-50'; ?>">Chat</a>
        </div>
        <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest italic"><?php echo number_format($totalLogs); ?> rekod dijumpai</p>
    </div>

    <!-- Log Table -->
    <div class="bg-white border border-slate-100 shadow-xl overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Waktu</th>
                    <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Pengguna</th>
                    <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Modul</th>
                    <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Tindakan</th>
                    <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Perincian</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="5" class="p-20 text-center text-slate-300 uppercase font-black text-[10px] tracking-widest">Tiada rekod aktiviti dijumpai.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): 
                        $badgeColor = 'bg-slate-100 text-slate-400';
                        if ($log['module'] == 'AUTH') $badgeColor = 'bg-amber-100 text-amber-600';
                        if ($log['module'] == 'FINANCE') $badgeColor = 'bg-green-100 text-green-600';
                        if ($log['module'] == 'EVENTS') $badgeColor = 'bg-blue-100 text-blue-600';
                        if ($log['module'] == 'MEMBERS') $badgeColor = 'bg-purple-100 text-purple-600';
                    ?>
                    <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                        <td class="p-6">
                            <p class="text-xs font-black text-kebana-blue"><?php echo date('d/m/Y', strtotime($log['created_at'])); ?></p>
                            <p class="text-[9px] font-bold text-slate-400 mt-1 uppercase"><?php echo date('H:i:s', strtotime($log['created_at'])); ?></p>
                        </td>
                        <td class="p-6">
                            <p class="text-xs font-black text-slate-700 uppercase"><?php echo htmlspecialchars($log['username'] ?? 'SYSTEM'); ?></p>
                            <p class="text-[9px] font-bold text-slate-400 mt-1 uppercase"><?php echo $log['ip_address']; ?></p>
                        </td>
                        <td class="p-6">
                            <span class="px-3 py-1 rounded-full text-[8px] font-black uppercase tracking-widest <?php echo $badgeColor; ?>">
                                <?php echo $log['module']; ?>
                            </span>
                        </td>
                        <td class="p-6 text-xs font-bold text-slate-600 italic">
                            <?php echo htmlspecialchars($log['action']); ?>
                        </td>
                        <td class="p-6 text-[10px] text-slate-400 font-medium max-w-xs truncate">
                            <?php echo htmlspecialchars($log['details'] ?? '-'); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-center gap-2">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="<?= URL_ROOT ?>/audit?page=<?php echo $i; ?><?php echo $module ? '&module='.$module : ''; ?>" 
                   class="w-10 h-10 flex items-center justify-center text-[10px] font-black border transition-all <?php echo $page == $i ? 'bg-kebana-blue text-white border-kebana-blue shadow-lg shadow-kebana-blue/20' : 'bg-white text-slate-400 border-slate-200 hover:border-kebana-blue hover:text-kebana-blue'; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
    </div>
</div>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
