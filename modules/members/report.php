<?php
/**
 * KEBANA Digital Management System - Member Reports & Analytics (MYDS Inspired)
 * File: modules/members/report.php
 */

use App\Core\Database;
use App\Helpers\MembersHelper;

$db = Database::getInstance()->getConnection();

// CSV export - MUST BE BEFORE ANY HTML OUTPUT
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    // Build query for filtered list (copy logic from below or share it)
    $filter_status = $_GET['status'] ?? '';
    $filter_village = $_GET['village'] ?? '';
    
    $where = "1=1";
    $params = [];
    $types = '';

    if ($filter_status !== '') {
        $where .= " AND status = ?";
        $params[] = $filter_status;
        $types .= 's';
    }
    if ($filter_village !== '') {
        $where .= " AND village LIKE ?";
        $params[] = '%' . $filter_village . '%';
        $types .= 's';
    }

    $list_sql = "SELECT member_id, full_name, ic_number, village, phone_no, status, created_at FROM tbl_member WHERE $where ORDER BY member_id DESC";
    $list_stmt = $db->prepare($list_sql);
    
    if ($list_stmt) {
        if ($types !== '') {
            $list_stmt->bind_param($types, ...$params);
        }
        $list_stmt->execute();
        $list_result = $list_stmt->get_result();
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="kebana_member_report_' . date('Ymd_His') . '.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID', 'Nama Penuh', 'No. IC', 'Kawasan', 'No. Telefon', 'Status', 'Tarikh Daftar']);
        while ($row = $list_result->fetch_assoc()) {
            fputcsv($out, [
                $row['member_id'],
                $row['full_name'],
                $row['ic_number'],
                $row['village'],
                $row['phone_no'],
                $row['status'],
                $row['created_at']
            ]);
        }
        fclose($out);
        $list_stmt->close();
        exit;
    }
}

require_once APP_ROOT . '/includes/header.php';

// Report access: Leadership, Secretary and Super Admin
if (!hasRole([1, 2, 3, 4, 33, 888])) {
    echo "<div class='p-12 text-center'><h1 class='text-2xl font-black text-red-600 uppercase tracking-widest'>AKSES DISEKAT</h1><p class='text-xs font-bold text-slate-400 mt-4 uppercase tracking-widest'>Hanya Kepimpinan, Setiausaha atau Super Admin dibenarkan melihat laporan ini.</p></div>";
    require_once APP_ROOT . '/includes/footer.php';
    exit;
}


// Summary stats
$total_members = MembersHelper::getMemberCount();
$active_members = count(MembersHelper::getMembersByStatus('Active'));
$inactive_members = count(MembersHelper::getMembersByStatus('Inactive'));
$growth_rate = MembersHelper::getGrowthRate();
$growth_data = MembersHelper::getGrowthDataForLast6Months();

// Fetch all members for demographics
$all_members_res = $db->query("SELECT gender, ic_number, village, status FROM tbl_member");
$villages = [];
$genders = ['Lelaki' => 0, 'Wanita' => 0];
$age_groups = ['Belia (18-30)' => 0, 'Dewasa (31-50)' => 0, 'Warga Emas (51+)' => 0, 'Lain-lain' => 0];

while ($m = $all_members_res->fetch_assoc()) {
    // Village counts
    $v = strtoupper($m['village']);
    $villages[$v] = ($villages[$v] ?? 0) + 1;
    
    // Gender logic: Use helper for consistent labeling and fallback
    $gender = MembersHelper::getGenderLabel($m);
    
    // IC Info for Age
    $info = MembersHelper::extractICInfo($m['ic_number']);
    
    if ($gender) {
        $genders[$gender] = ($genders[$gender] ?? 0) + 1;
    }

    if ($info) {
        if ($info['age'] >= 18 && $info['age'] <= 30) $age_groups['Belia (18-30)']++;
        elseif ($info['age'] >= 31 && $info['age'] <= 50) $age_groups['Dewasa (31-50)']++;
        elseif ($info['age'] >= 51) $age_groups['Warga Emas (51+)']++;
        else $age_groups['Lain-lain']++;
    }
}
arsort($villages);
$top_villages = array_slice($villages, 0, 5);

// Filter parameters for UI display
$filter_status = $_GET['status'] ?? '';
$filter_village = $_GET['village'] ?? '';

// Build query for UI list
$where = "1=1";
$params = [];
$types = '';

if ($filter_status !== '') {
    $where .= " AND status = ?";
    $params[] = $filter_status;
    $types .= 's';
}
if ($filter_village !== '') {
    $where .= " AND village LIKE ?";
    $params[] = '%' . $filter_village . '%';
    $types .= 's';
}

$filtered_members = [];
$list_sql = "SELECT member_id, full_name, ic_number, village, phone_no, status, created_at FROM tbl_member WHERE $where ORDER BY member_id DESC";
$list_stmt = $db->prepare($list_sql);
if ($list_stmt) {
    if ($types !== '') {
        $list_stmt->bind_param($types, ...$params);
    }
    $list_stmt->execute();
    $list_result = $list_stmt->get_result();
    while ($m = $list_result->fetch_assoc()) {
        $filtered_members[] = $m;
    }
    $list_stmt->close();
}

$page_title = 'LAPORAN & ANALISIS';
?>

<div class="space-y-12 pb-24">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 border-t-8 border-kebana-blue shadow-sm">
        <div>
            <h2 class="text-2xl font-black text-kebana-blue uppercase tracking-tight italic">Analisis Keahlian</h2>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Data Demografi dan Statistik Komuniti.</p>
        </div>
        <div class="flex gap-4">
            <a href="<?= URL_ROOT ?>/members" class="text-[10px] font-black text-slate-400 hover:text-kebana-blue uppercase tracking-widest flex items-center transition-colors">
                <i class="fa-solid fa-arrow-left mr-3"></i>
                KEMBALI
            </a>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-0 border border-slate-100 bg-white">
        <div class="p-8 border-r border-slate-50 flex flex-col justify-center hover:bg-slate-50 transition-colors">
            <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">JUMLAH KESELURUHAN</p>
            <p class="text-3xl font-black text-kebana-blue mt-2"><?php echo number_format($total_members); ?></p>
        </div>
        <div class="p-8 border-r border-slate-50 flex flex-col justify-center hover:bg-slate-50 transition-colors">
            <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">AHLI AKTIF</p>
            <p class="text-3xl font-black text-green-600 mt-2"><?php echo number_format($active_members); ?></p>
        </div>
        <div class="p-8 border-r border-slate-50 flex flex-col justify-center hover:bg-slate-50 transition-colors">
            <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">AHLI TIDAK AKTIF</p>
            <p class="text-3xl font-black text-slate-400 mt-2"><?php echo number_format($inactive_members); ?></p>
        </div>
        <div class="p-8 flex flex-col justify-center hover:bg-slate-50 transition-colors border-b-4 border-kebana-yellow">
            <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">KADAR PERTUMBUHAN</p>
            <p class="text-3xl font-black text-kebana-blue mt-2"><?php echo ($growth_rate >= 0 ? '+' : '') . $growth_rate; ?>%</p>
        </div>
    </div>

    <!-- Growth Chart -->
    <div class="bg-white p-10 border border-slate-100 shadow-xl relative overflow-hidden group">
        <div class="absolute bottom-0 right-0 p-10 opacity-5 group-hover:opacity-10 transition-opacity translate-y-4 translate-x-4">
            <i class="fa-solid fa-arrow-trend-up text-9xl"></i>
        </div>
        <div class="relative z-10 space-y-8">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-black text-kebana-blue uppercase tracking-[0.3em] flex items-center">
                    <span class="w-8 h-1 bg-kebana-yellow mr-4"></span>
                    Trend Pertumbuhan Keahlian (6 Bulan)
                </h3>
                <div class="flex items-center gap-4 text-[10px] font-black text-slate-300 uppercase tracking-widest">
                    <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-kebana-blue"></span> JUMLAH AHLI</span>
                </div>
            </div>
            <div class="h-[350px]">
                <canvas id="growthChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Quick Insights -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
        <!-- Gender Distribution -->
        <div class="bg-white p-10 border border-slate-100 shadow-xl space-y-10">
            <h3 class="text-xs font-black text-kebana-blue uppercase tracking-[0.3em] flex items-center">
                <i class="fa-solid fa-venus-mars mr-4 text-kebana-yellow"></i>
                Demografi Jantina
            </h3>
            <div class="relative h-64 flex items-center justify-center">
                <canvas id="genderChart"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <span class="text-2xl font-black text-kebana-blue"><?php echo $total_members; ?></span>
                    <span class="text-[8px] font-black text-slate-300 uppercase tracking-widest">JUMLAH</span>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <?php foreach ($genders as $label => $count): 
                    $pct = $total_members > 0 ? round(($count / $total_members) * 100) : 0;
                ?>
                <div class="p-4 bg-slate-50 border-l-4 <?php echo $label === 'Lelaki' ? 'border-kebana-blue' : 'border-kebana-yellow'; ?>">
                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest"><?php echo $label; ?></p>
                    <p class="text-lg font-black text-kebana-blue mt-1"><?php echo $pct; ?>%</p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Age Distribution -->
        <div class="bg-white p-10 border border-slate-100 shadow-xl space-y-10">
            <h3 class="text-xs font-black text-kebana-blue uppercase tracking-[0.3em] flex items-center">
                <i class="fa-solid fa-chart-simple-horizontal mr-4 text-kebana-yellow"></i>
                Taburan Umur
            </h3>
            <div class="h-64">
                <canvas id="ageChart"></canvas>
            </div>
            <div class="space-y-4">
                <?php foreach ($age_groups as $label => $count): 
                    $pct = array_sum($age_groups) > 0 ? round(($count / array_sum($age_groups)) * 100) : 0;
                ?>
                <div class="flex items-center justify-between text-[9px] font-black uppercase tracking-widest pb-3 border-b border-slate-50">
                    <span class="text-slate-400"><?php echo $label; ?></span>
                    <span class="text-kebana-blue"><?php echo $count; ?> AHLI</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Top Villages -->
        <div class="bg-kebana-blue p-10 text-white space-y-10 shadow-2xl relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 opacity-10 rotate-12">
                <i class="fa-solid fa-map-location-dot text-[200px]"></i>
            </div>
            <h3 class="text-xs font-black uppercase tracking-[0.3em] flex items-center relative z-10">
                <i class="fa-solid fa-location-dot mr-4 text-kebana-yellow"></i>
                Kawasan Utama
            </h3>
            <div class="space-y-8 relative z-10">
                <?php $rank = 1; foreach ($top_villages as $village => $count): ?>
                <div class="flex items-center justify-between group">
                    <div class="flex items-center gap-6">
                        <span class="w-8 h-8 flex items-center justify-center bg-white/10 text-[10px] font-black group-hover:bg-kebana-yellow group-hover:text-kebana-blue transition-all">0<?php echo $rank++; ?></span>
                        <span class="text-[10px] font-black uppercase tracking-widest"><?php echo $village; ?></span>
                    </div>
                    <div class="flex flex-col items-end">
                        <span class="text-sm font-black"><?php echo $count; ?></span>
                        <div class="w-12 h-1 bg-white/10 mt-1 overflow-hidden">
                            <div class="h-full bg-kebana-yellow" style="width: <?php echo ($count/max($villages))*100; ?>%"></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-8 border border-slate-100 shadow-sm">
        <h3 class="text-xs font-black text-kebana-blue uppercase tracking-widest mb-8 flex items-center">
            <i class="fa-solid fa-filter mr-4"></i>
            Tapis Data Keahlian
        </h3>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div>
                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3">Status</label>
                <select name="status" class="w-full px-5 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold uppercase transition-all rounded-none appearance-none">
                    <option value="">Semua</option>
                    <option value="Active" <?php echo $filter_status === 'Active' ? 'selected' : ''; ?>>Aktif</option>
                    <option value="Inactive" <?php echo $filter_status === 'Inactive' ? 'selected' : ''; ?>>Tidak Aktif</option>
                </select>
            </div>
            <div>
                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3">Kawasan</label>
                <input type="text" name="village" class="w-full px-5 py-4 bg-slate-50 border-b-2 border-slate-100 focus:border-kebana-blue focus:bg-white outline-none text-xs font-bold uppercase transition-all rounded-none" 
                       placeholder="Cth: Bintulu" value="<?php echo htmlspecialchars($filter_village); ?>">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-kebana-dark text-white py-4 text-xs font-black uppercase tracking-widest hover:bg-black transition-all shadow-lg">
                    TAPIS DATA
                </button>
            </div>
            <div class="flex items-end">
                <a href="<?= URL_ROOT ?>/members/report" class="w-full bg-slate-100 text-slate-400 py-4 text-xs font-black uppercase tracking-widest hover:bg-slate-200 transition-all text-center">
                    KOSONGKAN
                </a>
            </div>
        </form>
    </div>

    <!-- Detailed List -->
    <div class="bg-white border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-8 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
            <h3 class="text-xs font-black text-kebana-blue uppercase tracking-widest">Data Terperinci</h3>
            <div class="flex items-center gap-4">
                <span class="text-[9px] font-black bg-white text-slate-400 px-4 py-1.5 border border-slate-100 uppercase tracking-widest">
                    <?php echo count($filtered_members); ?> Rekod Dijumpai
                </span>
                <a href="?status=<?php echo urlencode($filter_status); ?>&village=<?php echo urlencode($filter_village); ?>&export=csv" 
                   class="bg-green-600 text-white px-6 py-2 text-[10px] font-black uppercase tracking-widest hover:bg-green-700 transition-all shadow-lg flex items-center">
                    <i class="fa-solid fa-file-csv mr-3"></i>
                    EKSPORT (CSV)
                </a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-50">
                        <th class="px-8 py-6 text-[9px] font-black text-slate-300 uppercase tracking-widest">Nama Ahli</th>
                        <th class="px-8 py-6 text-[9px] font-black text-slate-300 uppercase tracking-widest">No. IC</th>
                        <th class="px-8 py-6 text-[9px] font-black text-slate-300 uppercase tracking-widest">Kawasan</th>
                        <th class="px-8 py-6 text-[9px] font-black text-slate-300 uppercase tracking-widest">Jantina</th>
                        <th class="px-8 py-6 text-[9px] font-black text-slate-300 uppercase tracking-widest text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if (empty($filtered_members)): ?>
                        <tr><td colspan="5" class="px-8 py-20 text-center text-[10px] font-black text-slate-200 uppercase tracking-[0.3em]">Tiada Rekod Dijumpai</td></tr>
                    <?php else: ?>
                        <?php foreach ($filtered_members as $m): 
                            $info = MembersHelper::extractICInfo($m['ic_number']);
                        ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-8 py-5 text-xs font-black text-kebana-blue uppercase"><?php echo htmlspecialchars($m['full_name']); ?></td>
                                <td class="px-8 py-5 text-xs font-bold text-slate-600 italic"><?php echo htmlspecialchars($m['ic_number']); ?></td>
                                <td class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest"><?php echo htmlspecialchars($m['village']); ?></td>
                                <td class="px-8 py-5 text-[9px] font-black text-slate-400 uppercase tracking-widest">
                                    <?php echo MembersHelper::getGenderLabel($m); ?>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <a href="<?= URL_ROOT ?>/members/view/<?php echo $m['member_id']; ?>" class="text-[9px] font-black text-kebana-blue uppercase border-b border-kebana-blue pb-0.5 hover:text-kebana-accent transition-colors">Lihat Profil</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once APP_ROOT . '/includes/footer.php'; ?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Shared Chart Options
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        }
    };

    // 1. Membership Growth Chart (Line)
    const ctxGrowth = document.getElementById('growthChart').getContext('2d');
    new Chart(ctxGrowth, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($growth_data, 'label')); ?>,
            datasets: [{
                label: 'Jumlah Ahli',
                data: <?php echo json_encode(array_column($growth_data, 'total')); ?>,
                borderColor: '#003366',
                borderWidth: 4,
                backgroundColor: 'rgba(0, 51, 102, 0.05)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#003366',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8
            }]
        },
        options: {
            ...chartOptions,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: { font: { family: 'Inter', weight: 'bold', size: 10 } }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'Inter', weight: 'bold', size: 10 } }
                }
            }
        }
    });

    // 2. Gender Distribution Chart (Doughnut)
    const ctxGender = document.getElementById('genderChart').getContext('2d');
    new Chart(ctxGender, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_keys($genders)); ?>,
            datasets: [{
                data: <?php echo json_encode(array_values($genders)); ?>,
                backgroundColor: ['#003366', '#FFCC00'],
                borderWidth: 0,
                cutout: '80%'
            }]
        },
        options: chartOptions
    });

    // 3. Age Distribution Chart (Bar)
    const ctxAge = document.getElementById('ageChart').getContext('2d');
    new Chart(ctxAge, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_keys($age_groups)); ?>,
            datasets: [{
                data: <?php echo json_encode(array_values($age_groups)); ?>,
                backgroundColor: 'rgba(0, 51, 102, 0.1)',
                hoverBackgroundColor: '#003366',
                borderRadius: 4
            }]
        },
        options: {
            ...chartOptions,
            scales: {
                y: { display: false },
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'Inter', weight: 'bold', size: 9 } }
                }
            }
        }
    });
});
</script>
