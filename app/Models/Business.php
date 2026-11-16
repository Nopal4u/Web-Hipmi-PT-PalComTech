<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'F&B'               => ['label' => 'Makanan & Minuman (F&B)', 'color' => '#10B981', 'icon' => '☕', 'desc' => 'Cafe, Snack, Catering'],
        'Fashion & Apparel' => ['label' => 'Fashion & Apparel',       'color' => '#F59E0B', 'icon' => '👕', 'desc' => 'Clothing, Hijab, Sepatu, Thrift'],
        'Digital & Jasa IT' => ['label' => 'Digital & Jasa IT',       'color' => '#8B5CF6', 'icon' => '💻', 'desc' => 'Software, Web Dev, Kursus, Servis'],
        'Kreatif & Desain'  => ['label' => 'Kreatif & Desain',        'color' => '#EC4899', 'icon' => '🎨', 'desc' => 'Desain Grafis, Foto, Buket, Souvenir'],
    ];

    protected $fillable = [
        'name', 'category', 'color', 'founder', 'description',
        'instagram', 'whatsapp', 'logo', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function photos(): HasMany
    {
        return $this->hasMany(BusinessPhoto::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderByDesc('id');
    }

    public function initial(): string
    {
        return mb_strtoupper(mb_substr(trim($this->name) ?: '?', 0, 1));
    }

    public function gradient(): string
    {
        return "linear-gradient(135deg,{$this->color},color-mix(in srgb,{$this->color} 50%,#1E40AF))";
    }

    /** Nomor WhatsApp -> format 62xxxxxxxxxx untuk wa.me. */
    public function waNumber(): string
    {
        $digits = preg_replace('/\D/', '', (string) $this->whatsapp);

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }
        if (str_starts_with($digits, '8')) {
            return '62'.$digits;
        }

        return $digits;
    }

    public function waLink(): string
    {
        $text = 'Halo '.$this->name.', saya tertarik dengan produk/jasa Anda.';

        return 'https://wa.me/'.$this->waNumber().'?text='.urlencode($text);
    }
}
