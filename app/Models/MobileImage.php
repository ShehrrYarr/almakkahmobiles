<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileImage extends Model
{
    protected $fillable = ['mobile_unit_id', 'path'];

    public function unit()
    {
        return $this->belongsTo(MobileUnit::class, 'mobile_unit_id');
    }
}
