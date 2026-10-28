<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryRequestsOldItem extends Model
{
    use HasFactory;

    protected $table = 'inventory_requests_old_items';

    protected $fillable = [
        'imf_no',
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
        'updated_at',
    ];
}
