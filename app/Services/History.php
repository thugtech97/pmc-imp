<?php

namespace App\Services;

use App\Constants\Status;
use App\Models\DocumentHistory;
use App\Models\Ecommerce\InventoryRequest;
use App\Models\Ecommerce\PurchaseAdvice;
use App\Models\Ecommerce\SalesHeader;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Writes the IMF / MRS / PA-DP / PA-SR audit trail.
 *
 * Two ways in:
 *
 *  1. Automatically — App\Models\Concerns\RecordsDocumentHistory fires on every
 *     create/update of a tracked model and records the field diff. Nothing to
 *     call; it cannot be forgotten when a new code path updates a request.
 *
 *  2. With context — a controller calls History::context($model, [...]) right
 *     before its update() so the automatic entry gets proper wording, the
 *     remark the user typed, and the right visibility. One line, and the diff
 *     still comes for free.
 *
 * Explicit History::mrs() / History::pa() remain for things that are not a
 * column change at all (an item removed, a report printed, a delete request).
 *
 * Every write is wrapped: an audit-trail failure must never break the workflow
 * action that triggered it. This mirrors App\Services\Notifier.
 */
class History
{
    /**
     * Pending wording keyed by "Class#id", consumed by the next tracked write
     * on that exact model. Keyed rather than global because a single action
     * often updates the PA and its MRS together.
     *
     * @var array
     */
    protected static $context = [];

    /**
     * Set to true to stop recording (used when replaying or seeding).
     *
     * @var bool
     */
    protected static $muted = false;

    /**
     * Item-row diffs held until the end of the request, keyed by "TYPE#id".
     * A canvasser saving PO numbers across thirty lines should read as one
     * change in the trail, not thirty.
     *
     * @var array
     */
    protected static $itemBuffer = [];

    /**
     * @var bool
     */
    protected static $flushRegistered = false;

    /**
     * Describe the change that is about to be saved to $model.
     *
     * Accepted keys: action, title, requestor_title, description, remarks,
     * visible_to_requestor.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  array  $payload
     * @return void
     */
    public static function context($model, array $payload)
    {
        if (!$model) {
            return;
        }

        static::$context[static::key($model)] = $payload;
    }

    /**
     * Take (and clear) the pending context for $model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return array
     */
    public static function pullContext($model)
    {
        $key = static::key($model);

        if (!isset(static::$context[$key])) {
            return [];
        }

        $payload = static::$context[$key];
        unset(static::$context[$key]);

        return $payload;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return string
     */
    protected static function key($model)
    {
        return get_class($model) . '#' . ($model->getKey() ?: 'new');
    }

    /**
     * Record an entry against an MRS.
     *
     * @param  \App\Models\Ecommerce\SalesHeader|int|null  $mrs
     * @param  array  $payload
     * @return void
     */
    public static function mrs($mrs, array $payload)
    {
        if (is_numeric($mrs)) {
            $mrs = SalesHeader::find($mrs);
        }

        if (!$mrs) {
            return;
        }

        static::write(array_merge([
            'document_type'   => 'MRS',
            'document_id'     => $mrs->id,
            'document_number' => $mrs->order_number,
            'pa_type'         => null,
            'revision'        => (int) $mrs->revision,
        ], $payload));
    }

    /**
     * Record an entry against an IMF (inventory master file request).
     *
     * @param  \App\Models\Ecommerce\InventoryRequest|int|null  $imf
     * @param  array  $payload
     * @return void
     */
    public static function imf($imf, array $payload)
    {
        if (is_numeric($imf)) {
            $imf = InventoryRequest::find($imf);
        }

        if (!$imf) {
            return;
        }

        static::write(array_merge([
            'document_type'   => 'IMF',
            'document_id'     => $imf->id,
            'document_number' => 'IMF-' . $imf->id,
            'pa_type'         => null,
            'revision'        => (int) $imf->revision,
        ], $payload));
    }

    /**
     * Record an entry against a purchase advice. pa_type is derived, so the
     * trail can be filtered to PA-DP (raised from an MRS) or PA-SR (stand-alone).
     *
     * @param  \App\Models\Ecommerce\PurchaseAdvice|int|null  $pa
     * @param  array  $payload
     * @return void
     */
    public static function pa($pa, array $payload)
    {
        if (is_numeric($pa)) {
            $pa = PurchaseAdvice::find($pa);
        }

        if (!$pa) {
            return;
        }

        static::write(array_merge([
            'document_type'   => 'PA',
            'document_id'     => $pa->id,
            'document_number' => $pa->pa_number,
            'pa_type'         => static::paType($pa),
            'revision'        => (int) $pa->revision,
        ], $payload));
    }

    /**
     * 'DP' when the advice was raised from an MRS (it ends up delegated to a
     * canvasser), 'SR' when it is a stand-alone supply request.
     *
     * @param  \App\Models\Ecommerce\PurchaseAdvice  $pa
     * @return string
     */
    public static function paType($pa)
    {
        return optional($pa->mrs)->order_number ? 'DP' : 'SR';
    }

    /**
     * Route a payload to imf() / mrs() / pa() by document type.
     *
     * @param  string  $type  'IMF' | 'MRS' | 'PA'
     * @param  \Illuminate\Database\Eloquent\Model  $document
     * @param  array  $payload
     * @return void
     */
    public static function forDocument($type, $document, array $payload)
    {
        if ($type === 'PA') {
            static::pa($document, $payload);
        } elseif ($type === 'IMF') {
            static::imf($document, $payload);
        } else {
            static::mrs($document, $payload);
        }
    }

    /**
     * Hold item-row changes until the request ends, then write them as a single
     * grouped entry per document.
     *
     * @param  string  $type
     * @param  \Illuminate\Database\Eloquent\Model  $document
     * @param  array   $diff       [{ field, label, old, new }, ...]
     * @param  string  $itemLabel  how the row is named in the trail
     * @return void
     */
    public static function bufferItemChanges($type, $document, array $diff, $itemLabel)
    {
        if (static::$muted || !$document || empty($diff)) {
            return;
        }

        $key = $type . '#' . $document->getKey();

        if (!isset(static::$itemBuffer[$key])) {
            static::$itemBuffer[$key] = [
                'type'     => $type,
                'document' => $document,
                'changes'  => [],
            ];
        }

        foreach ($diff as $entry) {
            $entry['label'] = trim($itemLabel) !== '' ? $itemLabel . ' — ' . $entry['label'] : $entry['label'];
            static::$itemBuffer[$key]['changes'][] = $entry;
        }

        static::registerFlush();
    }

    /**
     * Flush on request termination, so no controller has to remember. The
     * public flush below stays available for loops that want the entry written
     * before they redirect.
     *
     * @return void
     */
    protected static function registerFlush()
    {
        if (static::$flushRegistered) {
            return;
        }

        static::$flushRegistered = true;

        try {
            app()->terminating(function () {
                History::flushItemChanges();
            });
        } catch (\Throwable $e) {
            // No container available (console one-offs): the explicit flush still works.
            static::$flushRegistered = false;
        }
    }

    /**
     * Drop buffered item changes without writing them — for the rollback path,
     * where the edits they describe never reached the database.
     *
     * @return void
     */
    public static function discardItemChanges()
    {
        static::$itemBuffer      = [];
        static::$flushRegistered = false;
    }

    /**
     * Write every buffered item change as one entry per document.
     *
     * @return void
     */
    public static function flushItemChanges()
    {
        $buffer = static::$itemBuffer;

        static::$itemBuffer      = [];
        static::$flushRegistered = false;

        foreach ($buffer as $group) {
            if (empty($group['changes'])) {
                continue;
            }

            $count = count($group['changes']);

            static::forDocument($group['type'], $group['document'], [
                'action'          => 'item_updated',
                'title'           => $count === 1 ? 'Item updated' : $count . ' item changes saved',
                'requestor_title' => $count === 1 ? 'An item was updated' : $count . ' item details were updated',
                'changes'         => $group['changes'],
            ]);
        }
    }

    /**
     * Persist one row. Never throws.
     *
     * @param  array  $payload
     * @return void
     */
    public static function write(array $payload)
    {
        if (static::$muted) {
            return;
        }

        try {
            $actor = Auth::user();

            $changes = isset($payload['changes']) ? $payload['changes'] : null;
            if (is_array($changes) && empty($changes)) {
                $changes = null;
            }

            DocumentHistory::create([
                'document_type'        => isset($payload['document_type']) ? $payload['document_type'] : 'MRS',
                'document_id'          => isset($payload['document_id']) ? $payload['document_id'] : 0,
                'document_number'      => isset($payload['document_number']) ? $payload['document_number'] : null,
                'pa_type'              => isset($payload['pa_type']) ? $payload['pa_type'] : null,
                'action'               => isset($payload['action']) ? $payload['action'] : 'updated',
                'title'                => static::clip(isset($payload['title']) ? $payload['title'] : 'Request updated', 190),
                'requestor_title'      => isset($payload['requestor_title']) ? static::clip($payload['requestor_title'], 190) : null,
                'description'          => isset($payload['description']) ? $payload['description'] : null,
                'status_from'          => isset($payload['status_from']) ? static::clip($payload['status_from'], 190) : null,
                'status_to'            => isset($payload['status_to']) ? static::clip($payload['status_to'], 190) : null,
                'remarks'              => isset($payload['remarks']) && $payload['remarks'] !== '' ? $payload['remarks'] : null,
                'changes'              => $changes,
                'revision'             => isset($payload['revision']) ? (int) $payload['revision'] : 0,
                'actor_id'             => $actor ? $actor->id : null,
                'actor_name'           => $actor ? $actor->name : null,
                'actor_role'           => $actor ? optional($actor->role)->name : null,
                'visible_to_requestor' => array_key_exists('visible_to_requestor', $payload) ? (bool) $payload['visible_to_requestor'] : true,
                'created_at'           => Carbon::now(),
            ]);
        } catch (\Throwable $e) {
            // An audit-trail failure must never break the workflow action.
            logger()->error('History failed: ' . $e->getMessage());
        }
    }

    /**
     * Turn a status string into { action, title, requestor_title, visible }.
     *
     * The stored statuses are written for MCD/purchasing staff, so the plain
     * wording for MRS is taken from SalesHeader's requestor-status mapper —
     * the single source of truth for what a department user is shown.
     *
     * @param  string  $documentType  'IMF', 'MRS' or 'PA'
     * @param  string  $status
     * @return array
     */
    public static function describeStatus($documentType, $status)
    {
        if ($documentType === 'IMF') {
            return static::describeImfStatus($status);
        }

        $status = (string) $status;
        $upper  = strtoupper($status);

        $action  = 'status';
        $title   = $status !== '' ? $status : 'Status updated';
        $visible = true;

        if (strpos($upper, 'CANCEL') !== false) {
            $action = 'cancelled';
            $title  = 'Request cancelled';
        } elseif ($upper === 'SAVED') {
            $action = 'created';
            $title  = 'Saved as draft';
        } elseif ($upper === 'POSTED') {
            $action = 'submitted';
            $title  = 'Submitted for WFS approval';
        } elseif (strpos($upper, 'IN-PROGRESS') !== false) {
            $action = 'status';
            $title  = 'Moving through WFS approval';
        } elseif (strpos($upper, 'ON-HOLD') !== false) {
            $action = 'held';
            $title  = 'Put on hold in WFS';
        } elseif ($status === 'REQUEST ON HOLD (Hold by MCD Planner)') {
            $action = 'returned';
            $title  = 'Returned to the requestor by the MCD Planner';
        } elseif ($status === 'HOLD (For MCD Planner re-edit)') {
            $action = 'held';
            $title  = 'Held for MCD Planner re-edit';
        } elseif (strpos($upper, 'REVISED MRS') === 0) {
            $action = 'revised';
            $title  = 'Revised by the requestor';
        } elseif (strpos($upper, 'FULLY APPROVED') !== false) {
            $action = 'approved';
            $title  = 'Fully approved in WFS';
        } elseif (strpos($upper, 'MRS FOR VERIFICATION') !== false || strpos($upper, 'FOR VERIFICATION') !== false) {
            $action = 'status';
            $title  = 'Endorsed by the MCD Planner for verification';
        } elseif (strpos($upper, 'VERIFIED (MCD VERIFIER)') === 0) {
            $action = 'verified';
            $title  = 'Verified by the MCD Verifier';
        } elseif (strpos($upper, 'APPROVED (MCD APPROVER)') === 0) {
            $action = 'approved';
            $title  = 'Approved by the MCD Approver';
        } elseif ($status === '(For Purchasing Receival)') {
            $action = 'assigned';
            $title  = 'Delegated to a canvasser for receiving';
        } elseif (strpos($upper, 'RECEIVED FOR CANVASS') === 0) {
            $action = 'received';
            $title  = 'Received for canvass';
        } elseif ($upper === 'COMPLETED') {
            $action = 'completed';
            $title  = 'Completed';
        }

        // Plain wording for the department user.
        if ($documentType === 'MRS') {
            $requestorTitle = SalesHeader::requestorStatusPartsFor($status);
            $requestorTitle = $requestorTitle['label'];
        } else {
            $requestorTitle = static::paRequestorTitle($action, $title);
        }

        return [
            'action'          => $action,
            'title'           => $title,
            'requestor_title' => $requestorTitle,
            'visible'         => $visible,
        ];
    }

    /**
     * IMF stages come from App\Constants\Status, so they are matched exactly
     * rather than sniffed out of free text.
     *
     * @param  string  $status
     * @return array
     */
    protected static function describeImfStatus($status)
    {
        $status = (string) $status;

        $map = [
            Status::SAVED              => ['created',   'Saved as draft',                          'Saved - not yet submitted'],
            Status::SUBMITTED          => ['submitted', 'Submitted for WFS approval',              'Submitted - for WFS approval'],
            Status::APPROVED_WFS       => ['approved',  'Fully approved in WFS',                   'Approved in WFS - for MCD Planner'],
            Status::APPROVED_MCD       => ['verified',  'Approved by the MCD Planner',             'Approved by MCD Planner - for MCD Manager'],
            Status::APPROVED_APPROVER  => ['approved',  'Approved by the MCD Approver',            'Approved by MCD Manager'],
            Status::HOLD_PLANNER       => ['returned',  'Returned to the requestor by the MCD Planner', 'Returned to you for revision - MCD Planner'],
            Status::HOLD_APPROVER      => ['held',      'Held by the MCD Approver for Planner re-edit', 'On hold - with MCD Planner for re-edit'],
            Status::REJECTED_PLANNER   => ['cancelled', 'Rejected by the MCD Planner',             'Rejected by MCD Planner'],
            Status::REJECTED_APPROVER  => ['cancelled', 'Rejected by the MCD Approver',            'Rejected by MCD Manager'],
            Status::CANCELLED          => ['cancelled', 'Cancelled',                               'Cancelled'],
            'PUBLISHED'                => ['completed', 'Published to the item master',            'Published to the item master'],
        ];

        if (isset($map[$status])) {
            $entry = $map[$status];

            return [
                'action'          => $entry[0],
                'title'           => $entry[1],
                'requestor_title' => $entry[2],
                'visible'         => true,
            ];
        }

        return [
            'action'          => 'status',
            'title'           => $status !== '' ? $status : 'Status updated',
            'requestor_title' => strtoupper($status),
            'visible'         => true,
        ];
    }

    /**
     * Requestor wording for a PA stage. The requestor never sees PA internals,
     * only that the purchasing side moved their request along.
     *
     * @return string
     */
    protected static function paRequestorTitle($action, $fallback)
    {
        switch ($action) {
            case 'created':   return 'Purchase advice prepared';
            case 'verified':  return 'Purchase advice passed MCD verification';
            case 'approved':  return 'Purchase advice approved by MCD Manager';
            case 'assigned':  return 'Purchase advice delegated to a canvasser';
            case 'received':  return 'Purchase advice received for canvass';
            case 'held':      return 'Purchase advice on hold with the MCD Planner';
            case 'returned':  return 'Purchase advice returned for revision';
            case 'revised':   return 'Purchase advice revised';
            case 'cancelled': return 'Purchase advice cancelled';
            default:          return $fallback;
        }
    }

    /**
     * @param  string|null  $value
     * @param  int  $length
     * @return string|null
     */
    protected static function clip($value, $length)
    {
        if ($value === null) {
            return null;
        }

        $value = (string) $value;

        return mb_strlen($value) > $length ? mb_substr($value, 0, $length - 1) . '…' : $value;
    }

    /**
     * Suspend recording for the duration of $callback (seeding, imports).
     *
     * @param  callable  $callback
     * @return mixed
     */
    public static function withoutRecording(callable $callback)
    {
        $previous = static::$muted;
        static::$muted = true;

        try {
            return $callback();
        } finally {
            static::$muted = $previous;
        }
    }
}
