<?php
require_once 'app/Core/Database.php';

$db = \App\Core\Database::getInstance()->getConnection();

echo "Repairing tbl_event data...\n";

// 1. Fix status '0' to 'Draft'
$res1 = $db->query("UPDATE tbl_event SET status = 'Draft' WHERE status = '0'");
echo "Fixed status '0' to 'Draft' in " . $db->affected_rows . " rows.\n";

// 2. Fix empty event_level to 'MASTER' (where it was supposed to be Master)
$res2 = $db->query("UPDATE tbl_event SET event_level = 'MASTER' WHERE (event_level = '' OR event_level = '0' OR event_level IS NULL) AND parent_event_id IS NULL");
echo "Fixed empty event_level to 'MASTER' in " . $db->affected_rows . " rows.\n";

// 3. Fix empty event_level to 'SUB' (where it was supposed to be Sub)
$res3 = $db->query("UPDATE tbl_event SET event_level = 'SUB' WHERE (event_level = '' OR event_level = '0' OR event_level IS NULL) AND parent_event_id IS NOT NULL");
echo "Fixed empty event_level to 'SUB' in " . $db->affected_rows . " rows.\n";

echo "Repair complete.\n";
