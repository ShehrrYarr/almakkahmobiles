<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeldOrder extends Model
{
    protected $fillable = [
        'user_id', 'vendor_id', 'customer_name', 'customer_mobile', 'comment', 'cart_items', 'held_at',
    ];

    protected $casts = [
        'cart_items' => 'array',
        'held_at'    => 'datetime',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
