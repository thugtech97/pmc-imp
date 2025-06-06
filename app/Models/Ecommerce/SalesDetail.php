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
}
