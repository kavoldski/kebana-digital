<?php
require_once 'bootstrap.php';
$db = \App\Core\Database::getInstance()->getConnection();
$result = $db->query("SHOW COLUMNS FROM tbl_event");
$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
}
echo "Columns in tbl_event: " . implode(", ", $columns) . "\n";
?>
