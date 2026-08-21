<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    protected $fillable = ['name', 'slug', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function mobileUnits()
    {
        return $this->hasMany(MobileUnit::class);
    }

    public function mobileSales()
    {
        return $this->hasMany(MobileSale::class);
    }

    public function mobileHeldOrders()
    {
        return $this->hasMany(MobileHeldOrder::class);
    }

    public function mobileBanks()
    {
        return $this->hasMany(MobileBank::class);
    }
}
