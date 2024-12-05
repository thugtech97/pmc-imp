<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RFPHeader extends Model
{
    use HasFactory;
    protected $connection = 'pmcorem';
    protected $table = 'rfpheaders';
}
