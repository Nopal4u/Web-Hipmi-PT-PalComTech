<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    use HasFactory;

    protected $fillable = [
        'numeral', 'name', 'short_name', 'color', 'icon',
        'focus', 'learning', 'leader_name', 'sort_order',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function getLeaderAttribute(): ?Member
    {
        return $this->members()
            ->where('group', 'division')
            ->orderBy('sort_order')
            ->first();
    }
}
