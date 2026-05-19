<?php
/**
 * KEBANA Digital Management System - Announcements List
 * File: modules/announcements/index.php
 */

use App\Helpers\AnnouncementHelper;

require_once APP_ROOT . '/includes/header.php';

// Only allow Setiausaha Pusat, Super Admin, Presiden
if (!in_array($current_role, [888, 1, 4])) {
    echo "<div class='p-12 text-center'><h1 class='text-2xl font-black text-red-600 uppercase tracking-widest'>AKSES DISEKAT</h1></div>";
    require_once APP_ROOT . '/includes/footer.php';
    exit;
}

$message = $_GET['msg'] ?? '';

// Handle delete
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    if (AnnouncementHelper::deleteAnnouncement($delete_id, $current_user_id)) {
        echo '<script>window.location.href = "' . URL_ROOT . '/announcements?msg=deleted";</script>';
        exit;
    }
}

$announcements = AnnouncementHelper::getAllAnnouncements();

$page_title = 'PENGURUSAN HEBAHAN';
?>

<div class="max-w-7xl mx-auto space-y-12 pb-24">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 border-t-8 border-kebana-blue shadow-sm">
        <div>
            <h2 class="text-2xl font-black text-kebana-blue tracking-tight uppercase italic">Senarai Hebahan / Pengumuman</h2>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Urus maklumat yang akan dipaparkan di portal awam</p>
        </div>
        <a href="<?= URL_ROOT ?>/announcements/create" class="bg-kebana-blue text-white px-8 py-4 text-[10px] font-black uppercase tracking-widest hover:bg-kebana-accent transition-all shadow-xl flex items-center">
            <i class="fa-solid fa-plus mr-3"></i> Tambah Hebahan
        </a>
    </div>

    <?php if ($message === 'success'): ?>
    <div class="p-6 bg-green-50 text-green-700 border-l-4 border-green-600 font-bold text-xs uppercase tracking-widest animate-fade-in">
        Hebahan berjaya disimpan.
    </div>
    <?php elseif ($message === 'deleted'): ?>
    <div class="p-6 bg-red-50 text-red-700 border-l-4 border-red-600 font-bold text-xs uppercase tracking-widest animate-fade-in">
        Hebahan telah dipadam.
    </div>
    <?php endif; ?>

    <!-- Table -->
    <div class="bg-white shadow-xl border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="py-5 px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Tajuk Hebahan</th>
                        <th class="py-5 px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                        <th class="py-5 px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Dicipta Oleh</th>
                        <th class="py-5 px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Tarikh Luput</th>
                        <th class="py-5 px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if (empty($announcements)): ?>
                    <tr>
                        <td colspan="5" class="py-12 text-center">
                            <i class="fa-solid fa-bullhorn text-4xl text-slate-200 mb-4 block"></i>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Tiada Hebahan Direkodkan</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($announcements as $ann): ?>
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="py-5 px-6">
                                <span class="text-xs font-black text-kebana-blue uppercase tracking-tight block">
                                    <?php echo htmlspecialchars($ann['title']); ?>
                                </span>
                            </td>
                            <td class="py-5 px-6">
                                <?php if ($ann['status'] == 'Active'): ?>
                                    <span class="px-3 py-1 bg-green-100 text-green-700 text-[9px] font-black uppercase tracking-widest">Aktif</span>
                                <?php elseif ($ann['status'] == 'Draft'): ?>
                                    <span class="px-3 py-1 bg-amber-100 text-amber-700 text-[9px] font-black uppercase tracking-widest">Draf</span>
                                <?php else: ?>
                                    <span class="px-3 py-1 bg-slate-100 text-slate-500 text-[9px] font-black uppercase tracking-widest">Tidak Aktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-5 px-6">
                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                                    <?php echo htmlspecialchars($ann['creator_name'] ?? 'Sistem'); ?>
                                </span>
                            </td>
                            <td class="py-5 px-6">
                                <span class="text-[10px] font-bold <?php echo ($ann['expires_at'] && strtotime($ann['expires_at']) < time()) ? 'text-red-500' : 'text-slate-500'; ?> uppercase tracking-widest">
                                    <?php echo $ann['expires_at'] ? date('d M Y, h:i A', strtotime($ann['expires_at'])) : '-'; ?>
                                </span>
                            </td>
                            <td class="py-5 px-6 text-right">
                                <div class="flex items-center justify-end space-x-3 opacity-50 group-hover:opacity-100 transition-opacity">
                                    <a href="<?= URL_ROOT ?>/announcements/edit/<?php echo $ann['announcement_id']; ?>" class="w-8 h-8 bg-slate-100 text-slate-600 hover:bg-kebana-blue hover:text-white rounded flex items-center justify-center transition-colors" title="Kemaskini">
                                        <i class="fa-solid fa-pen-to-square text-[10px]"></i>
                                    </a>
                                    <a href="<?= URL_ROOT ?>/announcements?delete_id=<?php echo $ann['announcement_id']; ?>" onclick="return confirm('Adakah anda pasti untuk memadam hebahan ini?');" class="w-8 h-8 bg-slate-100 text-slate-600 hover:bg-red-500 hover:text-white rounded flex items-center justify-center transition-colors" title="Padam">
                                        <i class="fa-solid fa-trash-can text-[10px]"></i>
                                    </a>
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
