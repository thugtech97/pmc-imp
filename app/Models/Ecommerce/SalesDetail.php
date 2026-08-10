<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\User;
use App\Models\Issuance;
use App\Models\Ecommerce\{
    Product, SalesHeader, ProductCategory
};

class SalesDetail extends Model
{
    use SoftDeletes;
    use \App\Models\Concerns\RecordsDocumentHistory;

    /** Item row of an MRS — changes are grouped onto the parent's audit trail. */
    protected $historyDocumentType = 'MRS';
    protected $historyIsItem = true;

    protected $table = 'ecommerce_sales_details';
    protected $fillable = ['sales_header_id', 'product_id', 'product_name', 'product_category', 'price', 'tax_amount', 'promo_id', 'promo_description', 
    'discount_amount', 'gross_amount', 'net_amount', 'qty', 'uom', 'cost_code', 'created_by', 'qty_to_order', 'par_to', 'frequency', 'date_needed', 'purpose',
    'previous_mrs', 'open_po', 'po_no', 'qty_ordered', 'po_date_released', 'is_pa', 'qty_delivered'];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function header()
    {
        return $this->belongsTo(SalesHeader::class, 'sales_header_id');
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class,'product_category');
    }

    public function getItemTotalPriceAttribute()
    {
        return $this->product->discountedprice;
    }

    public function issuances()
    {
        return $this->hasMany(Issuance::class, 'ecommerce_sales_details_id');
    }

    /**
     * The MRS this line belongs to — the audit trail hangs off the header.
     */
    public function historyParent()
    {
        return $this->header;
    }

    /**
     * How this line is named in the trail, e.g. "M-1042 Bearing 6204".
     */
    public function historyItemLabel()
    {
        $code = optional($this->product)->code;
        $name = $this->product_name ?: optional($this->product)->name;

        return trim(($code ? $code . ' ' : '') . ($name ?: 'Item #' . $this->id));
    }

    /**
     * @return array
     */
    public function historyTracked()
    {
        return [
            'qty'               => 'Requested qty',
            'qty_to_order'      => 'Qty to order',
            'qty_ordered'       => 'Qty ordered',
            'qty_delivered'     => 'Qty delivered',
            'uom'               => 'UoM',
            'cost_code'         => 'Cost code',
            'par_to'            => 'PAR to',
            'frequency'         => 'Frequency',
            'date_needed'       => 'Date needed',
            'purpose'           => 'Purpose',
            'po_no'             => 'PO number',
            'po_date_released'  => 'PO date released',
            'open_po'           => 'Open PO',
            'previous_mrs'      => 'Previous MRS',
            'promo_id'          => 'Hold flag',
            'promo_description' => 'Hold remarks',
            'is_pa'             => 'Linked PA',
        ];
    }
}
