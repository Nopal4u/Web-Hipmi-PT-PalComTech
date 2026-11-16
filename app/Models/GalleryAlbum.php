<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GalleryAlbum extends Model
{
    use HasFactory;

    public const CATEGORIES = ['Internal', 'Eksternal', 'Workshop', 'Expo', 'Raker'];

    protected $fillable = [
        'title', 'date_label', 'category', 'color',
        'description', 'is_pinned', 'sort_order',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
    ];

    public function photos(): HasMany
    {
        return $this->hasMany(GalleryPhoto::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderByDesc('id');
    }

    public function cover(): ?string
    {
        return $this->photos->first()?->path;
    }

    /** Gradasi latar saat album belum punya foto. */
    public function gradient(): string
    {
        return "linear-gradient(135deg,{$this->color},color-mix(in srgb,{$this->color} 45%,#0F172A))";
    }
}
