<?php
use App\Helpers\DocumentsHelper;
?>
<!-- Documents Display -->
<div id="grid-view" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
    <?php if (empty($docs)): ?>
        <div class="col-span-full py-20 text-center bg-white border border-slate-300">
            <p class="text-sm font-black text-slate-500 uppercase tracking-[0.2em]">Tiada fail dijumpai dalam arkib.</p>
        </div>
    <?php else: ?>
        <?php foreach ($docs as $d): 
            $ext = strtolower(pathinfo($d['file_path'], PATHINFO_EXTENSION));
            $icon = 'fa-file-lines';
            $icon_color = 'text-slate-500';
            if ($ext === 'pdf') { $icon = 'fa-file-pdf'; $icon_color = 'text-red-600'; }
            elseif (in_array($ext, ['jpg', 'jpeg', 'png'])) { $icon = 'fa-file-image'; $icon_color = 'text-blue-600'; }
            elseif ($ext === 'docx') { $icon = 'fa-file-word'; $icon_color = 'text-kebana-blue'; }
            elseif ($ext === 'xlsx') { $icon = 'fa-file-excel'; $icon_color = 'text-green-600'; }
            
            $size_str = isset($d['doc_size']) ? DocumentsHelper::formatBytes($d['doc_size']) : 'N/A';
            $dl_link = URL_ROOT . "/documents?track_id=" . $d['doc_id'] . "&file=" . urlencode($d['file_path']);
        ?>
        <div class="bg-white border border-slate-300 hover:border-kebana-blue transition-all group relative overflow-hidden flex flex-col h-full shadow-sm">
            <!-- Status Strip -->
            <div class="h-1 <?php echo $d['status'] === 'Approved' ? 'bg-green-500' : ($d['status'] === 'Rejected' ? 'bg-red-500' : 'bg-kebana-yellow'); ?>"></div>
            
            <div class="p-6 flex-1">
                <div class="flex items-start justify-between mb-4">
                    <i class="fa-solid <?php echo $icon; ?> <?php echo $icon_color; ?> text-4xl opacity-75 group-hover:opacity-100 transition-opacity"></i>
                    <div class="text-right">
                        <span class="block text-xs font-black uppercase tracking-widest text-slate-500"><?php echo strtoupper($ext); ?></span>
                        <span class="block text-[11px] font-bold text-slate-650 mt-1"><?php echo $size_str; ?></span>
                    </div>
                </div>
                
                <h3 class="text-sm font-black text-kebana-blue uppercase tracking-tight leading-tight mb-2 line-clamp-2" title="<?php echo htmlspecialchars($d['doc_name']); ?>">
                    <?php echo htmlspecialchars($d['doc_name']); ?>
                </h3>
                
                <div class="flex flex-wrap gap-1.5 mb-4">
                    <?php 
                    $tags = explode(',', $d['doc_tags'] ?? '');
                    foreach ($tags as $tag): 
                        $trimmed = trim($tag);
                        if ($trimmed):
                    ?>
                        <span class="px-2.5 py-1 bg-slate-50 text-[10px] font-bold text-slate-600 border border-slate-300 uppercase"><?php echo htmlspecialchars($trimmed); ?></span>
                    <?php endif; endforeach; ?>
                </div>

                <div class="mt-auto pt-4 border-t border-slate-200 space-y-2">
                    <div class="flex justify-between items-center">
                        <p class="text-xs font-black text-slate-500 uppercase tracking-tighter">
                            <i class="fa-solid fa-download mr-1"></i> <?php echo number_format($d['download_count']); ?>
                        </p>
                        <p class="text-xs font-black text-slate-500 uppercase tracking-tighter">
                            <?php echo date('d M Y', strtotime($d['uploaded_at'])); ?>
                        </p>
                    </div>
                    <p class="text-xs font-black text-slate-500 uppercase tracking-tighter">
                        OLEH: <span class="text-slate-650 font-bold"><?php echo htmlspecialchars($d['uploader_name'] ?? 'SISTEM'); ?></span>
                    </p>
                </div>
            </div>

            <div class="flex border-t border-slate-300">
                <a href="<?php echo $dl_link; ?>" target="_blank" class="flex-1 py-4 text-center text-xs font-black text-slate-600 uppercase tracking-widest hover:bg-kebana-blue hover:text-white transition-all border-r border-slate-300">
                    <i class="fa-solid fa-eye mr-2"></i> LIHAT
                </a>
                <a href="<?php echo $dl_link; ?>" download class="flex-1 py-4 text-center text-xs font-black text-slate-600 uppercase tracking-widest hover:bg-kebana-yellow hover:text-kebana-blue transition-all">
                    <i class="fa-solid fa-download mr-2"></i> UNDUH
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- List View (Table) -->
<div id="list-view" class="hidden bg-white border border-slate-300 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b-2 border-slate-300 bg-slate-50">
                    <th class="px-8 py-6 text-xs font-black text-kebana-blue uppercase tracking-widest">Fail</th>
                    <th class="px-8 py-6 text-xs font-black text-kebana-blue uppercase tracking-widest">Tag</th>
                    <th class="px-8 py-6 text-xs font-black text-kebana-blue uppercase tracking-widest">Info</th>
                    <th class="px-8 py-6 text-xs font-black text-kebana-blue uppercase tracking-widest">Populariti</th>
                    <th class="px-8 py-6 text-xs font-black text-kebana-blue uppercase tracking-widest text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-300">
                <?php if (empty($docs)): ?>
                    <tr><td colspan="5" class="py-20 text-center text-sm font-black text-slate-500 uppercase tracking-wider">Tiada fail dijumpai.</td></tr>
                <?php else: ?>
                    <?php foreach ($docs as $d): 
                        $ext = strtolower(pathinfo($d['file_path'], PATHINFO_EXTENSION));
                        $icon = 'fa-file-lines';
                        if ($ext === 'pdf') $icon = 'fa-file-pdf';
                        elseif (in_array($ext, ['jpg', 'jpeg', 'png'])) $icon = 'fa-file-image';
                        elseif ($ext === 'docx') $icon = 'fa-file-word';
                        elseif ($ext === 'xlsx') $icon = 'fa-file-excel';
                        $dl_link = URL_ROOT . "/documents?track_id=" . $d['doc_id'] . "&file=" . urlencode($d['file_path']);
                    ?>
                    <tr class="hover:bg-slate-50 transition-colors group">
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-4">
                                <i class="fa-solid <?php echo $icon; ?> text-2xl text-slate-400 group-hover:text-kebana-blue transition-colors"></i>
                                <div>
                                    <p class="text-sm font-black text-kebana-blue uppercase truncate max-w-[300px]" title="<?php echo htmlspecialchars($d['doc_name']); ?>">
                                        <?php echo htmlspecialchars($d['doc_name']); ?>
                                    </p>
                                    <p class="text-xs font-bold text-slate-500 uppercase mt-1"><?php echo strtoupper($ext); ?> • <?php echo DocumentsHelper::formatBytes($d['doc_size']); ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex flex-wrap gap-1.5">
                                <?php 
                                $tags = explode(',', $d['doc_tags'] ?? '');
                                foreach ($tags as $tag): 
                                    $trimmed = trim($tag);
                                    if ($trimmed):
                                ?>
                                    <span class="px-2.5 py-1 bg-slate-50 text-[10px] font-bold text-slate-600 border border-slate-300 uppercase"><?php echo htmlspecialchars($trimmed); ?></span>
                                <?php endif; endforeach; ?>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <p class="text-xs font-black text-slate-655 uppercase tracking-tighter"><?php echo date('d M Y', strtotime($d['uploaded_at'])); ?></p>
                            <p class="text-[11px] font-bold text-slate-500 uppercase mt-0.5">Oleh: <?php echo htmlspecialchars($d['uploader_name'] ?? 'Sistem'); ?></p>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden border border-slate-300">
                                    <?php $pct = min(100, ($d['download_count'] / 50) * 100); ?>
                                    <div class="h-full bg-kebana-yellow transition-all" style="width: <?php echo $pct; ?>%"></div>
                                </div>
                                <span class="text-xs font-black text-slate-655"><?php echo number_format($d['download_count']); ?></span>
                            </div>
                        </td>
                        <td class="px-8 py-6 text-right space-x-3">
                            <a href="<?php echo $dl_link; ?>" target="_blank" class="text-xs font-black text-slate-600 uppercase hover:text-kebana-blue transition-colors">Lihat</a>
                            <a href="<?php echo $dl_link; ?>" download class="text-xs font-black text-slate-600 uppercase hover:text-kebana-yellow transition-colors">Unduh</a>
                            <?php if (hasRole([888, 4])): ?>
                            <a href="?delete_id=<?php echo $d['doc_id']; ?>" onclick="return confirm('Padam fail?')" class="text-red-600 hover:text-red-800 transition-colors">
                                <i class="fa-solid fa-trash-can"></i>
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

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<div class="flex justify-center mt-12">
    <div class="flex gap-2">
        <?php for ($i = 1; $i <= $total_pages; $i++): 
            $active = ($i === $page);
            $url = "?page=$i" . ($filters['tag'] ? "&tag=" . urlencode($filters['tag']) : "") . ($filters['search'] ? "&search=" . urlencode($filters['search']) : "") . ($filters['ext'] ? "&ext=" . urlencode($filters['ext']) : "") . ($filters['sort'] ? "&sort=" . urlencode($filters['sort']) : "");
        ?>
        <a href="<?php echo $url; ?>" 
           onclick="fetchResults(<?php echo $i; ?>); return false;"
           class="w-10 h-10 flex items-center justify-center text-xs font-black border <?php echo $active ? 'bg-kebana-blue text-white border-kebana-blue shadow-lg' : 'bg-white text-slate-500 border-slate-300 hover:border-kebana-blue hover:text-kebana-blue'; ?> transition-all">
            <?php echo $i; ?>
        </a>
        <?php endfor; ?>
    </div>
</div>
<?php endif; ?>

<!-- Ensure active view mode styling and visibility is retained after DOM replacement -->
<script>
    if (typeof savedMode !== 'undefined') {
        setViewMode(savedMode);
    } else {
        const activeMode = localStorage.getItem('archive_view_mode') || 'grid';
        setViewMode(activeMode);
    }
</script>
