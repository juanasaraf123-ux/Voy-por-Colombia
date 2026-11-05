<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    public $timestamps = false;
    protected $guarded = [];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function municipalities()
    {
        return $this->hasMany(Municipality::class)->orderBy('name');
    }

    public function cover()
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }
}
