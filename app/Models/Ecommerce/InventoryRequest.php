<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryRequest extends Model
{
    use HasFactory;

    protected $table = 'inventory_requests';

    protected $fillable = [
        'stock_code',
        'department',
        'section',
        'division',
        'item_description',
        'brand',
        'OEM_ID',
        'UoM',
        'usage_rate_qty',
        'usage_frequency',
        'purpose',
        'min_qty',
        'max_qty',
        'status',
        'attachments',
        'submitted_at',
        'approved_at',
        'type'
    ];
}
