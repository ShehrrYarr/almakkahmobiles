<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileSale extends Model
{
    protected $fillable = [
        'shop_id', 'client_ref', 'customer_name', 'customer_mobile',
        'total_amount', 'discount_amount', 'pay_amount', 'user_id', 'comment', 'sale_date',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(MobileSaleItem::class);
    }

    public function payments()
    {
        return $this->hasMany(MobileSalePayment::class);
    }

    public function returns()
    {
        return $this->hasMany(MobileSaleReturn::class);
    }
}
