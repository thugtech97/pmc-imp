<?php

include(__DIR__ . '/config.php');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Expose-Headers: Content-Length, X-JSON");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: *");

$transaction_type =  $data['type'];
$transid = $data['transid'];

$data_result = sqlsrv_fetch_array(sqlsrv_query($conn, "select * from allowed_transactions where name = '" . $transaction_type . "' "));

if (isset($data['token'])) {
    if ($data_result['token'] == $data['token']) {
        $existData = sqlsrv_fetch_array(sqlsrv_query($conn, "select * from transactions where transid = '" . $transid . "' "));
        if($existData){
            sqlsrv_query($conn, "update transactions set status = 'CANCELLED' where transid = '" . $transid . "' ");
            sqlsrv_query($conn, "update approval_status set status = 'CANCELLED', current_seq = NULL, is_current = 1, updated_last_by = NULL, updated_last_by_name = NULL, remarks = NULL, updated_at = NULL, history = NULL 
            where transaction_id = '" . $existData['id'] . "' ");

            echo true;
        }          
    }
}