<?php
require_once 'app/Core/Database.php';

$db = \App\Core\Database::getInstance()->getConnection();
$result = $db->query("SELECT * FROM tbl_event WHERE event_id = 76");
if ($row = $result->fetch_assoc()) {
    print_r($row);
} else {
    echo "Event 76 not found.";
}
