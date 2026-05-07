<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'featured_image',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_category');
    }

    /** Rótulo para filtros e vitrine (catalog.category_display_labels). */
    public function filterDisplayName(): string
    {
        $labels = config('catalog.category_display_labels', []);

        return $labels[$this->slug] ?? $this->name;
    }
}
