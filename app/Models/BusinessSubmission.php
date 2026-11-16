<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessSubmission extends Model
{
    use HasFactory;

    public const STATUSES = [
        'menunggu'  => ['label' => 'Menunggu Review',  'color' => '#F59E0B'],
        'disetujui' => ['label' => 'Disetujui & Live', 'color' => '#10B981'],
        'ditolak'   => ['label' => 'Ditolak',          'color' => '#EF4444'],
    ];

    protected $fillable = [
        'brand', 'category', 'description', 'founder', 'study_program',
        'year', 'nim', 'whatsapp', 'instagram', 'status', 'business_id', 'photos',
    ];

    protected $casts = [
        'photos' => 'array',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status]['label'] ?? 'Menunggu Review';
    }

    public function statusColor(): string
    {
        return self::STATUSES[$this->status]['color'] ?? '#F59E0B';
    }
}
