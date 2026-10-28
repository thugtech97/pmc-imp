<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'account_name',
        'account_no',
        'branch',
        'qrcode',
        'is_default',
        'is_active',
        'user_id'
    ];
    public $timestamp = true;
}
