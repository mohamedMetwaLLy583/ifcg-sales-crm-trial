<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'placement',
        'eyebrow',
        'title',
        'subtitle',
        'button_text',
        'button_url',
        'secondary_button_text',
        'secondary_button_url',
        'image_path',
        'sort_order',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function scopePublished($query)
    {
        return $query
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    public function getStatusLabelAttribute(): string
    {
        if (!$this->is_active) {
            return 'معطل';
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return 'مجدول';
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return 'منتهي';
        }

        return 'منشور';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status_label) {
            'منشور' => 'success',
            'مجدول' => 'info',
            'منتهي' => 'warning',
            'معطل' => 'secondary',
            default => 'secondary',
        };
    }
}
