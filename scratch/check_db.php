<?php
require_once 'includes/dbconnect.php';
$result = $conn->query("SHOW COLUMNS FROM tbl_transaction");
while($row = $result->fetch_assoc()) {
    print_r($row);
}
$result = $conn->query("SHOW COLUMNS FROM tbl_user");
while($row = $result->fetch_assoc()) {
    print_r($row);
}
?>
