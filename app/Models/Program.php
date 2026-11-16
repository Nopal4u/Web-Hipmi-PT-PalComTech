<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Program extends Model
{
    use HasFactory;

    public const STATUSES = [
        'selesai'   => ['label' => 'Selesai',        'color' => '#10B981'],
        'berjalan'  => ['label' => 'Sedang Berjalan', 'color' => '#2563EB'],
        'mendatang' => ['label' => 'Mendatang',       'color' => '#F59E0B'],
    ];

    protected $fillable = [
        'division_id', 'title', 'description', 'schedule',
        'audience', 'status', 'sort_order',
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status]['label'] ?? 'Mendatang';
    }

    public function statusColor(): string
    {
        return self::STATUSES[$this->status]['color'] ?? '#F59E0B';
    }

    public function nextStatus(): string
    {
        $order = array_keys(self::STATUSES);
        $index = array_search($this->status, $order, true);

        return $order[($index === false ? 0 : $index + 1) % count($order)];
    }

    public function nextStatusLabel(): string
    {
        return self::STATUSES[$this->nextStatus()]['label'] ?? 'Mendatang';
    }
}
