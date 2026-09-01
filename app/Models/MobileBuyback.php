<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileBuyback extends Model
{
    protected $fillable = [
        'shop_id', 'mobile_unit_id', 'mobile_sale_id', 'user_id',
        'seller_name', 'seller_cnic', 'seller_phone', 'seller_address', 'seller_description',
        'battery', 'battery_cycle', 'has_box',
        'buyback_price', 'new_selling_price',
        'payment_method', 'mobile_bank_id', 'buyback_date',
    ];

    protected $casts = [
        'has_box' => 'boolean',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function unit()
    {
        return $this->belongsTo(MobileUnit::class, 'mobile_unit_id');
    }

    public function sale()
    {
        return $this->belongsTo(MobileSale::class, 'mobile_sale_id');
    }

    public function bank()
    {
        return $this->belongsTo(MobileBank::class, 'mobile_bank_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
