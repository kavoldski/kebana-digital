<?php
/**
 * KEBANA Management System - Edit Member (MYDS Inspired)
 * File: modules/members/edit.php
 */

use App\Helpers\MembersHelper;
use App\Core\Database;

require_once APP_ROOT . '/includes/header.php';

$db = Database::getInstance()->getConnection();

// Initialize variables
$message = '';
$message_type = '';
$member = null;

// Get member ID from URL
$member_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($member_id <= 0) {
    header('Location: /kebana-digital/members');
    exit;
}

$member = MembersHelper::getMemberById($member_id);

if (empty($member)) {
    header('Location: /kebana-digital/members');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_data = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'gender'    => $_POST['gender'] ?? null,
        'ic_number' => trim($_POST['ic_number'] ?? ''),
        'village'   => trim($_POST['village'] ?? ''),
        'phone_no'  => trim($_POST['phone_no'] ?? ''),
        'status'    => $_POST['status'] ?? 'Active'
    ];

    // Simple update logic since we're in a module file
    $stmt = $db->prepare("UPDATE tbl_member SET full_name = ?, gender = ?, ic_number = ?, village = ?, phone_no = ?, status = ?, updated_at = NOW() WHERE member_id = ?");
    $stmt->bind_param("ssssssi", $member_data['full_name'], $member_data['gender'], $member_data['ic_number'], $member_data['village'], $member_data['phone_no'], $member_data['status'], $member_id);

    if ($stmt->execute()) {
        $message = 'Maklumat ahli berjaya dikemaskini.';
        $message_type = 'success';
        $member = MembersHelper::getMemberById($member_id);
    } else {
        $message = 'Ralat: ' . $stmt->error;
        $message_type = 'error';
    }
    $stmt->close();
}

$page_title = 'KEMASKINI AHLI';
?>

<div class="space-y-12">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 border-t-8 border-kebana-blue shadow-sm">
        <div>
            <h2 class="text-2xl font-black text-kebana-blue uppercase tracking-tight italic">Kemaskini Maklumat</h2>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">ID AHLI: #<?php echo str_pad($member['member_id'], 4, '0', STR_PAD_LEFT); ?></p>
        </div>
        <a href="/kebana-digital/members/view/<?php echo $member['member_id']; ?>" class="text-[10px] font-black text-slate-400 hover:text-kebana-blue uppercase tracking-widest flex items-center transition-colors">
            <i class="fa-solid fa-arrow-left mr-3"></i>
            KEMBALI KE PROFIL
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
                        <label for="full_name" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">NAMA PENUH</label>
                        <input type="text" id="full_name" name="full_name" required 
                               class="w-full px-6 py-5 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold tracking-tight transition-all rounded-none"
                               value="<?php echo htmlspecialchars($member['full_name']); ?>">
                    </div>

                    <!-- Gender -->
                    <div>
                        <label for="gender" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">JANTINA</label>
                        <select id="gender" name="gender" required 
                                class="w-full px-6 py-5 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all rounded-none appearance-none">
                            <option value="Lelaki" <?php echo $member['gender'] === 'Lelaki' ? 'selected' : ''; ?>>LELAKI</option>
                            <option value="Perempuan" <?php echo $member['gender'] === 'Perempuan' ? 'selected' : ''; ?>>PEREMPUAN</option>
                        </select>
                    </div>

                    <!-- IC Number -->
                    <div>
                        <label for="ic_number" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">NO. KAD PENGENALAN</label>
                        <input type="text" id="ic_number" name="ic_number" required 
                               class="w-full px-6 py-5 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all rounded-none"
                               value="<?php echo htmlspecialchars($member['ic_number']); ?>">
                    </div>

                    <!-- Phone Number -->
                    <div>
                        <label for="phone_no" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">NO. TELEFON</label>
                        <input type="text" id="phone_no" name="phone_no" 
                               class="w-full px-6 py-5 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all rounded-none"
                               value="<?php echo htmlspecialchars($member['phone_no'] ?? ''); ?>">
                    </div>

                    <!-- Village -->
                    <div>
                        <label for="village" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">KAWASAN / KAMPUNG</label>
                        <input type="text" id="village" name="village" required 
                               class="w-full px-6 py-5 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold tracking-tight transition-all rounded-none"
                               value="<?php echo htmlspecialchars($member['village']); ?>">
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">STATUS KEAHLIAN</label>
                        <select id="status" name="status" required 
                                class="w-full px-6 py-5 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-sm font-bold transition-all rounded-none appearance-none">
                            <option value="Active" <?php echo $member['status'] === 'Active' ? 'selected' : ''; ?>>AKTIF</option>
                            <option value="Inactive" <?php echo $member['status'] === 'Inactive' ? 'selected' : ''; ?>>TIDAK AKTIF</option>
                            <option value="Pending" <?php echo $member['status'] === 'Pending' ? 'selected' : ''; ?>>MENUNGGU</option>
                        </select>
                    </div>
                </div>

                <div class="pt-10 flex flex-col md:flex-row gap-6">
                    <button type="submit" class="flex-1 bg-kebana-blue text-white py-6 text-xs font-black uppercase tracking-[0.3em] hover:bg-kebana-accent transition-all shadow-xl">
                        KEMASKINI MAKLUMAT
                    </button>
                    <a href="/kebana-digital/members/view/<?php echo $member['member_id']; ?>" class="px-10 py-6 border-2 border-slate-100 text-slate-400 text-xs font-black uppercase tracking-widest hover:bg-slate-50 transition-all text-center">
                        BATALKAN
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
