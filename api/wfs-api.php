<?php

// Creates this request's WFS transaction and approver chain, or resets the one
// it already has (after a hold, or a repair when no approver can see it) — a
// request never gets a second transaction. Required from inside a controller method, so it shares that
// method's scope: it reads $data and must not clobber the caller's variables
// (hence the $wfs prefix). Returns ['ok' => bool, 'message' => string for the
// requestor, 'detail' => string for the log]; never echoes.

include(__DIR__ . '/config.php');
// Guarded: if any output has already gone out, header() warns, and Laravel
// turns that warning into an exception that would fail the whole submit.
if (!headers_sent()) {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Expose-Headers: Content-Length, X-JSON");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Allow-Headers: *");
}

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

// Approvers are set per department in IMP (Admin > Departments) and passed in as
//   $data['approvers'] = ['first'  => ['main' => id, 'alternate' => id|null],
//                         'second' => ['main' => id, 'alternate' => id|null]]
// (Department::wfsApprovers(); the ids are WFS users.id). They are no longer
// looked up in WFS users by department name.
//   1st = template step MANAGER          — MRS and IMF
//   2nd = template step DIVISION MANAGER — MRS only
$wfsApprovers = (isset($data['approvers']) && is_array($data['approvers'])) ? $data['approvers'] : [];
$wfsDeptName = trim((string) $data['department']);
$wfsSetUpHint = ' Please ask the IMP administrator to set it in Admin > Departments.';

// The WFS user row if it exists and is active, null if not, false on a query error.
$wfsActiveUser = function ($id) use ($conn) {
    $query = sqlsrv_query($conn, "select TOP 1 id from users where id = ? and isActive = 1", [(int) $id]);
    if (!$query) {
        return false;
    }
    $row = sqlsrv_fetch_array($query);
    return $row ?: null;
};

$wfsApproverCount = 0;
$wfsSteps = [];

while ($wfsStep = sqlsrv_fetch_array($wfsTemplate)) {
    if ($wfsStep['designation'] == 'MANAGER') {
        $wfsSlot = 'first';
        $wfsSlotLabel = '1st approver';
    } elseif ($wfsStep['designation'] == 'DIVISION MANAGER' && $wfsStep['is_dynamic'] == 'YES' && strpos($wfsTransid, 'MRS') !== false) {
        $wfsSlot = 'second';
        $wfsSlotLabel = '2nd approver';
    } else {
        continue;
    }

    $wfsApproverId = isset($wfsApprovers[$wfsSlot]['main']) ? (int) $wfsApprovers[$wfsSlot]['main'] : 0;
    $wfsAlternateId = isset($wfsApprovers[$wfsSlot]['alternate']) ? (int) $wfsApprovers[$wfsSlot]['alternate'] : 0;

    if ($wfsApproverId <= 0) {
        return $wfsFail(
            'No WFS ' . $wfsSlotLabel . ' is set for your department (' . $wfsDeptName . ').' . $wfsSetUpHint,
            'Admin > Departments has no ' . $wfsSlot . ' approver for department "' . $wfsDeptName . '"'
        );
    }

    $wfsMainRow = $wfsActiveUser($wfsApproverId);
    if ($wfsMainRow === false) {
        return $wfsFail('Could not read the approvers from WFS.', 'approver lookup failed for WFS user ' . $wfsApproverId);
    }
    if (!$wfsMainRow) {
        return $wfsFail(
            'The ' . $wfsSlotLabel . ' set for your department (' . $wfsDeptName . ') is no longer an active WFS user.' . $wfsSetUpHint,
            $wfsSlot . ' approver WFS user ' . $wfsApproverId . ' is missing or inactive (department "' . $wfsDeptName . '")'
        );
    }

    if ($wfsAlternateId > 0) {
        $wfsAltRow = $wfsActiveUser($wfsAlternateId);
        if ($wfsAltRow === false) {
            return $wfsFail('Could not read the approvers from WFS.', 'alternate lookup failed for WFS user ' . $wfsAlternateId);
        }
        // An inactive alternate is just dropped: the main approver can still act.
        if (!$wfsAltRow || $wfsAlternateId === $wfsApproverId) {
            $wfsAlternateId = 0;
        }
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
