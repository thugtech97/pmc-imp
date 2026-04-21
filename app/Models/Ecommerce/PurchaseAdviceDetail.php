<?php

namespace App\Models\Ecommerce;

use App\Models\Ecommerce\Product;
use Illuminate\Database\Eloquent\Model;
use App\Models\Ecommerce\PurchaseAdvice;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseAdviceDetail extends Model
{
    use HasFactory;

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
        'rof_months',           // add this
        'rof_months_w_request', // add this
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
}