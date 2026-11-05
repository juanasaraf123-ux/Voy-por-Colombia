<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Colecciones temáticas de la galería (Paisajes, Sabores…). */
class Collection extends Model
{
    public $timestamps = false;
    protected $guarded = [];
    protected $table = 'collections';
}
