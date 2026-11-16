<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLES = [
        'super_admin' => ['label' => 'Super Admin', 'color' => '#2563EB'],
        'admin_media' => ['label' => 'Admin Media', 'color' => '#7C3AED'],
        'admin_umkm'  => ['label' => 'Admin UMKM',  'color' => '#059669'],
    ];

    /**
     * Hak akses modul panel admin.
     * Modul yang tidak disebutkan hanya boleh diakses Super Admin.
     */
    public const ACCESS = [
        'overview'      => ['super_admin', 'admin_media', 'admin_umkm'],
        'businesses'    => ['super_admin', 'admin_umkm'],
        'submissions'   => ['super_admin', 'admin_umkm'],
        'gallery'       => ['super_admin', 'admin_media'],
        'programs'      => ['super_admin', 'admin_media'],
        'info'          => ['super_admin', 'admin_media'],
        'registrations' => ['super_admin'],
        'team'          => ['super_admin'],
        'settings'      => ['super_admin'],
        'accounts'      => ['super_admin'],
        'audit'         => ['super_admin', 'admin_media', 'admin_umkm'],
        'password'      => ['super_admin', 'admin_media', 'admin_umkm'],
    ];

    protected $fillable = [
        'name', 'username', 'email', 'division', 'role',
        'is_active', 'must_change_password', 'last_login_at', 'password',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at'    => 'datetime',
            'password'             => 'hashed',
            'is_active'            => 'boolean',
            'must_change_password' => 'boolean',
            'last_login_at'        => 'datetime',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function canAccess(string $module): bool
    {
        return in_array($this->role, self::ACCESS[$module] ?? ['super_admin'], true);
    }

    public function roleLabel(): string
    {
        return self::ROLES[$this->role]['label'] ?? strtoupper(str_replace('_', ' ', (string) $this->role));
    }

    public function roleColor(): string
    {
        return self::ROLES[$this->role]['color'] ?? '#2563EB';
    }

    public function initial(): string
    {
        return mb_strtoupper(mb_substr(trim((string) $this->name) ?: 'A', 0, 1));
    }
}
