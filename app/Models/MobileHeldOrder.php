<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileHeldOrder extends Model
{
    protected $fillable = [
        'user_id', 'mobile_vendor_id', 'customer_name', 'customer_mobile', 'comment', 'cart_items', 'held_at',
    ];

    protected $casts = [
        'cart_items' => 'array',
        'held_at'    => 'datetime',
    ];

    public function vendor()
    {
        return $this->belongsTo(MobileVendor::class, 'mobile_vendor_id');
    }
}
