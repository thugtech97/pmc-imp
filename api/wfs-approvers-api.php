<?php

include(__DIR__ . '/config.php');
// Guarded: if output already started, header() warns and Laravel turns that
// warning into an exception that fails the whole request.
if (!headers_sent()) {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Expose-Headers: Content-Length, X-JSON");
    header("Access-Control-Allow-Methods: GET");
    header("Access-Control-Allow-Headers: *");
}

$transid = $data['transid'];
$results = [];

// WFS unreachable: no approvers to show (sqlsrv_query(false, ...) would throw).
if (!$conn) {
    return $results;
}

// By the MRS's own id when given: its number can be held in WFS by another MRS
// (or its own transaction can sit under an older number).
if (!empty($data['refno'])) {
    $transid_res = sqlsrv_fetch_array(sqlsrv_query($conn, "SELECT TOP 1 id FROM transactions WHERE ref_req_no = ? AND transid LIKE 'MRS%' ORDER BY id DESC", [(string) $data['refno']]), SQLSRV_FETCH_ASSOC);
} else {
    $transid_res = sqlsrv_fetch_array(sqlsrv_query($conn, "SELECT TOP 1 id FROM transactions WHERE transid LIKE '%" . $transid . "%'"), SQLSRV_FETCH_ASSOC);
}

if (isset($data['token']) && !empty($transid_res['id'])) {
    $sql = "
        SELECT a.*, u.name AS approver_name, u.designation
        FROM approval_status a
        LEFT JOIN users u ON a.approver_id = u.id
        WHERE a.transaction_id = " . $transid_res['id'] . "
        ORDER BY sequence_number";

    $stmt = sqlsrv_query($conn, $sql);
    if ($stmt !== false) {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $results[] = $row;
        }
    }
}

return $results;