<?php
/**
 * KEBANA Digital Management System - All Notifications
 * File: modules/notifications/index.php
 */

use App\Helpers\NotificationHelper;

if (!defined('APP_ROOT')) {
    require_once dirname(__DIR__, 2) . '/bootstrap.php';
}
require_once APP_ROOT . '/includes/header.php';

$userId = $_SESSION['user_id'];
$page_title = 'NOTIFIKASI SAYA';

// Handle Actions
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'clear_all') {
        NotificationHelper::deleteAll($userId);
        echo "<script>window.location.href = '" . URL_ROOT . "/notifications';</script>";
        exit;
    } elseif ($_GET['action'] === 'mark_all_read') {
        NotificationHelper::markAllAsRead($userId);
        echo "<script>window.location.href = '" . URL_ROOT . "/notifications';</script>";
        exit;
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$notifications = NotificationHelper::getAll($userId, $limit, $offset);
$total_notifications = NotificationHelper::countAll($userId);
$total_pages = ceil($total_notifications / $limit);

?>

<div class="space-y-12 pb-24">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 border-t-8 border-kebana-blue shadow-sm">
        <div>
            <h2 class="text-2xl font-black text-kebana-blue uppercase tracking-tight italic">Pusat Notifikasi</h2>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Kekal maklum dengan kemas kini sistem dan aktiviti terkini.</p>
        </div>
        <div class="flex flex-wrap gap-4">
            <a href="?action=mark_all_read" class="bg-slate-100 text-slate-600 px-8 py-4 text-xs font-black uppercase tracking-[0.2em] hover:bg-kebana-blue hover:text-white transition-all shadow-sm inline-flex items-center">
                <i class="fa-solid fa-check-double mr-3"></i>
                TANDA SEMUA DIBACA
            </a>
            <a href="?action=clear_all" onclick="return confirm('Adakah anda pasti ingin memadam semua notifikasi?')" class="bg-red-50 text-red-500 px-8 py-4 text-xs font-black uppercase tracking-[0.2em] hover:bg-red-500 hover:text-white transition-all shadow-sm inline-flex items-center border border-red-100">
                <i class="fa-solid fa-trash-can mr-3"></i>
                PADAM SEMUA
            </a>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="bg-white border border-slate-100 shadow-sm overflow-hidden">
        <?php if (empty($notifications)): ?>
            <div class="py-32 text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-slate-50 rounded-full mb-6">
                    <i class="fa-regular fa-bell-slash text-3xl text-slate-200"></i>
                </div>
                <h3 class="text-lg font-black text-slate-300 uppercase tracking-[0.2em]">Tiada Notifikasi</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2">Anda telah membersihkan semua notifikasi anda.</p>
            </div>
        <?php else: ?>
            <div class="divide-y divide-slate-50">
                <?php foreach ($notifications as $n): 
                    $isUnread = ($n['status'] === 'unread');
                ?>
                <div class="p-6 md:p-8 flex items-start gap-6 transition-colors hover:bg-slate-50/50 cursor-pointer <?php echo $isUnread ? 'bg-blue-50/30' : ''; ?>" 
                     onclick="markRead(<?php echo $n['notification_id']; ?>, '<?php echo $n['action_url']; ?>')">
                    <div class="flex-shrink-0 mt-1">
                        <div class="w-12 h-12 flex items-center justify-center <?php echo $isUnread ? 'bg-kebana-blue text-white' : 'bg-slate-100 text-slate-400'; ?> rounded-none shadow-sm">
                            <i class="fa-solid <?php 
                                switch($n['type']) {
                                    case 'proposal_submitted': echo 'fa-file-signature'; break;
                                    case 'proposal_approved': echo 'fa-circle-check'; break;
                                    case 'proposal_rejected': echo 'fa-circle-xmark'; break;
                                    case 'chat_message': echo 'fa-comment-dots'; break;
                                    default: echo 'fa-bell';
                                }
                            ?> text-lg"></i>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 mb-2">
                            <div class="flex items-center gap-3">
                                <span class="text-[10px] font-black text-kebana-blue uppercase tracking-widest"><?php echo str_replace('_', ' ', $n['type']); ?></span>
                                <?php if ($isUnread): ?>
                                    <span class="px-2 py-0.5 bg-kebana-yellow text-[8px] font-black text-kebana-blue uppercase">BARU</span>
                                <?php endif; ?>
                            </div>
                            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-tighter">
                                <i class="fa-regular fa-clock mr-1"></i>
                                <?php echo date('d F Y • h:i A', strtotime($n['created_at'])); ?>
                            </span>
                        </div>
                        <h4 class="text-base font-black text-slate-800 uppercase tracking-tight mb-1"><?php echo htmlspecialchars($n['title']); ?></h4>
                        <p class="text-sm text-slate-500 leading-relaxed max-w-3xl mb-4"><?php echo htmlspecialchars($n['message']); ?></p>
                        
                        <?php if ($n['action_url']): ?>
                            <a href="javascript:void(0)" onclick="markRead(<?php echo $n['notification_id']; ?>, '<?php echo $n['action_url']; ?>')" class="inline-flex items-center text-[10px] font-black text-kebana-blue uppercase tracking-[0.2em] hover:underline">
                                LIHAT BUTIRAN <i class="fa-solid fa-arrow-right ml-2 text-[8px]"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="flex justify-center mt-12">
        <div class="flex gap-2">
            <?php for ($i = 1; $i <= $total_pages; $i++): 
                $active = ($i === $page);
            ?>
            <a href="?page=<?php echo $i; ?>" 
               class="w-12 h-12 flex items-center justify-center text-[10px] font-black border <?php echo $active ? 'bg-kebana-blue text-white border-kebana-blue shadow-xl' : 'bg-white text-slate-400 border-slate-100 hover:border-kebana-blue hover:text-kebana-blue'; ?> transition-all">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
