<?php
/**
 * KEBANA Management System - Edit User
 * File: modules/users/edit.php
 */

use App\Core\Database;
use App\Helpers\UserHelper;
use App\Helpers\CawanganHelper;

if (!isAdmin()) {
    header('Location: /kebana-digital/dashboard');
    exit();
}

$user_id = $_GET['id'] ?? null;
if (!$user_id) {
    header('Location: /kebana-digital/users');
    exit();
}

$user = UserHelper::getUserById($user_id);
if (!$user) {
    header('Location: /kebana-digital/users?error=User not found');
    exit();
}

$message = '';
$message_type = '';

// Handle basic info update
if (isset($_POST['update_info'])) {
    $data = [
        'username' => $_POST['username'],
        'email' => $_POST['email'],
        'role' => (int)$_POST['role'],
        'cawangan_id' => $_POST['cawangan_id'] ?: null
    ];
    
    $result = UserHelper::updateUser($user_id, $data);
    $message = $result['message'];
    $message_type = $result['status'] ? 'success' : 'error';
    
    // Refresh user data
    if ($result['status']) {
        $user = UserHelper::getUserById($user_id);
    }
}

// Handle password reset
if (isset($_POST['reset_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $message = 'Kata laluan tidak sepadan.';
        $message_type = 'error';
    } else {
        $result = UserHelper::resetPassword($user_id, $new_password);
        $message = $result['message'];
        $message_type = $result['status'] ? 'success' : 'error';
    }
}

$page_title = 'KEMASKINI PENGGUNA';
require_once APP_ROOT . '/includes/header.php';

$cawangans = CawanganHelper::getAllCawangan();
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
            <h2 class="text-2xl font-black text-kebana-blue uppercase tracking-tight italic">Kemaskini: <?php echo htmlspecialchars($user['username']); ?></h2>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Urus maklumat akaun dan akses pengguna.</p>
        </div>
        <div>
            <a href="/kebana-digital/users" class="bg-slate-100 text-slate-600 px-8 py-4 text-[10px] font-black uppercase tracking-[0.2em] hover:bg-slate-200 transition-all inline-flex items-center">
                <i class="fa-solid fa-arrow-left mr-4"></i>
                KEMBALI KE SENARAI
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
        </div>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <!-- Basic Info Form -->
        <div class="bg-white border-t-8 border-kebana-blue shadow-sm p-10 space-y-10">
            <h3 class="text-xs font-black text-kebana-blue uppercase tracking-[0.2em] border-b border-slate-50 pb-4">Maklumat Asas</h3>
            
            <form action="" method="POST" class="space-y-8">
                <div class="grid grid-cols-1 gap-8">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Username</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Email Address</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Peranan (Role)</label>
                            <select name="role" required class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all">
                                <?php foreach ($role_names as $r_id => $r_name): ?>
                                    <option value="<?php echo $r_id; ?>" <?php echo $user['role'] == $r_id ? 'selected' : ''; ?>><?php echo $r_name; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Cawangan</label>
                            <select name="cawangan_id" class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all">
                                <option value="">PUSAT</option>
                                <?php foreach ($cawangans as $c): ?>
                                    <option value="<?php echo $c['cawangan_id']; ?>" <?php echo $user['cawangan_id'] == $c['cawangan_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['cawangan_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <button type="submit" name="update_info" class="w-full bg-kebana-blue text-white py-5 text-xs font-black uppercase tracking-[0.2em] hover:bg-kebana-accent transition-all shadow-xl">
                    KEMASKINI MAKLUMAT
                </button>
            </form>
        </div>

        <!-- Password Reset Form -->
        <div class="bg-white border-t-8 border-kebana-yellow shadow-sm p-10 space-y-10">
            <h3 class="text-xs font-black text-kebana-blue uppercase tracking-[0.2em] border-b border-slate-50 pb-4">Set Semula Kata Laluan</h3>
            
            <form action="" method="POST" class="space-y-8">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Kata Laluan Baru</label>
                    <input type="password" name="new_password" required class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all">
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Sahkan Kata Laluan</label>
                    <input type="password" name="confirm_password" required class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all">
                </div>

                <div class="p-6 bg-amber-50 border-l-4 border-amber-500 text-[10px] font-bold text-amber-700 uppercase tracking-tight leading-relaxed">
                    <i class="fa-solid fa-triangle-exclamation mr-2 text-amber-500"></i>
                    Tindakan ini akan menukar kata laluan pengguna serta-merta. Pastikan pengguna dimaklumkan mengenai perubahan ini.
                </div>

                <button type="submit" name="reset_password" class="w-full bg-kebana-yellow text-kebana-blue py-5 text-xs font-black uppercase tracking-[0.2em] hover:bg-yellow-400 transition-all shadow-xl">
                    SET SEMULA KATA LALUAN
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
