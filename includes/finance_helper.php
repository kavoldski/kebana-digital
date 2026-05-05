<?php
/**
 * KEBANA Finance Helper - Minimal
 */
function getFinanceTotals($conn) {
    $stmt = $conn->prepare("
        SELECT 
            COALESCE(SUM(CASE WHEN trans_type = 'Income' THEN amount ELSE 0 END), 0) as total_income,
            COALESCE(SUM(CASE WHEN trans
