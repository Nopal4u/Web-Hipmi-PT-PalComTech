<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Member extends Model
{
    use HasFactory;

    /**
     * Opsi tambahan modular pada form struktur (kunci modul → kolom data miliknya).
     * Dipakai agar admin bisa menambah/menghapus blok opsional per pengurus.
     */
    public const MODULES = [
        'bisnis' => ['business'],
        'cv'     => ['cv_url'],
        'porto'  => ['portfolio_url'],
        'sosmed' => ['linkedin', 'instagram'],
    ];

    protected $fillable = [
        'division_id', 'group', 'name', 'position', 'profile_type',
        'photo', 'expertise', 'study_program', 'year', 'faculty', 'nidn',
        'business', 'cv_url', 'portfolio_url', 'linkedin', 'instagram',
        'mods', 'sort_order',
    ];

    protected $casts = [
        'mods' => 'array',
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function scopeOrdered($query)
    {
        // Urutan tampil struktur: Dosen Pembimbing → Pengurus Inti (BPH) → Divisi 1..6.
        $group = $query->getQuery()->getGrammar()->wrap('group');

        return $query
            ->orderByRaw("CASE {$group} WHEN 'dosen' THEN 0 WHEN 'bph' THEN 1 ELSE 2 END")
            ->orderBy('division_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function isDosen(): bool
    {
        return $this->profile_type === 'dosen';
    }

    /**
     * Opsi tambahan yang aktif: ditambah admin ATAU sudah terisi datanya.
     *
     * @return array<int, string>
     */
    public function activeModules(): array
    {
        $active = collect((array) $this->mods);

        foreach (self::MODULES as $module => $fields) {
            foreach ($fields as $field) {
                if (filled($this->{$field})) {
                    $active->push($module);

                    break;
                }
            }
        }

        return $active->unique()->filter(fn ($module) => isset(self::MODULES[$module]))->values()->all();
    }

    /** Inisial untuk avatar fallback. */
    public function initial(): string
    {
        return mb_strtoupper(mb_substr(trim($this->name) ?: '?', 0, 1));
    }

    /** Warna aksen kartu berdasarkan grup/divisi. */
    public function accent(): string
    {
        if ($this->group === 'dosen') {
            return '#F59E0B';
        }
        if ($this->group === 'bph') {
            return '#2563EB';
        }

        return $this->division?->color ?? '#2563EB';
    }

    /** Label badge di kartu struktur. */
    public function badgeLabel(): string
    {
        if ($this->group === 'dosen') {
            return 'Dewan Pembina';
        }
        if ($this->group === 'bph') {
            return 'BPH Inti';
        }

        return $this->division?->short_name ?? 'Pengurus';
    }

    /** Angkatan singkat: 2024 -> '24. */
    public function shortYear(): string
    {
        $year = (string) $this->year;

        return preg_match('/^\d{4}$/', $year) ? "'".substr($year, 2) : $year;
    }
}
