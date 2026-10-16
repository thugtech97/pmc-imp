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
        'product_id',
        'stock_code_override',
        'stock_code_override_note',
        'stock_code_override_by',
        'stock_code_override_at',
    ];

    protected $casts = [
        'stock_code_override'    => 'boolean',
        'stock_code_override_at' => 'datetime',
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

    /**
     * An item's values as they stand today, for an Update IMF: the product
     * master plus the latest IMF line for the code (the product table has no
     * usage rate, min/max or purpose). The request form is pre-filled from
     * this and the same values are saved as the item's "before" snapshot, so
     * a field the requestor leaves alone never shows as changed.
     *
     * @param  string  $code
     * @return array|null  ['product' => Product, 'values' => [...]], null when no such product
     */
    public static function currentValuesFor($code)
    {
        $code = trim((string) $code);
        $product = $code === '' ? null : \App\Models\Ecommerce\Product::where('code', $code)->first();

        if (!$product) {
            return null;
        }

        // Prefer the line of the last fully approved IMF; any line otherwise.
        $line = self::where('stock_code', $code)
            ->whereHas('request', function ($q) {
                $q->whereIn('status', \App\Constants\Status::imfFinalApproved());
            })
            ->orderByDesc('id')
            ->first()
            ?: self::where('stock_code', $code)->orderByDesc('id')->first();

        return [
            'product' => $product,
            'line'    => $line,
            'values'  => [
                'stock_code'       => $product->code,
                'item_description' => $product->name,
                'brand'            => $line && $line->brand !== null ? $line->brand : $product->brand,
                'OEM_ID'           => $line && $line->OEM_ID !== null ? $line->OEM_ID : $product->oem,
                'UoM'              => $product->uom,
                'usage_rate_qty'   => $line ? $line->usage_rate_qty : null,
                'usage_frequency'  => $line ? $line->usage_frequency : null,
                'min_qty'          => $line ? $line->min_qty : null,
                'max_qty'          => $line ? $line->max_qty : null,
                'purpose'          => $line ? $line->purpose : null,
            ],
        ];
    }

    /**
     * The open Update IMF that already has this stock code, if any — one item
     * can only be on one Update IMF at a time. Finished IMFs (approved,
     * rejected, cancelled) don't count.
     *
     * @param  string  $code
     * @param  int|null  $exceptImf  the IMF being edited
     * @return int|null  that IMF's id
     */
    public static function openUpdateImfFor($code, $exceptImf = null)
    {
        $closed = array_merge(
            \App\Constants\Status::imfFinalApproved(),
            \App\Constants\Status::imfFinalRejected(),
            [
                \App\Constants\Status::REJECTED_PLANNER,
                \App\Constants\Status::REJECTED_MCD_VERIFIER,
                \App\Constants\Status::CANCELLED,
            ]
        );

        $line = self::where('stock_code', trim((string) $code))
            ->whereHas('request', function ($q) use ($closed, $exceptImf) {
                $q->where('type', 'update')->whereNotIn('status', $closed);
                if ($exceptImf) {
                    $q->where('id', '<>', $exceptImf);
                }
            })
            ->first();

        return $line ? (int) $line->imf_no : null;
    }
}
