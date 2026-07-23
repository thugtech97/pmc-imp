<?php

include(__DIR__ . '/config.php');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Expose-Headers: Content-Length, X-JSON");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: *");


$array_status = array();

// No ids to look up -> skip (avoids an invalid "ref_req_no in ()" query).
if (!isset($ids) || trim((string) $ids) === '') {
    return $array_status;
}

// IMF and MRS BOTH submit to WFS with details = 'IMP' and reuse their own
// table's auto-increment id as ref_req_no, so ref_req_no is NOT unique across
// the two modules. Scope the query by the transid prefix ('IMF' / 'MRS') that
// the caller passes in ($transidLike) so one module never picks up the other's
// rows even when their ref_req_no values collide.
$typeFilter = (isset($transidLike) && $transidLike !== '')
    ? " and t.transid like '%" . $transidLike . "%' "
    : "";

$data_result = sqlsrv_query($conn, "select t.id, t.ref_req_no, t.transid, t.status, a.updated_at, a.updated_last_by, a.updated_last_by_name from transactions as t, approval_status as a where t.ref_req_no in (" . $ids . ") and t.details = 'IMP' " . $typeFilter . " and t.status <> 'Pending' and t.id = a.transaction_id and a.updated_last_by IS NOT NULL");

if($data_result){
    while ($result = sqlsrv_fetch_array($data_result)) {
        $updated_at_string = $result['updated_at']->format('Y-m-d H:i:s');
        $array_status[] = $result['ref_req_no'] . '|' . $result['status'] . '|' . $updated_at_string . '|' . $result['updated_last_by_name']. '|' .$result['transid']. '|' .$result['updated_last_by'];
    }
}

return $array_status;
