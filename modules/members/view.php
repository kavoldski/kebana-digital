<?php
/**
 * KEBANA Digital Management System - View Member (MYDS Inspired)
 * File: modules/members/view.php
 */

use App\Helpers\MembersHelper;

require_once APP_ROOT . '/includes/header.php';

// Get member ID from URL
$member_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($member_id <= 0) {
    header('Location: ' . URL_ROOT . '/members');
    exit;
}

// Get member details
$member = MembersHelper::getMemberById($member_id);

if (empty($member)) {
    header('Location: ' . URL_ROOT . '/members');
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
            <a href="<?= URL_ROOT ?>/members" class="bg-slate-100 text-slate-600 px-8 py-4 text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all flex items-center">
                <i class="fa-solid fa-arrow-left mr-3"></i>
                SENARAI
            </a>
            <a href="<?= URL_ROOT ?>/members/edit/<?php echo $member['member_id']; ?>" class="bg-kebana-blue text-white px-8 py-4 text-[10px] font-black uppercase tracking-widest hover:bg-kebana-accent transition-all shadow-xl flex items-center">
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
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Jantina</p>
                            <p class="text-sm font-black text-kebana-blue uppercase"><?php echo htmlspecialchars(MembersHelper::getGenderLabel($member)); ?></p>
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
                        <?php 
                        $status_class = 'bg-amber-500 text-white shadow-lg shadow-amber-500/20';
                        if (strtolower($member['status']) === 'active') $status_class = 'bg-green-600 text-white shadow-lg shadow-green-600/20';
                        elseif (strtolower($member['status']) === 'inactive') $status_class = 'bg-slate-400 text-white shadow-lg shadow-slate-400/20';
                        ?>
                        <span class="px-4 py-1.5 text-[9px] font-black uppercase tracking-widest <?php echo $status_class; ?>">
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
                <a href="<?= URL_ROOT ?>/members?delete=<?php echo $member['member_id']; ?>" onclick="openDeleteModal(event)" class="block w-full py-4 bg-red-600 text-white text-[10px] font-black uppercase tracking-widest text-center hover:bg-red-700 transition-all shadow-lg shadow-red-600/20">
                    PADAM REKOD AHLI
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Premium Delete Confirmation Modal -->
<div id="deleteModalOverlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[150] opacity-0 pointer-events-none transition-all duration-300 ease-out flex items-center justify-center p-4">
    <div id="deleteCard" class="bg-white/95 backdrop-blur-md max-w-md w-full p-8 shadow-2xl border-t-8 border-red-600 transform scale-95 opacity-0 transition-all duration-300 ease-out space-y-6">
        
        <div class="flex items-center gap-4 text-left">
            <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center text-red-600 shrink-0">
                <i class="fa-solid fa-trash-can text-xl"></i>
            </div>
            <div>
                <h3 class="text-xl font-black text-kebana-blue tracking-tighter uppercase italic leading-none">
                    Padam Rekod Ahli
                </h3>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">
                    Tindakan ini tidak boleh diundur balik
                </p>
            </div>
        </div>

        <div class="text-xs text-slate-600 leading-relaxed uppercase tracking-wider bg-slate-50/50 p-4 border border-slate-100 text-left">
            Adakah anda pasti mahu memadam profil ahli: <strong class="text-kebana-blue font-black"><?php echo htmlspecialchars($member['full_name']); ?></strong>?
        </div>

        <div class="flex gap-3">
            <button type="button" onclick="closeDeleteModal()" class="w-1/2 bg-slate-100 text-slate-500 py-4 text-xs font-black uppercase tracking-[0.2em] hover:bg-slate-200 hover:text-slate-700 transition-all text-center">
                BATAL
            </button>
            <a href="<?= URL_ROOT ?>/members?delete=<?php echo $member['member_id']; ?>" class="w-1/2 bg-red-600 text-white py-4 text-xs font-black uppercase tracking-[0.2em] hover:bg-red-700 hover:shadow-red-200/50 hover:shadow-xl transition-all text-center flex items-center justify-center">
                PADAM
            </a>
        </div>
    </div>
</div>

<script>
function openDeleteModal(e) {
    if (e) e.preventDefault();
    const overlay = document.getElementById('deleteModalOverlay');
    const card = document.getElementById('deleteCard');
    
    overlay.classList.remove('opacity-0', 'pointer-events-none');
    overlay.classList.add('opacity-100');
    
    card.classList.remove('scale-95', 'opacity-0');
    card.classList.add('scale-100', 'opacity-100');
}

function closeDeleteModal() {
    const overlay = document.getElementById('deleteModalOverlay');
    const card = document.getElementById('deleteCard');
    
    overlay.classList.remove('opacity-100');
    overlay.classList.add('opacity-0', 'pointer-events-none');
    
    card.classList.remove('scale-100', 'opacity-100');
    card.classList.add('scale-95', 'opacity-0');
}

document.addEventListener('DOMContentLoaded', function() {
    const deleteOverlay = document.getElementById('deleteModalOverlay');
    deleteOverlay?.addEventListener('click', function(e) {
        if (e.target === deleteOverlay) {
            closeDeleteModal();
        }
    });
});
</script>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
