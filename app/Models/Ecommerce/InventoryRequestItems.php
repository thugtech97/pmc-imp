<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryRequestItems extends Model
{
    use HasFactory;
    use \App\Models\Concerns\RecordsDocumentHistory;

    /** Item row of an IMF — changes are grouped onto the parent's audit trail. */
    protected $historyDocumentType = 'IMF';
    protected $historyIsItem = true;

    protected $table = 'inventory_requests_items';

    protected $fillable = [
        'stock_code',
        'inventory_code',
        'item_class',
        'dlt',
        'item_description',
        'brand',
        'OEM_ID',
        'UoM',
        'usage_rate_qty',
        'usage_frequency',
        'purpose',
        'planner_remarks',
        'verifier_remarks',
        'min_qty',
        'max_qty',
        'imf_no',
        'product_id'
    ];

    public function request()
    {
        return $this->belongsTo(InventoryRequest::class, 'imf_no');
    }

    /**
     * The IMF this line belongs to — the audit trail hangs off the header.
     */
    public function historyParent()
    {
        return $this->request;
    }

    /**
     * How this line is named in the trail, e.g. "M-1042 Bearing 6204".
     */
    public function historyItemLabel()
    {
        $code = $this->stock_code;
        $name = $this->item_description;

        return trim(($code ? $code . ' ' : '') . ($name ?: 'Item #' . $this->id));
    }

    /**
     * @return array
     */
    public function historyTracked()
    {
        return [
            'stock_code'       => 'Stock code',
            'inventory_code'   => 'Inventory code',
            'item_class'       => 'Class',
            'dlt'              => 'DLT',
            'item_description' => 'Item description',
            'brand'            => 'Brand',
            'OEM_ID'           => 'OEM',
            'UoM'              => 'UoM',
            'usage_rate_qty'   => 'Usage rate qty',
            'usage_frequency'  => 'Usage frequency',
            'purpose'          => 'Purpose',
            'planner_remarks'  => 'Planner remark',
            'verifier_remarks' => 'Verifier remark',
            'min_qty'          => 'Min qty',
            'max_qty'          => 'Max qty',
        ];
    }
}
