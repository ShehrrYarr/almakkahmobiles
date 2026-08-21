<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileHeldOrder extends Model
{
    protected $fillable = [
        'shop_id', 'user_id', 'customer_name', 'customer_mobile', 'comment', 'cart_items', 'held_at',
    ];

    protected $casts = [
        'cart_items' => 'array',
        'held_at'    => 'datetime',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
