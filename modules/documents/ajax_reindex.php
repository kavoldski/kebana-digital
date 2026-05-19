<?php
/**
 * AJAX Re-index Endpoint
 */
require_once __DIR__ . '/../../bootstrap.php';

// Prevent timeout and memory limits for document reindexing
set_time_limit(0);
ini_set('memory_limit', '1024M');

use App\Services\RAGService;
use App\Core\Database;

header('Content-Type: application/json');

// Only Admin (888) or Setiausaha Pusat (4) can re-index
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], [888, 4])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$docId = $input['doc_id'] ?? null;
$reindexAll = $input['all'] ?? false;

try {
    $indexed = 0;
    $failed = 0;

    if ($reindexAll) {
        $db = Database::getInstance()->getConnection();
        $res = $db->query("SELECT doc_id FROM tbl_document");
        while ($row = $res->fetch_assoc()) {
            if (RAGService::indexDocument($row['doc_id'])) {
                $indexed++;
            } else {
                $failed++;
            }
        }
    } elseif ($docId) {
        if (RAGService::indexDocument($docId)) {
            $indexed = 1;
        } else {
            $failed = 1;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'indexed' => $indexed,
        'failed' => $failed,
        'message' => "Proses selesai. $indexed dokumen berjaya diindeks."
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
