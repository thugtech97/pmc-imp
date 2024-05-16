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
        $existData = sqlsrv_fetch_array(sqlsrv_query($conn, "select * from transactions where ref_req_no = '" . $data['refno'] . "' "));

        if($existData){
            sqlsrv_query($conn, "delete from transactions where ref_req_no = '" . $data['refno'] . "' ");
            sqlsrv_query($conn, "delete from approval_status where transaction_id = '" . $existData['id'] . "' ");        
        }

        $insert = "insert into transactions (ref_req_no,source_app,source_url,details,requestor,totalamount,converted_amount,department,transid,email,status,created_at,currency,purpose,name) 
        values ('" . $data['refno'] . "','" . $data['sourceapp'] . "','" . $data['sourceurl'] . "','" . $transaction_type . "','" . $data['requestor'] . "', 0,0,'" . $data['department'] . "',
        '" . $data['transid'] . "','" . $data['email'] . "','PENDING', GETDATE(),'PHP','" . $data['purpose'] . "','" . $data['name'] . "'); SELECT SCOPE_IDENTITY()";
        $result = sqlsrv_query($conn, $insert);
        sqlsrv_next_result($result);
        sqlsrv_fetch($result);

        $insertedID = sqlsrv_get_field($result, 0);
        if ($result) {

            $query = sqlsrv_query($conn, "select * from template_approvers where template_id = " . $data_result['template_id']);
            while ($qry = sqlsrv_fetch_array($query)) {

                if ($qry['designation'] == 'MANAGER') {

                    $gdept = sqlsrv_fetch_array(sqlsrv_query($conn, "select department from transactions where transid = '" . $transid . "' "));
  
                    $gdivision = sqlsrv_fetch_array(sqlsrv_query($conn, "select * from users where department like '%" . $gdept['department'] . "%' "));

                    $gmanager = sqlsrv_fetch_array(sqlsrv_query($conn, "select * from users where  division like '%" . $gdivision['division'] . "%' AND department like '%" . $gdivision['department'] . "%' AND is_alternate = 0"));
                    $alt_gm = sqlsrv_fetch_array(sqlsrv_query($conn, "select * from users where  division like '%" . $gdivision['division'] . "%' AND department like '%" . $gdivision['department'] . "%' AND is_alternate = 1"));
                    $alt_gm_id = 0;
                   if(isset($alt_gm)){
                    $alt_gm_id = $alt_gm['id'];
                    } 

                    $query_result = sqlsrv_query($conn, "insert into approval_status (transaction_id,approver_id,alternate_approver_id,sequence_number,status,created_at,is_current) values (" . $insertedID . ",'" . $gmanager['id'] . "','" . $alt_gm_id . "','" . $qry['sequence_number'] . "','PENDING',GETDATE(),1) ");
                    sqlsrv_next_result($query_result);
                    sqlsrv_fetch($query_result);

                    echo $query_result;
                }

                if ($qry['designation'] == 'EXECUTIVE' && strpos($transid, 'MRS') !== false) {
                    //$gdept = sqlsrv_fetch_array(sqlsrv_query($conn, "select department from transactions where transid = '" . $transid . "' "));
                    $alt_gm_id = 0;
                   if(isset($alt_gm)){
                    $alt_gm_id = $alt_gm['id'];
                    } 

                    $query_result = sqlsrv_query($conn, "insert into approval_status (transaction_id,approver_id,alternate_approver_id,sequence_number,status,created_at,is_current) values (" . $insertedID . ",'" . $qry['approver_id'] . "','" . $alt_gm_id . "','" . $qry['sequence_number'] . "','PENDING',GETDATE(),1) ");
                    sqlsrv_next_result($query_result);
                    sqlsrv_fetch($query_result);

                    echo $query_result;
                }
            }
        }
    }
}