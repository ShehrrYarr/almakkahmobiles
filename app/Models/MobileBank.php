<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileBank extends Model
{
    protected $fillable = ['name', 'account_no', 'branch', 'iban', 'swift', 'is_active'];

    public function payments()
    {
        return $this->hasMany(MobileSalePayment::class);
    }
}
