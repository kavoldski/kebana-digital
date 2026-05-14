<?php
require_once 'bootstrap.php';
$db = \App\Core\Database::getInstance()->getConnection();
$res = $db->query("SELECT user_id, username FROM tbl_user LIMIT 1");
if ($row = $res->fetch_assoc()) {
    echo "Found user: " . $row['username'] . " (ID: " . $row['user_id'] . ")\n";
} else {
    echo "No users found.\n";
}
?>
