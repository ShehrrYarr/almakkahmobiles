<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileSaleReturn extends Model
{
    protected $fillable = ['mobile_sale_id', 'user_id', 'reason'];

    public function sale()
    {
        return $this->belongsTo(MobileSale::class, 'mobile_sale_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(MobileSaleReturnItem::class);
    }
}
