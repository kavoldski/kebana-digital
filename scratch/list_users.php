<?php
require_once __DIR__ . '/../bootstrap.php';
$db = App\Core\Database::getInstance()->getConnection();
$res = $db->query("SELECT user_id, username, role, email FROM tbl_user");
$users = [];
while ($row = $res->fetch_assoc()) {
    $users[] = $row;
}
echo json_encode($users);
