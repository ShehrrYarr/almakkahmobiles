<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileSaleReturnItem extends Model
{
    protected $fillable = ['mobile_sale_return_id', 'mobile_sale_item_id', 'refund_amount'];

    public function saleReturn()
    {
        return $this->belongsTo(MobileSaleReturn::class, 'mobile_sale_return_id');
    }

    public function saleItem()
    {
        return $this->belongsTo(MobileSaleItem::class, 'mobile_sale_item_id');
    }
}
