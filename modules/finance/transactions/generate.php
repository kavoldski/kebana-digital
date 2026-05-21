<?php
/**
 * KEBANA Digital Management System - Report Generator
 * File: modules/finance/transactions/generate.php
 */
require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/dbconnect.php';

use App\Core\Database;

// Security check: Only allow high-level roles: Super Admin (888), Presiden (1), Timbalan 1 (2), Timbalan 2 (3), Bendahari Pusat (6), Bendahari Cawangan (55)
if (!hasRole([888, 1, 2, 3, 6, 55])) {
    die("Akses Disekat: Anda tidak mempunyai kebenaran untuk menjana laporan.");
}

$db = Database::getInstance()->getConnection();

$period_type = $_GET['period_type'] ?? '';
$date_start = $_GET['date_start'] ?? '';
$date_end = $_GET['date_end'] ?? '';
$date_value = $_GET['date_value'] ?? '';
$format = $_GET['format'] ?? 'pdf';

// Fallback to legacy date_value if start/end parameters are empty
if (empty($date_start) && !empty($date_value)) {
    $date_start = $date_value;
}
if (empty($date_end) && !empty($date_value)) {
    $date_end = $date_value;
}

if (empty($period_type) || empty($date_start) || empty($date_end)) {
    die("Parameter tidak sah.");
}

// Scoping cawangan
$current_role = (int)$_SESSION['role'];
$current_cawangan_id = isset($_SESSION['cawangan_id']) && $_SESSION['cawangan_id'] !== null && $_SESSION['cawangan_id'] !== '' ? (int)$_SESSION['cawangan_id'] : null;

$CAWANGAN_ROLES = [11, 22, 33, 44, 55, 66];

$where = "1=1";
$params = [];
$types = "";

if (in_array($current_role, $CAWANGAN_ROLES, true) && $current_cawangan_id !== null) {
    $where .= " AND COALESCE(e.cawangan_id, u.cawangan_id) = ?";
    $params[] = $current_cawangan_id;
    $types .= "i";
}

// Set up dates and Title Period label based on Harian, Bulanan, Tahunan
$title_period = '';
$file_period = '';

$months_ms = [
    1 => 'JANUARI', 2 => 'FEBRUARI', 3 => 'MAC', 4 => 'APRIL', 5 => 'MEI', 6 => 'JUN',
    7 => 'JULAI', 8 => 'OGOS', 9 => 'SEPTEMBER', 10 => 'OKTOBER', 11 => 'NOVEMBER', 12 => 'DISEMBER'
];

if ($period_type === 'daily') {
    // Chronological validation
    if ($date_start > $date_end) {
        die("Parameter tidak sah: Tarikh mula tidak boleh melebihi tarikh tamat.");
    }
    
    $where .= " AND t.trans_date BETWEEN ? AND ?";
    $params[] = $date_start;
    $params[] = $date_end;
    $types .= "ss";
    
    $d_start = date('d', strtotime($date_start));
    $m_start = (int)date('m', strtotime($date_start));
    $y_start = date('Y', strtotime($date_start));
    $month_start_name = $months_ms[$m_start] ?? strtoupper(date('F', strtotime($date_start)));
    
    if ($date_start === $date_end) {
        $title_period = "HARIAN ($d_start $month_start_name $y_start)";
        $file_period = "HARIAN_" . date('Ymd', strtotime($date_start));
    } else {
        $d_end = date('d', strtotime($date_end));
        $m_end = (int)date('m', strtotime($date_end));
        $y_end = date('Y', strtotime($date_end));
        $month_end_name = $months_ms[$m_end] ?? strtoupper(date('F', strtotime($date_end)));
        
        $title_period = "HARIAN ($d_start $month_start_name $y_start - $d_end $month_end_name $y_end)";
        $file_period = "HARIAN_" . date('Ymd', strtotime($date_start)) . "_TO_" . date('Ymd', strtotime($date_end));
    }
} elseif ($period_type === 'monthly') {
    if ($date_start > $date_end) {
        die("Parameter tidak sah: Bulan mula tidak boleh melebihi bulan tamat.");
    }
    
    $parts_start = explode('-', $date_start); // YYYY-MM
    $parts_end = explode('-', $date_end); // YYYY-MM
    
    if (count($parts_start) === 2 && count($parts_end) === 2) {
        $year_start = (int)$parts_start[0];
        $month_start = (int)$parts_start[1];
        
        $year_end = (int)$parts_end[0];
        $month_end = (int)$parts_end[1];
        
        $sql_start_date = $date_start . "-01";
        $sql_end_date = date("Y-m-t", strtotime($date_end . "-01"));
        
        $where .= " AND t.trans_date BETWEEN ? AND ?";
        $params[] = $sql_start_date;
        $params[] = $sql_end_date;
        $types .= "ss";
        
        $month_start_name = $months_ms[$month_start] ?? strtoupper(date('F', mktime(0, 0, 0, $month_start, 10)));
        
        if ($date_start === $date_end) {
            $title_period = "BULANAN ($month_start_name $year_start)";
            $file_period = "BULANAN_" . $year_start . sprintf("%02d", $month_start);
        } else {
            $month_end_name = $months_ms[$month_end] ?? strtoupper(date('F', mktime(0, 0, 0, $month_end, 10)));
            $title_period = "BULANAN ($month_start_name $year_start - $month_end_name $year_end)";
            $file_period = "BULANAN_" . $year_start . sprintf("%02d", $month_start) . "_TO_" . $year_end . sprintf("%02d", $month_end);
        }
    } else {
        die("Format tarikh bulanan tidak sah.");
    }
} elseif ($period_type === 'yearly') {
    if ((int)$date_start > (int)$date_end) {
        die("Parameter tidak sah: Tahun mula tidak boleh melebihi tahun tamat.");
    }
    
    $sql_start_date = $date_start . "-01-01";
    $sql_end_date = $date_end . "-12-31";
    
    $where .= " AND t.trans_date BETWEEN ? AND ?";
    $params[] = $sql_start_date;
    $params[] = $sql_end_date;
    $types .= "ss";
    
    if ($date_start === $date_end) {
        $title_period = "TAHUNAN ($date_start)";
        $file_period = "TAHUNAN_" . $date_start;
    } else {
        $title_period = "TAHUNAN ($date_start - $date_end)";
        $file_period = "TAHUNAN_" . $date_start . "_TO_" . $date_end;
    }
} else {
    die("Jenis tempoh tidak sah.");
}

// Fetch Cawangan Name if branch scoped
$cawangan_name = '';
if (in_array($current_role, $CAWANGAN_ROLES, true) && $current_cawangan_id !== null) {
    $stmt_caw = $db->prepare("SELECT cawangan_name FROM tbl_cawangan WHERE cawangan_id = ?");
    $stmt_caw->bind_param("i", $current_cawangan_id);
    $stmt_caw->execute();
    $res_caw = $stmt_caw->get_result();
    if ($row_caw = $res_caw->fetch_assoc()) {
        $cawangan_name = 'CAWANGAN ' . strtoupper($row_caw['cawangan_name']);
    }
    $stmt_caw->close();
}

// Determine official title
$official_title = "LAPORAN " . $title_period;
if ($cawangan_name !== '') {
    $official_title .= " " . $cawangan_name;
}
$official_title .= " PERSATUAN KENYAH BADENG SARAWAK";

// Query for transactions
$sql = "SELECT t.*, e.event_title, u.username as recorder_name 
        FROM tbl_transaction t 
        LEFT JOIN tbl_event e ON t.event_id = e.event_id 
        LEFT JOIN tbl_user u ON t.recorded_by = u.user_id 
        WHERE $where 
        ORDER BY t.trans_date ASC, t.trans_id ASC";

$stmt = $db->prepare($sql);
$transactions = [];
if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
    $stmt->close();
}

// Totals
$total_income = 0;
$total_expense = 0;
foreach ($transactions as $t) {
    if ($t['trans_type'] === 'Income') {
        $total_income += $t['amount'];
    } else {
        $total_expense += $t['amount'];
    }
}
$total_balance = $total_income - $total_expense;

if ($format === 'csv') {
    // Generate CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="LAPORAN_' . $file_period . ($cawangan_name ? '_' . str_replace(' ', '_', $cawangan_name) : '') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Header Info
    fputcsv($output, [$official_title]);
    fputcsv($output, []);
    
    // Column headers with split IN/OUT
    fputcsv($output, ['Tarikh', 'Kategori', 'Projek / Aktiviti', 'Mod Pembayaran', 'Perekod', 'MASUK (RM)', 'KELUAR (RM)']);
    
    // Data rows
    foreach ($transactions as $t) {
        $is_income = $t['trans_type'] === 'Income';
        fputcsv($output, [
            date('d/m/Y', strtotime($t['trans_date'])),
            $t['category'],
            $t['event_title'] ?? 'Dana Am Persatuan',
            $t['payment_mode'] ?: 'Cash',
            $t['recorder_name'] ?? 'Sistem',
            $is_income ? $t['amount'] : '',
            !$is_income ? $t['amount'] : ''
        ]);
    }
    
    // Blank & Summary
    fputcsv($output, []);
    fputcsv($output, ['', '', '', '', 'Total Pendapatan (IN)', number_format($total_income, 2), '']);
    fputcsv($output, ['', '', '', '', 'Total Perbelanjaan (OUT)', '', number_format($total_expense, 2)]);
    fputcsv($output, ['', '', '', '', 'Baki Bersih (NET)', number_format($total_balance, 2), '']);
    
    fclose($output);
    exit;
} else {
    // Generate PDF using DomPDF
    
    // Convert Logo to base64 for embedding in PDF
    $logo_path = APP_ROOT . '/public/assets/img/kebana-logo-icon.png';
    $logo_base64 = '';
    if (file_exists($logo_path)) {
        $logo_data = file_get_contents($logo_path);
        $logo_base64 = 'data:image/png;base64,' . base64_encode($logo_data);
    }
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>' . htmlspecialchars($official_title) . '</title>
        <style>
            @page {
                margin: 45px 45px 180px 45px;
            }
            body {
                font-family: Arial, sans-serif;
                font-size: 10px;
                color: #333;
                line-height: 1.4;
            }
            .header-container {
                width: 100%;
                border-bottom: 2px solid #003366;
                padding-bottom: 15px;
                margin-bottom: 25px;
            }
            .logo-cell {
                width: 80px;
                vertical-align: middle;
            }
            .logo-img {
                width: 70px;
                height: 70px;
            }
            .title-cell {
                vertical-align: middle;
                padding-left: 15px;
            }
            .title-text {
                font-size: 13px;
                font-weight: bold;
                color: #003366;
                text-transform: uppercase;
                margin: 0;
            }
            .subtitle-text {
                font-size: 8px;
                color: #666;
                text-transform: uppercase;
                margin-top: 5px;
                letter-spacing: 1px;
            }
            .summary-cards {
                width: 100%;
                margin-bottom: 25px;
                border-spacing: 10px 0;
                margin-left: -10px;
            }
            .summary-card {
                background: #f8fafc;
                border-top: 3px solid #64748b;
                padding: 10px 15px;
                width: 33.33%;
                box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            }
            .summary-card.income {
                border-top-color: #16a34a;
            }
            .summary-card.expense {
                border-top-color: #dc2626;
            }
            .summary-card.balance {
                border-top-color: #003366;
            }
            .card-label {
                font-size: 7px;
                text-transform: uppercase;
                color: #64748b;
                font-weight: bold;
                letter-spacing: 0.5px;
            }
            .card-value {
                font-size: 13px;
                font-weight: bold;
                color: #0f172a;
                margin-top: 3px;
            }
            .card-value.income {
                color: #16a34a;
            }
            .card-value.expense {
                color: #dc2626;
            }
            .card-value.balance {
                color: #003366;
            }
            .table-container {
                width: 100%;
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            th {
                background-color: #f8fafc;
                color: #64748b;
                font-size: 7px;
                font-weight: bold;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                padding: 8px 10px;
                border-bottom: 1px solid #e2e8f0;
                text-align: left;
            }
            td {
                padding: 8px 10px;
                border-bottom: 1px solid #f1f5f9;
                font-size: 9px;
            }
            .text-right {
                text-align: right;
            }
            .text-center {
                text-align: center;
            }
            .totals-container {
                position: absolute;
                bottom: -150px;
                left: 0;
                right: 0;
                height: 120px;
                border-top: 2px solid #003366;
                padding-top: 15px;
            }
            .footer-info {
                position: fixed;
                bottom: -170px;
                left: 0;
                right: 0;
                height: 20px;
                border-top: 1px solid #f1f5f9;
                padding-top: 5px;
                font-size: 7px;
                color: #94a3b8;
                text-transform: uppercase;
                text-align: right;
            }
        </style>
    </head>
    <body>
        <!-- Header -->
        <table class="header-container">
            <tr>';
            if ($logo_base64 !== '') {
                $html .= '<td class="logo-cell"><img src="' . $logo_base64 . '" class="logo-img" alt="Logo"></td>';
            }
            $html .= '
                <td class="title-cell">
                    <h1 class="title-text">' . htmlspecialchars($official_title) . '</h1>
                    <p class="subtitle-text">Sistem Pengurusan Digital Persatuan Kenyah Badeng Sarawak</p>
                </td>
            </tr>
        </table>
        
        <!-- Dashboard Widgets -->
        <table class="summary-cards">
            <tr>
                <td class="summary-card balance">
                    <div class="card-label">Baki Dalam Tempoh</div>
                    <div class="card-value balance">RM ' . number_format($total_balance, 2) . '</div>
                </td>
                <td class="summary-card income">
                    <div class="card-label">Total Pendapatan</div>
                    <div class="card-value income">RM ' . number_format($total_income, 2) . '</div>
                </td>
                <td class="summary-card expense">
                    <div class="card-label">Total Perbelanjaan</div>
                    <div class="card-value expense">RM ' . number_format($total_expense, 2) . '</div>
                </td>
            </tr>
        </table>
        
        <!-- Transaction Ledger Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 12%">Tarikh</th>
                        <th style="width: 22%">Kategori</th>
                        <th style="width: 26%">Projek / Aktiviti</th>
                        <th style="width: 10%">Mod</th>
                        <th style="width: 15%" class="text-right">MASUK (RM)</th>
                        <th style="width: 15%" class="text-right">KELUAR (RM)</th>
                    </tr>
                </thead>
                <tbody>';
                if (empty($transactions)) {
                    $html .= '<tr><td colspan="6" class="text-center" style="padding: 30px; color: #94a3b8; font-weight: bold; text-transform: uppercase; font-size: 8px;">Tiada Rekod Transaksi Ditemui</td></tr>';
                } else {
                    foreach ($transactions as $t) {
                        $is_income = $t['trans_type'] === 'Income';
                        $html .= '
                        <tr>
                            <td>' . date('d M Y', strtotime($t['trans_date'])) . '</td>
                            <td style="font-weight: bold; color: #0f172a;">' . htmlspecialchars(strtoupper($t['category'])) . '</td>
                            <td style="color: #64748b; font-style: italic;">' . htmlspecialchars($t['event_title'] ?? 'Dana Am Persatuan') . '</td>
                            <td>' . htmlspecialchars($t['payment_mode'] ?: 'Cash') . '</td>
                            <td class="text-right" style="font-weight: bold; color: #0f172a;">' . ($is_income ? 'RM ' . number_format($t['amount'], 2) : '-') . '</td>
                            <td class="text-right" style="font-weight: bold; color: #0f172a;">' . (!$is_income ? 'RM ' . number_format($t['amount'], 2) : '-') . '</td>
                        </tr>';
                    }
                }
                $html .= '
                </tbody>
            </table>
        </div>
        
        <!-- Bottom-Aligned Totals Block -->
        <div class="totals-container">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 50%; vertical-align: top; padding: 0;">
                        <span style="font-size: 8px; color: #64748b; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">Nota / Pengesahan</span>
                        <p style="font-size: 8px; color: #94a3b8; margin-top: 5px; text-transform: uppercase; line-height: 1.5;">
                            Laporan kewangan ini dijana secara digital.<br>
                            Sebarang pindaan mestilah diluluskan oleh Jawatankuasa Pusat Kewangan.
                        </p>
                    </td>
                    <td style="width: 50%; padding: 0; vertical-align: top;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="padding: 4px 0; font-size: 9px; color: #64748b; font-weight: bold; text-transform: uppercase;">JUMLAH MASUK (TOTAL IN):</td>
                                <td style="padding: 4px 0; font-size: 10px; font-weight: bold; color: #0f172a; text-align: right;">RM ' . number_format($total_income, 2) . '</td>
                            </tr>
                            <tr>
                                <td style="padding: 4px 0; font-size: 9px; color: #64748b; font-weight: bold; text-transform: uppercase;">JUMLAH KELUAR (TOTAL OUT):</td>
                                <td style="padding: 4px 0; font-size: 10px; font-weight: bold; color: #0f172a; text-align: right;">RM ' . number_format($total_expense, 2) . '</td>
                            </tr>
                            <tr style="border-top: 1px solid #e2e8f0;">
                                <td style="padding: 6px 0 0 0; font-size: 9px; font-weight: bold; color: #003366; text-transform: uppercase;">BAKI BERSIH (NET BALANCE):</td>
                                <td style="padding: 6px 0 0 0; font-size: 11px; font-weight: bold; color: #003366; text-align: right;">RM ' . number_format($total_balance, 2) . '</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Page Footer info -->
        <div class="footer-info">
            Dijana secara automatik oleh Kebana Digital pada ' . date('d/m/Y h:i A') . '
        </div>
    </body>
    </html>';

    // Dompdf initialization
    $options = new \Dompdf\Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    
    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    
    // Output the PDF
    $dompdf->stream('LAPORAN_' . $file_period . ($cawangan_name ? '_' . str_replace(' ', '_', $cawangan_name) : '') . '.pdf', ['Attachment' => false]);
    exit;
}
