<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductMedia extends Model
{
    protected $fillable = [
        'product_id',
        'file',
        'thumb_file',
        'checksum',
        'color_hex',
        'order',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->file);
    }

    public function getThumbUrlAttribute(): string
    {
        return $this->thumb_file
            ? Storage::url($this->thumb_file)
            : Storage::url($this->file);
    }
}
