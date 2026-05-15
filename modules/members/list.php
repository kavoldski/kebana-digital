<?php
/**
 * KEBANA Digital Management System - Members List (MYDS Inspired)
 * File: modules/members/list.php
 */

use App\Core\Database;
use App\Helpers\MembersHelper;

$db = Database::getInstance()->getConnection();

// Handle deletion
$message = '';
$message_type = '';
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $result = MembersHelper::deleteMember($del_id);
    if ($result['status']) {
        $message = 'Rekod ahli berjaya dipadam.';
        $message_type = 'success';
    } else {
        $message = 'Ralat pemadaman: ' . $result['message'];
        $message_type = 'error';
    }
}

$page_title = 'PENGURUSAN AHLI';

require_once APP_ROOT . '/includes/header.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;

// Get members
$search = $_GET['search'] ?? '';
$members_data = MembersHelper::getMembersPaginated($page, $per_page, $search);
$members = $members_data['members'];
$total_members = $members_data['total'];
$total_pages = ceil($total_members / $per_page);

// Stats
$active_members = count(MembersHelper::getMembersByStatus('Active'));
$inactive_members = count(MembersHelper::getMembersByStatus('Inactive'));
?>

<div class="space-y-12">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 border-t-8 border-kebana-blue shadow-sm">
        <div>
            <h2 class="text-2xl font-black text-kebana-blue uppercase tracking-tight italic">Senarai Ahli</h2>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Pangkalan Data Komuniti KEBANA Digital.</p>
        </div>
        <div class="flex gap-4">
            <?php if (in_array($current_role, [888, 1, 4, 33])): ?>
            <a href="/kebana-digital/members/report" class="bg-slate-100 text-kebana-blue px-10 py-4 text-xs font-black uppercase tracking-[0.2em] hover:bg-slate-200 transition-all shadow-sm inline-flex items-center">
                <i class="fa-solid fa-chart-mixed mr-4 text-lg"></i>
                LAPORAN & ANALISIS
            </a>
            <?php endif; ?>
            <a href="/kebana-digital/members/add" class="bg-kebana-blue text-white px-10 py-4 text-xs font-black uppercase tracking-[0.2em] hover:bg-kebana-accent transition-all shadow-xl inline-flex items-center">
                <i class="fa-solid fa-user-plus mr-4 text-lg"></i>
                DAFTAR AHLI BARU
            </a>
        </div>
    </div>

    <?php if (!empty($message)): ?>
    <div class="p-6 <?php echo $message_type === 'success' ? 'bg-green-50 border-green-600 text-green-800' : 'bg-red-50 border-red-600 text-red-800'; ?> border-l-4 font-black text-xs uppercase tracking-widest shadow-sm">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fa-solid <?php echo $message_type === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?> mr-4 text-lg"></i>
                <span><?php echo htmlspecialchars($message); ?></span>
            </div>
            <a href="/kebana-digital/members" class="text-[10px] font-black underline">TUTUP</a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-0 border border-slate-100">
        <div class="bg-white p-8 border-r border-slate-50 last:border-r-0 flex items-center justify-between group hover:bg-slate-50 transition-colors">
            <div>
                <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">JUMLAH KESELURUHAN</p>
                <p class="text-3xl font-black text-kebana-blue mt-2"><?php echo number_format($total_members); ?></p>
            </div>
            <i class="fa-solid fa-users text-kebana-blue opacity-10 group-hover:opacity-100 transition-opacity text-3xl"></i>
        </div>
        <div class="bg-white p-8 border-r border-slate-50 last:border-r-0 flex items-center justify-between group hover:bg-slate-50 transition-colors">
            <div>
                <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">AHLI AKTIF</p>
                <p class="text-3xl font-black text-green-600 mt-2"><?php echo number_format($active_members); ?></p>
            </div>
            <i class="fa-solid fa-user-check text-green-600 opacity-10 group-hover:opacity-100 transition-opacity text-3xl"></i>
        </div>
        <div class="bg-white p-8 last:border-r-0 flex items-center justify-between group hover:bg-slate-50 transition-colors border-b-4 border-slate-300">
            <div>
                <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">AHLI TIDAK AKTIF</p>
                <p class="text-3xl font-black text-slate-400 mt-2"><?php echo number_format($inactive_members); ?></p>
            </div>
            <i class="fa-solid fa-user-slash text-slate-400 opacity-10 group-hover:opacity-100 transition-opacity text-3xl"></i>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="bg-white p-6 border border-slate-100">
        <form method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="relative flex-1">
                <i class="fa-solid fa-magnifying-glass absolute left-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="CARI AHLI (NAMA, NO. IC, ID)..." class="w-full pl-14 pr-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold uppercase tracking-tight transition-all rounded-none">
            </div>
            <button type="submit" class="px-10 py-4 bg-kebana-dark text-white text-[10px] font-black uppercase tracking-widest hover:bg-black transition-all">
                TAPISAN
            </button>
            <?php if ($search): ?>
            <a href="/kebana-digital/members" class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center hover:text-red-500">
                KOSONGKAN
            </a>
            <?php endif; ?>
        </form>
    </div>

    <div id="live-search-results" class="space-y-12">

    <!-- Table -->
    <div class="bg-white border border-slate-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b-2 border-slate-100">
                        <th class="px-8 py-5 text-[10px] font-black text-kebana-blue uppercase tracking-[0.2em]">ID AHLI</th>
                        <th class="px-8 py-5 text-[10px] font-black text-kebana-blue uppercase tracking-[0.2em]">MAKLUMAT AHLI</th>
                        <th class="px-8 py-5 text-[10px] font-black text-kebana-blue uppercase tracking-[0.2em]">PENGENALAN</th>
                        <th class="px-8 py-5 text-[10px] font-black text-kebana-blue uppercase tracking-[0.2em]">KAWASAN</th>
                        <th class="px-8 py-5 text-[10px] font-black text-kebana-blue uppercase tracking-[0.2em] text-center">STATUS</th>
                        <th class="px-8 py-5 text-[10px] font-black text-kebana-blue uppercase tracking-[0.2em] text-right">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if (empty($members)): ?>
                    <tr>
                        <td colspan="6" class="px-8 py-24 text-center">
                            <i class="fa-solid fa-user-slash text-5xl text-slate-100 mb-6 block"></i>
                            <p class="text-[10px] font-black text-slate-300 uppercase tracking-[0.3em]">Tiada Rekod Ahli Ditemui</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($members as $member): ?>
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-8 py-6 text-xs font-black text-slate-300 group-hover:text-kebana-blue transition-colors">
                            #<?php echo str_pad($member['member_id'], 4, '0', STR_PAD_LEFT); ?>
                        </td>
                        <td class="px-8 py-6">
                            <p class="text-sm font-black text-kebana-blue uppercase tracking-tight"><?php echo htmlspecialchars($member['full_name']); ?></p>
                            <p class="text-[9px] text-slate-400 font-bold uppercase mt-1 tracking-widest"><?php echo htmlspecialchars($member['phone_no'] ?? 'TIADA TALIAN'); ?></p>
                        </td>
                        <td class="px-8 py-6 text-sm font-bold text-slate-600 tracking-tighter italic">
                            <?php echo htmlspecialchars($member['ic_number']); ?>
                        </td>
                        <td class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            <?php echo htmlspecialchars($member['village']); ?>
                        </td>
                        <td class="px-8 py-6 text-center">
                            <?php 
                            $status_class = 'bg-amber-500 text-white shadow-lg shadow-amber-500/20';
                            if (strtolower($member['status']) === 'active') $status_class = 'bg-green-600 text-white shadow-lg shadow-green-600/20';
                            elseif (strtolower($member['status']) === 'inactive') $status_class = 'bg-slate-400 text-white shadow-lg shadow-slate-400/20';
                            ?>
                            <span class="inline-block px-4 py-1.5 text-[9px] font-black uppercase tracking-widest <?php echo $status_class; ?>">
                                <?php echo htmlspecialchars($member['status']); ?>
                            </span>
                        </td>
                        <td class="px-8 py-6 text-right space-x-3">
                            <a href="/kebana-digital/members/view/<?php echo $member['member_id']; ?>" class="inline-flex items-center justify-center w-10 h-10 bg-slate-50 text-slate-300 hover:bg-kebana-blue hover:text-white transition-all shadow-sm" title="Lihat">
                                <i class="fa-solid fa-eye text-xs"></i>
                            </a>
                            <a href="/kebana-digital/members/edit/<?php echo $member['member_id']; ?>" class="inline-flex items-center justify-center w-10 h-10 bg-slate-50 text-slate-300 hover:bg-kebana-blue hover:text-white transition-all shadow-sm" title="Kemaskini">
                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="px-8 py-6 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
            <p class="text-[9px] font-black text-slate-300 uppercase tracking-[0.2em]">
                JUMLAH REKOD: <?php echo $total_members; ?> • PAPARAN: <?php echo count($members); ?>
            </p>
            <div class="flex space-x-2">
                <?php for ($i = 1; $i <= $total_pages; $i++): 
                    $page_url = "?page=$i" . ($search ? "&search=" . urlencode($search) : "");
                ?>
                <a href="<?php echo $page_url; ?>" class="w-10 h-10 flex items-center justify-center text-[10px] font-black <?php echo $i === $page ? 'bg-kebana-blue text-white shadow-xl shadow-kebana-blue/20' : 'bg-white text-slate-400 border border-slate-100 hover:bg-slate-100'; ?> transition-all">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    </div>
</div>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
