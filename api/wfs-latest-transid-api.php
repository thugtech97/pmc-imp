<?php

include(__DIR__ . '/config.php');

// Latest WFS transid for one request. IMF transids are random ('IMP-IMF-' .
// uniqid()), so a resubmit after a WFS hold has to look the existing one up to
// reset it instead of opening a second transaction. Scoped by the transid
// prefix ($transidLike) because IMF and MRS share ref_req_no values.
if (!isset($refno) || !isset($transidLike) || trim((string) $refno) === '') {
    return null;
}

$data_result = sqlsrv_query($conn, "select top 1 transid from transactions where ref_req_no = ? and details = 'IMP' and transid like ? order by id desc", [(string) $refno, '%' . $transidLike . '%']);

if ($data_result && ($row = sqlsrv_fetch_array($data_result))) {
    return $row['transid'];
}

return null;
