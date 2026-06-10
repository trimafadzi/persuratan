<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'username', 'nama_lengkap', 'jabatan', 'email',
        'password', 'foto_profil', 'unit_kerja_id', 'is_active', 'last_login',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login'        => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // ── Relasi ──────────────────────────────────────────

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id')
                    ->withTimestamps();
    }

    public function notifikasi(): HasMany
    {
        return $this->hasMany(Notifikasi::class, 'user_id');
    }

    public function notifikasiTidakDibaca(): HasMany
    {
        return $this->notifikasi()->where('is_read', false);
    }

    // ── RBAC Helpers ─────────────────────────────────────

    public function hasRole(string $slug): bool
    {
        return $this->roles()->where('slug', $slug)->exists();
    }

    public function hasAnyRole(array $slugs): bool
    {
        return $this->roles()->whereIn('slug', $slugs)->exists();
    }

    public function hasPermission(string $permission): bool
    {
        foreach ($this->roles as $role) {
            $perms = $role->permissions ?? [];
            if (in_array('*', $perms) || in_array($permission, $perms)) {
                return true;
            }
        }
        return false;
    }

    // ── Accessor ─────────────────────────────────────────

    public function getDisplayNameAttribute(): string
    {
        return $this->nama_lengkap ?? $this->name;
    }

    public function getInitialsAttribute(): string
    {
        $name = $this->nama_lengkap ?? $this->name;
        $words = explode(' ', $name);
        return strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
    }
}
