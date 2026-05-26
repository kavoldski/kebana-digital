<?php
/**
 * KEBANA Digital Management System - Production Image Diagnostics
 * File: diagnostics.php
 * Place this in the root folder of your Hostinger server (or run locally) to troubleshoot vanishing images.
 */

// Safety lock: change to false if you want to restrict public access later
$isLocked = true;

if ($isLocked) {
    die("Diagnostics is locked. Edit diagnostics.php and set \$isLocked = false to run it.");
}

require_once 'bootstrap.php';
use App\Core\Database;

$db = Database::getInstance()->getConnection();

$uploadDirs = [
    'uploads/' => APP_ROOT . '/uploads/',
    'uploads/announcements/' => APP_ROOT . '/uploads/announcements/',
    'uploads/documents/' => APP_ROOT . '/uploads/documents/',
    'uploads/receipts/' => APP_ROOT . '/uploads/receipts/',
    'uploads/events/' => APP_ROOT . '/uploads/events/'
];

// 1. Check Directory Status
$dirStatus = [];
foreach ($uploadDirs as $relPath => $absPath) {
    $exists = is_dir($absPath);
    $writable = $exists && is_writable($absPath);
    
    // Try to create if not exists
    if (!$exists) {
        @mkdir($absPath, 0755, true);
        $exists = is_dir($absPath);
        $writable = $exists && is_writable($absPath);
    }
    
    $dirStatus[$relPath] = [
        'abs_path' => $absPath,
        'exists' => $exists,
        'writable' => $writable,
        'perms' => $exists ? substr(sprintf('%o', fileperms($absPath)), -4) : 'N/A'
    ];
}

// 2. Fetch Announcement Images from DB
$dbImages = [];
$totalDbImages = 0;
$missingPhysicalImages = [];
$foundPhysicalImages = [];

$sql = "SELECT i.*, a.title as announcement_title 
        FROM tbl_announcement_image i 
        JOIN tbl_announcement a ON i.announcement_id = a.announcement_id 
        ORDER BY i.image_id DESC";
$result = $db->query($sql);

if ($result) {
    $totalDbImages = $result->num_rows;
    while ($row = $result->fetch_assoc()) {
        $fullPath = APP_ROOT . '/' . $row['image_path'];
        $fileExists = file_exists($fullPath);
        
        $imgInfo = [
            'id' => $row['image_id'],
            'announcement_id' => $row['announcement_id'],
            'title' => $row['announcement_title'],
            'db_path' => $row['image_path'],
            'full_path' => $fullPath,
            'file_exists' => $fileExists,
            'web_url' => URL_ROOT . '/' . $row['image_path']
        ];
        
        if ($fileExists) {
            $foundPhysicalImages[] = $imgInfo;
        } else {
            $missingPhysicalImages[] = $imgInfo;
        }
        $dbImages[] = $imgInfo;
    }
}
?>
<!doctype html>
<html lang="ms" class="h-full bg-slate-900 text-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KEBANA — Image Diagnostics Tool</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="min-h-full py-12 px-6 flex flex-col items-center">
    
    <div class="max-w-4xl w-full space-y-8">
        
        <!-- Header -->
        <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 p-8 rounded-3xl flex flex-col md:flex-row md:items-center justify-between gap-6 shadow-xl">
            <div class="flex items-center space-x-4">
                <div class="p-3 bg-[#2B308B] rounded-2xl">
                    <i class="fa-solid fa-stethoscope text-2xl text-yellow-400"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-white uppercase">KEBANA Digital Diagnostics</h1>
                    <p class="text-xs text-slate-400 font-medium mt-1">Sistem Diagnosis Status Fail &amp; Media Hebahan</p>
                </div>
            </div>
            <div class="flex items-center space-x-2 bg-slate-900/60 px-4 py-2 rounded-full border border-slate-700 text-xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="font-bold text-slate-300">Live Server Diagnostic</span>
            </div>
        </div>

        <!-- 1. Environment Info -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-slate-800/60 p-6 rounded-2xl border border-slate-700/30">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Laluan Root Projek (APP_ROOT)</span>
                <p class="text-sm font-bold text-slate-200 mt-2 truncate select-all" title="<?= APP_ROOT ?>"><?= APP_ROOT ?></p>
            </div>
            <div class="bg-slate-800/60 p-6 rounded-2xl border border-slate-700/30">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Pangkal URL (URL_ROOT)</span>
                <p class="text-sm font-bold text-slate-200 mt-2">
                    "<?= URL_ROOT ?>" 
                    <span class="text-[10px] text-slate-500 font-normal">
                        (<?= URL_ROOT === '' ? 'Menggunakan domain utama' : 'Subdirektori' ?>)
                    </span>
                </p>
            </div>
            <div class="bg-slate-800/60 p-6 rounded-2xl border border-slate-700/30">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Hos Pelayan (HTTP_HOST)</span>
                <p class="text-sm font-bold text-slate-200 mt-2"><?= htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'N/A') ?></p>
            </div>
        </div>

        <!-- 2. Write permissions checklist -->
        <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-3xl p-8 shadow-xl space-y-6">
            <div class="flex items-center space-x-2">
                <i class="fa-regular fa-folder-open text-yellow-400 text-lg"></i>
                <h2 class="text-lg font-black uppercase">Pemeriksaan Kebenaran Folder (Write Permissions)</h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($dirStatus as $relPath => $status): ?>
                    <div class="p-5 rounded-2xl flex items-center justify-between <?= $status['writable'] ? 'bg-emerald-950/20 border border-emerald-800/30' : 'bg-rose-950/20 border border-rose-800/30' ?>">
                        <div class="space-y-1 truncate pr-4">
                            <span class="text-xs font-black uppercase text-slate-300"><?= $relPath ?></span>
                            <p class="text-[9px] text-slate-500 truncate" title="<?= $status['abs_path'] ?>"><?= $status['abs_path'] ?></p>
                        </div>
                        <div class="flex items-center space-x-3 flex-shrink-0">
                            <span class="px-2 py-1 text-[9px] font-bold bg-slate-700 rounded text-slate-300">Perms: <?= $status['perms'] ?></span>
                            <?php if ($status['writable']): ?>
                                <span class="w-8 h-8 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-sm" title="Writable">
                                    <i class="fa-solid fa-check"></i>
                                </span>
                            <?php else: ?>
                                <span class="w-8 h-8 rounded-full bg-rose-500/20 text-rose-400 flex items-center justify-center text-sm animate-pulse" title="Not Writable">
                                    <i class="fa-solid fa-xmark"></i>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php 
            $anyWritableIssue = false;
            foreach ($dirStatus as $s) {
                if (!$s['writable']) $anyWritableIssue = true;
            }
            if ($anyWritableIssue): 
            ?>
                <div class="p-4 bg-rose-950/40 border-l-4 border-rose-500 rounded-r-xl text-rose-200 text-xs font-medium space-y-1">
                    <p class="font-black uppercase text-rose-300"><i class="fa-solid fa-triangle-exclamation mr-2 text-rose-400"></i> Amaran Kebenaran Fail!</p>
                    <p>Sesetengah folder media tidak wujud atau tidak boleh ditulis oleh pelayan. Sila cipta folder tersebut secara manual melalui File Manager Hostinger anda, dan tetapkan kebenaran fail (chmod) kepada <code class="bg-rose-900/60 px-1.5 py-0.5 rounded font-mono text-white">0755</code>.</p>
                </div>
            <?php else: ?>
                <div class="p-4 bg-emerald-950/40 border-l-4 border-emerald-500 rounded-r-xl text-emerald-200 text-xs font-medium">
                    <p class="font-bold"><i class="fa-solid fa-check-double mr-2 text-emerald-400"></i> Semua folder media sedia ada dan mempunyai kebenaran menulis (writable) yang baik!</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- 3. Database Sync & Physical Files status -->
        <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-3xl p-8 shadow-xl space-y-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <i class="fa-regular fa-image text-yellow-400 text-lg"></i>
                    <h2 class="text-lg font-black uppercase">Analisis Gambar Hebahan di Pangkalan Data</h2>
                </div>
                <div class="text-xs bg-slate-900 px-4 py-1.5 rounded-full text-slate-300 font-bold border border-slate-700">
                    Jumlah Rekod: <?= $totalDbImages ?>
                </div>
            </div>

            <!-- Stats grid -->
            <div class="grid grid-cols-2 gap-4">
                <div class="p-5 bg-emerald-950/20 border border-emerald-800/20 rounded-2xl text-center">
                    <span class="text-3xl font-black text-emerald-400 block"><?= count($foundPhysicalImages) ?></span>
                    <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest mt-1 block">Fail Fizikal Dijumpai</span>
                </div>
                <div class="p-5 bg-rose-950/20 border border-rose-800/20 rounded-2xl text-center">
                    <span class="text-3xl font-black text-rose-400 block"><?= count($missingPhysicalImages) ?></span>
                    <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest mt-1 block">Fail Hilang / Tiada</span>
                </div>
            </div>

            <?php if (!empty($missingPhysicalImages)): ?>
                <div class="space-y-4">
                    <h3 class="text-xs font-black uppercase tracking-wider text-rose-300 flex items-center">
                        <i class="fa-solid fa-circle-exclamation mr-2 text-rose-400"></i>
                        Senarai Gambar yang Rujukannya ada di Pangkalan Data, tetapi Fail Fizikal Tiada:
                    </h3>
                    
                    <div class="max-h-[300px] overflow-y-auto space-y-2.5 pr-2 border border-slate-700/50 p-4 bg-slate-900/40 rounded-2xl">
                        <?php foreach ($missingPhysicalImages as $img): ?>
                            <div class="p-3 bg-slate-800/50 rounded-xl border border-slate-700/30 flex flex-col sm:flex-row sm:items-center justify-between text-xs gap-3">
                                <div class="space-y-1">
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-tight block">Hebahan: <?= htmlspecialchars($img['title']) ?></span>
                                    <code class="font-mono text-rose-300 select-all block break-all"><?= $img['db_path'] ?></code>
                                </div>
                                <span class="px-3 py-1 bg-rose-500/20 text-rose-400 rounded-full text-[9px] font-black uppercase tracking-widest flex-shrink-0 self-start sm:self-center">
                                    <i class="fa-solid fa-xmark mr-1"></i> Fail Hilang
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="p-5 bg-amber-950/30 border-l-4 border-amber-500 rounded-r-xl text-amber-200 text-xs font-medium space-y-2">
                        <p class="font-black uppercase text-amber-300 flex items-center">
                            <i class="fa-solid fa-lightbulb mr-2 text-amber-400"></i>
                            Bagaimanakah cara menyelesaikan masalah fail hilang ini?
                        </p>
                        <p class="leading-relaxed">
                            Punca utama perkara ini berlaku ialah database disinkronkan dari localhost, tetapi folder media diabaikan oleh Git (<code class="bg-amber-900/60 px-1 py-0.5 rounded font-mono text-white">.gitignore</code>). Sila lakukan salah satu daripada berikut:
                        </p>
                        <ul class="list-disc pl-5 space-y-1.5">
                            <li><strong>Cara FTP/File Manager:</strong> Muat naik/salin kandungan folder <code class="bg-amber-900/60 px-1 py-0.5 rounded font-mono text-white">uploads/announcements/</code> dari komputer tempatan (XAMPP) anda ke dalam folder yang sama di Hostinger secara manual.</li>
                            <li><strong>Cara Cipta Semula:</strong> Padam hebahan tersebut melalui panel admin, dan cipta semula hebahan tersebut secara langsung pada pelayan Hostinger yang aktif agar fail dimuat naik terus ke sana.</li>
                        </ul>
                    </div>
                <?php else: ?>
                    <div class="p-4 bg-emerald-950/40 border-l-4 border-emerald-500 rounded-r-xl text-emerald-200 text-xs font-medium">
                        <p class="font-bold"><i class="fa-solid fa-check-double mr-2 text-emerald-400"></i> Tiada ketidakpadanan fail fizikal dikesan! Semua gambar hebahan yang didaftarkan dalam pangkalan data wujud di pelayan.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Diagnostics Actionable Summary -->
            <div class="bg-slate-800/80 backdrop-blur border border-slate-700/50 rounded-3xl p-8 shadow-xl space-y-6">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-circle-question text-yellow-400 text-lg"></i>
                    <h2 class="text-lg font-black uppercase">Panduan &amp; Cara Mengatasi Masalah Gambar Hilang</h2>
                </div>

                <div class="prose prose-invert max-w-none text-xs leading-relaxed text-slate-300 space-y-4">
                    <p>
                        Jika gambar anda <strong>masih tidak kelihatan</strong> walaupun failnya wujud, berikut adalah perkara teknikal lain yang perlu diperiksa:
                    </p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                        <div class="bg-slate-900/50 p-6 rounded-2xl border border-slate-700/30 space-y-2">
                            <h4 class="font-black text-white uppercase tracking-wider text-[10px] text-yellow-400">1. Kesan Cache Opcache Pelayan</h4>
                            <p>Pelayan web Hostinger anda mungkin menyimpan cache PHP (Opcache). Sila cuba layari semula portal dengan menambah parameter debug <code class="bg-slate-800 px-1 py-0.5 rounded font-mono text-white">?debug=kebana_debug</code> di hujung URL.</p>
                            <a href="<?= URL_ROOT ?>/portal?debug=kebana_debug" class="inline-block mt-2 text-yellow-400 hover:underline font-bold">Tekan Sini untuk Layar dengan Debug &rarr;</a>
                        </div>
                        
                        <div class="bg-slate-900/50 p-6 rounded-2xl border border-slate-700/30 space-y-2">
                            <h4 class="font-black text-white uppercase tracking-wider text-[10px] text-yellow-400">2. Kehilangan Folder uploads/ dalam Git</h4>
                            <p>Fail <code class="bg-slate-800 px-1 py-0.5 rounded font-mono text-white">.gitignore</code> mengabaikan folder <code class="bg-slate-800 px-1 py-0.5 rounded font-mono text-white">uploads/</code>. Ini bermakna jika anda memuat naik kod melalui Git, folder media di pelayan tidak akan dikemaskini. Muat naik fail secara manual sentiasa disyorkan untuk folder media.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center text-[10px] font-bold text-slate-500 uppercase tracking-widest py-8">
                &copy; <?= date('Y') ?> KEBANA Digital Management System &bull; Diagnosis Utiliti
            </div>
            
        </div>

    </body>
    </html>
