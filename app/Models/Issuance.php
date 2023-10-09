<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Ecommerce\SalesDetail;
use App\Models\User;
use App\Models\IssuanceItem;

class Issuance extends Model
{
    use HasFactory;

    protected $fillable = [
        "ecommerce_sales_details_id",
        "user_id",
        "qty",
        "received_by",
        "issued_by", "issuance_no"
    ];

    public function orderDetails()
    {
        return $this->belongsTo(SalesDetail::class, 'ecommerce_sales_details_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(IssuanceItem::class);
    }
}
