<?php

namespace App\Models\Ecommerce;

use App\Models\Ecommerce\Product;
use Illuminate\Database\Eloquent\Model;
use App\Models\Ecommerce\PurchaseAdvice;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseAdviceDetail extends Model
{
    use HasFactory;
    use \App\Models\Concerns\RecordsDocumentHistory;

    /** Item row of a PA — changes are grouped onto the parent's audit trail. */
    protected $historyDocumentType = 'PA';
    protected $historyIsItem = true;

    protected $fillable = [
        'purchase_advice_id',
        'product_id',
        'par_to',
        'qty_to_order',
        'previous_po',
        'current_po',
        'po_date_released',
        'qty_ordered',
        'cost_code',
        'remarks',
        'priority_no',
        'qty_per_delivery',
        'number_of_deliveries',
        'dlt',
        'date_needed',
        'class_note',
        'frequency',
        'open_po',
        'department',   // NEW
        'usage_rate_qty',
        'on_hand',
        'rof_months',           // add this
        'rof_months_w_request', // add this
        'is_hold',
        'hold_remarks',
    ];

    protected $casts = [
        'is_hold' => 'integer',
    ];

    // Relationship: PurchaseAdviceDetail belongs to a Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Relationship: PurchaseAdviceDetail belongs to a PurchaseAdvice
    public function purchaseAdvice()
    {
        return $this->belongsTo(PurchaseAdvice::class);
    }

    /**
     * The PA this line belongs to — the audit trail hangs off the header.
     */
    public function historyParent()
    {
        return $this->purchaseAdvice;
    }

    /**
     * How this line is named in the trail, e.g. "M-1042 Bearing 6204".
     */
    public function historyItemLabel()
    {
        $product = $this->product;
        $code    = optional($product)->code;
        $name    = optional($product)->name;

        return trim(($code ? $code . ' ' : '') . ($name ?: 'Item #' . $this->id));
    }

    /**
     * @return array
     */
    public function historyTracked()
    {
        return [
            'qty_to_order'         => 'Qty to order',
            'qty_ordered'          => 'Qty ordered',
            'qty_per_delivery'     => 'Qty per delivery',
            'number_of_deliveries' => 'Number of deliveries',
            'current_po'           => 'Current PO',
            'previous_po'          => 'Previous PO',
            'po_date_released'     => 'PO date released',
            'open_po'              => 'Open PO',
            'cost_code'            => 'Cost code',
            'par_to'               => 'PAR to',
            'priority_no'          => 'Priority',
            'date_needed'          => 'Date needed',
            'dlt'                  => 'DLT',
            'frequency'            => 'Frequency',
            'class_note'           => 'Class note',
            'department'           => 'Department',
            'usage_rate_qty'       => 'Usage rate qty',
            'on_hand'              => 'On hand',
            'rof_months'           => 'ROF months',
            'rof_months_w_request' => 'ROF months w/ request',
            'remarks'              => 'Remarks',
            'is_hold'              => 'On hold',
            'hold_remarks'         => 'Hold remarks',
        ];
    }
}
