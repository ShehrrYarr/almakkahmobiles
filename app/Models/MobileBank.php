<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileBank extends Model
{
    protected $fillable = ['shop_id', 'name', 'account_no', 'branch', 'iban', 'swift', 'is_active'];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function payments()
    {
        return $this->hasMany(MobileSalePayment::class);
    }
}
