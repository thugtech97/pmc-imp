<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'approved_at',
        'type',
        'approved_by',
        'note_planner',
        'note_verifier',
        'planner_approved_by',
        'approver_approved_by',
        'user_id',
        'revision',
        'revised_at',
    ];

    protected $casts = [
        'revised_at' => 'datetime',
    ];

    /**
     * "Rev1", "Rev2", ... — empty string until the IMF has been revised at least once.
     */
    public function getRevLabelAttribute()
    {
        return $this->revision > 0 ? 'Rev' . $this->revision : '';
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
            'submitted_at'         => 'Submitted date',
            'approved_at'          => 'Approved date',
            'approved_by'          => 'Approved by',
            'planner_approved_by'  => 'MCD Planner',
            'approver_approved_by' => 'Planning Supervisor',
            'note_planner'         => 'Planner note',
            'note_verifier'        => 'Verifier note',
        ];
    }
}
