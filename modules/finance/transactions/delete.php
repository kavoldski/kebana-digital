<?php
/**
 * KEBANA Management System - Delete Transaction
 * File: modules/finance/transactions/delete.php
 */

use App\Helpers\FinanceHelper;

require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/dbconnect.php';

$transId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$current_role = (int)($_SESSION['role'] ?? 0);
$current_cawangan_id = isset($_SESSION['cawangan_id']) ? (int)$_SESSION['cawangan_id'] : null;

// Only Treasurers and Admins can delete
$CAWANGAN_ROLES = [11, 22, 33, 44, 55, 66];
if (!in_array($current_role, [888, 6, 55])) {
    header("Location: /kebana-digital/finance/transactions/list?msg=denied");
    exit;
}

// Scoping
$scope_cawangan = in_array($current_role, $CAWANGAN_ROLES) ? $current_cawangan_id : null;

if (FinanceHelper::deleteTransaction($transId, $scope_cawangan)) {
    header("Location: /kebana-digital/finance/transactions/list?msg=deleted");
} else {
    header("Location: /kebana-digital/finance/transactions/list?msg=error");
}
exit;
