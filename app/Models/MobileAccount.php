<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileAccount extends Model
{
    protected $fillable = ['mobile_vendor_id', 'mobile_unit_id', 'purchase_batch', 'mobile_sale_id', 'debit', 'credit', 'description', 'created_by'];

    public function vendor()
    {
        return $this->belongsTo(MobileVendor::class, 'mobile_vendor_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function unit()
    {
        return $this->belongsTo(MobileUnit::class, 'mobile_unit_id');
    }

    public function sale()
    {
        return $this->belongsTo(MobileSale::class, 'mobile_sale_id');
    }
}
