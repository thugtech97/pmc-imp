<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryRequest extends Model
{
    use HasFactory;

    protected $table = 'inventory_requests';

    protected $fillable = [
        'department',
        'section',
        'division',
        'status',
        'attachments',
        'submitted_at',
        'approved_at',
        'type',
        'approved_by',
        'user_id',
    ];

    public function items()
    {
        return $this->hasMany(InventoryRequestItems::class, 'imf_no', 'id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
