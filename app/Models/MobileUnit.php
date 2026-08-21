<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileUnit extends Model
{
    protected $fillable = [
        'mobile_id', 'mobile_vendor_id', 'imei', 'storage', 'pta_status',
        'battery', 'battery_cycle', 'purchase_price', 'selling_price',
        'purchase_date', 'purchase_batch', 'description', 'status', 'user_id',
    ];

    public function mobile()
    {
        return $this->belongsTo(Mobile::class);
    }

    public function vendor()
    {
        return $this->belongsTo(MobileVendor::class, 'mobile_vendor_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->hasMany(MobileImage::class);
    }

    public function accounts()
    {
        return $this->hasMany(MobileAccount::class, 'mobile_unit_id');
    }
}
