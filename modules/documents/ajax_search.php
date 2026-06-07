<?php
/**
 * AJAX Search Endpoint for Digital Archive
 * File: modules/documents/ajax_search.php
 */

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

use App\Helpers\DocumentsHelper;

// Access Control: Admin/Executives/Secretaries/Treasurers
if (!hasRole([888, 1, 2, 3, 11, 22, 4, 33, 6, 55, 7, 66])) {
    header("HTTP/1.1 403 Forbidden");
    echo "<div class='p-12 text-center'><h1 class='text-2xl font-black text-red-600 uppercase tracking-widest'>AKSES DISEKAT</h1></div>";
    exit;
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

// Include the documents listing template
include __DIR__ . '/_documents_list.php';
