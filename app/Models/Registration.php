<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory;

    public const STATUSES = [
        'baru'      => ['label' => 'Baru',      'color' => '#2563EB'],
        'dihubungi' => ['label' => 'Dihubungi', 'color' => '#F59E0B'],
        'diterima'  => ['label' => 'Diterima',  'color' => '#10B981'],
        'ditolak'   => ['label' => 'Ditolak',   'color' => '#64748B'],
    ];

    protected $fillable = [
        'name', 'study_program', 'whatsapp', 'motivation', 'status',
    ];

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status]['label'] ?? 'Baru';
    }

    public function statusColor(): string
    {
        return self::STATUSES[$this->status]['color'] ?? '#2563EB';
    }

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
}
