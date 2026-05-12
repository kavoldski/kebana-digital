<?php
/**
 * KEBANA Management System - View Member (MYDS Inspired)
 * File: modules/members/view.php
 */

use App\Helpers\MembersHelper;

require_once APP_ROOT . '/includes/header.php';

// Get member ID from URL
$member_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($member_id <= 0) {
    header('Location: /kebana-digital/members');
    exit;
}

// Get member details
$member = MembersHelper::getMemberById($member_id);

if (empty($member)) {
    header('Location: /kebana-digital/members');
    exit;
}

$page_title = 'PROFIL AHLI';
?>

<div class="space-y-12">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 border-t-8 border-kebana-blue shadow-sm">
        <div>
            <h2 class="text-2xl font-black text-kebana-blue uppercase tracking-tight italic"><?php echo htmlspecialchars($member['full_name']); ?></h2>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">ID AHLI: #<?php echo str_pad($member['member_id'], 4, '0', STR_PAD_LEFT); ?></p>
        </div>
        <div class="flex gap-4">
            <a href="/kebana-digital/members" class="bg-slate-100 text-slate-600 px-8 py-4 text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all flex items-center">
                <i class="fa-solid fa-arrow-left mr-3"></i>
                SENARAI
            </a>
            <a href="/kebana-digital/members/edit/<?php echo $member['member_id']; ?>" class="bg-kebana-blue text-white px-8 py-4 text-[10px] font-black uppercase tracking-widest hover:bg-kebana-accent transition-all shadow-xl flex items-center">
                <i class="fa-solid fa-pen-to-square mr-3"></i>
                KEMASKINI
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-12">
            <div class="bg-white border border-slate-100 shadow-sm overflow-hidden">
                <div class="p-10 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
                    <h3 class="text-xs font-black text-kebana-blue uppercase tracking-widest">Maklumat Peribadi</h3>
                    <i class="fa-solid fa-id-card text-kebana-blue opacity-20"></i>
                </div>
                <div class="p-10 space-y-10">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Nama Penuh</p>
                            <p class="text-sm font-black text-kebana-blue uppercase"><?php echo htmlspecialchars($member['full_name']); ?></p>
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">No. Kad Pengenalan</p>
                            <p class="text-sm font-black text-kebana-blue"><?php echo htmlspecialchars($member['ic_number']); ?></p>
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">No. Telefon</p>
                            <p class="text-sm font-black text-kebana-blue"><?php echo htmlspecialchars($member['phone_no'] ?? 'TIADA TALIAN'); ?></p>
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Kawasan / Kampung</p>
                            <p class="text-sm font-black text-kebana-blue uppercase"><?php echo htmlspecialchars($member['village']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity / History placeholder -->
            <div class="bg-white border border-slate-100 shadow-sm overflow-hidden opacity-50">
                <div class="p-10 border-b border-slate-50 flex items-center justify-between">
                    <h3 class="text-xs font-black text-kebana-blue uppercase tracking-widest">Rekod Aktiviti & Kehadiran</h3>
                    <span class="text-[9px] font-black bg-slate-100 text-slate-400 px-3 py-1 uppercase tracking-widest">Akan Datang</span>
                </div>
                <div class="p-20 text-center">
                    <i class="fa-solid fa-calendar-lines-pen text-5xl text-slate-100 mb-6 block"></i>
                    <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Tiada Rekod Aktiviti Dijumpai</p>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="space-y-12">
            <div class="bg-white border border-slate-100 shadow-sm overflow-hidden">
                <div class="p-8 border-b border-slate-50 bg-slate-50/50">
                    <h3 class="text-xs font-black text-kebana-blue uppercase tracking-widest">Status Ahli</h3>
                </div>
                <div class="p-8 space-y-8">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Status Semasa</span>
                        <span class="px-4 py-1.5 text-[9px] font-black uppercase tracking-widest <?php echo strtolower($member['status']) === 'active' ? 'bg-green-600 text-white shadow-lg shadow-green-600/20' : 'bg-amber-500 text-white shadow-lg shadow-amber-500/20'; ?>">
                            <?php echo htmlspecialchars($member['status']); ?>
                        </span>
                    </div>
                    <div class="pt-8 border-t border-slate-50 space-y-6">
                        <div>
                            <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest mb-2">Tarikh Didaftarkan</p>
                            <p class="text-[11px] font-black text-kebana-blue"><?php echo date('d F Y', strtotime($member['created_at'])); ?></p>
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest mb-2">Kemaskini Terakhir</p>
                            <p class="text-[11px] font-black text-kebana-blue"><?php echo date('d F Y, H:i', strtotime($member['updated_at'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="bg-red-50 border border-red-100 p-8 space-y-6">
                <h3 class="text-[10px] font-black text-red-800 uppercase tracking-widest flex items-center">
                    <i class="fa-solid fa-triangle-exclamation mr-3 text-red-600"></i>
                    Zon Berisiko
                </h3>
                <p class="text-[9px] text-red-600/70 font-bold uppercase leading-relaxed tracking-tight">
                    Tindakan ini akan memadam rekod ahli secara kekal dari pangkalan data sistem.
                </p>
                <a href="/kebana-digital/members?delete=<?php echo $member['member_id']; ?>" class="block w-full py-4 bg-red-600 text-white text-[10px] font-black uppercase tracking-widest text-center hover:bg-red-700 transition-all shadow-lg shadow-red-600/20">
                    PADAM REKOD AHLI
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
