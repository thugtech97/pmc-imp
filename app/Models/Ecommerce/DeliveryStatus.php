<?php

namespace App\Models\Ecommerce;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Models\Ecommerce\SalesHeader;

class DeliveryStatus extends Model
{

    protected $table = 'ecommerce_delivery_status';
    protected $fillable = ['order_id', 'user_id', 'status', 'remarks'];


    public function getDeliveryAddressAttribute()
    {
        return "{$this->address_delivery_street} {$this->address_delivery_brgy}, {$this->address_delivery_city} {$this->address_delivery_province} {$this->address_delivery_zip}";
    }

    public function sales()
	{
	    return $this->belongsTo(SalesHeader::class,'order_id');
	}

    /**
     * Who recorded the status change — set by both the manual update and the automatic
     * warehouse marking.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
