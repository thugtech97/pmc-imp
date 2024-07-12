<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostCode extends Model
{
    use HasFactory;
    protected $connection = 'pmcorem';
    protected $table = 'costcodes';
}
