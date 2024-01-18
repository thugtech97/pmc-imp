<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryRequestItems extends Model
{
    use HasFactory;

    protected $table = 'inventory_requests_items';

    protected $fillable = [
        'stock_code',
        'item_description',
        'brand',
        'OEM_ID',
        'UoM',
        'usage_rate_qty',
        'usage_frequency',
        'purpose',
        'min_qty',
        'max_qty',
        'imf_no',
        'product_id'
    ];
}
