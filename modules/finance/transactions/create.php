<?php
$page_title = 'Add Transaction';
$css_path = '../../../src/css/dashboard.css';

require_once '../../../includes/header.php';
require_once '../../../includes/dbconnect.php';
require_once '../../../includes/auth.php';

$message = '';
if ($_POST) {
    $trans_type = trim($_POST['trans_type']);
    $amount = (float)$_POST['amount'];
    $category = trim($_POST['category']);
    $trans_date = $_POST['trans_date'];
    
    if ($trans_type && $amount > 0 && $category && $trans_date) {
        $stmt
