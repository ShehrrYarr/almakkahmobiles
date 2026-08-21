<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileUnit extends Model
{
    protected $fillable = [
        'shop_id', 'name', 'imei', 'imei2', 'storage', 'pta_status',
        'battery', 'battery_cycle', 'has_box', 'purchase_price', 'selling_price',
        'purchase_date', 'description',
        'seller_name', 'seller_cnic', 'seller_phone', 'seller_address', 'seller_description',
        'status', 'user_id',
    ];

    protected $casts = [
        'has_box' => 'boolean',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->hasMany(MobileImage::class);
    }
}
