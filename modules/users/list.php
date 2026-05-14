<?php
/**
 * KEBANA Digital Management System - User Management List
 * File: modules/users/list.php
 */

use App\Core\Database;
use App\Helpers\UserHelper;
use App\Helpers\CawanganHelper;

if (!isAdmin()) {
    header('Location: /kebana-digital/dashboard');
    exit();
}

$db = Database::getInstance()->getConnection();

// Handle deletion
$message = '';
$message_type = '';
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $result = UserHelper::deleteUser($del_id);
    if ($result['status']) {
        $message = $result['message'];
        $message_type = 'success';
    } else {
        $message = $result['message'];
        $message_type = 'error';
    }
}

$page_title = 'PENGURUSAN PENGGUNA';
require_once APP_ROOT . '/includes/header.php';

$users = UserHelper::getAllUsers();
$cawangans = CawanganHelper::getAllCawangan();

// Role mapping for display
$role_names = [
    888 => 'Super Admin',
    1   => 'Presiden',
    2   => 'Timbalan Presiden 1',
    3   => 'Timbalan Presiden 2',
    4   => 'Setiausaha Pusat',
    5   => 'Penolong Setiausaha Pusat',
    6   => 'Bendahari Kehormat',
    7   => 'Penolong Bendahari Kehormat',
    11  => 'Pengerusi Cawangan',
    22  => 'Timb. Pengerusi Cawangan',
    33  => 'Setiausaha Cawangan',
    44  => 'Pen. Setiausaha Cawangan',
    55  => 'Bendahari Cawangan',
    66  => 'Pen. Bendahari Cawangan'
];
?>

<div class="space-y-12">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 border-t-8 border-kebana-blue shadow-sm">
        <div>
            <h2 class="text-2xl font-black text-kebana-blue uppercase tracking-tight italic">Senarai Pengguna</h2>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Urus akses dan peranan pengguna sistem KEBANA.</p>
        </div>
        <div class="flex gap-4">
            <a href="/kebana-digital/users/add" class="bg-kebana-blue text-white px-10 py-4 text-xs font-black uppercase tracking-[0.2em] hover:bg-kebana-accent transition-all shadow-xl inline-flex items-center">
                <i class="fa-solid fa-user-plus mr-4 text-lg"></i>
                TAMBAH PENGGUNA
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
            <a href="/kebana-digital/users" class="text-[10px] font-black underline">TUTUP</a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Table -->
    <div class="bg-white border border-slate-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b-2 border-slate-100">
                        <th class="px-8 py-5 text-[10px] font-black text-kebana-blue uppercase tracking-[0.2em]">ID</th>
                        <th class="px-8 py-5 text-[10px] font-black text-kebana-blue uppercase tracking-[0.2em]">MAKLUMAT PENGGUNA</th>
                        <th class="px-8 py-5 text-[10px] font-black text-kebana-blue uppercase tracking-[0.2em]">PERANAN</th>
                        <th class="px-8 py-5 text-[10px] font-black text-kebana-blue uppercase tracking-[0.2em]">CAWANGAN</th>
                        <th class="px-8 py-5 text-[10px] font-black text-kebana-blue uppercase tracking-[0.2em]">TARIKH DAFTAR</th>
                        <th class="px-8 py-5 text-[10px] font-black text-kebana-blue uppercase tracking-[0.2em] text-right">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="px-8 py-24 text-center">
                            <i class="fa-solid fa-users-slash text-5xl text-slate-100 mb-6 block"></i>
                            <p class="text-[10px] font-black text-slate-300 uppercase tracking-[0.3em]">Tiada Rekod Pengguna Ditemui</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($users as $u): ?>
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-8 py-6 text-xs font-black text-slate-300 group-hover:text-kebana-blue transition-colors">
                            #<?php echo str_pad($u['user_id'], 3, '0', STR_PAD_LEFT); ?>
                        </td>
                        <td class="px-8 py-6">
                            <p class="text-sm font-black text-kebana-blue uppercase tracking-tight"><?php echo htmlspecialchars($u['username']); ?></p>
                            <p class="text-[9px] text-slate-400 font-bold uppercase mt-1 tracking-widest"><?php echo htmlspecialchars($u['email']); ?></p>
                        </td>
                        <td class="px-8 py-6">
                            <?php 
                            $role_color = 'bg-slate-100 text-slate-600';
                            if ($u['role'] == 888) $role_color = 'bg-red-600 text-white';
                            elseif ($u['role'] <= 7) $role_color = 'bg-kebana-blue text-white';
                            elseif ($u['role'] >= 11) $role_color = 'bg-kebana-yellow text-kebana-blue';
                            ?>
                            <span class="inline-block px-3 py-1 text-[9px] font-black uppercase tracking-widest <?php echo $role_color; ?>">
                                <?php echo $role_names[$u['role']] ?? 'Unknown'; ?>
                            </span>
                        </td>
                        <td class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            <?php echo htmlspecialchars($u['cawangan_name'] ?? 'PUSAT'); ?>
                        </td>
                        <td class="px-8 py-6 text-[10px] font-bold text-slate-500 italic">
                            <?php 
                            if (isset($u['created_at']) && !empty($u['created_at'])) {
                                echo date('d/m/Y', strtotime($u['created_at']));
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        </td>
                        <td class="px-8 py-6 text-right space-x-3">
                            <a href="/kebana-digital/users/edit/<?php echo $u['user_id']; ?>" class="inline-flex items-center justify-center w-10 h-10 bg-slate-50 text-slate-300 hover:bg-kebana-blue hover:text-white transition-all shadow-sm" title="Kemaskini">
                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                            </a>
                            <?php if ($u['user_id'] != $current_user_id): ?>
                            <a href="?delete=<?php echo $u['user_id']; ?>" class="inline-flex items-center justify-center w-10 h-10 bg-slate-50 text-slate-300 hover:bg-red-600 hover:text-white transition-all shadow-sm" title="Padam" onclick="return confirm('Adakah anda pasti mahu memadam pengguna ini?')">
                                <i class="fa-solid fa-trash text-xs"></i>
                            </a>
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
