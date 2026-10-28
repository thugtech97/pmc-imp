<?php

include(__DIR__ . '/config.php');

// Which of these requests ($refnos: ECOM ids) WFS actually has, as
// [ref_req_no => number of approval rows pointing at a real WFS user]. An id
// missing from the result has no transaction of its own in WFS; 0 means it has
// one that no approver can see. $transidLike picks the family ('MRS%' default,
// 'IMP-IMF-%' for IMF) since MRS and IMF share ref_req_no values. Returns null when WFS cannot be read, so
// the caller can tell "unknown" apart from "missing".
if (!$conn || empty($refnos)) {
    return null;
}

$refnos = array_values(array_map('strval', $refnos));
$transidLike = isset($transidLike) ? $transidLike : 'MRS%';
$placeholders = implode(',', array_fill(0, count($refnos), '?'));

$query = sqlsrv_query($conn, "
    select t.ref_req_no,
           (select count(*) from approval_status a
             where a.transaction_id = t.id
               and (exists (select 1 from users u where u.id = a.approver_id)
                    or exists (select 1 from users u where u.id = a.alternate_approver_id))) as approvers
    from transactions t
    where t.transid like ? and t.ref_req_no in ($placeholders)", array_merge([$transidLike], $refnos));

if (!$query) {
    return null;
}

$found = [];
while ($row = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC)) {
    $ref = trim((string) $row['ref_req_no']);
    $found[$ref] = max(isset($found[$ref]) ? $found[$ref] : 0, (int) $row['approvers']);
}

return $found;
