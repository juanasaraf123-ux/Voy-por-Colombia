<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public $timestamps = false;
    protected $guarded = [];

    public function entries()
    {
        return $this->belongsToMany(Entry::class, 'entry_categories');
    }

    public function scopeForSection($q, string $section)
    {
        return $q->where('section', $section)->orderBy('sort_order')->orderBy('name');
    }

    public function toPublic(): array
    {
        return ['id' => $this->id, 'slug' => $this->slug, 'name' => $this->name, 'icon' => $this->icon, 'color' => $this->color];
    }
}
