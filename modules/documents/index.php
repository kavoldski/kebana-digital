<?php
/**
 * KEBANA Management System - File Archive (MYDS Inspired)
 * File: modules/documents/index.php
 */

use App\Helpers\DocumentsHelper;

require_once APP_ROOT . '/includes/header.php';

// Access Control: Admin/Secretaries/Treasurers
if (!hasRole([888, 4, 33, 6, 55, 7, 66])) {
    echo "<div class='p-12 text-center'><h1 class='text-2xl font-black text-red-600 uppercase tracking-widest'>AKSES DISEKAT</h1></div>";
    require_once APP_ROOT . '/includes/footer.php';
    exit;
}

$filters = [
    'tag' => $_GET['tag'] ?? '',
    'search' => $_GET['search'] ?? ''
];

$docs = DocumentsHelper::getAllDocuments($filters);
$all_tags = DocumentsHelper::getUniqueTags();

$page_title = 'ARKIB FAIL & DOKUMEN';
?>

<div class="space-y-12 pb-24">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 border-t-8 border-kebana-blue shadow-sm">
        <div>
            <h2 class="text-2xl font-black text-kebana-blue uppercase tracking-tight italic">Pusat Arkib Digital</h2>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Pengurusan Dokumen Berpusat dengan Sistem Tagging Automatik.</p>
        </div>
        <a href="/kebana-digital/documents/upload" class="bg-kebana-blue text-white px-10 py-4 text-xs font-black uppercase tracking-[0.2em] hover:bg-kebana-accent transition-all shadow-xl inline-flex items-center">
            <i class="fa-solid fa-cloud-arrow-up mr-4 text-lg"></i>
            MUAT NAIK FAIL
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-8 border border-slate-100 shadow-sm">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
            <div>
                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3">Tapis mengikut Tag</label>
                <select name="tag" class="w-full px-5 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold uppercase transition-all rounded-none appearance-none">
                    <option value="">Semua Tag</option>
                    <?php foreach ($all_tags as $tag): ?>
                    <option value="<?php echo htmlspecialchars($tag); ?>" <?php echo $filters['tag'] === $tag ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($tag); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3">Cari Fail</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($filters['search']); ?>" placeholder="Masukkan kata kunci atau tag..."
                       class="w-full px-5 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold uppercase transition-all">
            </div>
            <button type="submit" class="bg-kebana-dark text-white py-4 text-xs font-black uppercase tracking-widest hover:bg-black transition-all shadow-lg">
                CARI FAIL
            </button>
        </form>
    </div>

    <!-- Documents Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
        <?php if (empty($docs)): ?>
            <div class="col-span-full py-20 text-center bg-white border border-slate-100">
                <p class="text-[10px] font-black text-slate-300 uppercase tracking-[0.3em]">Tiada fail dijumpai dalam arkib.</p>
            </div>
        <?php else: ?>
            <?php foreach ($docs as $d): 
                $ext = strtolower(pathinfo($d['file_path'], PATHINFO_EXTENSION));
                $icon = 'fa-file-lines';
                $icon_color = 'text-slate-400';
                if ($ext === 'pdf') { $icon = 'fa-file-pdf'; $icon_color = 'text-red-500'; }
                elseif (in_array($ext, ['jpg', 'jpeg', 'png'])) { $icon = 'fa-file-image'; $icon_color = 'text-blue-500'; }
                elseif ($ext === 'docx') { $icon = 'fa-file-word'; $icon_color = 'text-kebana-blue'; }
                elseif ($ext === 'xlsx') { $icon = 'fa-file-excel'; $icon_color = 'text-green-600'; }
            ?>
            <div class="bg-white border border-slate-100 hover:border-kebana-blue transition-all group relative overflow-hidden flex flex-col h-full shadow-sm">
                <!-- Status Strip -->
                <div class="h-1 <?php echo $d['status'] === 'Approved' ? 'bg-green-500' : ($d['status'] === 'Rejected' ? 'bg-red-500' : 'bg-kebana-yellow'); ?>"></div>
                
                <div class="p-6 flex-1">
                    <div class="flex items-start justify-between mb-4">
                        <i class="fa-solid <?php echo $icon; ?> <?php echo $icon_color; ?> text-4xl opacity-50 group-hover:opacity-100 transition-opacity"></i>
                        <span class="text-[8px] font-black uppercase tracking-widest text-slate-300"><?php echo strtoupper($ext); ?></span>
                    </div>
                    
                    <h3 class="text-sm font-black text-kebana-blue uppercase tracking-tight leading-tight mb-2 line-clamp-2" title="<?php echo htmlspecialchars($d['doc_name']); ?>">
                        <?php echo htmlspecialchars($d['doc_name']); ?>
                    </h3>
                    
                    <div class="flex flex-wrap gap-1 mb-4">
                        <?php 
                        $tags = explode(',', $d['doc_tags'] ?? '');
                        foreach ($tags as $tag): 
                            $trimmed = trim($tag);
                            if ($trimmed):
                        ?>
                            <span class="px-2 py-0.5 bg-slate-50 text-[8px] font-bold text-slate-400 border border-slate-100 uppercase"><?php echo htmlspecialchars($trimmed); ?></span>
                        <?php endif; endforeach; ?>
                    </div>

                    <div class="mt-auto pt-4 border-t border-slate-50 space-y-2">
                        <p class="text-[8px] font-black text-slate-300 uppercase tracking-tighter">
                            DIUPLOAD PADA: <span class="text-slate-500"><?php echo date('d M Y', strtotime($d['uploaded_at'])); ?></span>
                        </p>
                        <p class="text-[8px] font-black text-slate-300 uppercase tracking-tighter">
                            OLEH: <span class="text-slate-500"><?php echo htmlspecialchars($d['uploader_name'] ?? 'SISTEM'); ?></span>
                        </p>
                        <?php if ($d['event_title']): ?>
                        <p class="text-[8px] font-black text-kebana-blue/40 uppercase tracking-tighter italic">
                            PROJEK: <span class="text-kebana-blue/60"><?php echo htmlspecialchars($d['event_title']); ?></span>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="flex border-t border-slate-50">
                    <a href="/kebana-digital/<?php echo htmlspecialchars($d['file_path']); ?>" target="_blank" class="flex-1 py-4 text-center text-[9px] font-black text-slate-400 uppercase tracking-widest hover:bg-kebana-blue hover:text-white transition-all border-r border-slate-50">
                        <i class="fa-solid fa-eye mr-2"></i> LIHAT
                    </a>
                    <a href="/kebana-digital/<?php echo htmlspecialchars($d['file_path']); ?>" download class="flex-1 py-4 text-center text-[9px] font-black text-slate-400 uppercase tracking-widest hover:bg-kebana-yellow hover:text-kebana-blue transition-all">
                        <i class="fa-solid fa-download mr-2"></i> UNDUH
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
