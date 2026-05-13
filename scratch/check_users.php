<?php
require 'bootstrap.php';
$db = \App\Core\Database::getInstance()->getConnection();
$res = $db->query("SELECT user_id, username FROM tbl_user");
while($row = $res->fetch_assoc()) {
    echo "ID: " . $row['user_id'] . " - " . $row['username'] . "\n";
}
