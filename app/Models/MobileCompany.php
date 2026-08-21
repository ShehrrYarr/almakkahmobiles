<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileCompany extends Model
{
    protected $fillable = ['name'];

    public function mobiles()
    {
        return $this->hasMany(Mobile::class);
    }
}
