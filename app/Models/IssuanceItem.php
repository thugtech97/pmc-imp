<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IssuanceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        "issuance_id",
        "quantity",
        "release_date",
        "received_by",
        "issued_by"
    ];
}
