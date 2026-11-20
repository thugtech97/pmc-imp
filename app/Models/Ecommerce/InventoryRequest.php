<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class InventoryRequest extends Model
{
    use HasFactory;
    use \App\Models\Concerns\RecordsDocumentHistory;

    /** Audit trail bucket — see App\Models\Concerns\RecordsDocumentHistory. */
    protected $historyDocumentType = 'IMF';

    protected $table = 'inventory_requests';

    protected $fillable = [
        'priority',
        'department',
        'section',
        'division',
        'update_type',
        'status',
        'attachments',
        'submitted_at',
        'verified_at',
        'approved_at',
        'wfs_approved_at',
        'planner_reviewed_at',
        'planner_stock_at',
        'supervisor_approved_at',
        'type',
        'approved_by',
        'note_planner',
        'note_mcd_verifier',
        'note_verifier',
        'planner_approved_by',
        'verifier_approved_by',
        'approver_approved_by',
        'user_id',
        'revision',
        'revised_at',
        'imf_number',
    ];

    protected $casts = [
        'revised_at'             => 'datetime',
        'wfs_approved_at'        => 'datetime',
        'planner_reviewed_at'    => 'datetime',
        'planner_stock_at'       => 'datetime',
        'supervisor_approved_at' => 'datetime',
    ];

    /**
     * Every new IMF gets its IMF No. when it is saved, as the MRS gets its
     * order_number: IMF + YYYYMMDD + '-' + 4-digit series that restarts at 0001
     * each day (IMF20261120-0001, like the MRS's WFS transid MRS20261120-0001).
     * It is also the IMF's WFS transid. Set before the insert so the history
     * trail's "created" entry already carries it.
     */
    protected static function booted()
    {
        static::creating(function ($imf) {
            if (!$imf->imf_number) {
                $imf->imf_number = static::nextImfNumber();
            }
        });
    }

    /**
     * Next free IMF No. for the day, counted from IMP's own imf_number.
     * The unique index on the column is the backstop against two IMFs saved
     * at the same moment.
     *
     * @param  \Carbon\Carbon|string|null  $date  defaults to today
     * @return string
     */
    public static function nextImfNumber($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();
        $prefix = 'IMF' . $date->format('Ymd') . '-';

        $last = 0;
        $numbers = static::where('imf_number', 'like', $prefix . '%')->pluck('imf_number');
        foreach ($numbers as $number) {
            if (preg_match('/^' . preg_quote($prefix, '/') . '(\d{4,})$/', (string) $number, $m)) {
                $last = max($last, (int) $m[1]);
            }
        }

        return $prefix . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * The IMF No. to show: IMF20261120-0001, or 'IMF-81' (its old PDF name)
     * for an IMF saved before IMF numbers existed.
     */
    public function getDisplayNoAttribute()
    {
        return $this->imf_number ?: 'IMF-' . $this->id;
    }

    /**
     * display_no for an IMF known only by its id (stock-code clash messages).
     *
     * @param  int|string  $id
     * @return string
     */
    public static function displayNoFor($id)
    {
        $number = static::where('id', $id)->value('imf_number');

        return $number ?: 'IMF-' . $id;
    }

    /**
     * "Rev1", "Rev2", ... — empty string until the IMF has been revised at least once.
     */
    public function getRevLabelAttribute()
    {
        return $this->revision > 0 ? 'Rev' . $this->revision : '';
    }

    /**
     * When the department head approved this IMF in WFS.
     *
     * Older records have no wfs_approved_at: before the per-stage stamps they
     * shared approved_at with the Planning Supervisor, so that column is only
     * trustworthy as the WFS date while the Supervisor has not signed yet.
     *
     * @return \Carbon\Carbon|null
     */
    public function getDeptHeadSignedAtAttribute()
    {
        if ($this->wfs_approved_at) {
            return $this->wfs_approved_at;
        }

        if ($this->approved_at && !$this->supervisor_approved_at && !$this->approver_approved_by) {
            return \Carbon\Carbon::parse($this->approved_at);
        }

        return null;
    }

    /**
     * When the MCD Planner last acted — the stock-code pass when it happened,
     * otherwise the first review pass.
     *
     * @return \Carbon\Carbon|null
     */
    public function getPlannerSignedAtAttribute()
    {
        return $this->planner_stock_at ?: $this->planner_reviewed_at;
    }

    /**
     * When the MCD Verifier verified this IMF.
     *
     * @return \Carbon\Carbon|null
     */
    public function getVerifierSignedAtAttribute()
    {
        return $this->verified_at ? \Carbon\Carbon::parse($this->verified_at) : null;
    }

    /**
     * When the Planning Supervisor gave final approval. Falls back to
     * approved_at for records approved before the per-stage stamps existed.
     *
     * @return \Carbon\Carbon|null
     */
    public function getSupervisorSignedAtAttribute()
    {
        if ($this->supervisor_approved_at) {
            return $this->supervisor_approved_at;
        }

        return ($this->approver_approved_by && $this->approved_at)
            ? \Carbon\Carbon::parse($this->approved_at)
            : null;
    }

    public function items()
    {
        return $this->hasMany(InventoryRequestItems::class, 'imf_no', 'id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * Audit trail for this IMF, newest first.
     */
    public function histories()
    {
        return $this->hasMany(\App\Models\DocumentHistory::class, 'document_id')
            ->where('document_type', 'IMF')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');
    }

    /**
     * Fields whose changes are worth an audit-trail entry.
     *
     * @return array
     */
    public function historyTracked()
    {
        return [
            'status'               => 'Status',
            'revision'             => 'Revision',
            'update_type'          => 'Update type',
            'type'                 => 'Type',
            'priority'             => 'Priority',
            'department'           => 'Department',
            'section'              => 'Section',
            'division'             => 'Division',
            'attachments'          => 'Attachments',
            'submitted_at'           => 'Submitted date',
            'verified_at'            => 'Verified date',
            'approved_at'            => 'Approved date',
            'wfs_approved_at'        => 'Department Head approval date',
            'planner_reviewed_at'    => 'MCD Planner review date',
            'planner_stock_at'       => 'MCD Planner stock code date',
            'supervisor_approved_at' => 'Planning Supervisor approval date',
            'approved_by'          => 'Approved by',
            'planner_approved_by'  => 'MCD Planner',
            'verifier_approved_by' => 'MCD Verifier',
            'approver_approved_by' => 'Planning Supervisor',
            'note_planner'         => 'Planner note',
            'note_mcd_verifier'    => 'MCD Verifier note',
            'note_verifier'        => 'Planning Supervisor note',
        ];
    }
}
