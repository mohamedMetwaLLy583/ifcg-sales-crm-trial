<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ContentPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'title',
        'meta_title',
        'meta_description',
        'body',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function setSlugAttribute($value): void
    {
        $slug = trim((string) $value);
        $this->attributes['slug'] = $slug !== '' ? Str::slug($slug) : Str::slug((string) ($this->title ?? 'page'));
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->is_active ? 'منشورة' : 'مخفية';
    }

    public function getStatusBadgeAttribute(): string
    {
        return $this->is_active ? 'success' : 'secondary';
    }
}
