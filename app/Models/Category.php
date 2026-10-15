<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'parent_id'];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /** IDs de esta categoría y de sus subcategorías (para filtrar el catálogo). */
    public function idsWithChildren(): array
    {
        return $this->children()->pluck('id')->push($this->id)->all();
    }

    /** "Computación › Teclados" */
    public function getFullNameAttribute(): string
    {
        return $this->parent ? $this->parent->name . ' › ' . $this->name : $this->name;
    }
}
