<?php

namespace App\Models\Ecommerce;

use App\Models\Ecommerce\SalesDetail;
use App\Models\Ecommerce\SalesHeader;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PurchaseAdvice extends Model
{
    use \App\Models\Concerns\RecordsDocumentHistory;

    /** Audit trail bucket — split into PA-DP / PA-SR by App\Services\History::paType(). */
    protected $historyDocumentType = 'PA';

    protected $table = 'purchase_advice';

    protected $fillable = [
        'pa_number',
        'mrs_id',
        'planner_remarks',
        'verifier_remarks',
        'approver_remarks',
        'purchaser_remarks',
        'status',
        'created_by',
        'verified_by',
        'verified_at',
        'approved_by',
        'approved_at',
        'received_by',
        'received_at',
        'is_hold',
        'supporting_documents',
        'revision',
        'revised_at'
    ];
    protected $appends = ['final_status'];
    protected $casts = [
        'revised_at' => 'datetime',
    ];

    /**
     * "Rev1", "Rev2", ... — empty until the PA has been revised at least once.
     */
    public function getRevLabelAttribute()
    {
        return $this->revision > 0 ? 'Rev' . $this->revision : '';
    }

    // Relationships
    public function mrs()
    {
        return $this->belongsTo(SalesHeader::class, 'mrs_id', 'id');
    }

    public function items()
    {
        return $this->hasMany(SalesDetail::class, 'is_pa');
    }

    public function details()
    {
        return $this->hasMany(PurchaseAdviceDetail::class, 'purchase_advice_id', 'id');
    }

    public function planner()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function verifier(){
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function approver(){
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function purchaser(){
        return $this->belongsTo(User::class, 'received_by');
    }

    // Accessor for Final Status
    public function getFinalStatusAttribute(){
        $totalQtyToOrder = $this->items->sum('qty_to_order');
        $totalQtyOrdered = $this->items->sum('qty_ordered');
        $balance = $totalQtyToOrder - $totalQtyOrdered;

        if ($balance === 0) {
            return "COMPLETED";
        } elseif ($balance > 0 && $totalQtyOrdered > 0) {
            return "PARTIAL";
        } elseif ($totalQtyOrdered === 0) {
            return "UNSERVED";
        }

        return "UNKNOWN";
    }

    /**
     * Audit trail for this PA (DP or SR), newest first.
     */
    public function histories()
    {
        return $this->hasMany(\App\Models\DocumentHistory::class, 'document_id')
            ->where('document_type', 'PA')
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
            'is_hold'              => 'On hold',
            'planner_remarks'      => 'Planner remarks',
            'verifier_remarks'     => 'Verifier remarks',
            'approver_remarks'     => 'Approver remarks',
            'purchaser_remarks'    => 'Canvasser remarks',
            'verified_by'          => 'Verified by',
            'verified_at'          => 'Verified date',
            'approved_by'          => 'Approved by',
            'approved_at'          => 'Approved date',
            'received_by'          => 'Assigned canvasser',
            'received_at'          => 'Received date',
            'supporting_documents' => 'Supporting documents',
            'mrs_id'               => 'Linked MRS',
        ];
    }

    /**
     * Attachments as [{ path, name }, ...]. The pipe-joined storage format is
     * known here and nowhere else, so the view and the upload/replace/delete
     * endpoints cannot drift apart on how the column is read.
     *
     * @return array
     */
    public function supportingDocumentList()
    {
        $raw = trim((string) $this->supporting_documents);

        if ($raw === '') {
            return [];
        }

        $paths = array_values(array_filter(explode('|', $raw), 'strlen'));
        $docs  = [];

        foreach ($paths as $index => $path) {
            $docs[] = [
                'path' => $path,
                'name' => static::supportingDocumentName($path, $index),
            ];
        }

        return $docs;
    }

    /**
     * Files attached before the planner could manage documents were stored under
     * Laravel's random hash name, which reads as noise. Those keep the old
     * "Document N" caption; anything uploaded since shows its real filename.
     *
     * @param  string  $path
     * @param  int     $index
     * @return string
     */
    public static function supportingDocumentName($path, $index)
    {
        $name = basename($path);

        if (preg_match('/^[A-Za-z0-9]{30,}\.[A-Za-z0-9]+$/', $name)) {
            $ext = pathinfo($name, PATHINFO_EXTENSION);

            return 'Document ' . ($index + 1) . ($ext ? '.' . strtolower($ext) : '');
        }

        return $name;
    }

    public function mrs_numbers(){
        return SalesDetail::where('is_pa', $this->id)
                        ->with('header')
                        ->get()
                        ->pluck('header.order_number')
                        ->unique();
    }
}
