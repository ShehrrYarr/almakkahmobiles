<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notebook extends Model
{
    protected $fillable = ['name', 'data', 'updated_by'];

    protected $casts = [
        'data' => 'array',
    ];

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
