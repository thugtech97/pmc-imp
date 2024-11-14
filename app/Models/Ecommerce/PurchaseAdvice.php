<?php

namespace App\Models\Ecommerce;
use App\Models\Ecommerce\SalesDetail;
use App\Models\Ecommerce\SalesHeader;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PurchaseAdvice extends Model
{

    protected $table = 'purchase_advice';
    protected $fillable = [ 'pa_number', 'mrs_id', 'planner_remarks', 'status', 'created_by', 'verified_by', 'verified_at', 'approved_by', 'approved_at', 'received_by', 'received_at'];

    public function mrs()
    {
        return $this->belongsTo(SalesHeader::class, 'mrs_id', 'id');
    }

    public function items(){
        return $this->hasMany(SalesDetail::class,'is_pa');
    }

    public function planner(){
        return $this->belongsTo(User::class, 'created_by');
    }

    public function verifier(){
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function approver(){
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function purchaser(){
        return $this->belongsTo(User::class, 'received_by');
    }

    // In PurchaseAdvice.php
    public function mrs_numbers()
    {
        return SalesDetail::where('is_pa', $this->id)
                        ->with('header')
                        ->get()
                        ->pluck('header.order_number')
                        ->unique();
    }

}
