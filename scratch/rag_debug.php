<?php
require_once __DIR__ . '/../bootstrap.php';
$db = App\Core\Database::getInstance()->getConnection();

// Show tbl_document_chunks schema
$res = $db->query('DESCRIBE tbl_document_chunks');
echo 'tbl_document_chunks schema:' . PHP_EOL;
while ($row = $res->fetch_assoc()) {
    echo '  ' . $row['Field'] . ' - ' . $row['Type'] . PHP_EOL;
}

// Count total chunks
$cnt = $db->query('SELECT COUNT(*) as c, COUNT(DISTINCT doc_id) as docs FROM tbl_document_chunks')->fetch_assoc();
echo PHP_EOL . 'Total chunks: ' . $cnt['c'] . PHP_EOL;
echo 'Indexed documents: ' . $cnt['docs'] . PHP_EOL;

// Avg chunks per doc
$avg = $db->query('SELECT doc_id, COUNT(*) as chunk_count FROM tbl_document_chunks GROUP BY doc_id ORDER BY chunk_count DESC LIMIT 5');
echo PHP_EOL . 'Top 5 docs by chunk count:' . PHP_EOL;
while ($r = $avg->fetch_assoc()) {
    echo '  doc_id=' . $r['doc_id'] . ' => ' . $r['chunk_count'] . ' chunks' . PHP_EOL;
}

// Check if doc_tags and doc_description are in the chunk table or only document table
echo PHP_EOL . 'Sample chunk_text (first 3 chunks):' . PHP_EOL;
$sample = $db->query('SELECT doc_id, chunk_index, LEFT(chunk_text, 200) as preview, LENGTH(embedding) as emb_len FROM tbl_document_chunks LIMIT 3');
while ($r = $sample->fetch_assoc()) {
    echo '  doc_id=' . $r['doc_id'] . ' chunk=' . $r['chunk_index'] . ' emb_len=' . $r['emb_len'] . PHP_EOL;
    echo '  preview: ' . $r['preview'] . PHP_EOL . PHP_EOL;
}

// Check total documents vs indexed
$docs = $db->query('SELECT COUNT(*) as total FROM tbl_document')->fetch_assoc();
echo 'Total documents in archive: ' . $docs['total'] . PHP_EOL;

// Check doc names and their chunk counts
echo PHP_EOL . 'Document name + chunk info:' . PHP_EOL;
$docInfo = $db->query('
    SELECT d.doc_id, d.doc_name, d.doc_tags, COUNT(c.chunk_id) as chunks
    FROM tbl_document d
    LEFT JOIN tbl_document_chunks c ON d.doc_id = c.doc_id
    GROUP BY d.doc_id
    ORDER BY chunks DESC
    LIMIT 10
');
while ($r = $docInfo->fetch_assoc()) {
    echo '  [' . $r['doc_id'] . '] ' . $r['doc_name'] . ' (' . $r['chunks'] . ' chunks) tags=' . $r['doc_tags'] . PHP_EOL;
}
