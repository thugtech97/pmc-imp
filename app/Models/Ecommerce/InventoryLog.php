<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Ecommerce\Product;

class InventoryLog extends Model
{
    use HasFactory;

    protected $fillable = [
        "product_id",
        "old_value",
        "new_value",
        "file"
    ];

    public function product() 
    {
        return $this->belongsTo(Product::class);
    }
}
