<?php

// Creates this request's WFS transaction and approver chain, or resets the one
// it already has (after a hold, or a repair when no approver can see it) — a
// request never gets a second transaction. Required from inside a controller method, so it shares that
// method's scope: it reads $data and must not clobber the caller's variables
// (hence the $wfs prefix). Returns ['ok' => bool, 'message' => string for the
// requestor, 'detail' => string for the log]; never echoes.

include(__DIR__ . '/config.php');

$wfsSaved = ' Your request was saved but not submitted. Please try again later or contact IT.';

if (!$conn) {
    return [
        'ok'      => false,
        'message' => 'Could not connect to WFS.' . $wfsSaved,
        'detail'  => 'sqlsrv_connect failed: ' . json_encode(sqlsrv_errors()),
    ];
}

$wfsInTransaction = false;
$wfsFail = function ($message, $detail) use ($conn, $wfsSaved, &$wfsInTransaction) {
    $errors = sqlsrv_errors();
    if ($wfsInTransaction) {
        sqlsrv_rollback($conn);
    }
    return [
        'ok'      => false,
        'message' => $message . $wfsSaved,
        'detail'  => $detail . ($errors ? ' | sqlsrv: ' . json_encode($errors) : ''),
    ];
};

$wfsTransactionType = $data['type'];
$wfsTransid = $data['transid'];
$wfsTransStatus = (string) $data['status'];

$wfsQuery = sqlsrv_query($conn, "select * from allowed_transactions where name = ?", [$wfsTransactionType]);
if (!$wfsQuery) {
    return $wfsFail('Could not read from WFS.', 'allowed_transactions lookup failed');
}
$wfsAllowed = sqlsrv_fetch_array($wfsQuery);

if (!$wfsAllowed || !isset($data['token']) || $wfsAllowed['token'] != $data['token']) {
    return $wfsFail(
        'WFS rejected the submission (this app is not registered with WFS).',
        'allowed_transactions has no row for "' . $wfsTransactionType . '" or the token does not match'
    );
}

if (!sqlsrv_begin_transaction($conn)) {
    return $wfsFail('Could not write to WFS.', 'sqlsrv_begin_transaction failed');
}
$wfsInTransaction = true;

// This request's own transaction is found by ref_req_no (the ECOM id) within
// its family of transids, not by transid alone: an MRS renumbered after it was
// first submitted still owns its original transaction, and a resubmit must
// reset that one, never add a second. IMF and MRS share ref_req_no values,
// hence the family.
if (strpos($wfsTransid, 'MRS') === 0) {
    $wfsFamily = 'MRS%';
} elseif (strpos($wfsTransid, 'IMP-IMF-') === 0) {
    $wfsFamily = 'IMP-IMF-%';
} else {
    $wfsFamily = $wfsTransid;
}
$wfsQuery = sqlsrv_query($conn, "select TOP 1 * from transactions where ref_req_no = ? and transid like ? and details = ? order by id desc",
    [(string) $data['refno'], $wfsFamily, $wfsTransactionType]);
if (!$wfsQuery) {
    return $wfsFail('Could not read from WFS.', 'transactions lookup failed for ref_req_no ' . $data['refno']);
}
$wfsExisting = sqlsrv_fetch_array($wfsQuery);

// Who, if anyone, already holds the transid being submitted.
$wfsQuery = sqlsrv_query($conn, "select TOP 1 id, ref_req_no from transactions where transid = ?", [$wfsTransid]);
if (!$wfsQuery) {
    return $wfsFail('Could not read from WFS.', 'transactions lookup failed for ' . $wfsTransid);
}
$wfsHolder = sqlsrv_fetch_array($wfsQuery);

// No transaction of its own, and its number is held by another request (an MRS
// number reused after the MRS that first had it was renumbered). Treating that
// one as this request's would report success while no approver ever sees this
// request. 'code' lets the caller renumber and retry.
if (!$wfsExisting && $wfsHolder) {
    $wfsResult = $wfsFail(
        'This request number is already used in WFS by another request.',
        'transid ' . $wfsTransid . ' belongs to ref_req_no ' . $wfsHolder['ref_req_no'] . ', not ' . $data['refno']
    );
    $wfsResult['code'] = 'transid_taken';
    return $wfsResult;
}

// Its own transaction is under an older number and the current one is free:
// bring the transid in line so lookups by number find it again.
if ($wfsExisting && !$wfsHolder && $wfsExisting['transid'] !== $wfsTransid) {
    if (!sqlsrv_query($conn, "update transactions set transid = ? where id = ?", [$wfsTransid, $wfsExisting['id']])) {
        return $wfsFail('Could not write to WFS.', 'transid update ' . $wfsExisting['transid'] . ' -> ' . $wfsTransid . ' failed');
    }
}

if ($wfsExisting) {
    // Approval rows that some real WFS user can see (the old script wrote
    // approver_id 0 when it found no manager, and could stop part-way).
    $wfsQuery = sqlsrv_query($conn, "select count(*) as n from approval_status a
        where a.transaction_id = ?
          and (exists (select 1 from users u where u.id = a.approver_id)
               or exists (select 1 from users u where u.id = a.alternate_approver_id))", [$wfsExisting['id']]);
    if (!$wfsQuery) {
        return $wfsFail('Could not read from WFS.', 'approval_status lookup failed for ' . $wfsTransid);
    }
    $wfsVisible = (int) sqlsrv_fetch_array($wfsQuery)['n'];
}

if ($wfsExisting && $wfsVisible > 0) {
    if (strpos($wfsTransStatus, 'ON HOLD') !== false || strpos($wfsTransStatus, 'APPROVED (MCD Planner)') !== false) {
        $wfsReset = sqlsrv_query($conn, "update transactions set status = 'PENDING' where id = ?", [$wfsExisting['id']])
            && sqlsrv_query($conn, "update approval_status set status = 'PENDING', current_seq = NULL, is_current = 1, updated_last_by = NULL, updated_last_by_name = NULL, remarks = NULL, updated_at = NULL, history = NULL
                where transaction_id = ?", [$wfsExisting['id']]);
        if (!$wfsReset) {
            return $wfsFail('WFS could not reopen the request for approval.', 'reset to PENDING failed for ' . $wfsTransid);
        }
    }

    sqlsrv_commit($conn);
    return ['ok' => true, 'message' => '', 'detail' => ''];
}

if ($wfsExisting) {
    // Repair: this request's transaction exists but no approver can see it
    // (approver_id 0, or the old script stopped part-way). Reset the same row
    // with the current details; its approval rows are reset in place below.
    $wfsRepair = sqlsrv_query($conn,
        "update transactions set status = 'PENDING', source_url = ?, requestor = ?, department = ?, email = ?, purpose = ?, name = ? where id = ?",
        [$data['sourceurl'], $data['requestor'], $data['department'], $data['email'], $data['purpose'], $data['name'], $wfsExisting['id']]
    );
    if (!$wfsRepair) {
        return $wfsFail('WFS could not repair the request.', 'transaction reset failed for ' . $wfsTransid);
    }
    $wfsInsertedID = $wfsExisting['id'];
} else {
    $wfsInsert = sqlsrv_query($conn,
        "insert into transactions (ref_req_no,source_app,source_url,details,requestor,totalamount,converted_amount,department,transid,email,status,created_at,currency,purpose,name)
        values (?,?,?,?,?,0,0,?,?,?,'PENDING',GETDATE(),'PHP',?,?); SELECT SCOPE_IDENTITY()",
        [$data['refno'], $data['sourceapp'], $data['sourceurl'], $wfsTransactionType, $data['requestor'],
         $data['department'], $wfsTransid, $data['email'], $data['purpose'], $data['name']]
    );
    if (!$wfsInsert || !sqlsrv_next_result($wfsInsert) || !sqlsrv_fetch($wfsInsert)) {
        return $wfsFail('WFS could not save the request.', 'transactions insert failed for ' . $wfsTransid);
    }
    $wfsInsertedID = sqlsrv_get_field($wfsInsert, 0);
}

$wfsTemplate = sqlsrv_query($conn, "select * from template_approvers where template_id = ?", [$wfsAllowed['template_id']]);
if (!$wfsTemplate) {
    return $wfsFail('Could not read the WFS approver template.', 'template_approvers lookup failed for template ' . $wfsAllowed['template_id']);
}

// WFS users.department is a '|'-separated list of department names. A plain
// substring match lets 'MINE OPERATION' pick the manager of 'MINE OPERATION
// GENERAL SERVICES' (lowest id wins), so an exact list entry is tried first and
// the substring match is only the fallback. Returns the row, null, or false on
// a query error.
$wfsUserByDept = function ($where) use ($conn, $data) {
    $dept = str_replace(['[', '%', '_'], ['[[]', '[%]', '[_]'], trim($data['department']));
    $matches = [
        "'|' + replace(replace(cast(department as nvarchar(max)), '| ', '|'), ' |', '|') + '|' like ?" => '%|' . $dept . '|%',
        "department like ?" => '%' . $dept . '%',
    ];
    foreach ($matches as $condition => $param) {
        $query = sqlsrv_query($conn, "select TOP 1 * from users where $condition and $where order by id ASC", [$param]);
        if (!$query) {
            return false;
        }
        $row = sqlsrv_fetch_array($query);
        if ($row) {
            return $row;
        }
    }
    return null;
};

$wfsApproverCount = 0;
$wfsSteps = [];

while ($wfsStep = sqlsrv_fetch_array($wfsTemplate)) {
    $wfsApproverId = null;
    $wfsAlternateId = 0;

    if ($wfsStep['designation'] == 'MANAGER') {
        $wfsManager = $wfsUserByDept("designation='MANAGER' and is_alternate=0 and isActive=1");
        $wfsAlternate = $wfsUserByDept("designation='MANAGER' and is_alternate=1 and isActive=1");
        if ($wfsManager === false || $wfsAlternate === false) {
            return $wfsFail('Could not read the approvers from WFS.', 'manager lookup failed for department ' . $data['department']);
        }
        if (!$wfsManager) {
            return $wfsFail('WFS has no active manager set up for your department (' . $data['department'] . ').', 'no MANAGER in WFS users for department ' . $data['department']);
        }

        $wfsApproverId = $wfsManager['id'];
        $wfsAlternateId = $wfsAlternate ? $wfsAlternate['id'] : 0;
    }
    elseif ($wfsStep['designation'] == 'DIVISION MANAGER' && $wfsStep['is_dynamic'] == 'YES' && strpos($wfsTransid, 'MRS') !== false) {
        $wfsDivision = $wfsUserByDept("1=1");
        if ($wfsDivision === false) {
            return $wfsFail('Could not read the approvers from WFS.', 'division lookup failed for department ' . $data['department']);
        }
        if (!$wfsDivision) {
            return $wfsFail('WFS has no users set up for your department (' . $data['department'] . ').', 'no WFS user (for division) in department ' . $data['department']);
        }
        $wfsDivLike = '%' . $wfsDivision['division'] . '%';
        $wfsDivManager = sqlsrv_fetch_array(sqlsrv_query($conn, "select TOP 1 * from users where designation like '%Division Manager%' and division like ? order by id ASC", [$wfsDivLike]));
        if (!$wfsDivManager) {
            return $wfsFail('WFS has no division manager set up for your division (' . $wfsDivision['division'] . ').', 'no Division Manager in WFS users for division ' . $wfsDivision['division']);
        }
        $wfsAlternate = sqlsrv_fetch_array(sqlsrv_query($conn, "select TOP 1 * from users where designation like '%Division Manager%' and division like ? and is_alternate=1 order by id ASC", [$wfsDivLike]));

        $wfsApproverId = $wfsDivManager['id'];
        $wfsAlternateId = $wfsAlternate ? $wfsAlternate['id'] : 0;
    }

    if ($wfsApproverId === null) {
        continue;
    }

    // Reset this step's existing row in place (repair), or add it.
    $wfsStepUpdate = sqlsrv_query($conn,
        "update approval_status set approver_id = ?, alternate_approver_id = ?, status = 'PENDING', current_seq = NULL, is_current = 1, updated_last_by = NULL, updated_last_by_name = NULL, remarks = NULL, updated_at = NULL, history = NULL
        where transaction_id = ? and sequence_number = ?",
        [$wfsApproverId, $wfsAlternateId, $wfsInsertedID, $wfsStep['sequence_number']]
    );
    if (!$wfsStepUpdate) {
        return $wfsFail('WFS could not set up the approvers.', 'approval_status reset failed for ' . $wfsTransid . ' step ' . $wfsStep['sequence_number']);
    }
    if (sqlsrv_rows_affected($wfsStepUpdate) < 1) {
        $wfsStepInsert = sqlsrv_query($conn,
            "insert into approval_status (transaction_id,approver_id,alternate_approver_id,sequence_number,status,created_at,is_current) values (?,?,?,?,'PENDING',GETDATE(),1)",
            [$wfsInsertedID, $wfsApproverId, $wfsAlternateId, $wfsStep['sequence_number']]
        );
        if (!$wfsStepInsert) {
            return $wfsFail('WFS could not set up the approvers.', 'approval_status insert failed for ' . $wfsTransid . ' step ' . $wfsStep['sequence_number']);
        }
    }
    $wfsSteps[] = (int) $wfsStep['sequence_number'];
    $wfsApproverCount++;
}

// A transaction with no approvers would sit in WFS forever with no one to act on it.
if ($wfsApproverCount === 0) {
    return $wfsFail('WFS has no approvers set up for this request.', 'template ' . $wfsAllowed['template_id'] . ' produced no approval_status rows for ' . $wfsTransid);
}

// On a repair, drop leftover rows for steps the template no longer produces.
$wfsSteps = array_values(array_unique($wfsSteps));
$wfsStepList = implode(',', array_fill(0, count($wfsSteps), '?'));
if (!sqlsrv_query($conn, "delete from approval_status where transaction_id = ? and sequence_number not in ($wfsStepList)", array_merge([$wfsInsertedID], $wfsSteps))) {
    return $wfsFail('WFS could not set up the approvers.', 'clearing leftover approval rows failed for ' . $wfsTransid);
}

sqlsrv_commit($conn);
return ['ok' => true, 'message' => '', 'detail' => ''];
