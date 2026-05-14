<?php
/**
 * KEBANA Digital Management System - Add New User
 * File: modules/users/add.php
 */

use App\Core\Database;
use App\Helpers\CawanganHelper;

if (!isAdmin()) {
    header('Location: /kebana-digital/dashboard');
    exit();
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = Database::getInstance()->getConnection();
    
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = (int)$_POST['role'];
    $cawangan_id = $_POST['cawangan_id'] ?: null;
    $password = $_POST['password'];
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $db->prepare("
        INSERT INTO tbl_user (username, email, role, cawangan_id, password_hash) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    if ($stmt) {
        $stmt->bind_param("ssiis", $username, $email, $role, $cawangan_id, $password_hash);
        
        if ($stmt->execute()) {
            $stmt->close();
            header('Location: /kebana-digital/users?message=Pengguna berjaya didaftarkan&type=success');
            exit();
        } else {
            $error = $stmt->error;
            $stmt->close();
            if (strpos($error, 'Duplicate entry') !== false) {
                $message = 'Username atau Email sudah wujud.';
            } else {
                $message = 'Ralat sistem: ' . $error;
            }
            $message_type = 'error';
        }
    } else {
        $message = 'Ralat sistem (Prepare failed): ' . $db->error;
        $message_type = 'error';
    }
}

$page_title = 'DAFTAR PENGGUNA BARU';
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
            <h2 class="text-2xl font-black text-kebana-blue uppercase tracking-tight italic">Daftar Pengguna Baru</h2>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Cipta akaun akses baru untuk warga KEBANA Digital.</p>
        </div>
        <div>
            <a href="/kebana-digital/users" class="bg-slate-100 text-slate-600 px-8 py-4 text-[10px] font-black uppercase tracking-[0.2em] hover:bg-slate-200 transition-all inline-flex items-center">
                <i class="fa-solid fa-arrow-left mr-4"></i>
                KEMBALI KE SENARAI
            </a>
        </div>
    </div>

    <?php if (!empty($message)): ?>
    <div class="p-6 bg-red-50 border-red-600 text-red-800 border-l-4 font-black text-xs uppercase tracking-widest shadow-sm">
        <div class="flex items-center">
            <i class="fa-solid fa-triangle-exclamation mr-4 text-lg"></i>
            <span><?php echo htmlspecialchars($message); ?></span>
        </div>
    </div>
    <?php endif; ?>

    <div class="max-w-4xl mx-auto">
        <div class="bg-white border-t-8 border-kebana-blue shadow-sm p-10 space-y-10">
            <form action="" method="POST" class="space-y-10">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Username</label>
                        <input type="text" name="username" required placeholder="Contoh: ahmad_kebana" class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Email Address</label>
                        <input type="email" name="email" required placeholder="email@kebana.local" class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Peranan (Role)</label>
                        <select name="role" required class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all">
                            <option value="">Pilih Peranan...</option>
                            <?php foreach ($role_names as $r_id => $r_name): ?>
                                <option value="<?php echo $r_id; ?>"><?php echo $r_name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Cawangan</label>
                        <select name="cawangan_id" class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all">
                            <option value="">PUSAT</option>
                            <?php foreach ($cawangans as $c): ?>
                                <option value="<?php echo $c['cawangan_id']; ?>"><?php echo htmlspecialchars($c['cawangan_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Kata Laluan</label>
                        <input type="password" name="password" required class="w-full px-6 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all">
                    </div>
                    
                    <div class="flex items-end">
                        <div class="p-6 bg-slate-50 border-l-4 border-slate-300 text-[9px] font-bold text-slate-500 uppercase tracking-widest leading-relaxed">
                            Pastikan kata laluan yang kuat diberikan kepada pengguna baru.
                        </div>
                    </div>
                </div>

                <div class="pt-6">
                    <button type="submit" class="w-full bg-kebana-blue text-white py-5 text-xs font-black uppercase tracking-[0.2em] hover:bg-kebana-accent transition-all shadow-xl">
                        DAFTAR PENGGUNA
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
