<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileSalePayment extends Model
{
    protected $fillable = ['mobile_sale_id', 'method', 'mobile_bank_id', 'amount', 'reference_no', 'notes', 'processed_by', 'paid_at'];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function sale()
    {
        return $this->belongsTo(MobileSale::class, 'mobile_sale_id');
    }

    public function bank()
    {
        return $this->belongsTo(MobileBank::class, 'mobile_bank_id');
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
