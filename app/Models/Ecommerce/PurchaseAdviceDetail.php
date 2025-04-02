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
        'product_id',
        'purchase_advice_id',
        'par_to',
        'qty_to_order',
        'previous_po',
        'current_po',
        'po_date_released',
        'qty_ordered',
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