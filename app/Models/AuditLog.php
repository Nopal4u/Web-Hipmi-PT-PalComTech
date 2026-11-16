<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'actor', 'action', 'module', 'description'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $action, string $module, string $description, ?User $user = null): void
    {
        $user ??= auth()->user();

        static::create([
            'user_id'     => $user?->id,
            'actor'       => $user?->username ?? $user?->name ?? 'system',
            'action'      => $action,
            'module'      => $module,
            'description' => $description,
        ]);
    }
}
