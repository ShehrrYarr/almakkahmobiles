<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileVendor extends Model
{
    protected $fillable = ['name', 'office_address', 'city', 'CNIC', 'mobile_no', 'picture', 'created_by'];

    public function accounts()
    {
        return $this->hasMany(MobileAccount::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function units()
    {
        return $this->hasMany(MobileUnit::class);
    }
}
