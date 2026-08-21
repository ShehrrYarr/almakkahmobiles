<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mobile extends Model
{
    protected $fillable = ['name', 'description', 'min_qty', 'mobile_company_id', 'mobile_group_id', 'user_id'];

    public function company()
    {
        return $this->belongsTo(MobileCompany::class, 'mobile_company_id');
    }

    public function group()
    {
        return $this->belongsTo(MobileGroup::class, 'mobile_group_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function units()
    {
        return $this->hasMany(MobileUnit::class);
    }
}
