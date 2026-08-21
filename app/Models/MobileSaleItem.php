<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileSaleItem extends Model
{
    protected $fillable = ['mobile_sale_id', 'mobile_unit_id', 'price', 'discount', 'user_id'];

    public function sale()
    {
        return $this->belongsTo(MobileSale::class, 'mobile_sale_id');
    }

    public function unit()
    {
        return $this->belongsTo(MobileUnit::class, 'mobile_unit_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function returnItems()
    {
        return $this->hasMany(MobileSaleReturnItem::class, 'mobile_sale_item_id');
    }
}
