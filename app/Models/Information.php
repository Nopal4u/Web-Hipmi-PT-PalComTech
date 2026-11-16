<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Information extends Model
{
    use HasFactory;

    protected $table = 'information';

    public const CATEGORIES = [
        'Open Recruitment' => '#2563EB',
        'Agenda & Event'   => '#8B5CF6',
        'Pengumuman'       => '#0EA5E9',
    ];

    public const STATUSES = [
        'buka'   => ['label' => 'Sedang Dibuka',   'color' => '#10B981'],
        'segera' => ['label' => 'Segera Berakhir', 'color' => '#F59E0B'],
        'tutup'  => ['label' => 'Selesai/Tutup',   'color' => '#64748B'],
    ];

    protected $fillable = [
        'title', 'category', 'date_label', 'status', 'summary',
        'description', 'action_label', 'action_url', 'banner',
        'is_pinned', 'is_hidden',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_hidden' => 'boolean',
    ];

    public function categoryColor(): string
    {
        return self::CATEGORIES[$this->category] ?? '#0EA5E9';
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status]['label'] ?? 'Sedang Dibuka';
    }

    public function statusColor(): string
    {
        return self::STATUSES[$this->status]['color'] ?? '#10B981';
    }

    /** Open recruitment yang sudah ditutup tidak boleh menampilkan tautan daftar. */
    public function isLocked(): bool
    {
        return $this->status === 'tutup' && $this->category === 'Open Recruitment';
    }

    public function scopeVisible($query)
    {
        return $query->where('is_hidden', false);
    }
}
