<?php
/**
 * KEBANA Digital Management System - Add Member Form (MYDS Inspired)
 * File: modules/members/add.php
 */

$page_title = 'DAFTAR AHLI BARU';

use App\Helpers\MembersHelper;

require_once APP_ROOT . '/includes/header.php';

if (!hasRole([888, 1, 4, 33])) {
    echo "<div class='p-12 text-center'><h1 class='text-2xl font-black text-red-600 uppercase tracking-widest'>AKSES DISEKAT</h1></div>";
    require_once APP_ROOT . '/includes/footer.php';
    exit;
}

// Initialize variables
$message = '';
$message_type = '';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_data = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'gender'    => $_POST['gender'] ?? null,
        'ic_number' => trim($_POST['ic_number'] ?? ''),
        'village'   => trim($_POST['village'] ?? ''),
        'phone_no'  => trim($_POST['phone_no'] ?? ''),
        'status'    => $_POST['status'] ?? 'Active'
    ];

    $result = MembersHelper::addMember($member_data);

    if ($result['status']) {
        $message = $result['message'];
        $message_type = 'success';
        // Success alert with redirect hint
        echo "<script>setTimeout(() => { window.location.href = '" . URL_ROOT . "/members'; }, 2000);</script>";
    } else {
        $message = $result['message'];
        $message_type = 'error';
    }
}
?>

<div class="space-y-12">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 border-t-8 border-kebana-blue shadow-sm">
        <div>
            <h2 class="text-2xl font-black text-kebana-blue uppercase tracking-tight italic">Pendaftaran Ahli</h2>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Sila masukkan maklumat lengkap ahli baru.</p>
        </div>
        <a href="<?= URL_ROOT ?>/members" class="text-[10px] font-black text-slate-400 hover:text-kebana-blue uppercase tracking-widest flex items-center transition-colors">
            <i class="fa-solid fa-arrow-left mr-3"></i>
            KEMBALI KE SENARAI
        </a>
    </div>

    <?php if (!empty($message)): ?>
    <div class="p-6 <?php echo $message_type === 'success' ? 'bg-green-50 border-green-600 text-green-800' : 'bg-red-50 border-red-600 text-red-800'; ?> border-l-4 font-black text-xs uppercase tracking-widest shadow-sm">
        <div class="flex items-center">
            <i class="fa-solid <?php echo $message_type === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?> mr-4 text-lg"></i>
            <span><?php echo htmlspecialchars($message); ?></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Form Section -->
    <div class="bg-white border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-8 md:p-12">
            <form method="POST" class="space-y-10">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <!-- Full Name -->
                    <div class="md:col-span-2">
                        <label for="full_name" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">NAMA PENUH (SEPERTI DALAM IC)</label>
                        <input type="text" id="full_name" name="full_name" required 
                               class="w-full px-6 py-5 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold tracking-tight transition-all rounded-none"
                               placeholder="Contoh: AHMAD BIN ABDULLAH"
                               value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                    </div>

                    <!-- Gender -->
                    <div>
                        <label for="gender" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">JANTINA</label>
                        <select id="gender" name="gender" required 
                                class="w-full px-6 py-5 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all rounded-none appearance-none">
                            <option value="" disabled <?php echo !isset($_POST['gender']) ? 'selected' : ''; ?>>PILIH JANTINA</option>
                            <option value="Lelaki" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Lelaki') ? 'selected' : ''; ?>>LELAKI</option>
                            <option value="Wanita" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Wanita') ? 'selected' : ''; ?>>WANITA</option>
                        </select>
                    </div>

                    <!-- IC Number -->
                    <div>
                        <label for="ic_number" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">NO. KAD PENGENALAN</label>
                        <input type="text" id="ic_number" name="ic_number" required 
                               class="w-full px-6 py-5 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all rounded-none"
                               placeholder="Contoh: 900101-13-5555"
                               value="<?php echo isset($_POST['ic_number']) ? htmlspecialchars($_POST['ic_number']) : ''; ?>">
                    </div>

                    <!-- Phone Number -->
                    <div>
                        <label for="phone_no" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">NO. TELEFON</label>
                        <input type="text" id="phone_no" name="phone_no" 
                               class="w-full px-6 py-5 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all rounded-none"
                               placeholder="Contoh: 012-3456789"
                               value="<?php echo isset($_POST['phone_no']) ? htmlspecialchars($_POST['phone_no']) : ''; ?>">
                    </div>

                    <!-- Village -->
                    <div>
                        <label for="village" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">KAWASAN / KAMPUNG</label>
                        <input type="text" id="village" name="village" required 
                               class="w-full px-6 py-5 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold tracking-tight transition-all rounded-none"
                               placeholder="Contoh: KAMPUNG DATA KAKUS"
                               value="<?php echo isset($_POST['village']) ? htmlspecialchars($_POST['village']) : ''; ?>">
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">STATUS KEAHLIAN</label>
                        <select id="status" name="status" required 
                                class="w-full px-6 py-5 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all rounded-none appearance-none">
                            <option value="Active" <?php echo (isset($_POST['status']) && $_POST['status'] === 'Active') ? 'selected' : ''; ?>>AKTIF</option>
                            <option value="Inactive" <?php echo (isset($_POST['status']) && $_POST['status'] === 'Inactive') ? 'selected' : ''; ?>>TIDAK AKTIF</option>
                            <option value="Pending" <?php echo (isset($_POST['status']) && $_POST['status'] === 'Pending') ? 'selected' : ''; ?>>MENUNGGU</option>
                        </select>
                    </div>
                </div>

                <div class="pt-10 flex flex-col md:flex-row gap-6">
                    <button type="submit" class="flex-1 bg-kebana-blue text-white py-6 text-xs font-black uppercase tracking-[0.3em] hover:bg-kebana-accent transition-all shadow-xl">
                        SIMPAN MAKLUMAT AHLI
                    </button>
                    <button type="reset" class="px-10 py-6 border-2 border-slate-100 text-slate-400 text-xs font-black uppercase tracking-widest hover:bg-slate-50 transition-all">
                        SET SEMULA
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Info Box -->
    <div class="bg-slate-50 p-8 border border-slate-100">
        <div class="flex items-start space-x-6">
            <div class="w-12 h-12 bg-kebana-blue/5 rounded-none flex items-center justify-center text-kebana-blue">
                <i class="fa-solid fa-circle-info text-xl"></i>
            </div>
            <div>
                <h4 class="text-xs font-black text-kebana-blue uppercase tracking-widest">Nota Penting</h4>
                <p class="text-[10px] text-slate-400 font-bold uppercase mt-2 leading-relaxed tracking-tight">
                    Semua maklumat yang didaftarkan adalah sulit dan tertakluk di bawah Akta Perlindungan Data Peribadi 2010. Sila pastikan No. Kad Pengenalan adalah tepat untuk mengelakkan ralat pendaftaran.
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
