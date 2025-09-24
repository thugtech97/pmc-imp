<?php

include(__DIR__ . '/config.php');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Expose-Headers: Content-Length, X-JSON");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: *");

$transid = $data['transid'];
$results = [];

$transid_res = sqlsrv_fetch_array(sqlsrv_query($conn, "SELECT TOP 1 id FROM transactions WHERE transid LIKE '%" . $transid . "%'"), SQLSRV_FETCH_ASSOC);

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