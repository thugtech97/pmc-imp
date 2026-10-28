<?php

namespace App\Models\Concerns;

use App\Services\History;

/**
 * Records an audit-trail entry whenever a tracked request changes.
 *
 * Applied to the three request headers (IMF, MRS, PA) and to their item rows.
 * Because it hangs off Eloquent's model events, a new code path that updates a
 * request is logged without anyone remembering to add a call — the one gap is
 * query-builder updates (Model::where(...)->update()), which bypass events by
 * design; those few spots log explicitly through App\Services\History.
 *
 * A model using this trait must define:
 *   protected $historyDocumentType   'IMF' | 'MRS' | 'PA'
 *   historyTracked()                 [column => human label] worth logging
 * and, for item rows:
 *   protected $historyIsItem = true;
 *   historyParent()                  the header the row belongs to
 *   historyItemLabel()               how the row should be named in the trail
 */
trait RecordsDocumentHistory
{
    /**
     * @return void
     */
    public static function bootRecordsDocumentHistory()
    {
        static::created(function ($model) {
            // Item rows are not logged on create: a new request would otherwise
            // bury its own "created" entry under one line per item. Controllers
            // log deliberate single-item additions themselves.
            if (!$model->historyIsItemRow()) {
                $model->recordHistoryCreated();
            }
        });

        static::updated(function ($model) {
            $model->recordHistoryUpdated();
        });
    }

    /**
     * @return bool
     */
    public function historyIsItemRow()
    {
        return isset($this->historyIsItem) && $this->historyIsItem;
    }

    /**
     * @return string
     */
    public function historyType()
    {
        return isset($this->historyDocumentType) ? $this->historyDocumentType : 'MRS';
    }

    /**
     * The header this record belongs to (itself, for a header).
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function historyDocument()
    {
        return $this->historyIsItemRow() ? $this->historyParent() : $this;
    }

    /**
     * First entry in a request's trail.
     *
     * @return void
     */
    public function recordHistoryCreated()
    {
        $context = History::pullContext($this);

        $labels = [
            'IMF' => 'IMF raised',
            'MRS' => 'Request created',
            'PA'  => 'Purchase advice created',
        ];
        $type = $this->historyType();

        $this->writeHistory(array_merge([
            'action'          => 'created',
            'title'           => isset($labels[$type]) ? $labels[$type] : 'Record created',
            'requestor_title' => $type === 'PA' ? 'Purchase advice prepared' : 'Request created',
            'status_to'       => $this->status,
        ], $context));
    }

    /**
     * Diff a save and log it. Status moves get proper stage wording; everything
     * else is logged as a field-level change list.
     *
     * @return void
     */
    public function recordHistoryUpdated()
    {
        $diff = $this->historyDiff();

        if (empty($diff)) {
            return;
        }

        if ($this->historyIsItemRow()) {
            $document = $this->historyDocument();

            if ($document) {
                History::bufferItemChanges($this->historyType(), $document, $diff, $this->historyItemLabel());
            }

            return;
        }

        $context = History::pullContext($this);
        $payload = [
            'action'  => 'updated',
            'title'   => 'Request details updated',
            'changes' => $diff,
        ];

        $statusChange = null;
        foreach ($diff as $entry) {
            if ($entry['field'] === 'status') {
                $statusChange = $entry;
                break;
            }
        }

        if ($statusChange) {
            $described = History::describeStatus($this->historyType(), $statusChange['new']);

            $payload['action']          = $described['action'];
            $payload['title']           = $described['title'];
            $payload['requestor_title'] = $described['requestor_title'];
            $payload['status_from']     = $statusChange['old'];
            $payload['status_to']       = $statusChange['new'];
        }

        // A revision bump is the headline whenever it happens, even alongside a
        // status move — it is what the Rev badge on the printed form refers to.
        foreach ($diff as $entry) {
            if ($entry['field'] === 'revision' && (int) $entry['new'] > (int) $entry['old']) {
                $payload['action']          = 'revised';
                $payload['title']           = 'Revised (Rev' . (int) $entry['new'] . ')';
                $payload['requestor_title'] = 'Revised (Rev' . (int) $entry['new'] . ')';
                break;
            }
        }

        $this->writeHistory(array_merge($payload, $context));
    }

    /**
     * Changed tracked fields, as [{ field, label, old, new }, ...].
     *
     * @return array
     */
    public function historyDiff()
    {
        $tracked = $this->historyTracked();
        $changes = $this->getChanges();
        $diff    = [];

        foreach ($changes as $field => $new) {
            if (!isset($tracked[$field])) {
                continue;
            }

            $old = $this->getOriginal($field);

            if ($this->historyValuesMatch($old, $new)) {
                continue;
            }

            $diff[] = [
                'field' => $field,
                'label' => $tracked[$field],
                'old'   => $this->historyStringify($old),
                'new'   => $this->historyStringify($new),
            ];
        }

        return $diff;
    }

    /**
     * Loose comparison that treats null, '' and 0-vs-"0" as unchanged, so a form
     * re-submit does not manufacture entries.
     *
     * @return bool
     */
    protected function historyValuesMatch($old, $new)
    {
        if ($old === $new) {
            return true;
        }

        if (($old === null || $old === '') && ($new === null || $new === '')) {
            return true;
        }

        if (is_numeric($old) && is_numeric($new)) {
            return (float) $old === (float) $new;
        }

        return (string) $this->historyStringify($old) === (string) $this->historyStringify($new);
    }

    /**
     * @return string|null
     */
    protected function historyStringify($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        return (string) $value;
    }

    /**
     * Send a payload to the right History entry point.
     *
     * @param  array  $payload
     * @return void
     */
    public function writeHistory(array $payload)
    {
        $document = $this->historyDocument();

        if (!$document) {
            return;
        }

        History::forDocument($this->historyType(), $document, $payload);
    }
}
