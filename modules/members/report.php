<?php
/**
 * KEBANA Management System - Member Reports & Analytics (MYDS Inspired)
 * File: modules/members/report.php
 */

use App\Core\Database;
use App\Helpers\MembersHelper;

require_once APP_ROOT . '/includes/header.php';

$db = Database::getInstance()->getConnection();

// Report access: Secretary and Super Admin
if (!hasRole([4, 33, 888])) {
    echo "<div class='p-12 text-center'><h1 class='text-2xl font-black text-red-600 uppercase tracking-widest'>AKSES DISEKAT</h1><p class='text-xs font-bold text-slate-400 mt-4 uppercase tracking-widest'>Hanya Setiausaha atau Super Admin dibenarkan melihat laporan ini.</p></div>";
    require_once APP_ROOT . '/includes/footer.php';
    exit;
}

// Helper: Extract info from Malaysia IC
function extractICInfo($ic) {
    $ic = preg_replace('/[^0-9]/', '', $ic);
    if (strlen($ic) !== 12) return null;
    
    $year = substr($ic, 0, 2);
    $month = substr($ic, 2, 2);
    $day = substr($ic, 4, 2);
    $last_digit = substr($ic, -1);
    
    $current_year = date('Y');
    $birth_year = ($year > date('y')) ? "19$year" : "20$year";
    $age = $current_year - $birth_year;
    $gender = ($last_digit % 2 === 0) ? 'Wanita' : 'Lelaki';
    
    return ['age' => $age, 'gender' => $gender];
}

// Summary stats
$total_members = MembersHelper::getMemberCount();
$active_members = count(MembersHelper::getMembersByStatus('Active'));
$inactive_members = count(MembersHelper::getMembersByStatus('Inactive'));

// Fetch all members for demographics
$all_members_res = $db->query("SELECT ic_number, village, status FROM tbl_member");
$villages = [];
$genders = ['Lelaki' => 0, 'Wanita' => 0];
$age_groups = ['Belia (18-30)' => 0, 'Dewasa (31-50)' => 0, 'Warga Emas (51+)' => 0, 'Lain-lain' => 0];

while ($m = $all_members_res->fetch_assoc()) {
    // Village counts
    $v = strtoupper($m['village']);
    $villages[$v] = ($villages[$v] ?? 0) + 1;
    
    // IC Info
    $info = extractICInfo($m['ic_number']);
    if ($info) {
        $genders[$info['gender']]++;
        if ($info['age'] >= 18 && $info['age'] <= 30) $age_groups['Belia (18-30)']++;
        elseif ($info['age'] >= 31 && $info['age'] <= 50) $age_groups['Dewasa (31-50)']++;
        elseif ($info['age'] >= 51) $age_groups['Warga Emas (51+)']++;
        else $age_groups['Lain-lain']++;
    }
}
arsort($villages);
$top_villages = array_slice($villages, 0, 5);

// Filters
$filter_status = $_GET['status'] ?? '';
$filter_village = $_GET['village'] ?? '';

// Build query for filtered list
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

// CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="kebana_member_report_' . date('Ymd_His') . '.csv"');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Nama Penuh', 'No. IC', 'Kawasan', 'No. Telefon', 'Status', 'Tarikh Daftar']);
    foreach ($filtered_members as $row) {
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
    exit;
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
            <a href="/kebana-digital/members" class="text-[10px] font-black text-slate-400 hover:text-kebana-blue uppercase tracking-widest flex items-center transition-colors">
                <i class="fa-solid fa-arrow-left mr-3"></i>
                KEMBALI
            </a>
            <a href="?status=<?php echo urlencode($filter_status); ?>&village=<?php echo urlencode($filter_village); ?>&export=csv" class="bg-green-600 text-white px-10 py-4 text-xs font-black uppercase tracking-[0.2em] hover:bg-green-700 transition-all shadow-xl inline-flex items-center">
                <i class="fa-solid fa-file-csv mr-4 text-lg"></i>
                EKSPORT DATA
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
            <p class="text-3xl font-black text-amber-500 mt-2"><?php echo number_format($inactive_members); ?></p>
        </div>
        <div class="p-8 flex flex-col justify-center hover:bg-slate-50 transition-colors border-b-4 border-kebana-yellow">
            <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">KADAR PERTUMBUHAN</p>
            <p class="text-3xl font-black text-kebana-blue mt-2">+12%</p>
        </div>
    </div>

    <!-- Quick Insights -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
        <!-- Gender & Age Distribution -->
        <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-12">
            <div class="bg-white p-8 border border-slate-100 shadow-sm space-y-8">
                <h3 class="text-xs font-black text-kebana-blue uppercase tracking-widest flex items-center">
                    <i class="fa-solid fa-venus-mars mr-4"></i>
                    Demografi Jantina
                </h3>
                <div class="space-y-6">
                    <?php 
                    $total_gendered = $genders['Lelaki'] + $genders['Wanita'];
                    foreach ($genders as $label => $count): 
                        $pct = $total_gendered > 0 ? round(($count / $total_gendered) * 100) : 0;
                    ?>
                    <div>
                        <div class="flex justify-between text-[10px] font-black uppercase tracking-widest mb-3">
                            <span><?php echo $label; ?></span>
                            <span><?php echo $count; ?> Ahli (<?php echo $pct; ?>%)</span>
                        </div>
                        <div class="h-3 bg-slate-50 overflow-hidden">
                            <div class="h-full <?php echo $label === 'Lelaki' ? 'bg-kebana-blue' : 'bg-kebana-yellow'; ?> transition-all duration-1000" style="width: <?php echo $pct; ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="bg-white p-8 border border-slate-100 shadow-sm space-y-8">
                <h3 class="text-xs font-black text-kebana-blue uppercase tracking-widest flex items-center">
                    <i class="fa-solid fa-chart-simple-horizontal mr-4"></i>
                    Taburan Umur
                </h3>
                <div class="space-y-6">
                    <?php 
                    $total_aged = array_sum($age_groups);
                    foreach ($age_groups as $label => $count): 
                        $pct = $total_aged > 0 ? round(($count / $total_aged) * 100) : 0;
                    ?>
                    <div>
                        <div class="flex justify-between text-[10px] font-black uppercase tracking-widest mb-3">
                            <span><?php echo $label; ?></span>
                            <span><?php echo $pct; ?>%</span>
                        </div>
                        <div class="h-3 bg-slate-50 overflow-hidden">
                            <div class="h-full bg-kebana-blue/20 group-hover:bg-kebana-blue transition-all" style="width: <?php echo $pct; ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Top Villages -->
        <div class="bg-kebana-blue p-8 text-white space-y-8 shadow-xl">
            <h3 class="text-xs font-black uppercase tracking-widest flex items-center">
                <i class="fa-solid fa-map-location-dot mr-4 text-kebana-yellow"></i>
                Kawasan Utama
            </h3>
            <div class="space-y-6">
                <?php $rank = 1; foreach ($top_villages as $village => $count): ?>
                <div class="flex items-center justify-between border-b border-white/10 pb-4">
                    <div class="flex items-center gap-4">
                        <span class="text-xs font-black text-kebana-yellow">#<?php echo $rank++; ?></span>
                        <span class="text-[10px] font-black uppercase tracking-widest"><?php echo $village; ?></span>
                    </div>
                    <span class="text-xs font-black"><?php echo $count; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <p class="text-[9px] font-bold text-white/40 uppercase tracking-tighter italic mt-4">
                * Berdasarkan data pendaftaran terkini.
            </p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-8 border border-slate-100 shadow-sm">
        <h3 class="text-xs font-black text-kebana-blue uppercase tracking-widest mb-8 flex items-center">
            <i class="fa-solid fa-filter mr-4"></i>
            Penjana Laporan Dinamik
        </h3>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-8">
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
                    JANA LAPORAN
                </button>
            </div>
        </form>
    </div>

    <!-- Detailed List -->
    <div class="bg-white border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-8 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
            <h3 class="text-xs font-black text-kebana-blue uppercase tracking-widest">Data Terperinci</h3>
            <span class="text-[9px] font-black bg-white text-slate-400 px-4 py-1.5 border border-slate-100 uppercase tracking-widest">
                <?php echo count($filtered_members); ?> Rekod Dijumpai
            </span>
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
                            $info = extractICInfo($m['ic_number']);
                        ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-8 py-5 text-xs font-black text-kebana-blue uppercase"><?php echo htmlspecialchars($m['full_name']); ?></td>
                                <td class="px-8 py-5 text-xs font-bold text-slate-600 italic"><?php echo htmlspecialchars($m['ic_number']); ?></td>
                                <td class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest"><?php echo htmlspecialchars($m['village']); ?></td>
                                <td class="px-8 py-5 text-[9px] font-black text-slate-400 uppercase tracking-widest">
                                    <?php echo $info ? $info['gender'] : 'N/A'; ?>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <a href="/kebana-digital/members/view/<?php echo $m['member_id']; ?>" class="text-[9px] font-black text-kebana-blue uppercase border-b border-kebana-blue pb-0.5 hover:text-kebana-accent transition-colors">Lihat Profil</a>
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
