<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'parent_id', 'name', 'slug', 'description', 'is_active', 'image'
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function scopeRoot($q)
    {
        return $q->whereNull('parent_id');
    }

    public function scopeSearch($q, $search)
    {
        return $q->where(function ($qq) use ($search) {
            $qq->where('name', 'like', "%{$search}%")
               ->orWhere('description', 'like', "%{$search}%");
        });
    }

    public function scopeOrdered($q, $by = 'name', $dir = 'asc')
    {
        return $q->orderBy($by, $dir);
    }

    public function calculateDepth(): int
    {
        $depth = 0;
        $parent = $this->parent;

        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }

        return $depth;
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}
