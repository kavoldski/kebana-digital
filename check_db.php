<?php
require_once __DIR__ . '/app/Core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();
$res = $db->query("SHOW COLUMNS FROM tbl_document");
$columns = [];
while ($row = $res->fetch_assoc()) {
    $columns[] = $row['Field'];
}
echo json_encode($columns);
