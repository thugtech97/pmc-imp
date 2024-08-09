<?php

namespace App\Models\Ecommerce;
use Illuminate\Database\Eloquent\Model;

class PurchaseAdvice extends Model
{

    protected $table = 'purchase_advice';
    protected $fillable = [ 'pa_number', 'mrs_id'];

}
