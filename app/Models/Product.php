<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = [
        'sku',
        'title',
        'slug',
        'status',
        'curation_status',
        'short_description',
        'technical_description',
        'featured_image',
        'og_image',
        'available_colors',
        'materials',
        'supplier_code',
        'source_supplier',
        'quality_score',
        'quality_notes',
        'processed_at',
        'enriched_at',
        'is_featured',
        'is_launch',
        'is_premium',
    ];

    protected function casts(): array
    {
        return [
            'available_colors' => 'array',
            'materials' => 'array',
            'quality_notes' => 'array',
            'processed_at' => 'datetime',
            'enriched_at' => 'datetime',
            'is_featured' => 'boolean',
            'is_launch' => 'boolean',
            'is_premium' => 'boolean',
        ];
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_category');
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class)->orderBy('order');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function getFeaturedImageUrlAttribute(): ?string
    {
        return $this->featured_image ? Storage::url($this->featured_image) : null;
    }

    public function getOgImageUrlAttribute(): ?string
    {
        return $this->og_image ? Storage::url($this->og_image) : null;
    }
}
