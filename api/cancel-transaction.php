<?php

include(__DIR__ . '/config.php');
// Guarded: if output already started, header() warns and Laravel turns that
// warning into an exception that fails the whole request.
if (!headers_sent()) {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Expose-Headers: Content-Length, X-JSON");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Allow-Headers: *");
}

$transaction_type =  $data['type'];
$transid = $data['transid'];

// WFS unreachable: report failure so the caller shows "Unable to cancel"
// instead of sqlsrv_query(false, ...) throwing a server error.
if (!$conn) {
    return false;
}

$data_result = sqlsrv_fetch_array(sqlsrv_query($conn, "select * from allowed_transactions where name = '" . $transaction_type . "' "));

if (isset($data['token'])) {
    if ($data_result['token'] == $data['token']) {
        // By the MRS's own id when given: its number can be held in WFS by another
        // MRS, and cancelling by number would cancel that one instead.
        if (!empty($data['refno'])) {
            $existData = sqlsrv_fetch_array(sqlsrv_query($conn, "select TOP 1 * from transactions where ref_req_no = ? and transid like 'MRS%' order by id desc", [(string) $data['refno']]));
        } else {
            $existData = sqlsrv_fetch_array(sqlsrv_query($conn, "select * from transactions where transid = '" . $transid . "' "));
        }
        if($existData){
            sqlsrv_query($conn, "update transactions set status = 'CANCELLED' where id = ?", [$existData['id']]);
            sqlsrv_query($conn, "update approval_status set status = 'CANCELLED', current_seq = NULL, is_current = 1, updated_last_by = NULL, updated_last_by_name = NULL, remarks = NULL, updated_at = NULL, history = NULL 
            where transaction_id = '" . $existData['id'] . "' ");

            echo true;
        }          
    }
}