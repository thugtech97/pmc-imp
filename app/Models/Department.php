<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Ecommerce\SalesHeader;

class Department extends Model
{
    use HasFactory;

    public function orders()
    {
        return $this->hasManyThrough(SalesHeader::class, User::class, 'department_id', 'user_id', 'id', 'id');
    }
}
