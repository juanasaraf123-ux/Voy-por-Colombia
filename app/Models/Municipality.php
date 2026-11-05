<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Municipality extends Model
{
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['lat' => 'float', 'lng' => 'float'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
