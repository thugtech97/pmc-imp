<?php

namespace App\Models\Ecommerce;
use App\Models\Ecommerce\SalesHeader;
use Illuminate\Database\Eloquent\Model;

class PurchaseAdvice extends Model
{

    protected $table = 'purchase_advice';
    protected $fillable = [ 'pa_number', 'mrs_id'];

    public function mrs()
    {
        return $this->belongsTo(SalesHeader::class, 'mrs_id', 'id');
    }

}
