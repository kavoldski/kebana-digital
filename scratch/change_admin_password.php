<?php
require_once __DIR__ . '/../bootstrap.php';
$db = App\Core\Database::getInstance()->getConnection();
$new_hash = password_hash('password123', PASSWORD_BCRYPT);
$db->query("UPDATE tbl_user SET password_hash = '{$new_hash}' WHERE username = 'su.pusat' OR username = 'kavoldski'");
echo "Passwords updated successfully!\n";
