<?php

include(__DIR__ . '/config.php');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Expose-Headers: Content-Length, X-JSON");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: *");


$array_status = array();
$data_result = sqlsrv_query($conn, "select t.ref_req_no, t.status, a.updated_at, a.updated_last_by_name from transactions as t, approval_status as a where t.ref_req_no in (" .$ids. ") and t.details = 'IMP' and t.status <> 'Pending' and t.id = a.transaction_id");

if($data_result){
    while ($result = sqlsrv_fetch_array($data_result)) {

       // Convert the DateTime object to a string using the format method
       $updated_at_string = $result['updated_at']->format('Y-m-d H:i:s');
        
       // Concatenate the values into the array
       $array_status[] = $result['ref_req_no'] . '|' . $result['status'] . '|' . $updated_at_string . '|' . $result['updated_last_by_name'];
        
    }
}

return $array_status;