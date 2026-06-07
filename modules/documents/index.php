<?php
/**
 * KEBANA Digital Management System - File Archive (MYDS Inspired)
 * File: modules/documents/index.php
 */

use App\Helpers\DocumentsHelper;

// Handle Download Tracking - MUST BE BEFORE HEADER.PHP TO AVOID "HEADERS ALREADY SENT" ERROR
if (isset($_GET['track_id']) && isset($_GET['file'])) {
    if (hasRole([888, 1, 2, 3, 11, 22, 4, 33, 6, 55, 7, 66])) {
        $track_id = (int)$_GET['track_id'];
        DocumentsHelper::incrementDownloadCount($track_id);
        header("Location: " . URL_ROOT . "/" . $_GET['file']);
        exit;
    }
}

require_once APP_ROOT . '/includes/header.php';

// Access Control: Admin/Executives/Secretaries/Treasurers
if (!hasRole([888, 1, 2, 3, 11, 22, 4, 33, 6, 55, 7, 66])) {
    echo "<div class='p-12 text-center'><h1 class='text-2xl font-black text-red-600 uppercase tracking-widest'>AKSES DISEKAT</h1></div>";
    require_once APP_ROOT . '/includes/footer.php';
    exit;
}

// Handle Deletion
if (isset($_GET['delete_id']) && hasRole([888, 4])) {
    $delete_id = (int)$_GET['delete_id'];
    if (DocumentsHelper::deleteDocument($delete_id)) {
        echo "<script>window.location.href = '" . URL_ROOT . "/documents?deleted=1';</script>";
        exit;
    }
}


$current_role = (int)($_SESSION['role'] ?? 0);
$current_cawangan = isset($_SESSION['cawangan_id']) ? (int)$_SESSION['cawangan_id'] : null;
$is_pusat = in_array($current_role, [888, 1, 2, 3, 4, 5, 6, 7]);

$scope_cawangan = $is_pusat ? null : $current_cawangan;

$filters = [
    'tag' => $_GET['tag'] ?? '',
    'search' => $_GET['search'] ?? '',
    'sort' => $_GET['sort'] ?? 'newest',
    'ext' => $_GET['ext'] ?? '',
    'cawangan_id' => $scope_cawangan
];

// Pagination Logic
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

$docs = DocumentsHelper::getAllDocuments($filters, $limit, $offset);
$total_docs = DocumentsHelper::countAllDocuments($filters);
$total_pages = ceil($total_docs / $limit);
$all_tags = DocumentsHelper::getUniqueTags();

// Stats Data
$stats = DocumentsHelper::getArchiveStats($scope_cawangan);
$distribution = DocumentsHelper::getFileTypeDistribution($scope_cawangan);
$trend = DocumentsHelper::getMonthlyUploadTrend($scope_cawangan);

$page_title = 'ARKIB FAIL & DOKUMEN';
?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div class="space-y-12 pb-24">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 border border-slate-300 border-t-8 border-t-kebana-blue shadow-sm">
        <div>
            <h2 class="text-2xl font-black text-kebana-blue uppercase tracking-tight italic">Pusat Arkib Digital</h2>
            <p class="text-xs font-black text-slate-500 uppercase tracking-widest mt-2">Pengurusan Dokumen Berpusat dengan Sistem Tagging Automatik.</p>
        </div>
        <div class="flex gap-4">
            <button onclick="toggleAISearch()" class="bg-indigo-600 text-white px-6 py-4 text-xs font-black uppercase tracking-widest hover:bg-indigo-700 transition-all flex items-center shadow-lg">
                <i class="fa-solid fa-robot mr-3"></i>
                AI SEARCH
            </button>
            <button onclick="toggleManagementView()" class="bg-slate-100 text-slate-650 px-6 py-4 text-xs font-black uppercase tracking-widest hover:bg-slate-200 transition-all flex items-center border border-slate-300">
                <i class="fa-solid fa-chart-pie mr-3"></i>
                INSIGHTS
            </button>
            <a href="<?= URL_ROOT ?>/documents/upload" class="bg-kebana-blue text-white px-10 py-4 text-sm font-black uppercase tracking-[0.2em] hover:bg-kebana-accent transition-all shadow-xl inline-flex items-center">
                <i class="fa-solid fa-cloud-arrow-up mr-4 text-lg"></i>
                MUAT NAIK FAIL
            </a>
        </div>
    </div>

    <!-- KPI Stats Bar -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-0 border border-slate-300 bg-white shadow-sm overflow-hidden">
        <div class="p-8 border-r border-slate-300 flex flex-col justify-center bg-slate-50/30">
            <p class="text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Jumlah Fail</p>
            <p class="text-2xl font-black text-kebana-blue"><?php echo number_format($stats['total_files']); ?></p>
        </div>
        <div class="p-8 border-r border-slate-300 flex flex-col justify-center">
            <p class="text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Saiz Arkib</p>
            <p class="text-2xl font-black text-kebana-blue"><?php echo DocumentsHelper::formatBytes($stats['total_size'] ?? 0); ?></p>
        </div>
        <div class="p-8 border-r border-slate-300 flex flex-col justify-center">
            <p class="text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Fail Paling Popular</p>
            <p class="text-sm font-black text-kebana-blue truncate" title="<?php echo htmlspecialchars($stats['popular_doc'] ?? 'N/A'); ?>">
                <?php echo htmlspecialchars($stats['popular_doc'] ?? 'N/A'); ?>
            </p>
        </div>
        <div class="p-8 border-r border-slate-300 flex flex-col justify-center">
            <p class="text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Indeks RAG</p>
            <?php 
                $db = \App\Core\Database::getInstance()->getConnection();
                $rag_count = $db->query("SELECT COUNT(DISTINCT doc_id) as count FROM tbl_document_chunks")->fetch_assoc()['count'];
                $rag_pct = $stats['total_files'] > 0 ? ($rag_count / $stats['total_files']) * 100 : 0;
            ?>
            <div class="flex items-center gap-3">
                <p class="text-2xl font-black text-indigo-600"><?php echo number_format($rag_count); ?></p>
                <span class="text-[10px] font-bold text-slate-500 uppercase"><?php echo round($rag_pct); ?>% SIAP</span>
            </div>
        </div>
        <div class="p-8 flex flex-col justify-center bg-kebana-yellow/5">
            <?php if (in_array($current_role, [888, 4])): ?>
                <button onclick="reindexAll()" id="reindex-btn" class="w-full text-left group">
                    <p class="text-[10px] font-black text-kebana-blue/70 uppercase tracking-widest mb-2 group-hover:text-kebana-blue transition-colors">Tindakan Admin</p>
                    <p class="text-xs font-black text-kebana-blue uppercase italic flex items-center">
                        <i class="fa-solid fa-arrows-rotate mr-2 group-hover:rotate-180 transition-all duration-500"></i>
                        KEMASKINI INDEKS
                    </p>
                </button>
            <?php else: ?>
                <p class="text-[10px] font-black text-kebana-blue/70 uppercase tracking-widest mb-2">Status Capaian</p>
                <p class="text-xs font-black text-kebana-blue uppercase italic">
                    <i class="fa-solid fa-shield-halved mr-2"></i>
                    <?php echo $is_pusat ? 'Akses Global (Pusat)' : 'Akses Cawangan'; ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- AI Search Panel -->
    <div id="ai-search-panel" class="hidden animate-in fade-in slide-in-from-top-4 duration-500">
        <!-- Context Preview Modal (Nested) -->
        <div id="context-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-6 bg-indigo-950/90 backdrop-blur-xl">
            <div class="bg-white text-slate-900 w-full max-w-4xl shadow-2xl border border-slate-300 border-t-8 border-t-indigo-600 animate-in zoom-in duration-300">
                <div class="p-8 border-b border-slate-300 flex justify-between items-center">
                    <div>
                        <h4 id="context-title" class="text-base font-black text-indigo-650 uppercase tracking-widest">Tajuk Dokumen</h4>
                        <p class="text-xs font-bold text-slate-500 uppercase mt-1">Petikan Teks Yang Dijumpai Oleh AI</p>
                    </div>
                    <button onclick="closeContextModal()" class="text-slate-500 hover:text-red-500 transition-colors">
                        <i class="fa-solid fa-xmark text-2xl"></i>
                    </button>
                </div>
                <div class="p-12 max-h-[60vh] overflow-y-auto">
                    <div class="bg-slate-50 p-10 border-l-4 border-indigo-300 italic text-xl leading-relaxed text-slate-800 font-medium" id="context-body">
                        <!-- Chunk text here -->
                    </div>
                </div>
                <div class="p-8 bg-slate-50 border-t border-slate-300 flex justify-between items-center">
                    <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Rujukan Pintar RAG</span>
                    <a id="context-link" href="#" target="_blank" class="bg-indigo-600 text-white px-8 py-3 text-xs font-black uppercase tracking-widest hover:bg-indigo-700 transition-all flex items-center shadow-lg">
                        <i class="fa-solid fa-file-pdf mr-2"></i>
                        BUKA FAIL PENUH
                    </a>
                </div>
            </div>
        </div>

        <div class="bg-indigo-900 text-white p-12 border-b-8 border-indigo-450 shadow-2xl relative overflow-hidden">
            <!-- Decorative Elements -->
            <div class="absolute top-0 right-0 p-12 opacity-10 pointer-events-none">
                <i class="fa-solid fa-robot text-9xl"></i>
            </div>

            <div class="max-w-4xl mx-auto relative z-10">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-2xl font-black uppercase tracking-tight italic">Carian Pintar AI</h3>
                        <p class="text-xs font-black text-indigo-200 uppercase tracking-widest mt-2">Tanya apa sahaja tentang dokumen dalam arkib anda.</p>
                    </div>
                    <button onclick="toggleAISearch()" class="text-indigo-200 hover:text-white transition-colors">
                        <i class="fa-solid fa-xmark text-2xl"></i>
                    </button>
                </div>

                <div class="flex gap-4 mb-8">
                    <input type="text" id="ai-query" placeholder="Cth: Berapakah bajet Festival Belia 2026?" 
                           class="flex-1 bg-indigo-950/70 border-2 border-indigo-600 p-6 text-base font-bold placeholder:text-indigo-300 outline-none focus:border-indigo-300 transition-all"
                           onkeypress="if(event.key === 'Enter') performAISearch()">
                    <button onclick="performAISearch()" id="ai-search-btn" class="bg-indigo-500 hover:bg-indigo-400 px-10 text-sm font-black uppercase tracking-widest transition-all shadow-lg flex items-center">
                        <i class="fa-solid fa-paper-plane mr-3"></i>
                        TANYA
                    </button>
                </div>

                <!-- AI Response Area -->
                <div id="ai-response-area" class="hidden space-y-8 animate-in fade-in duration-700">
                    <div class="bg-white/10 backdrop-blur-md p-8 border border-white/20">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-xs">
                                <i class="fa-solid fa-robot"></i>
                            </div>
                            <span class="text-xs font-black uppercase tracking-widest">Jawapan AI</span>
                        </div>
                        <div id="ai-answer" class="text-base leading-relaxed font-medium text-indigo-50 prose prose-invert max-w-none">
                            <!-- Answer will be injected here -->
                        </div>
                        <div class="mt-6 pt-6 border-t border-white/10 flex items-center justify-between">
                            <span id="ai-time" class="text-[10px] font-black text-indigo-300 uppercase tracking-widest">PROSES: 0ms</span>
                            <span class="text-[10px] font-black text-indigo-300 uppercase tracking-widest">DIJANAKAN OLEH GEMINI FLASH</span>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-xs font-black text-indigo-200 uppercase tracking-widest mb-4">Sumber Rujukan</h4>
                        <div id="ai-sources" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <!-- Sources will be injected here -->
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div id="ai-loading" class="hidden py-12 text-center">
                    <div class="inline-flex gap-2">
                        <div class="w-3 h-3 bg-indigo-300 rounded-full animate-bounce"></div>
                        <div class="w-3 h-3 bg-indigo-300 rounded-full animate-bounce [animation-delay:-0.15s]"></div>
                        <div class="w-3 h-3 bg-indigo-300 rounded-full animate-bounce [animation-delay:-0.3s]"></div>
                    </div>
                    <p class="text-xs font-black text-indigo-200 uppercase tracking-widest mt-6 animate-pulse">AI sedang menganalisis dokumen anda...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Management Insights (Charts) -->
    <div id="management-view" class="hidden grid grid-cols-1 lg:grid-cols-3 gap-8 animate-in fade-in slide-in-from-top-4 duration-500">
        <div class="lg:col-span-1 bg-white p-8 border border-slate-350 shadow-sm">
            <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-6 flex items-center">
                <i class="fa-solid fa-file-invoice mr-2 text-kebana-blue"></i>
                Pecahan Jenis Fail
            </h3>
            <div style="height: 200px;">
                <canvas id="typeChart"></canvas>
            </div>
        </div>
        <div class="lg:col-span-2 bg-white p-8 border border-slate-350 shadow-sm">
            <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-6 flex items-center">
                <i class="fa-solid fa-chart-line mr-2 text-kebana-blue"></i>
                Trend Muat Naik (6 Bulan)
            </h3>
            <div style="height: 200px;">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Filter & View Control Bar -->
    <div class="flex flex-col xl:flex-row gap-6">
        <div class="flex-1 bg-white p-8 border border-slate-300 shadow-sm">
            <form id="filter-form" onsubmit="event.preventDefault(); fetchResults(1);" class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
                <div>
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-3">Tapis Tag</label>
                    <select name="tag" onchange="fetchResults(1)" class="w-full px-5 py-4 bg-slate-50 border-b-2 border-slate-300 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold uppercase transition-all rounded-none appearance-none">
                        <option value="">Semua Tag</option>
                        <?php foreach ($all_tags as $tag): ?>
                        <option value="<?php echo htmlspecialchars($tag); ?>" <?php echo $filters['tag'] === $tag ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($tag); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-3">Jenis Fail</label>
                    <select name="ext" onchange="fetchResults(1)" class="w-full px-5 py-4 bg-slate-50 border-b-2 border-slate-300 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold uppercase transition-all rounded-none appearance-none">
                        <option value="">Semua Format</option>
                        <option value="pdf" <?php echo $filters['ext'] === 'pdf' ? 'selected' : ''; ?>>PDF Document</option>
                        <option value="docx" <?php echo $filters['ext'] === 'docx' ? 'selected' : ''; ?>>Word Document</option>
                        <option value="xlsx" <?php echo $filters['ext'] === 'xlsx' ? 'selected' : ''; ?>>Excel Spreadsheet</option>
                        <option value="png" <?php echo $filters['ext'] === 'png' ? 'selected' : ''; ?>>Image (PNG/JPG)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-3">Susun Ikut</label>
                    <select name="sort" onchange="fetchResults(1)" class="w-full px-5 py-4 bg-slate-50 border-b-2 border-slate-300 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold uppercase transition-all rounded-none appearance-none">
                        <option value="newest" <?php echo $filters['sort'] === 'newest' ? 'selected' : ''; ?>>Terbaru</option>
                        <option value="popular" <?php echo $filters['sort'] === 'popular' ? 'selected' : ''; ?>>Paling Popular</option>
                        <option value="size" <?php echo $filters['sort'] === 'size' ? 'selected' : ''; ?>>Saiz Terbesar</option>
                        <option value="name" <?php echo $filters['sort'] === 'name' ? 'selected' : ''; ?>>Nama (A-Z)</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <div class="flex-1">
                        <input type="text" name="search" id="search-input" oninput="debounceSearch()" value="<?php echo htmlspecialchars($filters['search']); ?>" placeholder="Cari fail..."
                               class="w-full px-5 py-4 bg-slate-50 border-b-2 border-slate-300 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold uppercase transition-all">
                    </div>
                    <button type="submit" class="bg-kebana-dark text-white px-6 py-4 text-xs font-black uppercase tracking-widest hover:bg-black transition-all shadow-md">
                        CARI
                    </button>
                </div>
            </form>
        </div>
        
        <div class="bg-white p-8 border border-slate-300 shadow-sm flex items-center justify-center gap-4">
            <button onclick="setViewMode('grid')" id="btn-grid" class="w-12 h-12 flex items-center justify-center border-2 border-kebana-blue bg-kebana-blue text-white transition-all">
                <i class="fa-solid fa-grip"></i>
            </button>
            <button onclick="setViewMode('list')" id="btn-list" class="w-12 h-12 flex items-center justify-center border-2 border-slate-300 text-slate-500 hover:border-kebana-blue hover:text-kebana-blue transition-all">
                <i class="fa-solid fa-list-ul"></i>
            </button>
        </div>
    </div>
    <div id="live-search-results" class="space-y-12">
        <?php include __DIR__ . '/_documents_list.php'; ?>
    </div>
</div>

<script>
function toggleManagementView() {
    const view = document.getElementById('management-view');
    view.classList.toggle('hidden');
    
    if (!view.classList.contains('hidden')) {
        renderCharts();
    }
}

function setViewMode(mode) {
    const grid = document.getElementById('grid-view');
    const list = document.getElementById('list-view');
    const btnGrid = document.getElementById('btn-grid');
    const btnList = document.getElementById('btn-list');
    
    if (mode === 'grid') {
        grid.classList.remove('hidden');
        list.classList.add('hidden');
        btnGrid.classList.add('bg-kebana-blue', 'text-white');
        btnGrid.classList.remove('border-slate-100', 'text-slate-300');
        btnList.classList.add('border-slate-100', 'text-slate-300');
        btnList.classList.remove('bg-kebana-blue', 'text-white');
    } else {
        grid.classList.add('hidden');
        list.classList.remove('hidden');
        btnList.classList.add('bg-kebana-blue', 'text-white');
        btnList.classList.remove('border-slate-100', 'text-slate-300');
        btnGrid.classList.add('border-slate-100', 'text-slate-300');
        btnGrid.classList.remove('bg-kebana-blue', 'text-white');
    }
    
    localStorage.setItem('archive_view_mode', mode);
}

// Load preference
const savedMode = localStorage.getItem('archive_view_mode') || 'grid';
setViewMode(savedMode);

let typeChart = null;
let trendChart = null;

function renderCharts() {
    if (typeChart) return;
    
    // Type Distribution Chart
    const ctxType = document.getElementById('typeChart').getContext('2d');
    const typeData = <?php echo json_encode($distribution); ?>;
    
    typeChart = new Chart(ctxType, {
        type: 'doughnut',
        data: {
            labels: typeData.map(d => d.ext.toUpperCase()),
            datasets: [{
                data: typeData.map(d => d.count),
                backgroundColor: ['#003366', '#FFCC00', '#1e293b', '#94a3b8', '#ef4444', '#22c55e'],
                borderWidth: 0,
                spacing: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'rectRounded',
                        padding: 20,
                        font: { size: 9, weight: '900', family: 'Inter' }
                    }
                }
            },
            cutout: '70%'
        }
    });

    // Monthly Trend Chart
    const ctxTrend = document.getElementById('trendChart').getContext('2d');
    const trendData = <?php echo json_encode($trend); ?>;
    
    trendChart = new Chart(ctxTrend, {
        type: 'line',
        data: {
            labels: trendData.map(d => d.month_year),
            datasets: [{
                label: 'Muat Naik',
                data: trendData.map(d => d.count),
                borderColor: '#003366',
                backgroundColor: 'rgba(0, 51, 102, 0.05)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#FFCC00',
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: { size: 9, weight: '700' }
                    },
                    grid: { display: false }
                },
                x: {
                    ticks: {
                        font: { size: 9, weight: '700' }
                    },
                    grid: { display: false }
                }
            }
        }
    });
}

async function performAISearch() {
    const queryInput = document.getElementById('ai-query');
    const query = queryInput.value.trim();
    if (!query) return;

    const loading = document.getElementById('ai-loading');
    const results = document.getElementById('ai-response-area');
    const searchBtn = document.getElementById('ai-search-btn');

    loading.classList.remove('hidden');
    results.classList.add('hidden');
    searchBtn.disabled = true;
    searchBtn.classList.add('opacity-50');

    try {
        const response = await fetch('<?= URL_ROOT ?>/modules/documents/ajax_rag_search.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ query })
        });
        const data = await response.json();

        if (data.success) {
            document.getElementById('ai-answer').innerHTML = data.answer.replace(/\n/g, '<br>');
            document.getElementById('ai-time').textContent = `PROSES: ${data.time}ms`;

            const sourcesContainer = document.getElementById('ai-sources');
            sourcesContainer.innerHTML = '';

            data.sources.forEach(source => {
                const score = Math.round(source.score * 100);
                const ext = source.file_path.split('.').pop().toLowerCase();
                const searchParam = ext === 'pdf' ? `#search="${encodeURIComponent(source.chunk_text.substring(0, 40))}"` : '';
                const fullUrl = `<?= URL_ROOT ?>/${source.file_path}${searchParam}`;

                const card = document.createElement('div');
                card.className = "bg-white/10 border border-white/20 p-5 hover:bg-white/15 transition-all cursor-pointer group shadow-md";
                card.onclick = () => openContextModal(source.doc_name, source.chunk_text, fullUrl);
                
                card.innerHTML = `
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-[10px] font-black text-indigo-300 uppercase tracking-widest">Relevansi: ${score}%</span>
                        <i class="fa-solid fa-file-${ext === 'pdf' ? 'pdf' : 'lines'} text-sm text-indigo-200"></i>
                    </div>
                    <p class="text-xs font-black text-white uppercase truncate mb-2">${source.doc_name}</p>
                    <p class="text-[11px] text-indigo-150 line-clamp-2 opacity-75 group-hover:opacity-100 transition-opacity italic">"${source.chunk_text.substring(0, 80)}..."</p>
                    <div class="mt-3 flex gap-2">
                        <span class="text-[10px] font-black text-indigo-300 uppercase group-hover:text-white transition-colors">Lihat Konteks <i class="fa-solid fa-arrow-right ml-1"></i></span>
                    </div>
                `;
                sourcesContainer.appendChild(card);
            });

            results.classList.remove('hidden');
        } else {
            alert("Ralat: " + data.message);
        }
    } catch (error) {
        console.error("AI Search Error:", error);
        alert("Ralat sistem semasa memproses carian AI.");
    } finally {
        loading.classList.add('hidden');
        searchBtn.disabled = false;
        searchBtn.classList.remove('opacity-50');
    }
}

function openContextModal(title, text, url) {
    document.getElementById('context-title').textContent = title;
    document.getElementById('context-body').innerHTML = `"${text}"`;
    document.getElementById('context-link').href = url;
    document.getElementById('context-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden'; // Prevent scroll
}

function closeContextModal() {
    document.getElementById('context-modal').classList.add('hidden');
    document.body.style.overflow = '';
}

function toggleAISearch() {
    const panel = document.getElementById('ai-search-panel');
    panel.classList.toggle('hidden');
    if (!panel.classList.contains('hidden')) {
        document.getElementById('ai-query').focus();
    }
}

async function reindexAll() {
    if (!confirm('Adakah anda ingin mengindeks semula semua dokumen dalam arkib? Ini mungkin mengambil masa beberapa minit.')) return;

    const btn = document.getElementById('reindex-btn');
    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<p class="text-[10px] font-black text-kebana-blue uppercase italic flex items-center animate-pulse"><i class="fa-solid fa-spinner fa-spin mr-2"></i> MEMPROSES...</p>`;

    try {
        const response = await fetch('<?= URL_ROOT ?>/modules/documents/ajax_reindex.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ all: true })
        });
        const data = await response.json();
        alert(data.message);
        location.reload();
    } catch (error) {
        console.error("Re-index Error:", error);
        alert("Ralat sistem semasa mengindeks semula.");
        btn.innerHTML = originalContent;
        btn.disabled = false;
    }
}

let debounceTimeout = null;

function debounceSearch() {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        fetchResults(1);
    }, 300);
}

async function fetchResults(page = 1) {
    const resultsContainer = document.getElementById('live-search-results');
    resultsContainer.style.opacity = '0.5';
    resultsContainer.style.pointerEvents = 'none';

    const form = document.getElementById('filter-form');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    params.set('page', page);

    try {
        const response = await fetch(`<?= URL_ROOT ?>/modules/documents/ajax_search.php?${params.toString()}`);
        if (!response.ok) throw new Error('HTTP error ' + response.status);
        const html = await response.text();
        resultsContainer.innerHTML = html;
    } catch (error) {
        console.error('Error fetching search results:', error);
        resultsContainer.innerHTML = `<div class="py-20 text-center bg-white border border-slate-300 text-red-600 font-black uppercase tracking-widest">Ralat semasa memuatkan data. Sila cuba lagi.</div>`;
    } finally {
        resultsContainer.style.opacity = '1';
        resultsContainer.style.pointerEvents = 'auto';
    }
}
</script>

<style>
.animate-in {
    animation: animate-in 0.3s ease-out;
}
@keyframes animate-in {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>
