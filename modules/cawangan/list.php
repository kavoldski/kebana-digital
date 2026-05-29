<?php
/**
 * KEBANA Digital Management System - Cawangan List
 * File: modules/cawangan/list.php
 */

use App\Helpers\CawanganHelper;

if (!isAdmin()) {
    header('Location: ' . URL_ROOT . '/dashboard');
    exit();
}

// Handle status toggle
$message = '';
$message_type = '';
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $result = CawanganHelper::toggleStatus($id);
    $message = $result['message'];
    $message_type = $result['status'] ? 'success' : 'error';
}

$page_title = 'PENGURUSAN CAWANGAN';
require_once APP_ROOT . '/includes/header.php';

$search = $_GET['search'] ?? '';
$cawangans = CawanganHelper::getAllCawangan();
if ($search) {
    $cawangans = array_filter($cawangans, function($c) use ($search) {
        return stripos($c['cawangan_name'], $search) !== false || stripos($c['cawangan_code'], $search) !== false;
    });
}
?>

<div class="space-y-12">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 border-t-8 border-kebana-blue shadow-sm">
        <div>
            <h2 class="text-2xl font-black text-kebana-blue uppercase tracking-tight italic">Senarai Cawangan</h2>
            <p class="text-xs font-black text-slate-500 uppercase tracking-widest mt-2">Urus dan daftar cawangan KEBANA di seluruh Sarawak.</p>
        </div>
        <div class="flex gap-4">
            <a href="<?= URL_ROOT ?>/cawangan/add" class="bg-kebana-blue text-white px-10 py-4 text-sm font-black uppercase tracking-[0.2em] hover:bg-kebana-accent transition-all shadow-xl inline-flex items-center">
                <i class="fa-solid fa-plus mr-4 text-lg"></i>
                TAMBAH CAWANGAN
            </a>
        </div>
    </div>

    <?php if (!empty($message)): ?>
    <div class="p-6 <?php echo $message_type === 'success' ? 'bg-green-50 border-green-600 text-green-900' : 'bg-red-50 border-red-600 text-red-900'; ?> border-l-4 font-black text-sm uppercase tracking-widest shadow-sm">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fa-solid <?php echo $message_type === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?> mr-4 text-lg"></i>
                <span><?php echo htmlspecialchars($message); ?></span>
            </div>
            <a href="<?= URL_ROOT ?>/cawangan" class="text-xs font-black underline">TUTUP</a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Search Bar -->
    <div class="bg-white p-8 border border-slate-300 shadow-sm">
        <form method="GET" class="flex flex-col md:flex-row gap-6">
            <div class="flex-1 relative">
                <i class="fa-solid fa-magnifying-glass absolute left-5 top-1/2 -translate-y-1/2 text-slate-500"></i>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       class="w-full pl-14 pr-6 py-4 bg-slate-50 border-b-2 border-slate-300 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold uppercase transition-all"
                       placeholder="Cari mengikut nama atau kod cawangan...">
            </div>
            <button type="submit" class="bg-kebana-dark text-white px-10 py-4 text-sm font-black uppercase tracking-widest hover:bg-black transition-all shadow-lg">
                CARI
            </button>
            <?php if ($search): ?>
            <a href="<?= URL_ROOT ?>/cawangan" class="px-6 py-4 text-xs font-black text-slate-650 uppercase tracking-widest flex items-center hover:text-red-500">
                KOSONGKAN
            </a>
            <?php endif; ?>
        </form>
    </div>

    <div id="live-search-results" class="space-y-12">

    <!-- Table -->
    <div class="bg-white border border-slate-300 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b-2 border-slate-300">
                        <th class="px-8 py-5 text-xs font-black text-kebana-blue uppercase tracking-[0.2em]">KOD</th>
                        <th class="px-8 py-5 text-xs font-black text-kebana-blue uppercase tracking-[0.2em]">NAMA CAWANGAN</th>
                        <th class="px-8 py-5 text-xs font-black text-kebana-blue uppercase tracking-[0.2em]">STATUS</th>
                        <th class="px-8 py-5 text-xs font-black text-kebana-blue uppercase tracking-[0.2em]">TARIKH DAFTAR</th>
                        <th class="px-8 py-5 text-xs font-black text-kebana-blue uppercase tracking-[0.2em] text-right">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-300">
                    <?php if (empty($cawangans)): ?>
                    <tr>
                        <td colspan="5" class="px-8 py-24 text-center">
                            <i class="fa-solid fa-building-circle-exclamation text-5xl text-slate-300 mb-6 block"></i>
                            <p class="text-sm font-black text-slate-500 uppercase tracking-[0.2em]">Tiada Rekod Cawangan Ditemui</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($cawangans as $c): ?>
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-8 py-6">
                            <span class="inline-block px-3 py-1 bg-slate-100 text-kebana-blue text-xs font-black uppercase tracking-widest border border-slate-300">
                                <?php echo htmlspecialchars($c['cawangan_code'] ?: 'N/A'); ?>
                            </span>
                        </td>
                        <td class="px-8 py-6">
                            <p class="text-base font-black text-kebana-blue uppercase tracking-tight"><?php echo htmlspecialchars($c['cawangan_name']); ?></p>
                        </td>
                        <td class="px-8 py-6">
                            <?php if ($c['is_active']): ?>
                                <span class="inline-flex items-center px-3 py-1.5 bg-green-100 text-green-800 text-[11px] font-black uppercase tracking-widest rounded-full border border-green-300">
                                    <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                                    Aktif
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-800 text-[11px] font-black uppercase tracking-widest rounded-full border border-red-300">
                                    <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>
                                    Tidak Aktif
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-8 py-6 text-xs font-bold text-slate-650 italic">
                            <?php 
                            if (isset($c['created_at']) && !empty($c['created_at'])) {
                                echo date('d/m/Y', strtotime($c['created_at']));
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        </td>
                        <td class="px-8 py-6 text-right space-x-3">
                            <a href="<?= URL_ROOT ?>/cawangan/edit/<?php echo $c['cawangan_id']; ?>" class="inline-flex items-center justify-center w-10 h-10 bg-slate-100 text-slate-600 hover:bg-kebana-blue hover:text-white transition-all shadow-sm border border-slate-300" title="Kemaskini">
                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                            </a>
                            <a href="?toggle=<?php echo $c['cawangan_id']; ?>" class="inline-flex items-center justify-center w-10 h-10 bg-slate-100 text-slate-600 <?php echo $c['is_active'] ? 'hover:bg-red-600' : 'hover:bg-green-600'; ?> hover:text-white transition-all shadow-sm border border-slate-300" title="<?php echo $c['is_active'] ? 'Nyahaktifkan' : 'Aktifkan'; ?>" onclick="return confirm('Adakah anda pasti mahu menukar status cawangan ini?')">
                                <i class="fa-solid <?php echo $c['is_active'] ? 'fa-toggle-on' : 'fa-toggle-off'; ?> text-xs"></i>
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
</div>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
